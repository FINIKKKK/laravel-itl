<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Favorite;
use App\Models\Like;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LikesController extends Controller {
    /**
     * Добавление или убрать лайк
     */
    public function addOrRemove(Request $req) {
        // Проверяем данные запроса
        $validator = Validator::make($req->all(), [
            'item_id' => 'required|integer',
            'type' => 'required|string'
        ]);
        // Прокидываем ошибки, если данные не прошли валидацию
        if ($validator->fails()) {
            return response()->json([
                'status' => config('app.error_status'),
                'message' => $validator->errors()->all()
            ], config('app.error_status'));
        }

        // Проверяем, есть ли вообще такой элемент
        if ($req->type === 'post') {
            // Получаем пост по id
            $post = Post::find($req->item_id);
            // Проверяем есть ли пост
            if (!$post) {
                return response()->json([
                    'status' => config('app.error_status'),
                    'message' => ['Пост не найден'],
                ], config('app.error_status'));
            }
        } else {
            if ($req->type === 'comment') {
                // Получаем комментарий по id
                $comment = Comment::find($req->item_id);
                // Проверяем есть ли комментарий
                if (!$comment) {
                    return response()->json([
                        'status' => config('app.error_status'),
                        'message' => ['Комментарий не найден'],
                    ], config('app.error_status'));
                }
            }
        }


        // Получаем пользователя
        $user = auth()->user();

        // Проверяем, существует ли уже элемент в избранном пользователя
        if ($req->type === 'post') {
            $like = Like::where('user_id', $user->id)
                ->where('liketable_id', $post->id)
                ->where('liketable_type', Post::class)
                ->first();
        } else {
            if ($req->type === 'comment') {
                $like = Like::where('user_id', $user->id)
                    ->where('liketable_id', $comment->id)
                    ->where('liketable_type', Comment::class)
                    ->first();
            }
        }

        // Если есть, то удаляем
        if ($like) {
            $like->delete();
            return response()->json([
                'status' => config('app.success_status'),
                'message' => ['Элемент удален из понравившееся'],
            ], config('app.success_status'));
        } // Если нет, то добавляем
        else {
            if ($req->type === 'post') {
                $like = Like::create([
                    'user_id' => $user->id,
                    'liketable_id' => $post->id,
                    'liketable_type' => Post::class
                ]);
            } else {
                if ($req->type === 'comment') {
                    $like = Like::create([
                        'user_id' => $user->id,
                        'liketable_id' => $comment->id,
                        'liketable_type' => Comment::class
                    ]);
                }
            }
        }

        // Возвращаем список элементов
        return response()->json([
            'status' => config('app.success_status'),
            'message' => ['Элемент добавлен в понравившееся'],
        ], config('app.success_status'));
    }

    /**
     * Получить все лайканные элементы пользователя
     */
    public
    function getAll(
        Request $req
    ) {
        // Получаем пользователя
        $user = auth()->user();

        // Получение всех избранных элементов пользователя
        $likes = Like::where('user_id', $user->id)->get();

        // Добавляем для каждого элемента дополнительное поле - тип элемента
        foreach ($likes as $like) {
            $str = explode('\\', $like->liketable_type);
            $type = strtolower(end($str));
            $like->type = $type;
        }

        // Возвращаем список элементов
        return response()->json([
            'status' => config('app.success_status'),
            'data' => $likes,
        ], config('app.success_status'));
    }
}
