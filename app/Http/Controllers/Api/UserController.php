<?php

namespace App\Http\Controllers;

use App\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;


/**
 * Class UserController
 * @package App\Http\Controllers
 */
class UserController extends ApiController
{
    /**
     * @SWG\Get(
     *   path="/api/users/search",
     *   summary="Search for users by given query",
     *   operationId="searchUser",
     *   tags={"friend_request"},
     *   @SWG\Parameter(name="query", in="query", type="string"),
     *   @SWG\Response(response=200, description="successful operation", examples={
     *     "application/json": {
     *      {
     *      "id": 1,
     *      "firstname": "Lajos",
     *      "lastname": "Kovcs",
     *      },
     *      {
     *      "id": 2,
     *      "firstname": "Kelemen",
     *      "lastname": "Tenkes",
     *      }
     *     }
     *   }),
     *   @SWG\Response(response=401, description="Unauthenticated", examples={
     *     "application/json": {
     *       "message"="Unauthenticated.",
     *     }
     *   }),
     *   @SWG\Response(response=405, description="Validation error", examples={
     *     "application/json": {
     *       "message"="Query required.",
     *     }
     *   })
     * )
     */
    public function searchUser(Request $request)
    {
        /** @var $validator \Illuminate\Validation\Validator */
        $validator = Validator::make($request->all(), [
            'query' => [
                'required'
            ]
        ]);

        if ($validator->fails()) {
            return \response()->json($validator->errors(), Response::HTTP_METHOD_NOT_ALLOWED);
        }

        $data = $request->all();

        $users = User::search($data['query'])->get(['id', 'firstname', 'lastname']);

        /** @var Collection $users */
        return $users;
    }

    /**
     * @SWG\Get(
     *   path="/api/users/{userId}",
     *   summary="Get user object by user id",
     *   operationId="getUser",
     *   tags={"friend_request"},
     *   @SWG\Parameter(name="userId", in="path", type="string"),
     *   @SWG\Response(response=200, description="successful operation", examples={
     *     "application/json": {
     *      {
     *      "id": 1,
     *      "firstname": "Lajos",
     *      "lastname": "Kovcs",
     *      "email": "example@example.com",
     *      "created_at": "2017-10-17 21:02:54",
     *      "updated_at": "2017-10-18 19:30:43"
     *      }
     *     }
     *   }),
     *   @SWG\Response(response=401, description="Unauthenticated", examples={
     *     "application/json": {
     *       "message"="Unauthenticated.",
     *     }
     *   }),
     *   @SWG\Response(response=404, description="Not Found")
     * )
     */
    public function getUser(Request $request, $userId)
    {
        $user = User::findOrFail($userId);
        return $user;
    }
}
