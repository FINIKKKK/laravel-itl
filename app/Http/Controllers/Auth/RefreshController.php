<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Auth\BaseController;

class RefreshController extends BaseController {
    public function refresh() {
        return $this->respondWithToken(auth()->refresh());
    }
}
