<?php

namespace App\Http\Controllers;


use App\Friend;
use App\FriendRequest;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AuthenticatedUserController extends ApiController
{
    /**
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
     *   path="/api/user/friend_request/{id}/accept",
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
     *   path="/api/user/friend_request/{id}/decline",
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
