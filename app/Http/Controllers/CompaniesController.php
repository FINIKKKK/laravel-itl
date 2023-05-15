<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CompaniesController extends Controller
{
    /**
     * Создание компании
     */
    public function create(Request $request) {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'min:2', 'unique:companies,name'],
            'url_address' => ['required', 'url', 'unique:companies,url_address'],
        ], [
                'string' => 'Поле должно быть строчкой',
                'required' => 'Поле обязательно для заполнения',
                'name.min' => 'Название должно быть минимум :min символов',
                'name.unique' => 'Компания с таким названием уже существует',
                'url' => 'Некорректный url адресс',
                'url_address.unique' => 'Такой url адресс уже используется',
            ]
        );
        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()], 400);
        }

        $user = auth()->guard('api')->user();
        $company = Company::create([
            'name' => $request->name,
            'url_address' => $request->url_address,
            'user_id' => $user->id,
        ]);
        return $company;
    }

    /**
     * Получение всех компаний пользователя
     */
    public function getAll() {
        $companies = Company::all();
        return $companies;
    }
}
