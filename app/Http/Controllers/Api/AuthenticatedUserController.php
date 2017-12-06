<?php

namespace App\Http\Controllers;


use App\DeviceToken;
use App\Friend;
use App\FriendRequest;
use App\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class AuthenticatedUserController extends ApiController
{
    /**
     * @param Request $request
     * @return mixed
     *
     * @SWG\Get(
     *   path="/api/user",
     *   summary="Return the currently authenticated user",
     *   operationId="getCurrentUser",
     *   @SWG\Response(response=200, description="successful operation", examples={
     *     "application/json": {
     *      "id": 1,
     *      "firstname": "Lajos",
     *      "lastname": "Kovcs",
     *      "email": "lajos.kovacs@innonic.com",
     *      "created_at": "2017-10-17 21:02:54",
     *      "updated_at": "2017-10-18 19:30:43"
     *      }
     *   }),
     *   @SWG\Response(response=401, description="Unauthenticated", examples={
     *     "application/json": {
     *       "message"="Unauthenticated.",
     *     }
     *   })
     * )
     */
    public function getCurrentUser(Request $request)
    {
        return $request->user();
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @SWG\Put(
     *   path="/api/user",
     *   summary="Update the current user",
     *   operationId="update",
     *   @SWG\Parameter(name="firstname", in="body", @SWG\Schema(type="string")),
     *   @SWG\Parameter(name="lastname", in="body", @SWG\Schema(type="string")),
     *   @SWG\Parameter(name="email", in="body", @SWG\Schema(type="string")),
     *   @SWG\Response(response=200, description="Successfully updated!", examples={
     *    "application/json": {
     *      {
     *      "id": 1,
     *      "firstname": "Lajos",
     *      "lastname": "Kovcs",
     *      "email": "lajos.kovacs@innonic.com",
     *      "created_at": "2017-10-17 21:02:54",
     *      "updated_at": "2017-10-18 19:30:43"
     *      }
     *    }
     *   }),
     *   @SWG\Response(response=405, description="Validation unsuccessful.", examples={
     *     "application/json": {
     *       "firstname": {
     *          "The firstname field is required."
     *       }
     *     }
     *   })
     * )
     */
    public function update(Request $request)
    {
        // validate
        // read more on validation at http://laravel.com/docs/validation
        $rules = array(
            'firstname'     => 'required',
            'lastname'      => 'required',
            'email'         => 'required|email',
        );
        $validator = Validator::make($request->all(), $rules);

        // process the login
        if ($validator->fails()) {
            return \response()->json($validator->errors(), Response::HTTP_METHOD_NOT_ALLOWED);
        }

        $user = User::find($request->user()->id);
        $user->firstname    = $request->get('firstname');
        $user->lastname     = $request->get('lastname');
        $user->email        = $request->get('email');
        $user->save();

        return $user;
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @SWG\Post(
     *   path="/api/user/add_device_token",
     *   summary="Adding a new device token to the user",
     *   operationId="addDeviceToken",
     *   @SWG\Parameter(name="token", in="body", @SWG\Schema(type="string")),
     *   @SWG\Response(response=200, description="Successfully added token.")
     * )
     */
    public function addDeviceToken(Request $request)
    {
        $rules = array(
            'token'     => 'required'
        );
        /** @var \Illuminate\Validation\Validator $validator */
        $validator = Validator::make($request->all(), $rules);

        // process the login
        if ($validator->fails()) {
            return \response()->json($validator->errors(), Response::HTTP_METHOD_NOT_ALLOWED);
        }

        /** @var Collection $tokens */
        $tokens = DeviceToken::where('user_id', $request->user()->id)
            ->andWhere('token', $request->get('token'))
            ->get();
        if ($tokens->isEmpty()) {
            $new_token = new DeviceToken();
            $new_token->token = $request->get('token');
            $new_token->user_id = $request->user()->id;
            $new_token->save();
        }

        return response()->json('Successfully added token.', Response::HTTP_OK);
    }

    /**
     * @SWG\Get(
     *   path="/api/user/friend_requests",
     *   summary="List friend requests of the authenticated user",
     *   operationId="getCurrentUserFriendRequests",
     *   @SWG\Response(response=200, description="successful operation", examples={
     *     "application/json": {
     *      {
     *          "id": 3,
     *          "owner_id": 2,
     *          "target_id": 1,
     *          "status": 0,
     *          "created_at": "2017-10-29 14:02:19",
     *          "updated_at": "2017-10-29 14:02:21",
     *          "owner": {
     *              "id": 2,
     *              "firstname": "Kelemen",
     *              "lastname": "Kabatan",
     *              "email": "kelemen.kabatban@innonic.com",
     *              "created_at": "2017-10-24 17:05:23",
     *              "updated_at": "2017-10-24 17:05:25"
     *          }
     *      }
     *     }
     *   }),
     *   @SWG\Response(response=401, description="Unauthenticated", examples={
     *     "application/json": {
     *       "message"="Unauthenticated.",
     *     }
     *   })
     * )
     */
    public function getCurrentUserFriendRequests(Request $request)
    {
        return $request->user()->friendRequests()->with('owner')->get();
    }

    /**
     * @SWG\Get(
     *   path="/api/user/friends",
     *   summary="List friends of the authenticated user",
     *   operationId="getCurrentUserFriends",
     *   @SWG\Response(response=200, description="successful operation", examples={
     *     "application/json": {
     *      {
     *          "id": 3,
     *          "user_id": 1,
     *          "friend_id": 2,
     *          "created_at": "2017-10-29 14:02:19",
     *          "updated_at": "2017-10-29 14:02:21",
     *          "owner": {
     *              "id": 2,
     *              "firstname": "Kelemen",
     *              "lastname": "Kabatan",
     *              "email": "kelemen.kabatban@innonic.com",
     *              "created_at": "2017-10-24 17:05:23",
     *              "updated_at": "2017-10-24 17:05:25"
     *          }
     *      }
     *     }
     *   }),
     *   @SWG\Response(response=401, description="Unauthenticated", examples={
     *     "application/json": {
     *       "message"="Unauthenticated.",
     *     }
     *   })
     * )
     */
    public function getCurrentUserFriends(Request $request)
    {
        return $request->user()->friends()->with('friend')->get();
    }

    /**
     * @SWG\Post(
     *   path="/api/user/friend_requests/{id}/accept",
     *   summary="Accept a friend request",
     *   operationId="acceptFriendRequest",
     *   @SWG\Parameter(name="id", in="path", type="number"),
     *   @SWG\Response(response=200, description="Successfully accepted request."),
     *   @SWG\Response(response=400, description="Not Found", examples={
     *     "application/json": {
     *       "message"="There is no request with the given id: 1",
     *     }
     *   })
     * )
     */
    public function acceptFriendRequest(Request $request, int $friendRequestId)
    {
        $friendRequest = $this->findFriendRequest($friendRequestId);
        $this->createFriendFromFriendRequest($friendRequest);
        $this->deleteFriendRequest($friendRequest);
        return response()->json('Successfully accepted request.', \Illuminate\Http\Response::HTTP_OK);
    }

    /**
     * @SWG\Post(
     *   path="/api/user/friend_requests/{id}/decline",
     *   summary="Decline a friend request",
     *   operationId="acceptFriendRequest",
     *   @SWG\Parameter(name="id", in="path", type="number"),
     *   @SWG\Response(response=200, description="Successfully accepted request."),
     *   @SWG\Response(response=400, description="Not Found", examples={
     *     "application/json": {
     *       "message"="There is no request with the given id: 1",
     *     }
     *   })
     * )
     */
    public function declineFriendRequest(Request $request, int $friendRequestId)
    {
        $friendRequest = $this->findFriendRequest($friendRequestId);
        $this->deleteFriendRequest($friendRequest);

        return response()->json('Successfully declined request.', \Illuminate\Http\Response::HTTP_OK);
    }

    protected function findFriendRequest($friendRequestId)
    {
        try {
            return FriendRequest::findOrFail($friendRequestId);
        } catch (ModelNotFoundException $exception) {
            return response()->json('There is no request with the given id: ' . $friendRequestId, Response::HTTP_NOT_FOUND);
        }
    }

    protected function deleteFriendRequest($friendRequest)
    {
        $friendRequest->delete();
    }

    protected function createFriendFromFriendRequest($friendRequest)
    {
        $owner_id = $friendRequest->owner_id;
        $target_id = $friendRequest->target_id;

        $relationOne = new Friend();
        $relationOne->user_id = $owner_id;
        $relationOne->friend_id = $target_id;
        $relationOne->save();

        $relationTwo = new Friend();
        $relationTwo->user_id = $target_id;
        $relationTwo->friend_id = $owner_id;
        $relationTwo->save();
    }
}
