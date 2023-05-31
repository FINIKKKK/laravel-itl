<?php

namespace App\Http\Controllers;

use App\Models\Favorite;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FavoritesController extends Controller {
    /**
     * Добавление или удаление элемента из избранного
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

        // Получаем пост по id
        $post = Post::find($req->get('item_id'));
        // Проверяем есть ли пост
        if (!$post) {
            return response()->json([
                'status' => config('app.error_status'),
                'message' => ['Пост не найден'],
            ], config('app.error_status'));
        }

        // Проверяем, существует ли уже элемент в избранном пользователя
        $favorite = Favorite::where('user_id', $req->user()->id)
            ->where('favoritable_id', $post->id)
            ->where('favoritable_type', Post::class)
            ->first();

        // Если есть, то удаляем
        if ($favorite) {
            $favorite->delete();
            return response()->json([
                'status' => config('app.success_status'),
                'message' => ['Элемент удален из избранного'],
            ], config('app.success_status'));
        } // Если нет, то добавляем
        else {
            $favorite = Favorite::create([
                'user_id' => $req->user()->id,
                'favoritable_id' => $post->id,
                'favoritable_type' => Post::class
            ]);
        }

        // Возвращаем список элементов
        return response()->json([
            'status' => config('app.success_status'),
            'message' => ['Элемент добавлен в избранное'],
        ], config('app.success_status'));
    }

    /**
     * Получить все избранные элементы пользователя
     */
    public function getAll(Request $req) {
        // Получение всех избранных элементов пользователя
        $favorites = Favorite::where('user_id', $req->user()->id)
            ->with('favoritable')
            ->with('favoritable.company:id,name,slug')
            ->get();


        // Добавляем для каждого элемента дополнительное поле - тип элемента
        $favorites->each(function ($favorite) {
            $str = explode('\\', $favorite->favoritable_type);
            $type = strtolower(end($str));
            $favorite->type = $type;
        });

        $groupedFavorites = $favorites->groupBy(function ($favorite) {
            return $favorite->favoritable->company->name;
        });

        $sortedFavorites = $groupedFavorites->sortByDesc(function ($favorites) {
            return $favorites->max('created_at');
        });

        $result = $sortedFavorites->map(function ($favorites, $companyName) {
            $sortedFavorites = $favorites->sortByDesc(function ($favorite) {
                return $favorite->created_at;
            });

            return [
                'id' => $favorites->first()->favoritable->company->id,
                'name' => $companyName,
                'slug' => $favorites->first()->favoritable->company->slug,
                'favorites' => $sortedFavorites->values()->map(function ($favorite) {
                    return $favorite;
                }),
            ];
        });

        // Возвращаем список элементов
        return response()->json([
            'status' => config('app.success_status'),
            'data' => $result,
        ], config('app.success_status'));
    }
}
