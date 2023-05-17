<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PostsController extends Controller
{
    /**
     * Создание поста
     */
    public function create(Request $req) {
        // Проверяем данные запроса
        $validator = Validator::make($req->all(), [
            'title' => 'required|string|min:5|max:200',
            'body' => 'required',
            'section_id' => 'required|integer',
        ]);
        // Прокидываем ошибки, если данные не прошли валидацию
        if ($validator->fails()) {
            return response()->json([
                'status' => config('app.error_status'),
                'message' => $validator->errors()
            ], config('app.error_status'));
        }

        // Получаем текущего пользователя
        $user = auth()->user();

        // Создаем пост
        $post = Post::create([
            'title' => $req->title,
            'body' => json_encode($req->body),
            'user_id' => $user->id,
            'section_id' => $req->section_id,
        ]);
        // Возвращаем пост
        return response()->json([
            'status' => config('app.success_status'),
            'data' => $post,
        ], config('app.success_status'));
    }

    /**
     * Получение всех постов
     */
    public function getAll(Request $req) {
        // Проверяем данные запроса
        $validator = Validator::make($req->all(), [
            'section_id' => 'required|integer',
        ]);
        // Прокидываем ошибки, если данные не прошли валидацию
        if ($validator->fails()) {
            return response()->json([
                'status' => config('app.error_status'),
                'message' => $validator->errors()
            ], config('app.error_status'));
        }

        // Получаем список постов
        // + Добавляем информацию об авторе поста
        // + Без поля body
        // + Сортируем по дате (сначала новые)
        $posts = Post::where('section_id', $req->section_id)->with('user')
            ->without('body')
            ->orderBy('created_at', 'desc')
            ->get();

        // Возвращаем список постов
        return response()->json([
            'status' => config('app.success_status'),
            'data' => $posts,
        ], config('app.success_status'));
    }

    /**
     * Получение одного поста по id
     */
    public function getOne($id) {
        // Получаем пост по id и привязываем информацию о пользователе
        $post = Post::with('user')->find($id);
        // Проверяем есть ли такой пост
        if (!$post) {
            return response()->json([
                'status' => config('app.error_status'),
                'message' => 'Пост не найден',
            ], config('app.error_status'));
        }

        // Конвертируем body у поста из строки в массив
        $post->body = json_decode($post->body);
        // Возвращаем пост
        return response()->json([
            'status' => config('app.success_status'),
            'data' => $post,
        ], config('app.success_status'));
    }

    /**
     * Обновление поста по id
     */
    public function update(Request $req, $id) {
        // Получаем пост по id
        $post = Post::find($id);
        // Проверяем есть ли пост
        if (!$post) {
            return response()->json([
                'status' => config('app.error_status'),
                'message' => 'Пост не найден',
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

        // Обновляем пост
        $post->update($req->all());
        // Возвращаем обновленный пост
        return response()->json([
            'status' => config('app.success_status'),
            'data' => $post,
        ], config('app.success_status'));
    }

    /**
     * Удаление поста по id
     */
    public function delete($id) {
        // Получаем пост по id
        $post = Post::find($id);
        // Проверяем есть ли пост
        if (!$post) {
            return response()->json([
                'status' => config('app.error_status'),
                'message' => 'Пост не найден',
            ], config('app.error_status'));
        }

        // Удаляем все комментарии, связанные с постом
        Comment::where('post_id', $id)->delete();
        // Удаляем пост
        $post->delete();

        // Возвращаем сообщение об успешном удалении
        return response()->json([
            'status' => config('app.success_status'),
            'message' => 'Пост успешно удален',
        ], config('app.success_status'));
    }
}
