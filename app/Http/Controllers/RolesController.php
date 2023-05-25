<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;

class RolesController extends Controller {
    /**
     * Получение всех ролей
     */
    public function getAll(Request $req) {
        $users = Role::all();
        return $users;
    }
}
