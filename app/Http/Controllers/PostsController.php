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
            return $this->validationErrorResponse($validator);
        }

        // Проверяем есть ли компания
        $company = Section::find($req->get('company_id'));
        if (!$company) {
            return response()->json([
                'status' => config('app.error_status'),
                'message' => ['Компания не найдена'],
            ], config('app.error_status'));
        }

        // Находим компанию пользователя
        $company = $req->user()->companies()->find($req->get('company_id'));
        $role = $company->pivot->role_id;

        // Проверяем есть ли раздел
        $section = Section::find($req->get('section_id'));
        if (!$section) {
            return response()->json([
                'status' => config('app.error_status'),
                'message' => ['Раздел не найден'],
            ], config('app.error_status'));
        }

        // Создаем пост
        $post = Post::create([
            'title' => $req->get('title'),
            'body' => json_encode($req->get('body')),
            'user_id' => $req->user()->id,
            'section_id' => $req->get('section_id'),
            'company_id' => $req->get('company_id'),
        ]);

        if ($role === 1) {
            $post->onModeration = false;
            $post->save();
        }

        // Возвращаем пост
        return response()->json([
            'status' => config('app.success_status'),
            'data' => $post,
        ], config('app.success_status'));
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
            return $this->validationErrorResponse($validator);
        }

        // Получаем список постов
        // + Без поля body
        // + Сортируем по дате (сначала новые)
        $posts = Post::where('section_id', $req->get('section_id'))
            ->without('body')
            ->orderBy('created_at', 'desc')
            ->get();

        // Возвращаем список постов
        return response()->json([
            'status' => config('app.success_status'),
            'data' => $posts,
        ], config('app.success_status'));
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
            return $this->validationErrorResponse($validator);
        }

        // Получаем пользователя
        $posts = $req->user()->posts()->where('company_id', $req->get('company_id'))->get();

        // Возвращаем обновленный пост
        return response()->json([
            'status' => config('app.success_status'),
            'data' => $posts
        ], config('app.success_status'));
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
            return $this->validationErrorResponse($validator);
        }


        // Получаем пользователя
        $company = Company::find($req->get('company_id'));

        $posts = $company->posts()->where('onModeration', true)->get();

        // Возвращаем обновленный пост
        return response()->json([
            'status' => config('app.success_status'),
            'data' => $posts
        ], config('app.success_status'));
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
            return response()->json([
                'status' => config('app.error_status'),
                'message' => ['Пост не найден'],
            ], config('app.error_status'));
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
        return response()->json([
            'status' => config('app.success_status'),
            'data' => $post,
        ], config('app.success_status'));
    }

    /**
     * Обновление поста по id
     */
    public function update(Request $req, $id) {
        // Проверяем есть ли пост
        $post = Post::find($id);
        if (!$post) {
            return response()->json([
                'status' => config('app.error_status'),
                'message' => ['Пост не найден'],
            ], config('app.error_status'));
        }

        // Проверяем данные запроса
        $validator = Validator::make($req->all(), [
            'title' => 'string|min:5|max:200',
            'body' => '',
        ]);
        // Прокидываем ошибки, если данные не прошли валидацию
        if ($validator->fails()) {
            return $this->validationErrorResponse($validator);
        }

        // Обновляем пост
        $post->update($req->all());

        // Возвращаем обновленный пост
        return response()->json([
            'status' => config('app.success_status'),
            'data' => $post,
        ], config('app.success_status'));
    }

    /**
     * Удаление поста по id
     */
    public function delete($id) {
        // Проверяем есть ли пост
        $post = Post::find($id);
        if (!$post) {
            return response()->json([
                'status' => config('app.error_status'),
                'message' => ['Пост не найден'],
            ], config('app.error_status'));
        }

        // Удаляем пост
        $post->delete();

        // Возвращаем сообщение об успешном удалении
        return response()->json([
            'status' => config('app.success_status'),
            'message' => ['Пост успешно удален'],
        ], config('app.success_status'));
    }
}
