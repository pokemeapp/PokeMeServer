<?php

namespace App\Http\Controllers;

use App\DeviceToken;
use App\Friend;
use App\FriendRequest;
use App\Mail\FriendRequestSent;
use App\User;
use Bnb\PushNotifications\Device;
use Bnb\PushNotifications\Notification;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;


/**
 * Class FriendRequestController
 * @package App\Http\Controllers
 */
class FriendRequestController extends ApiController
{
    /**
     * @SWG\Post(
     *   path="/api/users/{userId}/send_request",
     *   summary="Send a friend request",
     *   operationId="post",
     *   tags={"friend_request"},
     *   @SWG\Parameter(name="userId", in="path", type="string"),
     *   @SWG\Response(response=200, description="The request sent to the user!"),
     *   @SWG\Response(response=400, description="Validation unsuccessful.", examples={
     *     "application/json": {
     *       "user_id": {
     *          "The user_id field is required."
     *       },
     *
     *       "user_id": {
     *          "The friend request already exists for the target user."
     *       },
     *
     *       "user_id": {
     *          "The user already requested a request to you. Check your friend requests!"
     *       },
     *     }
     *   })
     * )
     */
    public function post(Request $request, $userId)
    {
        $currentUserId = $request->user()->id;

        $user = User::findOrFail($userId);

        if ($this->isFriendRequestAlreadyExists($currentUserId, $userId)) {
            return \response()->json(
                "The friend request already exists for the target user.",
                Response::HTTP_BAD_REQUEST
            );
        }

        if ($this->isFriendRequestAlreadyExistsFromOtherSide($currentUserId, $userId)) {
            return \response()->json(
                "The user already requested a request to you. Check your friend requests!",
                Response::HTTP_BAD_REQUEST
            );
        }

        $this->createFriendRequest($request->user(), $userId);
        return \response()->json("The request sent to the user!",Response::HTTP_OK);
    }

    private function isFriendRequestAlreadyExists($currentUserId, $targetId)
    {
        return FriendRequest::where([
            'owner_id' => $currentUserId,
            'target_id' => $targetId
        ])->get()->isNotEmpty();
    }

    private function isFriendRequestAlreadyExistsFromOtherSide($currentUserId, $targetId)
    {
        return FriendRequest::where([
            'owner_id' => $targetId,
            'target_id' => $currentUserId
        ])->get()->isNotEmpty();
    }

    private function createFriendRequest(User $user, $targetId)
    {
        $friendRequest = new FriendRequest();
        $friendRequest->owner_id = $user->id;
        $friendRequest->target_id = $targetId;
        $friendRequest->status = false;
        $friendRequest->save();


        //TODO: Reafctor into EVENT
        /** @var User $target */
        $target = User::findOrFail($targetId);
        $target = User::findOrFail($targetId);
        $targetDeviceTokens = $target->device_tokens()->get();

        Mail::to($target->email)->send(new FriendRequestSent());

        $notification = new Notification("New Friend Request", "You have a new Friend Request from " . $user->fullName());

        /** @var DeviceToken $token */
        foreach ($targetDeviceTokens as $token) {
            $device = Device::apns($token->token);
            $device->metadata('friend_request_id', $friendRequest->id);
            $device->metadata('user_id', $user->id);
            $device->metadata('notification_type', "notification");
            $notification->push($device);
        }
        $results = $notification->send();
    }
}
