<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Favorite;
use App\Models\Like;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CommentsController extends Controller {
    /**
     * Создание комментария
     */
    public function create(Request $req) {
        // Проверяем данные запроса
        $validator = Validator::make($req->all(), [
            'text' => 'required|string|min:5|max:250',
            'post_id' => 'required|integer',
            'parent_id' => 'integer',
        ]);
        // Прокидываем ошибки, если данные не прошли валидацию
        if ($validator->fails()) {
            return response()->json([
                'status' => config('app.error_status'),
                'message' => $validator->errors()->all()
            ], config('app.error_status'));
        }

        // Проверяем есть ли пост
        $post = Post::find($req->get('post_id'));
        if (!$post) {
            return response()->json([
                'status' => config('app.error_status'),
                'message' => ['Пост не найден'],
            ], config('app.error_status'));
        }

        // Создаем комментарий и подгружаем информацию об авторе комментария
        $comment = Comment::create([
            'text' => $req->get('text'),
            'post_id' => $req->get('post_id'),
            'user_id' => $req->user()->id,
            'parent_comment_id' => $req->get('parent_id'),
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
        // Проверяем данные запроса
        $validator = Validator::make($req->all(), [
            'post_id' => 'required|integer',
        ]);
        // Прокидываем ошибки, если данные не прошли валидацию
        if ($validator->fails()) {
            return response()->json([
                'status' => config('app.error_status'),
                'message' => $validator->errors()->all()
            ], config('app.error_status'));
        }

        // Получаем только те комментарии, которые являются родителями
        // + Определенного поста
        // + Привязываем информацио об авторе
        // + Сортируем по дате (сначала новые)
        $comments = Comment::whereNull('parent_comment_id')
            ->where('post_id', $req->get('post_id'))
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();

        // Получаем пользователя
        $user = $req->user();

        // Пробегаймся по комментриям, и добавляем к какждому дочерние комментарии
        foreach ($comments as $comment) {
            $children = Comment::where('parent_comment_id', $comment->id)->with('user')->get();
            $comment->children = $children;
            // Если пользователь авторизован
            if ($user) {
                // Проверяем, есть ли лайк на посте
                $like = Like::where('user_id', $user->id)
                    ->where('likeable_id', $comment->id)
                    ->where('likeable_type', Comment::class)
                    ->first();
                // Если есть, то помечаем поле, как отмеченное
                if ($like) {
                    $comment->isLike = true;
                } // Если нету, то помечаем поле, как неотмеченное
                else {
                    $comment->isLike = false;
                }
            } // Если пользователь неавторизован
            else {
                $comment->isFavorite = false;
            }
        }

        // Возвращаем комментарии
        return response()->json([
            'status' => config('app.success_status'),
            'data' => $comments,
        ], config('app.success_status'));
    }

    /**
     * Обновление комментария по id
     */
    public function update(Request $req, $id) {
        // Проверяем есть ли комментарий
        $comment = Comment::find($id);
        if (!$comment) {
            return response()->json([
                'status' => config('app.error_status'),
                'message' => ['Комментарий не найден'],
            ], config('app.error_status'));
        }

        // Проверяем данные запроса
        $validator = Validator::make($req->all(), [
            'text' => 'string|min:5|max:250',
        ]);
        // Прокидываем ошибки, если данные не прошли валидацию
        if ($validator->fails()) {
            return response()->json([
                'status' => config('app.error_status'),
                'message' => $validator->errors()->all()
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
        // Проверяем есть ли комментарий
        $comment = Comment::find($id);
        if (!$comment) {
            return response()->json([
                'status' => config('app.error_status'),
                'message' => ['Комментарий не найден'],
            ], config('app.error_status'));
        }

        // Удаляем комментарий
        $comment->delete();

        // Возвращаем сообщение об успешном удалении комментария
        return response()->json([
            'status' => config('app.success_status'),
            'message' => ['Комментарий успешно удален'],
        ], config('app.success_status'));
    }
}
