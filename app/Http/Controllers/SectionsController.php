<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Post;
use App\Models\Section;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SectionsController extends Controller {
    /**
     * Создание раздела
     */
    public function create(Request $req) {
        // Проверяем есть ли компанию
        $company = Company::find($req->get('company_id'));
        if (!$company) {
            return response()->json([
                'status' => config('app.error_status'),
                'message' => ['Компания не найдена'],
            ], config('app.error_status'));
        }

        // Проверяем данные запроса
        $validator = Validator::make($req->all(), [
            'title' => 'required|string|min:5|max:200',
            'body' => '',
            'company_id' => 'required|integer',
            'parent_id' => 'integer',
        ]);
        // Прокидываем ошибки, если данные не прошли валидацию
        if ($validator->fails()) {
            return response()->json([
                'status' => config('app.error_status'),
                'message' => $validator->errors()->all()
            ], config('app.error_status'));
        }

        // Создаем раздел (касты)
        $section = Section::create([
            'title' => $req->get('title'),
            'body' => json_encode($req->get('body')),
            'user_id' => $req->user()->id,
            'company_id' => $req->get('company_id'),
            'parent_id' => $req->get('parent_id'),
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
                'message' => $validator->errors()->all()
            ], config('app.error_status'));
        }

        // Получаем список разделов только родительские
        // + Определенной компании
        // + Без поля body
        // + Сортируем по дате (сначала новые)
        $sections = Section::whereNull('parent_id')
            ->where('company_id', $req->company_id)
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
        // Проверяем есть ли такой раздел
        $section = Section::with('user')->with(['parent:id,title'])->find($id);
        if (!$section->count()) {
            return response()->json([
                'status' => config('app.error_status'),
                'message' => ['Раздел не найден'],
            ], config('app.error_status'));
        }

        // Конвертируем body у раздела из строки в массив
        $section->body = json_decode($section->body);

        // Получаем дочерние разделы
        $childSections = Section::where('parent_id', $id)->get();
        // Получаем дочерние посты
        $posts = Post::where('section_id', $id)->get();
        // Создаем поле data и прокидываем данные
        $section->data = [
            'sections' => $childSections,
            'posts' => $posts,
        ];

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
        // Проверяем есть ли раздел
        $section = Section::find($id);
        if (!$section) {
            return response()->json([
                'status' => config('app.error_status'),
                'message' => ['Раздел не найден'],
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
                'message' => $validator->errors()->all()
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
        // Проверяем есть ли раздел
        $section = Section::find($id);
        if (!$section) {
            return response()->json([
                'status' => config('app.error_status'),
                'message' => ['Раздел не найден'],
            ], config('app.error_status'));
        }

        // Удаляем раздел
        $section->delete();

        // Возвращаем сообщение об успешном удалении
        return response()->json([
            'status' => config('app.success_status'),
            'message' => ['Раздел успешно удален'],
        ], config('app.success_status'));
    }
}
