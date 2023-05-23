<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UsersController extends Controller {

    /**
     * Получение всех пользователей
     */
    public function getAll() {
        $users = User::all();
        return $users;
    }

    /**
     * Обновление данных пользователя
     */
    public function updateUserData(Request $req) {
        // Проверяем данные запроса
        $validator = Validator::make($req->all(), [
            'firstName' => 'string',
            'lastName' => 'string',
            'email' => 'string|max:250|unique:users,email',
            'avatar' => 'image|mimes:png,jpg,jpeg|max:2048',
        ]);
        // Прокидываем ошибки, если данные не прошли валидацию
        if ($validator->fails()) {
            return response()->json([
                'status' => config('app.error_status'),
                'message' => $validator->errors()->all()
            ], config('app.error_status'));
        }

        // Получаем пользователя
        $user = auth()->user();

        // Если нужно, то обновляем аватар
        if ($req->avatar) {
            // Экземляр конроллера для загрузки файлов
            $uploadController = new UploadFileController();
            // Создаем запрос с путями для загрузки
            $uploadReq = new Request([
                'image' => $req->avatar,
                'path' => config('app.img_path_avatar')
            ]);
            // Загружаем файл и получаем его путь
            $avatarUrl = $uploadController->upload($uploadReq);
        }

        // Обновляем только те элементы, которые приходят
        $fields = [
            'firstName',
            'lastName',
            'email',
        ];
        $data = $req->only($fields);
        $data['avatar'] = $avatarUrl;
        $user->fill($data);
        $user->save();

        // Возвращаем обновленный пост
        return response()->json([
            'status' => config('app.success_status'),
            'data' => $user,
        ], config('app.success_status'));
    }

    /**
     * Обновление пароля пользователя
     */
    public function updatePassword(Request $req) {
        // Проверяем данные запроса
        $validator = Validator::make($req->all(), [
            'old_password' => 'required|string',
            'password' => 'required|string|min:6|confirmed',
        ]);
        // Прокидываем ошибки, если данные не прошли валидацию
        if ($validator->fails()) {
            return response()->json([
                'status' => config('app.error_status'),
                'message' => $validator->errors()->all()
            ], config('app.error_status'));
        }

        // Получаем пользователя
        $user = auth()->user();

        // Проверяем совпадают ли пароли
        if (!Hash::check($req->old_password, $user->password)) {
            return response()->json([
                'status' => config('app.error_status'),
                'message' => ['Неверный пароль'],
            ], config('app.error_status'));
        }

        // Обновляем пароль
        $user->update([
            'password' => $req->password
        ]);

        // Возвращаем обновленный пост
        return response()->json([
            'status' => config('app.success_status'),
            'data' => $user,
        ], config('app.success_status'));
    }
}
