<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UploadFileController extends Controller {
    /**
     * Загрузка файлов
     */
    public function upload(Request $req) {
        // Проверяем данные запроса
        $validator = Validator::make($req->all(), [
            'image' => 'required|image|mimes:png,jpg,jpeg|max:2048',
        ]);
        // Прокидываем ошибки, если данные не прошли валидацию
        if ($validator->fails()) {
            return response()->json([
                'status' => config('app.error_status'),
                'message' => $validator->errors()->all()
            ], config('app.error_status'));
        }

        // Загружаем изображение
        $image = $req->image;
        if ($image->isValid()) {
            // Путь для загрузки изображений
            $imgPath = config('img_path');
            // Генерируем название изображения
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            // Загружаем изображение в папку
            $image->move(public_path($imgPath), $imageName);

            // Возвращаем полный путь изображения
            return response()->json([
                'status' => config('app.success_status'),
                'data' => "http://127.0.0.1:8000/{$imgPath}/" . $imageName,
            ], config('app.success_status'));
        } else {
            // Прокидываем ошибку, если изображение не было загружено
            return response()->json([
                'status' => config('app.error_status'),
                'message' => 'Ошибка загрузки изображения',
            ], config('app.error_status'));
        }
    }
}
