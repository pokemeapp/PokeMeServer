<?php

namespace App\Http\Controllers;

use App\DeviceToken;
use App\Friend;
use App\Habit;
use Bnb\PushNotifications\Device;
use Bnb\PushNotifications\Notification;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;


/**
 * Class HabitController
 * @package App\Http\Controllers
 */
class HabitController extends ApiController
{

    /**
     * @SWG\Get(
     *   path="/api/habits",
     *   summary="Get all habits for the current user.",
     *   operationId="getHabits",
     *   tags={"habits"},
     *   @SWG\Response(response=200, description="successful operation", examples={
     *     "application/json": {
     *        {
     *          "id": 1,
     *          "type": "warning",
     *          "name": "Smoke",
     *          "description": "Don't smoke! It's unhealthy.",
     *          "day": "0101010",
     *          "hour": "04:23:10",
     *          "rejected": 0,
     *          "owner_id": 1,
     *          "created_at": "2017-12-12 00:00:00",
     *          "updated_at": "2017-12-12 00:00:00"
     *        },
     *        {
     *          "id": 2,
     *          "type": "message",
     *          "name": "Example",
     *          "description": "Example desc",
     *          "day": "0100000",
     *          "hour": "01:15:10",
     *          "rejected": 2,
     *          "owner_id": 1,
     *          "created_at": "2017-11-10 00:00:00",
     *          "updated_at": "2017-11-10 00:00:00"
     *        }
     *     }
     *   })
     * )
     */
    public function getHabits(Request $request)
    {
        $id = $request->user()->id;

        $habits = Habit::where("owner_id", $id)->get();
        return $habits;
    }

    /**
     * @SWG\Post(
     *   path="/api/habits",
     *   summary="Create new habit.",
     *   operationId="postHabit",
     *   tags={"habits"},
     *   @SWG\Parameter(name="type", in="body", @SWG\Schema(type="string")),
     *   @SWG\Parameter(name="name", in="body", @SWG\Schema(type="string")),
     *   @SWG\Parameter(name="description", in="body", @SWG\Schema(type="string")),
     *   @SWG\Parameter(name="day", in="body", @SWG\Schema(type="string")),
     *   @SWG\Parameter(name="hour", in="body", @SWG\Schema(type="string")),
     *   @SWG\Response(response=200, description="successful operation", examples={
     *     "application/json": {
     *        {
     *          "id": 1,
     *          "type": "warning",
     *          "name": "Smoke",
     *          "description": "Don't smoke! It's unhealthy.",
     *          "day": "0101010",
     *          "hour": "04:23:10",
     *          "rejected": 0,
     *          "owner_id": 1,
     *          "created_at": "2017-12-12 00:00:00",
     *          "updated_at": "2017-12-12 00:00:00"
     *        }
     *     }
     *   })
     * )
     */
    public function postHabit(Request $request)
    {
        /** @var $validator \Illuminate\Validation\Validator */
        $validator = Validator::make($request->all(), [
            'type' => [
                'required',
                'string'
            ],
            'name' => [
                'required',
                'string'
            ],
            'description' => [
                'required',
                'string'
            ],
            'day' => [
                'required',
                'string',
                'min:7',
                'max:7',
                'regex:/^[0-1]*$/',
            ],
            'hour' => [
                'required'
            ]
        ]);

        if ($validator->fails()) {
            return \response()->json($validator->errors(), Response::HTTP_METHOD_NOT_ALLOWED);
        }

        $id = $request->user()->id;
        $habit = new Habit();
        $habit->owner_id = $id;
        $habit->type = $request->get("type");
        $habit->name = $request->get("name");
        $habit->description = $request->get("description");
        $habit->day = $request->get("day");
        $habit->hour = $request->get("hour");
        $habit->rejected = 0;
        $habit->save();

        return $habit;
    }

    /**
     * @SWG\Post(
     *   path="/api/habits/{habitId}/reject",
     *   summary="Reject a habit for 10 minutes.",
     *   operationId="rejectHabit",
     *   tags={"habits"},
     *   @SWG\Parameter(name="habitId", in="path", type="number"),
     *   @SWG\Response(response=200, description="successful operation")
     * )
     */
    public function rejectHabit(Request $request, $habitId)
    {
        try {
            /** @var Habit $habit */
            $habit = Habit::findOrFail($habitId);
        } catch (ModelNotFoundException $exception) {
            return \response()->json($exception->getMessage(), Response::HTTP_METHOD_NOT_ALLOWED);
        }

        if ($habit->owner_id != $request->user()->id) {
            return \response()->json("The habit you requested is not belongs to you!", Response::HTTP_METHOD_NOT_ALLOWED);
        }

        if ($habit->reject()) {
            $user = $request->user();
            $friends = Friend::where(["user_id" => $user->id])->get();

            $notification = new Notification("Habit snooze warning", "Hi! Your firend snoozed a habit 3 times! Check him out and poke! ");

            foreach ($friends as $friend) {
                $targetDeviceTokens = DeviceToken::where("user_id", $friend->friend_id)->get();
                /** @var DeviceToken $token */
                foreach ($targetDeviceTokens as $token) {
                    $device = Device::apns($token->token);
                    $device->metadata("notification_type", "habbitsnooze");
                    $device->metadata("friend_id", $user->id);
                    $notification->push($device);
                }
            }
            $results = $notification->send();
        }
        return $habit;
    }

    /**
     * @SWG\Post(
     *   path="/api/habits/{habitId}/done",
     *   summary="Done a habit.",
     *   operationId="doneHabit",
     *   tags={"habits"},
     *   @SWG\Parameter(name="habitId", in="path", type="number"),
     *   @SWG\Response(response=200, description="successful operation")
     * )
     */
    public function doneHabit(Request $request, $habitId)
    {
        try {
            /** @var Habit $habit */
            $habit = Habit::findOrFail($habitId);
        } catch (ModelNotFoundException $exception) {
            return \response()->json($exception->getMessage(), Response::HTTP_METHOD_NOT_ALLOWED);
        }

        if ($habit->owner_id != $request->user()->id) {
            return \response()->json("The habit you requested is not belongs to you!", Response::HTTP_METHOD_NOT_ALLOWED);
        }

        $habit->done();

        return $habit;
    }


}
