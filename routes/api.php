<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('register', 'Auth\Api\ApiRegisterController@register');
Route::post('forgot_password', 'Auth\Api\ApiRegisterController@forgot_password');

/*
 * AuthenticatedUser routes
 */
Route::get('/user', 'AuthenticatedUserController@getCurrentUser');
Route::put('/user', 'AuthenticatedUserController@update');
Route::get('/user/friend_requests', 'AuthenticatedUserController@getCurrentUserFriendRequests');
Route::get('/user/friends', 'AuthenticatedUserController@getCurrentUserFriends');
Route::post('/user/friend_requests/{id}/accept', 'AuthenticatedUserController@acceptFriendRequest');
Route::post('/user/friend_requests/{id}/decline', 'AuthenticatedUserController@declineFriendRequest');

/**
 * Users routes
 */
Route::get('/usersearch', 'UserController@searchUser');

/*
 * Friend Request routes
 */
Route::post('/user/friend_request', 'FriendRequestController@post');

/*
 * Poke routes
 */
Route::get('/pokes/prototypes', 'PokeController@getPrototypes');
Route::post('/pokes/prototypes', 'PokeController@postPrototype');
Route::get('/pokes/prototypes/{id}', 'PokeController@getPrototype');
Route::put('/pokes/prototypes/{id}', 'PokeController@putPrototype');
Route::delete('/pokes/prototypes/{id}', 'PokeController@deletePrototype');

//Route::get('/pokes');
//Route::post('/send_poke');


