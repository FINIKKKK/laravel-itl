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

        // Проверяем есть ли элемент
        $type = "App\\Models\\" . ucfirst($req->get('type'));
        $elem = $type::whereId($req->get('item_id'))->first();
        if (!$elem) {
            return $this->response('Элемент не найден', true, true);
        }

        // Создаем или получаем элемент
        $favorite = Favorite::firstOrCreate([
            'user_id' => $req->user()->id,
            'favoritable_id' => $elem->id,
            'favoritable_type' => $elem::class
        ]);


        // Если есть, то удаляем
        if (!$favorite->wasRecentlyCreated) {
            $favorite->delete();
            // Возвращаем успешное удаление
            return $this->response(false, false, false);
        }

        // Возвращаем успешное добавление
        return $this->response(true, false, false);
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

        // Группируем элементы по компании
        $groupedFavorites = $favorites->groupBy(function ($favorite) {
            return $favorite->favoritable->company->name;
        });

        // Сортируем по дате добавления в избранное
        $sortedFavorites = $groupedFavorites->sortByDesc(function ($favorites) {
            return $favorites->max('created_at');
        });

        // Приводи всё к одному виду
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
