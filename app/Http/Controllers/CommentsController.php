<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CommentsController extends Controller
{
    /**
     * Создание комментария
     */
    public function create(Request $req) {
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
            'text' => 'required|string|min:10|max:250',
            'post_id' => 'required|integer',
            'comment_id' => '',
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

        // Создаем комментарий и подгдаем информацию об авторе комментария
        $comment = Comment::create([
            'text' => $req->text,
            'post_id' => $req->post_id,
            'user_id' => $user->id,
            'comment_id' => $req->reply_id,
        ])->load('user');
        // Возвращаем комментарий
        return response()->json([
            'status' => config('app.success_status'),
            'data' => $comment,
        ], config('app.success_status'));
    }

    /**
     * Получение всех комментариев определенного поста
     */
    public function getAll(Request $req) {
        $comments = Comment::whereNull('comment_id')->where('post_id', $req->post_id)->with('user')
            ->orderBy('created_at', 'desc')->get();

        foreach ($comments as $comment) {
            $children = Comment::where('comment_id', $comment->id)->with('user')->get();
            $comment->children = $children;
        }

        return $comments;
    }

    /**
     * Обновление комментария по id
     */
    public function update(Request $req, $id) {
        // Получаем комментарий по id
        $comment = Comment::find($id);
        // Проверяем есть ли комментарий
        if (!$comment) {
            return response()->json([
                'status' => config('app.error_status'),
                'message' => 'Комментарий не найден',
            ], config('app.error_status'));
        }

        // Проверяем данные запроса
        $validator = Validator::make($req->all(), [
            'text' => 'string|min:10|max:250',
        ]);
        // Прокидываем ошибки, если данные не прошли валидацию
        if ($validator->fails()) {
            return response()->json([
                'status' => config('app.error_status'),
                'message' => $validator->errors()
            ], config('app.error_status'));
        }

        // Обновляем комментарий
        $comment->update($req->all());
        // Возвращаем обновленный комментарий
        return response()->json([
            'status' => config('app.success_status'),
            'data' => $comment,
        ], config('app.success_status'));
    }

    /**
     * Удаление комментария по id
     */
    public function delete($id) {
        // Получаем комментарий по id
        $comment = Comment::find($id);
        // Проверяем есть ли комментарий
        if (!$comment) {
            return response()->json([
                'status' => config('app.error_status'),
                'message' => 'Комментарий не найден',
            ], config('app.error_status'));
        }

        // Удаляем комментарий
        $comment->delete();
        // Возвращаем сообщение об успешном удалении комментария
        return response()->json([
            'status' => config('app.success_status'),
            'message' => 'Комментарий успешно удален',
        ], config('app.success_status'));
    }
}
