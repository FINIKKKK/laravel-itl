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
        // Получаем текущего пользователя
        $user = auth()->user();
        // Проверяем аутенфикацию пользователя
        if (!$user) {
            return response()->json([
                'status' => config('app.auth_error_status'),
                'message' => config('app.auth_error_message')
            ], config('app.auth_error_status'));
        }

        // Проверяем данные запроса
        $validator = Validator::make($req->all(), [
            'title' => 'required|string|min:35|max:200',
            'body' => 'required',
        ]);
        // Прокидываем ошибки, если данные не прошли валидацию
        if ($validator->fails()) {
            return response()->json([
                'status' => config('app.error_status'),
                'message' => $validator->errors()
            ], config('app.error_status'));
        }

        // Создаем пост
        $post = Post::create([
            'title' => $req->title,
            'body' => json_encode($req->body),
            'user_id' => $user->id,
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
    public function getAll() {
        // Получаем список постов
        // Также добавляем информацию об авторе поста
        // Без поля body
        // Отсортированный по дате (сначала новые)
        $posts = Post::with('user')
            ->select('id', 'title', 'created_at', 'updated_at', 'user_id')
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
        // Получаем текущего пользователя
        $user = auth()->user();
        // Проверяем аутенфикацию пользователя
        if (!$user) {
            return response()->json([
                'status' => config('app.auth_error_status'),
                'message' => config('app.auth_error_message')
            ], config('app.auth_error_status'));
        }

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
            'title' => 'string|min:35|max:200',
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
