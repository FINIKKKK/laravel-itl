<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Auth\BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class LoginController extends BaseController {
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
}
