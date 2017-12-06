<?php

namespace App\Http\Controllers;

use App\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;


/**
 * Class UserController
 * @package App\Http\Controllers
 */
class UserController extends ApiController
{
    /**
     * @SWG\Get(
     *   path="/api/usersearch",
     *   summary="Search for users by given query",
     *   operationId="searchUser",
     *   @SWG\Response(response=200, description="successful operation", examples={
     *     "application/json": {
     *      {
     *      "id": 1,
     *      "firstname": "Lajos",
     *      "lastname": "Kovcs",
     *      "email": "lajos.kovacs@innonic.com",
     *      "created_at": "2017-10-17 21:02:54",
     *      "updated_at": "2017-10-18 19:30:43"
     *      },
     *      {
     *      "id": 2,
     *      "firstname": "Kelemen",
     *      "lastname": "Tenkes",
     *      "email": "tenkes.kelemen@asd.com",
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

        /** TODO: Refactor this! */

        $data = $request->all();

        $users = User::search($data['query'])->get();

        /** @var Collection $users */
        return $users;
    }
}
