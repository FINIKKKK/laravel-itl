<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CompaniesController extends Controller {
    /**
     * Создание компании
     */
    public function create(Request $req) {
        // Проверяем данные запроса
        $validator = Validator::make($req->all(), [
            'name' => 'required|string|min:2|max:150|unique:companies,name',
            'url_address' => 'required|url|unique:companies,url_address',
        ]);
        // Прокидываем ошибки, если данные не прошли валидацию
        if ($validator->fails()) {
            return response()->json([
                'status' => config('app.error_status'),
                'message' => $validator->errors()->all()
            ], config('app.error_status'));
        }

        // Получаем текущего пользователя
        $user = auth()->user();

        // Создаем slug
        $slug = Str::slug($req->name);

        // Создаем компанию
        $company = Company::create([
            'name' => $req->name,
            'slug' => $slug,
            'url_address' => $req->url_address,
            'user_id' => $user->id,
        ]);

        // Получаем роль (модератор)
        $role = Role::findOrFail(1);
        // Добавляем роль к пользователю в этой компании
        $user->companies()->attach($company, ['role_id' => $role->id]);

        // Возвращаем компанию
        return response()->json([
            'status' => config('app.success_status'),
            'data' => $company,
        ], config('app.success_status'));
    }

    /**
     * Получение всех компаний пользователя
     */
    public function getAll() {
        // Получаем текущего пользователя
        $user = auth()->user();

        // Получаем список компаний пользователя
        $user = User::with('companies.users')->find($user->id);

        // Добавляем поле - количество пользователей в компании
        $companies = $user->companies->map(function ($company) {
            $company->users_count = $company->users->count();
            unset($company->users);
            return $company;
        });

        // Возвращаем список компаний пользователя
        return response()->json([
            'status' => config('app.success_status'),
            'data' => $companies,
        ], config('app.success_status'));
    }

    /**
     * Получение компании по slug
     */
    public function getOne($slug) {
        // Проверяем есть ли компанию
        $company = Company::where('slug', $slug)->first();
        if (!$company) {
            return response()->json([
                'status' => config('app.error_status'),
                'message' => ['Компания не найдена'],
            ], config('app.error_status'));
        }
        // Возвращаем список компаний пользователя
        return response()->json([
            'status' => config('app.success_status'),
            'data' => $company,
        ], config('app.success_status'));
    }

    /**
     * Добавление поьзователя к компании
     */
    public function addUser(Request $req) {
        // Проверяем данные запроса
        $validator = Validator::make($req->all(), [
            'user_id' => 'required|integer',
            'company_id' => 'required|integer',
        ]);
        // Прокидываем ошибки, если данные не прошли валидацию
        if ($validator->fails()) {
            return response()->json([
                'status' => config('app.error_status'),
                'message' => $validator->errors()->all()
            ], config('app.error_status'));
        }

        // Проверяем есть ли пользователь
        $user = User::find($req->user_id);
        if (!$user) {
            return response()->json([
                'status' => config('app.error_status'),
                'message' => ['Пользователь не найден'],
            ], config('app.error_status'));
        }

        // Проверяем есть ли компания
        $company = Company::find($req->company_id);
        if (!$company) {
            return response()->json([
                'status' => config('app.error_status'),
                'message' => ['Компания не найдена'],
            ], config('app.error_status'));
        }

        // Проверяем есть ли роль (пользователь)
        $role = Role::findOrFail(3);
        // Добавляем пользователя к компании с ролью (пользователь)
        $user->companies()->attach($company, ['role_id' => $role->id]);

        // Возвращаем сообщение об успешном добавлении пользователя
        return response()->json([
            'status' => config('app.success_status'),
            'data' => ['Пользователь добавлен к компании'],
        ], config('app.success_status'));
    }

    /**
     * Получение всех пользователей компании
     */
    public function getUsers($id) {
        // Проверяем есть ли компания
        $company = Company::find($id);
        if (!$company) {
            return response()->json([
                'status' => config('app.error_status'),
                'message' => 'Компания не найден',
            ], config('app.error_status'));
        }

        // Получаем пользователей компании
        $users = $company->users;

        // Возвращаем список пользователей
        return response()->json([
            'status' => config('app.success_status'),
            'data' => $users,
        ], config('app.success_status'));
    }

    /**
     * Изменение роли у пользователя в компании
     */
    public function changeRoleUser(Request $req) {
        // Проверяем данные запроса
        $validator = Validator::make($req->all(), [
            'role_id' => 'required|integer',
            'user_id' => 'required|integer',
            'company_id' => 'required|integer',
        ]);
        // Прокидываем ошибки, если данные не прошли валидацию
        if ($validator->fails()) {
            return response()->json([
                'status' => config('app.error_status'),
                'message' => $validator->errors()->all()
            ], config('app.error_status'));
        }

        // Проверяем, существует ли указанная роль
        $role = Role::find($req->role_id);
        if (!$role) {
            return response()->json([
                'status' => config('app.error_status'),
                'message' => 'Указанная роль не существует.'
            ], config('app.error_status'));
        }

        // Проверяем есть ли пользователь
        $user = User::find($req->user_id);
        if (!$user) {
            return response()->json([
                'status' => config('app.error_status'),
                'message' => ['Пользователь не найден'],
            ], config('app.error_status'));
        }

        // Проверяем есть ли компания
        $company = Company::find($req->company_id);
        if (!$company) {
            return response()->json([
                'status' => config('app.error_status'),
                'message' => ['Компания не найдена'],
            ], config('app.error_status'));
        }

        // Обновляем роль пользователя для данной компании
        $user->companies()->updateExistingPivot($req->company_id, ['role_id' => $req->role_id]);

        // Возвращаем сообщение об успешном изменении роли пользователя
        return response()->json([
            'status' => config('app.success_status'),
            'data' => ['Роль пользователя успешно обновлена'],
        ], config('app.success_status'));
    }

    /**
     * Удаление пользователя из компании
     */
    public function removeUser(Request $req) {
        // Проверяем данные запроса
        $validator = Validator::make($req->all(), [
            'user_id' => 'required|integer',
            'company_id' => 'required|integer',
        ]);
        // Прокидываем ошибки, если данные не прошли валидацию
        if ($validator->fails()) {
            return response()->json([
                'status' => config('app.error_status'),
                'message' => $validator->errors()->all()
            ], config('app.error_status'));
        }

        // Проверяем есть ли пользователь
        $user = User::find($req->user_id);
        if (!$user) {
            return response()->json([
                'status' => config('app.error_status'),
                'message' => ['Пользователь не найден'],
            ], config('app.error_status'));
        }

        // Проверяем есть ли компания
        $company = Company::find($req->company_id);
        if (!$company) {
            return response()->json([
                'status' => config('app.error_status'),
                'message' => ['Компания не найдена'],
            ], config('app.error_status'));
        }

        // Удаляем пользователя из компании
        $user->companies()->detach($company->id);

        // Возвращаем сообщение об успешном удалении пользователя из компании
        return response()->json([
            'status' => config('app.success_status'),
            'data' => ['Пользователь удален из компании'],
        ], config('app.success_status'));
    }
}
