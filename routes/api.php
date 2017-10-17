<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('register', 'Auth\RegisterController@register');

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:api');
