<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Favorite;
use App\Models\Like;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LikesController extends BaseController {
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
            return $this->validationErrors($validator);
        }

        // Проверяем есть ли элемент
        $type = "App\\Models\\" . ucfirst($req->get('type'));
        $elem = $type::whereId($req->get('item_id'))->first();
        if (!$elem) {
            return $this->response('Элемент не найден', true, true);
        }

        // Создаем или получаем элемент
        $like = Like::firstOrCreate([
            'user_id' => $req->user()->id,
            'likeable_id' => $elem->id,
            'likeable_type' => $elem::class
        ]);

        // Если есть, то удаляем
        if (!$like->wasRecentlyCreated) {
            $like->delete();
            // Возвращаем успешное удаление
            return $this->response(false, false, false);
        }

        // Возвращаем успешное добавление
        return $this->response(true, false, false);
    }

    /**
     * Получить все лайканные элементы пользователя
     */
    public function getAll(Request $req) {
        // Получение всех избранных элементов пользователя
        $likes = Like::where('user_id', $req->user()->id)->get();

        // Добавляем для каждого элемента дополнительное поле - тип элемента
        foreach ($likes as $like) {
            $str = explode('\\', $like->likeable_type);
            $type = strtolower(end($str));
            $like->type = $type;
        }

        // Возвращаем список элементов
        return $this->response($likes, false, false);
    }
}
