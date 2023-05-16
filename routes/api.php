<?php

use App\Http\Controllers\CommentsController;
use App\Http\Controllers\CompaniesController;
use App\Http\Controllers\PostsController;
use App\Http\Controllers\SectionsController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\UploadFileController;
use App\Http\Controllers\AuthController;


/*
|---------------------------------------------------------------
| Авторизация и регистрация
|---------------------------------------------------------------
*/
Route::controller(AuthController::class)
    ->prefix('auth')
    ->name('auth.')
    ->group(callback: function () {
        /**
         * Регистрация нового пользователя
         */
        Route::name('register')->post('/register', 'register');

        /**
         * Вход в аккаунт
         */
        Route::name('login')->post('/login', 'login');

        // Группа маршрутов, требующих аутентификации
        Route::middleware('auth')->group(function () {
            /**
             * Выход из аккаунта
             */
            Route::name('logout')->post('/logout', 'logout');

            /**
             * Возвращение информациюи о текущем пользователе
             */
            Route::name('me')->get('/me', 'me');
        });
    });


/*
|---------------------------------------------------------------
| Пользователи
|---------------------------------------------------------------
*/
Route::controller(UsersController::class)
    ->prefix('users')
    ->name('users.')
    ->group(callback: function () {
        /**
         * Получение всех пользователей
         */
        Route::name('getAll')->get('/', 'getAll');
    });


/*
|---------------------------------------------------------------
| Компании
|---------------------------------------------------------------
*/
Route::controller(CompaniesController::class)
    ->prefix('companies')
    ->name('companies.')
    ->group(callback: function () {
        /**
         * Получение всех компаний
         */
        Route::name('getAll')->get('/', 'getAll');

        // Группа маршрутов, требующих аутентификации
        Route::middleware('auth')->group(function () {
            /**
             * Создание новой компании
             */
            Route::name('create')->post('/', 'create');
        });
    });


/*
|---------------------------------------------------------------
| Посты
|---------------------------------------------------------------
*/
Route::controller(PostsController::class)
    ->prefix('posts')
    ->name('posts.')
    ->group(callback: function () {
        /**
         * Получение всех постов
         */
        Route::name('getAll')->get('/', 'getAll');

        /**
         * Получение поста по id
         */
        Route::name('getOne')->get('/{id}', 'getOne');

        // Группа маршрутов, требующих аутентификации
        Route::middleware('auth')->group(function () {
            /**
             * Создание нового поста
             */
            Route::name('create')->post('/', 'create');

            /**
             * Обновление поста по id
             */
            Route::name('update')->patch('/{id}', 'update');

            /**
             * Удаление поста по id
             */
            Route::name('delete')->delete('/{id}', 'delete');
        });
    });


/*
|---------------------------------------------------------------
| Комментарии
|---------------------------------------------------------------
*/
Route::controller(CommentsController::class)
    ->prefix('comments')
    ->name('comments.')
    ->group(callback: function () {
        /**
         * Получение всех комментариев
         */
        Route::name('getAll')->get('/', 'getAll');

        // Группа маршрутов, требующих аутентификации
        Route::middleware('auth')->group(function () {
            /**
             * Создание нового комментария
             */
            Route::name('create')->post('/', 'create');

            /**
             * Обновление комментария по id
             */
            Route::name('update')->patch('/{id}', 'update');

            /**
             * Удаление комментария по id
             */
            Route::name('delete')->delete('/{id}', 'delete');
        });
    });


// Загрузка файлов
Route::post('/upload', [UploadFileController::class, 'upload']);


/*
|---------------------------------------------------------------
| Разделы
|---------------------------------------------------------------
*/
Route::controller(SectionsController::class)
    ->prefix('sections')
    ->name('sections.')
    ->group(callback: function () {
        /**
         * Получение всех разделов
         */
        Route::name('getAll')->get('/', 'getAll');

        /**
         * Получение раздела по id
         */
        Route::name('getOne')->get('/{id}', 'getOne');

        // Группа маршрутов, требующих аутентификации
        Route::middleware('auth')->group(function () {
            /**
             * Создание нового раздела
             */
            Route::name('create')->post('/', 'create');

            /**
             * Обновление раздела по id
             */
            Route::name('update')->patch('/{id}', 'update');

            /**
             * Удаление раздела по id
             */
            Route::name('delete')->delete('/{id}', 'delete');
        });
    });

