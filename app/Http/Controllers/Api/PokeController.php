<?php

namespace App\Http\Controllers;

use App\DeviceToken;
use App\Friend;
use App\Poke;
use App\PokePrototype;
use App\User;
use Bnb\PushNotifications\Device;
use Bnb\PushNotifications\Notification;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;


/**
 * Class PokeController
 * @package App\Http\Controllers
 */
class PokeController extends ApiController
{

    /**
     * @SWG\Get(
     *   path="/api/pokes/{friendId}",
     *   summary="Get all poke for the current user for a friend.",
     *   operationId="getPokes",
     *   tags={"pokes"},
     *   @SWG\Parameter(name="friendId", in="path", type="number"),
     *   @SWG\Response(response=200, description="successful operation", examples={
     *     "application/json": {
     *        {
     *          "id": 1,
     *          "prototype_id": 2,
     *          "owner_id": 1,
     *          "target_id": 2,
     *          "response": "",
     *          "created_at": "2017-12-09 00:00:00",
     *          "updated_at": null
     *       }
     *     }
     *   })
     * )
     */
    public function getPokes(Request $request, $friendId)
    {
        $id = $request->user()->id;

        /** @var Collection $friendship */
        $friendship = Friend::where([
            "user_id" => $id,
            "friend_id" => $friendId
        ])->get();
        if ($friendship->isEmpty()) {
            return \response()->json("The user is not your friend!", Response::HTTP_METHOD_NOT_ALLOWED);
        }

        $pokes = Poke::where(
            [
                "owner_id" => $id,
                "target_id" => $friendId,
            ]
        )
        ->orWhere(
            [
                "owner_id" => $friendId,
                "target_id" => $id,
            ]
        )->orderBy("created_at")->get();
        return $pokes;
    }

    /**
     * @SWG\Post(
     *   path="/api/pokes/{pokeId}/response",
     *   summary="Send a response for a poke.",
     *   operationId="postPokeResponse",
     *   tags={"pokes"},
     *   @SWG\Parameter(name="pokeId", in="path", type="number"),
     *   @SWG\Parameter(name="response", in="body", @SWG\Schema(type="string")),
     *   @SWG\Response(response=200, description="successful operation")
     * )
     */
    public function postPokeResponse(Request $request, $pokeId)
    {
        /** @var $validator \Illuminate\Validation\Validator */
        $validator = Validator::make($request->all(), [
            'response' => [
                'required'
            ]
        ]);

        if ($validator->fails()) {
            return \response()->json($validator->errors(), Response::HTTP_METHOD_NOT_ALLOWED);
        }

        try {
            $poke = Poke::findOrFail($pokeId);
        } catch (ModelNotFoundException $exception) {
            return \response()->json($exception->getMessage(), Response::HTTP_NOT_FOUND);
        }

        if ($request->user()->id != $poke->target_id) {
            return \response()->json("You are not the target of that poke.", Response::HTTP_METHOD_NOT_ALLOWED);
        }

        $poke->response = $request->get("response");
        $poke->save();

        /** @var User $user */
        $user = $request->user();
        $targetUser = $poke->owner_id;
        $targetDeviceTokens = DeviceToken::where("user_id", $targetUser)->get();
        $notification = new Notification("New response for your poke", $user->fullName() . "responded for your poke with: " . $request->get('response'));

        /** @var DeviceToken $token */
        foreach ($targetDeviceTokens as $token) {
            $device = Device::apns($token->token);
            $device->metadata('notification_type', "poke");
            $device->metadata('friend_id', $targetUser);
            $notification->push($device);
        }
        $results = $notification->send();

        return $poke;
    }

    /**
     * @SWG\Post(
     *   path="/api/pokes/{pokeId}/response/yes",
     *   summary="Send a yes response for a poke.",
     *   operationId="postYesPokeResponse",
     *   tags={"pokes"},
     *   @SWG\Parameter(name="pokeId", in="path", type="number"),
     *   @SWG\Response(response=200, description="successful operation")
     * )
     */
    public function postYesPokeResponse(Request $request, $pokeId)
    {
        try {
            $poke = Poke::findOrFail($pokeId);
        } catch (ModelNotFoundException $exception) {
            return \response()->json($exception->getMessage(), Response::HTTP_NOT_FOUND);
        }

        if ($request->user()->id != $poke->target_id) {
            return \response()->json("You are not the target of that poke.", Response::HTTP_METHOD_NOT_ALLOWED);
        }

        $poke->response = 'Yes';
        $poke->save();

        return $poke;
    }

