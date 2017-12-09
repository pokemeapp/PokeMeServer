<?php

namespace App\Http\Controllers;

use App\DeviceToken;
use App\Poke;
use App\PokePrototype;
use App\User;
use Bnb\PushNotifications\Device;
use Bnb\PushNotifications\Notification;
use Illuminate\Database\Eloquent\Collection;
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
     *   path="/api/pokes",
     *   summary="Get all poke for the current user.",
     *   operationId="getPokes",
     *   tags={"pokes"},
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
    public function getPokes(Request $request)
    {
        $id = $request->user()->id;
        $pokes = Poke::where("owner_id", $id)->orWhere("target_id", $id)->orderBy("created_at")->get();
        return $pokes;
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
        $prototype->validateOwnership($request->user()->id);
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
        $poke->save();

        //TODO: Reafctor into EVENT
        $notification = new Notification($prototype->name, $prototype->message);
        $notification->metadata('prototype_id', $prototypeId);
        $notification->metadata('target_id', $request->get('target_id'));

        /** @var DeviceToken $token */
        foreach ($targetDeviceTokens as $token) {
            $notification->push(Device::apns($token->token));
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
