<?php

use App\Http\Controllers\DonationController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('ping', function () {
    return 'pong';
})->name('ping');

Route::post('/login', [LoginController::class, 'login'])->name('login');

Route::middleware('auth')->group(function () {
    Route::get('me', UserController::class)->name('me');

    Route::prefix('donations')->name('donations.')->group(function () {
        Route::get('', [DonationController::class, 'index'])->name('index');
        Route::put('{donation}', [DonationController::class, 'update'])->name('update');
    });
});

