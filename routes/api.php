<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('register', 'Auth\Api\ApiRegisterController@register');
Route::post('forgot_password', 'Auth\Api\ApiRegisterController@forgot_password');

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:api');
