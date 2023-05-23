<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
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
    public function updateUserData($id, Request $req) {
        // Получаем пользователя по id
        $user = User::find($id);
        // Проверяем есть ли пользователя
        if (!$user) {
            return response()->json([
                'status' => config('app.error_status'),
                'message' => 'Пользователь не найден',
            ], config('app.error_status'));
        }

        // Проверяем данные запроса
        $validator = Validator::make($req->all(), [
            'firstName' => 'string',
            'lastName' => 'string',
            'email' => 'string|max:250|unique:users,email',
            'avatar' => '',
        ]);
        // Прокидываем ошибки, если данные не прошли валидацию
        if ($validator->fails()) {
            return response()->json([
                'status' => config('app.error_status'),
                'message' => $validator->errors()->all()
            ], config('app.error_status'));
        }
        if ($req->avatar) {
            $uploadController = new UploadFileController();
            $uploadReq = new Request(['image' => $req->avatar]);
            $avatarUrl = $uploadController->upload($uploadReq);
        }

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
}