    /**
     * @SWG\Post(
     *   path="/api/pokes/{pokeId}/response/no",
     *   summary="Send a no response for a poke.",
     *   operationId="postNoPokeResponse",
     *   tags={"pokes"},
     *   @SWG\Parameter(name="pokeId", in="path", type="number"),
     *   @SWG\Response(response=200, description="successful operation")
     * )
     */
    public function postNoPokeResponse(Request $request, $pokeId)
    {
        try {
            $poke = Poke::findOrFail($pokeId);
        } catch (ModelNotFoundException $exception) {
            return \response()->json($exception->getMessage(), Response::HTTP_NOT_FOUND);
        }

        if ($request->user()->id != $poke->target_id) {
            return \response()->json("You are not the target of that poke.", Response::HTTP_METHOD_NOT_ALLOWED);
        }

        $poke->response = 'No';
        $poke->save();

        return $poke;
    }

    /**
     * @SWG\Get(
     *   path="/api/pokes/prototypes",
     *   summary="Get all poke prototype for the current user.",
     *   operationId="getPrototypes",
     *   tags={"pokes"},
     *   @SWG\Response(response=200, description="successful operation", examples={
     *     "application/json": {
     *      {
     *      "id": 1,
     *      "name": "Ebéd",
     *      "message": "Jössz ebédelni?",
     *      "owner_id": 1,
     *      "created_at": "2017-10-17 21:02:54",
     *      "updated_at": "2017-10-18 19:30:43"
     *      },
     *      {
     *      "id": 2,
     *      "name": "Cigi",
     *      "message": "Cigi szünet?",
     *      "owner_id": 1,
     *      "created_at": "2017-10-17 21:02:54",
     *      "updated_at": "2017-10-18 19:30:43"
     *      }
     *     }
     *   })
     * )
     */
    public function getPrototypes(Request $request)
    {
        $prototypes = PokePrototype::where('owner_id', $request->user()->id)->get();
        return $prototypes;
    }

    /**
     * @SWG\Post(
     *   path="/api/pokes/prototypes",
     *   summary="Create new poke prototype.",
     *   operationId="postPrototype",
     *   tags={"pokes"},
     *   @SWG\Parameter(name="name", in="body", @SWG\Schema(type="string")),
     *   @SWG\Parameter(name="message", in="body", @SWG\Schema(type="string")),
     *   @SWG\Response(response=200, description="successful operation", examples={
     *     "application/json": {
     *      "id": 4,
     *      "name": "Ebéd",
     *      "message": "Jössz ebédelni?",
     *      "owner_id": 1,
     *      "created_at": "2017-10-17 21:02:54",
     *      "updated_at": "2017-10-18 19:30:43"
     *     }
     *   })
     * )
     */
    public function postPrototype(Request $request)
    {
        $validator = $this->validateRequest($request->all());
        if ($validator->fails()) {
            return \response()->json($validator->errors(), Response::HTTP_METHOD_NOT_ALLOWED);
        }
        $prototype = new PokePrototype();
        $prototype->name = $request->get('name');
        $prototype->message = $request->get('message');
        $prototype->owner_id = $request->user()->id;
        $prototype->save();
        return $prototype;
    }

    /**
     * @SWG\Get(
     *   path="/api/pokes/prototypes/{prototypeId}",
     *   summary="Get a poke prototype by id.",
     *   operationId="getPrototype",
     *   tags={"pokes"},
     *   @SWG\Parameter(name="prototypeId", in="path", type="number"),
     *   @SWG\Response(response=200, description="successful operation", examples={
     *     "application/json": {
     *      "id": 1,
     *      "name": "Ebéd",
     *      "message": "Jössz ebédelni?",
     *      "owner_id": 1,
     *      "created_at": "2017-10-17 21:02:54",
     *      "updated_at": "2017-10-18 19:30:43"
     *     }
     *   })
     * )
     */
    public function getPrototype(Request $request, $id)
    {
        /** @var PokePrototype $prototype */
        $prototype = PokePrototype::findOrFail($id);
        return $prototype;
        
    }

