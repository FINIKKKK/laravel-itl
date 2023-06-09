<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends BaseController {

    /**
     * Регистрация пользователя
     */
    public function register(Request $req) {
        // Проверяем данные запроса
        $validator = Validator::make($req->all(), [
            'firstName' => 'required|string',
            'lastName' => 'required|string',
            'email' => 'required|string|max:250|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
        ]);
        // Прокидываем ошибки, если данные не прошли валидацию
        if ($validator->fails()) {
            return $this->validationErrors($validator);
        }

        // Создаем пользователя
        $user = User::create([
            'firstName' => $req->get('firstName'),
            'lastName' => $req->get('lastName'),
            'email' => $req->get('email'),
            // Зашифровываем пароль
            'password' => Hash::make($req->get('password')),
        ]);
        // Получаем токен
        $token = auth()->login($user);

        // Возвращаем данные пользователя и его токен
        return response()->json([
            'status' => config('app.errors.status.success'),
            'data' => [
                'user' => $user,
                'token' => [
                    'access_token' => $token,
                    'token_type' => 'bearer',
                    'expires_in' => auth()->factory()->getTTL() * 60,
                ],
            ]
        ]);
    }

    /**
     * Вход в аккаунт
     */
    public function login(Request $req) {
        // Проверяем данные запроса
        $validator = Validator::make($req->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);
        // Прокидываем ошибки, если данные не прошли валидацию
        if ($validator->fails()) {
            return $this->validationErrors($validator);
        }

        // Выбираем только поля email и password из запроса
        $loginValue = $req->only('email', 'password');
        // Проверяем авторизацию пользователя
        $token = auth()->setTTL(config('app.token_lifetime'))->attempt($loginValue);
        // Если не прошел, то прокидываем ошибку
        if (!$token) {
            return $this->response('Неверный email или пароль', true, true);
        }

        // Получаем компании пользователя
        $user = User::find($req->user()->id);
        $user->makeHidden('companies');

        // Добавляем поле - количество пользователей в компании
        $companies = $user->companies->map(function ($company) {
            $company->users_count = $company->users->count();
            unset($company->users);
            return $company;
        });

        // Возвращаем данные пользователя и его токен
        return response()->json([
            'status' => config('app.errors.status.success'),
            'data' => [
                'user' => $user,
                'token' => [
                    'access_token' => $token,
                    'token_type' => 'bearer',
                    'expires_in' => auth()->factory()->getTTL() * 60,
                ],
                'companies' => $companies,
            ]
        ]);
    }

    /**
     * Получение информации о текущем пользователе
     */
    public function me(Request $req) {
        // Получаем компании пользователя
        $user = User::find($req->user()->id);
        $user->makeHidden('companies');

        // Добавляем поле - количество пользователей в компании
        $companies = $user->companies->map(function ($company) {
            $company->users_count = $company->users->count();
            unset($company->users);
            return $company;
        });

        // Возвращение информации о текущем пользователе и его компаний
        return $this->response([
            'user' => $user,
            'companies' => $companies,
        ], false, false);
    }

    /**
     * Выход из аккаунта
     */
    public function logout() {
        // Выход из аккаунта
        auth()->logout();

        // Возвращаем сообщение об успешном выходе
        return $this->response('Успешный выход из аккаунта', false, true);
    }
}
