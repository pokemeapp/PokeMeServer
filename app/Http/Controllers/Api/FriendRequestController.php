<?php

namespace App\Http\Controllers;

use App\FriendRequest;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;


/**
 * Class FriendRequestController
 * @package App\Http\Controllers
 */
class FriendRequestController extends ApiController
{
    public function post(Request $request)
    {
        $currentUserId = $request->user()->id;
        $userIds = User::select('id')->where('id' ,'>' ,0)->get('id')->pluck('id')->toArray();
        $userIds = array_diff($userIds, array($currentUserId));

        /** @var $validator \Illuminate\Validation\Validator */
        $validator = Validator::make($request->all(), [
            'user_id' => [
                'required',
                Rule::in($userIds)
            ]
        ]);

        if ($validator->fails()) {
            return \response()->json($validator->errors(), Response::HTTP_BAD_REQUEST);
        }

        if ($this->isFriendRequestAlreadyExists($request->user(),$request->all())) {
            return \response()->json(
                "The friend request already exists for the target user.",
                Response::HTTP_BAD_REQUEST
            );
        }

        if ($this->isFriendRequestAlreadyExistsFromOtherSide($request->user(),$request->all())) {
            return \response()->json(
                "The user already requested a request to you. Check your friend requests!",
                Response::HTTP_BAD_REQUEST
            );
        }

        $this->createFriendRequest($request->user(),$request->all());
        return \response()->json("The request sent to the user!",Response::HTTP_CREATED);
    }

    private function isFriendRequestAlreadyExists(User $user, array $data)
    {
        return FriendRequest::where([
            'owner_id' => $user->id,
            'target_id' => $data['user_id']
        ])->get()->isNotEmpty();
    }

    private function isFriendRequestAlreadyExistsFromOtherSide(User $user, array $data)
    {
        return FriendRequest::where([
            'owner_id' => $data['user_id'],
            'target_id' => $user->id
        ])->get()->isNotEmpty();
    }

    private function createFriendRequest(User $user, array $data)
    {
        $friendRequest = new FriendRequest();
        $friendRequest->owner_id = $user->id;
        $friendRequest->target_id = $data['user_id'];
        $friendRequest->status = false;

        $friendRequest->save();
        //TODO: Send friend request email for target user
        //TODO: Send push notification for target user
    }
}