    /**
     * @SWG\Put(
     *   path="/api/pokes/prototypes/{prototypeId}",
     *   summary="Update a poke prototype.",
     *   operationId="putPrototype",
     *   tags={"pokes"},
     *   @SWG\Parameter(name="prototypeId", in="path", type="number"),
     *   @SWG\Parameter(name="name", in="body", @SWG\Schema(type="string")),
     *   @SWG\Parameter(name="message", in="body", @SWG\Schema(type="string")),
     *   @SWG\Response(response=200, description="successful operation", examples={
     *     "application/json": {
     *      "id": 4,
     *      "name": "Ebéd",
     *      "message": "Jössz ebédelni?",
     *      "owner_id": 1,
     *      "created_at": "2017-10-17 21:02:54",
     *      "updated_at": "2017-10-18 19:30:43"
     *     }
     *   })
     * )
     */
    public function putPrototype(Request $request, $id)
    {
        $validator = $this->validateRequest($request->all());
        if ($validator->fails()) {
            return \response()->json($validator->errors(), Response::HTTP_METHOD_NOT_ALLOWED);
        }
        /** @var PokePrototype $prototype */
        $prototype = PokePrototype::findOrFail($id);
        $prototype->validateOwnership($request->user()->id);
        $prototype->name = $request->get('name');
        $prototype->message = $request->get('message');
        $prototype->save();
        return $prototype;
    }
    /**
     * @SWG\Delete(
     *   path="/api/pokes/prototypes/{prototypeId}",
     *   summary="Deletes a poke prototype",
     *   operationId="deletePrototype",
     *   tags={"pokes"},
     *   @SWG\Parameter(name="prototypeId", in="path", type="number"),
     *   @SWG\Response(response=204, description="successful deletion")
     * )
     */
    public function deletePrototype(Request $request, $id)
    {
        /** @var PokePrototype $prototype */
        $prototype = PokePrototype::findOrFail($id);
        $prototype->validateOwnership($request->user()->id);
        $prototype->delete();
        return \response()->json('', Response::HTTP_NO_CONTENT);
    }

    /**
     * @SWG\Post(
     *   path="/api/pokes/prototypes/{prototypeId}/send",
     *   summary="Send a poke.",
     *   operationId="postPokes",
     *   tags={"pokes"},
     *   @SWG\Parameter(name="prototypeId", in="path", type="string"),
     *   @SWG\Parameter(name="target_id", in="body", @SWG\Schema(type="integer")),
     *   @SWG\Response(response=200, description="successful operation")
     * )
     */
    public function postPokes(Request $request, $prototypeId)
    {
        /** @var $validator \Illuminate\Validation\Validator */
        $validator = Validator::make($request->all(), [
            'target_id' => [
                'required'
            ]
        ]);

        if ($validator->fails()) {
            return \response()->json($validator->errors(), Response::HTTP_METHOD_NOT_ALLOWED);
        }
        /** @var PokePrototype $prototype */
        $prototype = PokePrototype::findOrFail($prototypeId);
        /** @var User $target */
        $target = User::findOrFail($request->get('target_id'));
        /** @var Collection $targetDeviceTokens */
        $targetDeviceTokens = $target->device_tokens()->get();

        if ($targetDeviceTokens->isEmpty()) {
            return \response()->json("The target user has no device token.", Response::HTTP_I_AM_A_TEAPOT);
        }

        $poke = new Poke();
        $poke->prototype_id = $prototypeId;
        $poke->owner_id = $request->user()->id;
        $poke->target_id = $request->get('target_id');
        $poke->response = "";
        $poke->save();

        $notification = new Notification($prototype->name, $prototype->message);


        /** @var DeviceToken $token */
        foreach ($targetDeviceTokens as $token) {
            $device = Device::apns($token->token);
            $device->metadata('prototype_id', $prototypeId);
            $device->metadata('notification_type', "poke");
            $device->metadata('friend_id', $request->user()->id);
            $notification->push($device);
        }
        $results = $notification->send();

        return \response()->json("OK", Response::HTTP_OK);
    }

    protected function validateRequest($data)
    {
        /** @var $validator \Illuminate\Validation\Validator */
        $validator = Validator::make($data, [
            'name' => [
                'required'
            ],
            'message' => [
                'required'
            ]
        ]);

        return $validator;
    }
}
