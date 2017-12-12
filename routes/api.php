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
Route::post('/user/add_device_token', 'AuthenticatedUserController@addDeviceToken');
Route::get('/user/friend_requests', 'AuthenticatedUserController@getCurrentUserFriendRequests');
Route::post('/user/friend_requests/{id}/accept', 'AuthenticatedUserController@acceptFriendRequest');
Route::post('/user/friend_requests/{id}/decline', 'AuthenticatedUserController@declineFriendRequest');
Route::get('/user/friends', 'AuthenticatedUserController@getCurrentUserFriends');
Route::delete('/user/friends/{friendId}', 'AuthenticatedUserController@deleteFriend');

Route::get('/users/search', 'UserController@searchUser');
Route::get('/users/{userId}', 'UserController@getUser');
Route::post('/users/{userId}/send_request', 'FriendRequestController@post');

/*
 * Poke routes
 */
Route::get('/pokes/prototypes', 'PokeController@getPrototypes');
Route::post('/pokes/prototypes', 'PokeController@postPrototype');
Route::get('/pokes/prototypes/{prototypeId}', 'PokeController@getPrototype');
Route::put('/pokes/prototypes/{prototypeId}', 'PokeController@putPrototype');
Route::delete('/pokes/prototypes/{prototypeId}', 'PokeController@deletePrototype');
Route::post('/pokes/prototypes/{prototypeId}/send','PokeController@postPokes');

Route::get('/pokes/{friendId}', 'PokeController@getPokes');
Route::post('/pokes/{pokeId}/response', 'PokeController@postPokeResponse');

Route::get('/habits', 'HabitController@getHabits');
Route::post('/habits', 'HabitController@postHabit');
Route::post('/habits/{habitId}/reject', 'HabitController@rejectHabit');
Route::post('/habits/{habitId}/done', 'HabitController@doneHabit');

