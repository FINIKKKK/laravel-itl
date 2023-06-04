<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CompaniesController extends BaseController {
    /**
     * Создание компании
     */
    public function create(Request $req) {
        // Проверяем данные запроса
        $validator = Validator::make($req->all(), [
            'name' => 'required|string|min:2|max:150|unique:companies,name',
            'url_address' => 'required|string|unique:companies,url_address',
        ]);
        // Прокидываем ошибки, если данные не прошли валидацию
        if ($validator->fails()) {
            return $this->validationErrors($validator);
        }

        // Получаем текущего пользователя
        $user = $req->user();

        // Создаем slug
        $slug = Str::slug($req->get('name'));

        // Создаем компанию
        $company = Company::create([
            'name' => $req->get('name'),
            'slug' => $slug,
            'url_address' => $req->get('url_address'),
            'user_id' => $user->id,
        ]);

        // Добавляем поле - количество пользователей
        $company->users_count = 1;

        // Получаем роль (модератор)
        $role = Role::findOrFail(1);
        // Добавляем роль к пользователю в этой компании
        $user->companies()->attach($company, ['role_id' => $role->id]);

        // Возвращаем компанию
        return response()->json([
            'status' => config('app.errors.status.success'),
            'data' => $company,
        ], config('app.errors.status.success'));
    }

    /**
     * Получение всех компаний пользователя
     */
    public function getAll(Request $req) {
        // Получаем текущего пользователя
        $user = $req->user();

        // Получаем список компаний пользователя
        $user = User::with('companies.users')->find($user->id);

        // Добавляем поле - количество пользователей в компании
        $companies = $user->companies->map(function ($company) {
            $company->users_count = $company->users->count();
            unset($company->users);
            return $company;
        });

        // Возвращаем список компаний пользователя
        return $this->response($companies, false, false);
    }

    /**
     * Получение компании по slug
     */
    public function getOne($slug, Request $req) {
        // Получаем текущего пользователя
        $user = $req->user();

        // Проверяем есть ли компанию
        $company = $user->companies()->where('slug', $slug)->first();
        if (!$company) {
            return $this->response('Компания не найдена', true, true);
        }

        // Возвращаем список компаний пользователя
        return $this->response($company, false, false);
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
            return $this->validationErrors($validator);
        }

        // Проверяем есть ли пользователь
        $user = User::find($req->get('user_id'));
        if (!$user) {
            return $this->response('Пользователь не найден', true, true);
        }

        // Проверяем есть ли компания
        $company = Company::find($req->get('company_id'));
        if (!$company) {
            return $this->response('Компания не найдена', true, true);
        }

        // Проверяем есть ли роль (пользователь)
        $role = Role::findOrFail(3);
        // Добавляем пользователя к компании с ролью (пользователь)
        $user->companies()->attach($company, ['role_id' => $role->id]);

        // Возвращаем сообщение об успешном добавлении пользователя
        return $this->response('Пользователь добавлен к компании', false, true);
    }

    /**
     * Получение всех пользователей компании
     */
    public function getUsers($id) {
        // Проверяем есть ли компания
        $company = Company::find($id);
        if (!$company) {
            return $this->response('Компания не найдена', true, true);
        }

        // Получаем пользователей компании
        $users = $company->users;

        // Возвращаем список пользователей
        return $this->response($users, false, false);
    }

    /**
     * Изменение компании
     */
    public function update($id, Request $req) {
        // Проверяем есть ли компания
        $company = Company::find($id);
        if (!$company) {
            return $this->response('Компания не найдена', true, true);
        }

        // Проверяем данные запроса
        $validator = Validator::make($req->all(), [
            'name' => 'string|min:2|max:150|unique:companies,name',
            'url_address' => 'string|unique:companies,url_address',
        ]);
        // Прокидываем ошибки, если данные не прошли валидацию
        if ($validator->fails()) {
            return $this->validationErrors($validator);
        }

        // Создаем slug
        $slug = Str::slug($req->get('name'));

        // Обновляем компанию
        $company->update([
            'name' => $req->get('name'),
            'url_address' => $req->get('url_address'),
        ]);

        // Возвращаем сообщение об успешном изменении роли пользователя
        return $this->response($company, false, false);
    }

    /**
     * Изменение роли у пользователя в компании
     */
    public function changeRoleUser($id, Request $req) {
        // Проверяем есть ли компания
        $company = Company::find($id);
        if (!$company) {
            return $this->response('Компания не найдена', true, true);
        }

        // Проверяем данные запроса
        $validator = Validator::make($req->all(), [
            'role_id' => 'required|integer',
            'user_id' => 'required|integer',
        ]);
        // Прокидываем ошибки, если данные не прошли валидацию
        if ($validator->fails()) {
            return $this->validationErrors($validator);
        }

        // Проверяем, существует ли указанная роль
        $role = Role::find($req->get('role_id'));
        if (!$role) {
            return $this->response('Указанная роль не существует', true, true);
        }

        // Проверяем есть ли пользователь
        $user = User::find($req->get('user_id'));
        if (!$user) {
            return $this->response('Пользователь не найден', true, true);
        }

        // Обновляем роль пользователя для данной компании
        $user->companies()->updateExistingPivot($req->get('company_id'), ['role_id' => $req->get('role_id')]);

        // Возвращаем сообщение об успешном изменении роли пользователя
        return $this->response('Роль пользователя успешно обновлена', false, true);
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
            return $this->validationErrors($validator);
        }

        // Проверяем есть ли пользователь
        $user = User::find($req->get('user_id'));
        if (!$user) {
            return $this->response('Пользователь не найден', true, true);
        }

        // Проверяем есть ли компания
        $company = Company::find($req->get('company_id'));
        if (!$company) {
            return $this->response('Компания не найдена', true, true);
        }

        // Удаляем пользователя из компании
        $user->companies()->detach($company->id);

        // Возвращаем сообщение об успешном удалении пользователя из компании
        return $this->response('Пользователь удален из компании', false, true);
    }
}
