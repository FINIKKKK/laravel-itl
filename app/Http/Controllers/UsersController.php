<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UsersController extends Controller
{
    /**
     * Получение всех пользователей
     */
    public function getAll() {
        $users = User::all();
        return $users;
    }
}
