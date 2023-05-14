<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends BaseController
{

    protected function respondWithToken($token) {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60,
        ]);
    }

    public function register(Request $request) {
        $validator = Validator::make($request->all(), [
            'firstName' => ['required', 'string'],
            'lastName' => ['required', 'string'],
            'email' => ['required', 'email', 'unique:users,email', 'max:250'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ], [
                'string' => 'Поле должно быть строчкой',
                'required' => 'Поле обязательно для заполнения',
                'email' => 'Некорректный email',
                'email.unique' => 'Пользователь с такой почтой уже зарегистрован',
                'confirmed' => 'Пароли не совпадают',
                'password.min' => 'Пароль должен содержать не менее :min символов',
                'email.max' => 'Email должнен содержать не более :max символов',
            ]
        );
        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()], 400);
        }

        $user = User::create([
            'firstName' => $request->firstName,
            'lastName' => $request->lastName,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);
        $token = Auth::guard('api')->login($user);
        return $this->respondWithToken($token);
    }

    public function login(Request $request) {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);
        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()], 400);
        }

        $loginValue = $request->only('email', 'password');
        $token = Auth::attempt($loginValue);
        if (!$token) {
            return response()->json(['main_message' => 'Неверный email или пароль'], 400);
        }
        return $this->respondWithToken($token);
    }

    public function logout() {
        auth()->logout();
        return response()->json(['message' => 'Successfully logged out']);
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
