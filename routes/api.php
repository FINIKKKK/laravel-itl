<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\MeController;
use App\Http\Controllers\Auth\RefreshController;
use App\Http\Controllers\Auth\RegisterController;
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
    Route::post('/register', [RegisterController::class, 'register']);
    Route::post('/login', [LoginController::class, 'login']);
    Route::middleware('auth:api')->group(function () {
        Route::get('/me', [MeController::class, 'me']);
        Route::post('/refresh', [RefreshController::class, 'refresh']);
        Route::post('/logout', [LogoutController::class, 'logout']);
    });
});