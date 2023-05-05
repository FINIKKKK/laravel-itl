<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Company;

class GetAllController extends Controller {
    public function getAll() {
        $companies = Company::all();
        return $companies;
    }
}
