<?php

use Illuminate\Support\Facades\Route;

Route::get('ping', function () {
    return 'pong';
})->name('ping');

//Route::post('/login', 'Auth\\LoginController@login');
//Route::post('/logout', 'Auth\\LoginController@logout');
