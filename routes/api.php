<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('register', 'Auth\Api\ApiRegisterController@register');
Route::post('forgot_password', 'Auth\Api\ApiRegisterController@forgot_password');

/*
 * AuthenticatedUser routes
 */
Route::get('/user', 'AuthenticatedUserController@getCurrentUser');
Route::get('/user/friend_requests', 'AuthenticatedUserController@getCurrentUserFriendRequests');
Route::get('/user/friends', 'AuthenticatedUserController@getCurrentUserFriends');
Route::post('/user/friend_requests/{id}/accept', 'AuthenticatedUserController@acceptFriendRequest');
Route::post('/user/friend_requests/{id}/decline', 'AuthenticatedUserController@declineFriendRequest');

/*
 * Friend Request routes
 */
Route::post('friend_request', 'FriendRequestController@post');
