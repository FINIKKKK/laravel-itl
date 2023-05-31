<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UploadImageController extends BaseController {
    /**
     * Загрузка файлов
     */
    public function upload(Request $req) {
        // Проверяем данные запроса
        $validator = Validator::make($req->all(), [
            'image' => 'required|image|mimes:png,jpg,jpeg|max:2048',
            'path' => 'string'
        ]);
        // Прокидываем ошибки, если данные не прошли валидацию
        if ($validator->fails()) {
            return $this->validationErrors($validator);
        }

        // Получаем изображение
        $image = $req->image;

        // Проверяем изображение
        if ($image->isValid()) {
            // Путь для загрузки изображений
            $path = config('database.connections.pgsql.host');
            // Путь для загрузки изображений
            $imgPath = config('app.path.img.main');

            // Менять пути, взависимости от типа изображения
            if ($req->get('path')) {
                $imgPath = $req->get('path');
            }

            // Проверяем размер изображения
            $dimensions = getimagesize($image);
            $width = $dimensions[0];
            $height = $dimensions[1];

            if ($width >= 256 && $height >= 256) {
                // Изображение соответствует требуемому размеру
                // Выполняем код для сохранения изображения
                //                $image->store('images'); // Пример сохранения в директорию 'public/images'

                // Генерируем название изображения
                $imageName = time() . '.' . $image->getClientOriginalExtension();
                // Загружаем изображение в папку
                $image->move(public_path($imgPath), $imageName);

                // Возвращаем полный путь изображения
                return "http://{$path}:8000/{$imgPath}/{$imageName}";
            } else {
                return $this->response("Изображение должно быть не менее 256x256 пикселей", true, true);
            }
        } else {
            // Прокидываем ошибку, если изображение не было загружено
            return $this->response('Ошибка загрузки изображения', true, true);
        }
    }
}
