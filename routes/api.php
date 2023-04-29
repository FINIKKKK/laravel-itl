<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UsersController;
use Illuminate\Support\Facades\Route;

// Route::group(['middleware' => 'api', 'prefix' => 'auth'], function ($router) {
//     Route::post('/register', [AuthController::class, 'register'])->name('register');
//     Route::post('/login', [AuthController::class, 'login'])->name('login');
//     Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
//     Route::get('/me', [AuthController::class, 'me'])->name('me');
//     Route::post('/refresh', [AuthController::class, 'refresh'])->name('refresh');
// });

Route::group(['prefix' => 'users'], function ($router) {
    Route::get('/', [UsersController::class, 'getAll'])->name('getAll');
});

Route::group(['prefix' => 'auth'], function () {
    Route::controller(AuthController::class)->group(function () {
        Route::post('/register', 'register');
        Route::post('/login', 'login');
        Route::get('/me', 'me');
        Route::post('/refresh', 'refresh');
        Route::post('/logout', 'logout');
    });
});