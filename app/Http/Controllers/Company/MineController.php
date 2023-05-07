<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\Request;

class MineController extends Controller {
    public function mine(Request $request) {
        $user = auth()->guard('api')->user();
        $company = Company::where('user_id', $user->id)->first();
        if (!$company) {
            return response()->json(['message' => 'У вас нет компании'], 400);
        }
        return $company;
    }
}
