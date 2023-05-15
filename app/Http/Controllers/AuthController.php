<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends BaseController
{

    // Возворащение пользователя и его токена
    protected function respondUserWithToken($user, $token) {
        return response()->json([
            'status' => config('app.success_status'),
            'data' => [
                'user' => $user,
                'token' => [
                    'access_token' => $token,
                    'token_type' => 'bearer',
                    'expires_in' => auth()->factory()->getTTL() * 60,
                ],
            ]]);
    }

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
            return response()->json([
                'status' => config('app.error_status'),
                'message' => $validator->errors()
            ], config('app.error_status'));
        }

        // Создаем пользователя
        $user = User::create([
            'firstName' => $req->firstName,
            'lastName' => $req->lastName,
            'email' => $req->email,
            // Зашифровываем пароль
            'password' => Hash::make($req->password),
        ]);
        // Получаем токен
        $token = auth()->login($user);

        // Возвращаем данные пользователя и его токен
        return $this->respondUserWithToken($user, $token);
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
            return response()->json([
                'status' => config('app.error_status'),
                'message' => $validator->errors()
            ], config('app.error_status'));
        }

        // Выбираем только поля email и password из запроса
        $loginValue = $req->only('email', 'password');
        // Проверяем авторизацию пользователя
        $token = auth()->attempt($loginValue);
        // Если не прошел, то прокидываем ошибку
        if (!$token) {
            return response()->json([
                'status' => config('app.error_status'),
                'message' => 'Неверный email или пароль',
            ], config('app.error_status'));
        }

        // Получаем данные пользователя
        $user = auth()->user();
        // Возвращаем данные пользователя и его токен
        return $this->respondUserWithToken($user, $token);
    }

    /**
     * Выход из аккаунта
     */
    public function logout() {
        // Выход из аккаунта
        auth()->logout();

        // Возвращаем сообщение об успешном выходе
        return response()->json([
            'status' => config('app.success_status'),
            'message' => 'Успешный выход из аккаунта',
        ], config('app.success_status'));
    }

    public function me() {
        $user = auth()->user();
        $company = Company::where('user_id', $user->id)->first();

        return response()->json([
            'user' => $user,
            'company' => $company,
        ]);
    }
}
