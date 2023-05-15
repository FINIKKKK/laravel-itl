<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
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
            // Прокидываем ошибку, если пользователя нет
            return response()->json([
                'status' => config('app.auth_error_status'),
                'message' => config('app.auth_error_message')
            ], config('app.auth_error_status'));
        }

        // Проверяем данные поста
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

        $posts = Post::with('user')->select('id', 'title', 'created_at', 'updated_at', 'user_id')->orderBy('created_at', 'desc')->get();
        return $posts;
    }

    /**
     * Получение одного поспа по id
     */
    public function getOne($id) {
        $post = Post::find($id);
        $post->body = json_decode($post->body, true);

        $user = User::find($post->user_id);
        $post->user = $user;
        return $post;
    }

    /**
     * Обновление поста по id
     */
    public function update(Request $req, $id) {
        $post = Post::findOrFail($id);
        $post->update($req->all());
        return $post;
    }

    /**
     * Удаление поста по id
     */
    public function delete($id) {
        $post = Post::findOrFail($id);
        Comment::where('post_id', $id)->delete();
        $post->delete();
        return response()->json(['message' => 'Пост успешно удален']);
    }
}
