<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MailController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\UploadFileController;

// Auth Routes ##########################
Route::namespace('App\Http\Controllers\Auth')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('/register', 'RegisterController@register');
        Route::post('/login', 'LoginController@login');
        Route::middleware('auth:api')->group(function () {
            Route::get('/me', 'MeController@me');
            Route::post('/refresh', 'RefreshController@refresh');
            Route::post('/logout', 'LogoutController@logout');
        });
    });
});
Route::get('/send-email', [MailController::class, 'sendEmail']);

// Users Routes ##########################
Route::group(['prefix' => 'users'], function ($router) {
    Route::get('/', [UsersController::class, 'getAll'])->name('getAll');
});

// Companies Routes ##########################
Route::namespace('App\Http\Controllers\Company')->group(function () {
    Route::prefix('companies')->group(function () {
        Route::get('/', 'GetAllController@getAll');
        Route::middleware('auth:api')->group(function () {
            Route::post('/', 'CreateController@create');
            Route::get('/mine', 'MineController@mine');
        });
    });
});

// Posts Routes ##########################
Route::namespace('App\Http\Controllers\Post')->group(function () {
    Route::prefix('posts')->group(function () {
        Route::get('/', 'GetAllController@getAll');
        Route::get('/{id}', 'GetOneController@getOne');
        Route::middleware('auth:api')->group(function () {
            Route::post('/', 'CreateController@create');
            Route::patch('/{id}', 'UpdateController@update');
            Route::delete('/{id}', 'DeleteController@delete');
        });
    });
});

// Comments Routes ##########################
Route::namespace('App\Http\Controllers\Comment')->group(function () {
    Route::prefix('comments')->group(function () {
        Route::get('/', 'GetAllController@getAll');
        Route::middleware('auth:api')->group(function () {
            Route::post('/', 'CreateController@create');
            Route::patch('/{id}', 'UpdateController@update');
            Route::delete('/{id}', 'DeleteController@delete');
        });
    });
});

// Upload File ##########################
Route::post('/upload', [UploadFileController::class, 'upload']);