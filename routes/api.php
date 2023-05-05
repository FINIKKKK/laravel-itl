<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\MeController;
use App\Http\Controllers\Auth\RefreshController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Company\CreateController;
use App\Http\Controllers\Company\GetAllController;
use App\Http\Controllers\UsersController;
use Illuminate\Support\Facades\Route;

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

Route::group(['prefix' => 'companies'], function () {
    Route::get('/', [GetAllController::class, 'getAll']);
    Route::middleware('auth:api')->group(function () {
        Route::post('/', [CreateController::class, 'create']);
    });
});