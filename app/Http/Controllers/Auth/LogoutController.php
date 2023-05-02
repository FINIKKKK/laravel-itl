<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Auth\BaseController;

class LogoutController extends BaseController {
    public function logout() {
        auth()->logout();
        return response()->json(['message' => 'Successfully logged out']);
    }
}
