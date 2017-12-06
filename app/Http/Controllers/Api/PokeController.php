<?php

namespace App\Http\Controllers;

use App\Poke;
use App\PokePrototype;
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
     *   path="/api/poke/prototypes",
     *   summary="Get all poke prototype for the current user.",
     *   operationId="getPrototypes",
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
        $prototypes = PokePrototype::where('owner_id', $request->user()->id);
        return $prototypes;
    }

    /**
     * @SWG\Get(
     *   path="/api/poke/prototypes/{id}",
     *   summary="Get a poke prototype by id.",
     *   operationId="getPrototype",
     *   @SWG\Parameter(name="id", in="path", type="number"),
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
        $prototype = PokePrototype::findOrFail($id);
        $this->validateOwnership($prototype, $request->user()->id);
        return $prototype;
        
    }

    /**
     * @SWG\Post(
     *   path="/api/poke/prototypes",
     *   summary="Create new poke prototype.",
     *   operationId="postPrototype",
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
        $this->validateRequest($request->all());
        $prototype = new PokePrototype();
        $prototype->name = $request->get('name');
        $prototype->message = $request->get('message');
        $prototype->owner_id = $request->user()->id;
        $prototype->save();
        return $prototype;
    }

    /**
     * @SWG\Put(
     *   path="/api/poke/prototypes/{id}",
     *   summary="Update a poke prototype.",
     *   operationId="putPrototype",
     *   @SWG\Parameter(name="id", in="path", type="number"),
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
        $this->validateRequest($request->all());
        $prototype = PokePrototype::findOrFail($id);
        $this->validateOwnership($prototype, $request->user()->id);
        $prototype->name = $request->get('name');
        $prototype->message = $request->get('message');
        $prototype->save();
        return $prototype;
    }
    /**
     * @SWG\Delete(
     *   path="/api/poke/prototypes/{id}",
     *   summary="Deletes a poke prototype",
     *   operationId="deletePrototype",
     *   @SWG\Parameter(name="id", in="path", type="number"),
     *   @SWG\Response(response=204, description="successful deletion")
     * )
     */
    public function deletePrototype(Request $request, $id)
    {
        $prototype = PokePrototype::findOrFail($id);
        $this->validateOwnership($prototype, $request->user()->id);
        $prototype->delete();
        return \response()->json('', Response::HTTP_NO_CONTENT);
    }

    protected function validateOwnership($result, $user_id)
    {
        if ($result->owner_id !== $user_id) {
            throw new \Exception('You are not the owner of that poke prototype.');
        }
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

        if ($validator->fails()) {
            return \response()->json($validator->errors(), Response::HTTP_METHOD_NOT_ALLOWED);
        }
    }
}
