<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Company;
use App\Models\Favorite;
use App\Models\Like;
use App\Models\Post;
use App\Models\Section;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PostsController extends BaseController {
    /**
     * Создание поста
     */
    public function create(Request $req) {
        // Проверяем данные запроса
        $validator = Validator::make($req->all(), [
            'title' => 'required|string|min:5|max:200',
            'body' => 'required',
            'section_id' => 'required|integer',
            'company_id' => 'required|integer',
        ]);
        // Прокидываем ошибки, если данные не прошли валидацию
        if ($validator->fails()) {
            return $this->validationErrors($validator);
        }

        // Проверяем есть ли компания
        $company = Section::find($req->get('company_id'));
        if (!$company) {
            return $this->response('Компания не найдена', true, true);
        }

        // Проверяем есть ли раздел
        $section = Section::find($req->get('section_id'));
        if (!$section) {
            return $this->response('Раздел не найден', true, true);
        }

        // Находим компанию пользователя
        $company = $req->user()->companies()->find($req->get('company_id'));
        // Получаем его роль в этой компании
        $role = $company->pivot->role_id;

        // Создаем пост
        $post = Post::create([
            'title' => $req->get('title'),
            'body' => json_encode($req->get('body')),
            'user_id' => $req->user()->id,
            'section_id' => $req->get('section_id'),
            'company_id' => $req->get('company_id'),
        ]);

        // Если он "Модератор", то сразу публикуем
        if ($role === 1) {
            $post->onModeration = false;
            $post->save();
        }

        // Возвращаем пост
        return $this->response($post, false, false);
    }

    /**
     * Получение всех постов
     */
    public function getAll(Request $req) {
        // Проверяем данные запроса
        $validator = Validator::make($req->all(), [
            'section_id' => 'required|integer',
        ]);
        // Прокидываем ошибки, если данные не прошли валидацию
        if ($validator->fails()) {
            return $this->validationErrors($validator);
        }

        // Получаем список постов
        // + Без поля body
        // + Сортируем по дате (сначала новые)
        $posts = Post::where('section_id', $req->get('section_id'))
            ->where('onModeration', false)
            ->without('body')
            ->orderBy('created_at', 'desc')
            ->get();

        // Возвращаем список постов
        return $this->response($posts, false, false);
    }

    /**
     * Получение всех постов пользователя
     */
    public function getMy(Request $req) {
        // Проверяем данные запроса
        $validator = Validator::make($req->all(), [
            'company_id' => 'required|integer',
        ]);
        // Прокидываем ошибки, если данные не прошли валидацию
        if ($validator->fails()) {
            return $this->validationErrors($validator);
        }

        // Проверяем есть ли компания
        $company = Company::find($req->get('company_id'));
        if (!$company) {
            return $this->response('Компания не найдена', true, true);
        }

        // Получаем все посты пользователя
        $posts = $req->user()->posts()
            ->where('company_id', $req->get('company_id'))
            ->orderBy('created_at', 'desc')
            ->get();

        // Возвращаем обновленный пост
        return $this->response($posts, false, false);
    }

    /**
     * Получение всех постов пользователя
     */
    public function getModeration(Request $req) {
        // Проверяем данные запроса
        $validator = Validator::make($req->all(), [
            'company_id' => 'required|integer',
        ]);
        // Прокидываем ошибки, если данные не прошли валидацию
        if ($validator->fails()) {
            return $this->validationErrors($validator);
        }

        // Проверяем есть ли компания
        $company = Company::find($req->get('company_id'));
        if (!$company) {
            return $this->response('Компания не найдена', true, true);
        }

        // Получаем все посты на модерации
        $posts = $company->posts()
            ->where('onModeration', true)
            ->orderBy('created_at', 'desc')
            ->get();

        // Возвращаем обновленный пост
        return $this->response($posts, false, false);
    }

    /**
     * Получение одного поста по id
     */
    public function getOne(Request $req, $id) {
        // Получаем пользователя
        $user = $req->user();

        // Получаем пост по id и привязываем информацию о пользователе и разделе
        $post = Post::with('author:id,firstName,lastName')->with([
            'section:id,title',
            //            'likes' => function ($query) use ($user) {
            //                $query->where('users.id', $user->id);
            //            },
            //            'favorites' => function ($query) use ($user) {
            //                $query->where('users.id', $user->id);
            //            }
        ])->find($id);
        // Проверяем есть ли такой пост
        if (!$post) {
            return $this->response('Пост не найден', true, true);
        }

        // Конвертируем body у поста из строки в массив
        $post->body = json_decode($post->body);

        // Если пользователь авторизован
        //        if ($user) {
        //            // Проверяем, существует ли пост в избранном пользователя
        //            $favorite = Favorite::where('user_id', $user->id)
        //                ->where('favoritable_id', $post->id)
        //                ->where('favoritable_type', Post::class)
        //                ->first();
        //            // Если есть, то помечаем поле, как отмеченное
        //            if ($favorite) {
        //                $post->isFavorite = true;
        //            } // Если нету, то помечаем поле, как неотмеченное
        //            else {
        //                $post->isFavorite = false;
        //            }
        //
        //            // Проверяем, есть ли лайк на посте
        //            $like = Like::where('user_id', $user->id)
        //                ->where('likeable_id', $post->id)
        //                ->where('likeable_type', Post::class)
        //                ->first();
        //            // Если есть, то помечаем поле, как отмеченное
        //            if ($like) {
        //                $post->isLike = true;
        //            } // Если нету, то помечаем поле, как неотмеченное
        //            else {
        //                $post->isLike = false;
        //            }
        //        } // Если пользователь неавторизован
        //        else {
        //        }
        $post->isFavorite = false;
        $post->isLike = false;

        // Возвращаем пост
        return $this->response($post, false, false);
    }

    /**
     * Обновление поста по id
     */
    public function update(Request $req, $id) {
        // Проверяем есть ли пост
        $post = Post::find($id);
        if (!$post) {
            return $this->response('Пост не найден', true, true);
        }

        // Проверяем данные запроса
        $validator = Validator::make($req->all(), [
            'title' => 'string|min:5|max:200',
            'body' => '',
        ]);
        // Прокидываем ошибки, если данные не прошли валидацию
        if ($validator->fails()) {
            return $this->validationErrors($validator);
        }

        // Обновляем пост
        $post->update([
            'title' => $req->get('title'),
            'body' => $req->get('body'),
        ]);

        // Возвращаем обновленный пост
        return $this->response($post, false, false);
    }

    /**
     * Убираем пост с модерации
     */
    public function removeFromModeration(Request $req, $id) {
        // Проверяем есть ли пост
        $post = Post::find($id);
        if (!$post) {
            return $this->response('Пост не найден', true, true);
        }

        // Убираем пост с модерации
        $post->update([
            'onModeration' => false,
        ]);

        // Возвращаем обновленный пост
        return $this->response($post, false, false);
    }

    /**
     * Удаление поста по id
     */
    public function delete($id) {
        // Проверяем есть ли пост
        $post = Post::find($id);
        if (!$post) {
            return $this->response('Пост не найден', true, true);
        }

        // Удаляем пост
        $post->delete();

        // Возвращаем сообщение об успешном удалении
        return $this->response('Пост успешно удален', false, true);
    }
}
