<?php

namespace App\Http\Controllers;

use App\Models\Favorite;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FavoritesController extends BaseController {
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
            return $this->validationErrors($validator);
        }

        // Получаем пост по id
        $post = Post::find($req->get('item_id'));
        // Проверяем есть ли пост
        if (!$post) {
            return $this->response('Пост не найден', true, true);
        }

        // Проверяем, существует ли уже элемент в избранном пользователя
        $favorite = Favorite::where('user_id', $req->user()->id)
            ->where('favoritable_id', $post->id)
            ->where('favoritable_type', Post::class)
            ->first();

        // Если есть, то удаляем
        if ($favorite) {
            $favorite->delete();
            return $this->response('Элемент удален из избранного', false, true);
        } // Если нет, то добавляем
        else {
            $favorite = Favorite::create([
                'user_id' => $req->user()->id,
                'favoritable_id' => $post->id,
                'favoritable_type' => Post::class
            ]);
        }

        // Возвращаем список элементов
        return $this->response('Элемент добавлен в избранное', false, true);
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
        return $this->response($result, false, false);
    }
}
