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
        // Получаем компанию по id
        $company = Company::find($req->company_id);
        // Проверяем есть ли компанию
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

        // Получаем текущего пользователя
        $user = auth()->user();

        // Создаем раздел (касты)
        $section = Section::create([
            'title' => $req->title,
            'body' => json_encode($req->body),
            'user_id' => $user->id,
            'company_id' => $req->company_id,
            'parent_id' => $req->parent_id,
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
        // Получаем раздел по id и привязываем информацию о пользователе и разделе
        $section = Section::with('user')->with(['parent:id,title'])->find($id);
        // Проверяем есть ли такой раздел
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
        $posts = Post::where('section_id', $id)->get();

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
        // Получаем раздел по id
        $section = Section::find($id);
        // Проверяем есть ли раздел
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
        // Получаем раздел по id
        $section = Section::find($id);
        // Проверяем есть ли раздел
        if (!$section) {
            return response()->json([
                'status' => config('app.error_status'),
                'message' => ['Раздел не найден'],
            ], config('app.error_status'));
        }

        // Удаляем все посты, связанные с разделом
        //        Post::where('section_id', $id)->delete();
        // Удаляем раздел
        $section->delete();

        // Возвращаем сообщение об успешном удалении
        return response()->json([
            'status' => config('app.success_status'),
            'message' => ['Раздел успешно удален'],
        ], config('app.success_status'));
    }
}
