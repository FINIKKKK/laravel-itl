<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Auth\BaseController;

class MeController extends BaseController {
    public function me() {
        return response()->json(auth()->user());
    }
}
