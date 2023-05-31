<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UsersController extends BaseController {

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
            return $this->validationErrors($validator);
        }

        // Получаем пользователя
        $user = $req->user();

        // Обновляем только те элементы, которые приходят
        $fields = [
            'firstName',
            'lastName',
            'email',
        ];
        $data = $req->only($fields);
        $user->fill($data);
        $user->save();

        // Возвращаем обновленный пост
        return $this->response($user, false, false);
    }

    /**
     * Обновление аватарки пользователя
     */
    public function updateAvatar(Request $req) {
        // Проверяем данные запроса
        $validator = Validator::make($req->all(), [
            'avatar' => 'required|image|mimes:png,jpg,jpeg|max:2048',
        ]);
        // Прокидываем ошибки, если данные не прошли валидацию
        if ($validator->fails()) {
            return $this->validationErrors($validator);
        }

        // Получаем пользователя
        $user = $req->user();

        // Экземляр конроллера для загрузки файлов
        $uploadController = new UploadImageController();
        // Создаем запрос с путями для загрузки
        $uploadReq = new Request([
            'image' => $req->avatar,
            'path' => config('app.path.img.avatar')
        ]);
        // Загружаем файл и получаем его путь
        $avatarUrl = $uploadController->upload($uploadReq);

        // Обновляем аватарку у пользователя
        $user->update([
            'avatar' => $avatarUrl
        ]);

        // Возвращаем обновленный пост
        return $this->response($avatarUrl, false, false);
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
            return $this->validationErrors($validator);
        }

        // Получаем пользователя
        $user = $req->user();

        // Проверяем совпадают ли пароли
        if (!Hash::check($req->get('old_password'), $user->password)) {
            return $this->response('Неверный пароль', true, true);
        }

        // Обновляем пароль
        $user->update([
            'password' => $req->get('password')
        ]);

        // Возвращаем обновленный пост
        return $this->response($user, false, false);
    }
}
