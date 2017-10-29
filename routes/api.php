<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('register', 'Auth\Api\ApiRegisterController@register');
Route::post('forgot_password', 'Auth\Api\ApiRegisterController@forgot_password');

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:api');

Route::get('/user/friend_requests', function (Request $request) {
    return $request->user()->friendRequests()->with('owner')->get();
})->middleware('auth:api');

Route::get('/user/friends', function (Request $request) {
    return $request->user()->friends()->with('friend')->get();
})->middleware('auth:api');

Route::post('/user/friend_requests/{id}/accept', function (Request $request, int $id) {
    $stuff = \App\FriendRequest::findOrFail($id);
    $owner_id = $stuff->owner_id;
    $target_id = $stuff->target_id;

    $relationOne = new \App\Friend();
    $relationOne->user_id = $owner_id;
    $relationOne->friend_id = $target_id;
    $relationOne->save();

    $relationTwo = new \App\Friend();
    $relationTwo->user_id = $target_id;
    $relationTwo->friend_id = $owner_id;
    $relationTwo->save();

    $stuff->delete();

    return response()->json('Successfully accepted request.', \Illuminate\Http\Response::HTTP_OK);

})->middleware('auth:api');

Route::post('/user/friend_requests/{id}/decline', function (Request $request, int $id) {
    $stuff = \App\FriendRequest::findOrFail($id);
    $stuff->delete();
    return response()->json('Successfully declined request.', \Illuminate\Http\Response::HTTP_OK);
})->middleware('auth:api');


Route::post('friend_request', 'FriendRequestController@post');
