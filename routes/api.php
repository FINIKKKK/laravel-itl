<?php

use App\Http\Controllers\MailController;
use App\Http\Controllers\UsersController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'users'], function ($router) {
    Route::get('/', [UsersController::class, 'getAll'])->name('getAll');
});

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

Route::namespace('App\Http\Controllers\Company')->group(function () {
    Route::prefix('companies')->group(function () {
        Route::get('/', 'GetAllController@getAll');
        Route::middleware('auth:api')->group(function () {
            Route::post('/', 'CreateController@create');
            Route::get('/mine', 'MineController@mine');
        });
    });
});

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