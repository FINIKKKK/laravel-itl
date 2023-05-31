<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;

class RolesController extends BaseController {
    /**
     * Получение всех ролей
     */
    public function getAll(Request $req) {
        // Получаем все роли
        $roles = Role::all();

        // Возвращаем список ролей
        return $this->response($roles, false, false);
    }
}
