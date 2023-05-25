<?php

namespace App\Http\Controllers;

use App\Models\Company;
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
        $companies = Company::where('user_id', $user->id)->get();
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
        // Получаем компанию по slug
        $company = Company::where('slug', $slug)->first();
        // Проверяем есть ли компанию
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
}
