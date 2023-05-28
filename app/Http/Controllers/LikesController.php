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
            'type' => 'required|string|in:post,section,comment'
        ]);

        // Прокидываем ошибки, если данные не прошли валидацию
        if ($validator->fails()) {
            return response()->json([
                'status' => config('app.error_status'),
                'message' => $validator->errors()->all()
            ], config('app.error_status'));
        }

        // Получаем по id
        $entity = (ucfirst($req->get('type')))::whereId($req->item_id)->first();

        // Проверяем есть ли пост
        if (!$entity) {
            return response()->json([
                'status' => config('app.error_status'),
                'message' => ['Элемент не найден'],
            ], config('app.error_status'));
        }

        // Проверяем, существует ли уже элемент в избранном пользователя
        $like = Like::firstOrCreate([
            'user_id' => $req->user()->id,
            'liketable_id' => $entity->id,
            'liketable_type' => $entity::class
        ]);

        // Если есть, то удаляем
        if (!$like->wasRecentlyCreated()) {

            $like->delete();

            return response()->json([
                'status' => config('app.success_status'),
                'message' => ['Элемент удален из понравившееся'],
            ], config('app.success_status'));
        }

        // Возвращаем список элементов
        //
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
