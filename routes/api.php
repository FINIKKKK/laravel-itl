<?php

use Illuminate\Support\Facades\Route;


/*
|---------------------------------------------------------------
| Авторизация и регистрация
|---------------------------------------------------------------
*/
Route::controller(\App\Http\Controllers\AuthController::class)
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
Route::controller(\App\Http\Controllers\UsersController::class)
    ->prefix('users')
    ->name('users.')
    ->group(callback: function () {
        // Группа маршрутов, требующих аутентификации
        Route::middleware('auth')->group(function () {
            /**
             * Обновление данных пользователя
             */
            Route::name('updateUserData')->patch('/', 'updateUserData');

            /**
             * Обновление аватарки пользователяя
             */
            Route::name('updateAvatar')->post('/avatar', 'updateAvatar');

            /**
             * Обновление пароля пользователя
             */
            Route::name('updatePassword')->patch('/password', 'updatePassword');
        });
    });


/*
|---------------------------------------------------------------
| Компании
|---------------------------------------------------------------
*/
Route::controller(\App\Http\Controllers\CompaniesController::class)
    ->prefix('companies')
    ->name('companies.')
    ->group(callback: function () {
        // Группа маршрутов, требующих аутентификации
        Route::middleware('auth')->group(function () {
            /**
             * Создание новой компании
             */
            Route::name('create')->post('/', 'create');

            /**
             * Получение всех компаний пользователя
             */
            Route::name('getAll')->get('/', 'getAll');

            /**
             * Получении компании по slug
             */
            Route::name('getOne')->get('/{slug}', 'getOne');

            // Группа маршрутов, связанных с пользователями компании
            Route::prefix('users')->name('users.')->group(function () {
                /**
                 * Добавление пользователя в компанию
                 */
                Route::name('addUser')->post('/', 'addUser');

                /**
                 * Получение всех пользователей компании
                 */
                Route::name('getUsers')->get('/{id}', 'getUsers');

                /**
                 * Изменение роли у пользователя в компании
                 */
                Route::name('changeRoleUser')->patch('/role', 'changeRoleUser');

                /**
                 * Удаление пользователя из компании
                 */
                Route::name('removeUser')->delete('/', 'removeUser');
            });
        });
    });


/*
|---------------------------------------------------------------
| Посты
|---------------------------------------------------------------
*/
Route::controller(\App\Http\Controllers\PostsController::class)
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
             * Получение постов пользователя
             */
            Route::name('getMy')->get('/get/my', 'getMy');

            /**
             * Получение постов, которые находяться на модерации
             */
            Route::name('getModeration')->get('/get/moderation', 'getModeration');

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
Route::controller(\App\Http\Controllers\CommentsController::class)
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


/*
|---------------------------------------------------------------
| Загрузка изображений
|---------------------------------------------------------------
*/
Route::post('/upload', [\App\Http\Controllers\UploadImageController::class, 'upload']);


/*
|---------------------------------------------------------------
| Разделы
|---------------------------------------------------------------
*/
Route::controller(\App\Http\Controllers\SectionsController::class)
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


/*
|-------------------------------------------------------------
| Избранное
|-------------------------------------------------------------
*/
Route::controller(\App\Http\Controllers\FavoritesController::class)
    ->prefix('favorites')
    ->name('favorites.')
    ->group(callback: function () {
        // Группа маршрутов, требующих аутентификации
        Route::middleware('auth')->group(function () {
            /**
             * Добавление или удаление элемента из избранного
             */
            Route::name('addOrRemove')->post('/', 'addOrRemove');

            /**
             * Получить все избранные элементы пользователя
             */
            Route::name('getAll')->get('/', 'getAll');
        });
    });


/*
|-------------------------------------------------------------
| Лайки
|-------------------------------------------------------------
*/
Route::controller(\App\Http\Controllers\LikesController::class)
    ->prefix('likes')
    ->name('likes.')
    ->group(callback: function () {
        // Группа маршрутов, требующих аутентификации
        Route::middleware('auth')->group(function () {
            /**
             * Добавление или убрать лайк
             */
            Route::name('addOrRemove')->post('/', 'addOrRemove');

            /**
             * Получить все лайканные элементы пользователя
             */
            Route::name('getAll')->get('/', 'getAll');
        });
    });

