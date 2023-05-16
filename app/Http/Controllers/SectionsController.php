<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Section;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SectionsController extends Controller
{
    /**
     * Создание раздела
     */
    public function create(Request $req) {
        // Проверяем данные запроса
        $validator = Validator::make($req->all(), [
            'title' => 'required|string|min:15|max:200',
            'body' => 'required',
        ]);
        // Прокидываем ошибки, если данные не прошли валидацию
        if ($validator->fails()) {
            return response()->json([
                'status' => config('app.error_status'),
                'message' => $validator->errors()
            ], config('app.error_status'));
        }

        // Создаем раздел
        $section = Section::create([
            'title' => $req->title,
            'body' => json_encode($req->body),
        ]);
        // Возвращаем раздел
        return response()->json([
            'status' => config('app.success_status'),
            'data' => $section,
        ], config('app.success_status'));
    }

    /**
     * Получение всех разделов
     */
    public function getAll(Request $req) {
        // Проверяем данные запроса
        $validator = Validator::make($req->all(), [
            'company_id' => 'required|integer',
        ]);
        // Прокидываем ошибки, если данные не прошли валидацию
        if ($validator->fails()) {
            return response()->json([
                'status' => config('app.error_status'),
                'message' => $validator->errors()
            ], config('app.error_status'));
        }

        // Получаем список разделов определенной компании
        // + Без поля body
        // + Сортируем по дате (сначала новые)
        $sections = Section::where('company_id', $req->company_id)
            ->without('body')
            ->orderBy('created_at', 'desc')
            ->get();

        // Возвращаем список разделов
        return response()->json([
            'status' => config('app.success_status'),
            'data' => $sections,
        ], config('app.success_status'));
    }

    /**
     * Получение одного раздела по id
     */
    public function getOne($id) {
        // Получаем раздел по id
        $section = Section::find($id);
        // Проверяем есть ли такой раздел
        if (!$section) {
            return response()->json([
                'status' => config('app.error_status'),
                'message' => 'Раздел не найден',
            ], config('app.error_status'));
        }

        // Конвертируем body у раздела из строки в массив
        $section->body = json_decode($section->body);
        // Возвращаем раздел
        return response()->json([
            'status' => config('app.success_status'),
            'data' => $section,
        ], config('app.success_status'));
    }

    /**
     * Обновление раздела по id
     */
    public function update(Request $req, $id) {
        // Получаем раздел по id
        $section = Section::find($id);
        // Проверяем есть ли раздел
        if (!$section) {
            return response()->json([
                'status' => config('app.error_status'),
                'message' => 'Раздел не найден',
            ], config('app.error_status'));
        }

        // Проверяем данные запроса
        $validator = Validator::make($req->all(), [
            'title' => 'string|min:15|max:200',
            'body' => '',
        ]);
        // Прокидываем ошибки, если данные не прошли валидацию
        if ($validator->fails()) {
            return response()->json([
                'status' => config('app.error_status'),
                'message' => $validator->errors()
            ], config('app.error_status'));
        }

        // Обновляем раздел
        $section->update($req->all());
        // Возвращаем обновленный раздел
        return response()->json([
            'status' => config('app.success_status'),
            'data' => $section,
        ], config('app.success_status'));
    }

    /**
     * Удаление раздела по id
     */
    public function delete($id) {
        // Получаем раздел по id
        $section = Section::find($id);
        // Проверяем есть ли раздел
        if (!$section) {
            return response()->json([
                'status' => config('app.error_status'),
                'message' => 'Раздел не найден',
            ], config('app.error_status'));
        }

        // Удаляем все посты, связанные с разделом
//        Post::where('section_id', $id)->delete();
        // Удаляем раздел
        $section->delete();

        // Возвращаем сообщение об успешном удалении
        return response()->json([
            'status' => config('app.success_status'),
            'message' => 'Раздел успешно удален',
        ], config('app.success_status'));
    }
}
