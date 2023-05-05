<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Auth\BaseController;

class RegisterController extends BaseController {
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
}