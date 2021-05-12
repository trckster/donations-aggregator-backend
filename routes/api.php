<?php

use App\Http\Controllers\Auth\LoginController;
use Illuminate\Support\Facades\Route;

Route::get('ping', function () {
    return 'pong';
})->name('ping');

Route::post('/login', [LoginController::class, 'login'])->name('login');
//Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
