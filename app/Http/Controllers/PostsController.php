<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Favorite;
use App\Models\Like;
use App\Models\Post;
use App\Models\Section;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PostsController extends Controller {
    /**
     * Создание поста
     */
    public function create(Request $req) {
        // Проверяем данные запроса
        $validator = Validator::make($req->all(), [
            'title' => 'required|string|min:5|max:200',
            'body' => 'required',
            'section_id' => 'required|integer',
        ]);
        // Прокидываем ошибки, если данные не прошли валидацию
        if ($validator->fails()) {
            return response()->json([
                'status' => config('app.error_status'),
                'message' => $validator->errors()->all()
            ], config('app.error_status'));
        }

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
        ]);

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
            return response()->json([
                'status' => config('app.error_status'),
                'message' => $validator->errors()->all()
            ], config('app.error_status'));
        }

        // Получаем список постов
        // + Добавляем информацию об авторе поста
        // + Без поля body
        // + Сортируем по дате (сначала новые)
        $posts = Post::where('section_id', $req->get('section_id'))->with('user')
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
     * Получение одного поста по id
     */
    public function getOne(Request $req, $id) {
        // Получаем пользователя
        $user = $req->user();

        // Получаем пост по id и привязываем информацию о пользователе и разделе
        $post = Post::with('user')->with(['section:id,title','likes'=> function($query) use ($user) {
            $query->where('users.id', $user->id);
        }, 'favorites' => function($query) use ($user) {
            $query->where('users.id', $user->id);
        }])->find($id);
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
        if ($user) {
            // Проверяем, существует ли пост в избранном пользователя
            $favorite = Favorite::where('user_id', $user->id)
                ->where('favoritable_id', $post->id)
                ->where('favoritable_type', Post::class)
                ->first();
            // Если есть, то помечаем поле, как отмеченное
            if ($favorite) {
                $post->isFavorite = true;
            } // Если нету, то помечаем поле, как неотмеченное
            else {
                $post->isFavorite = false;
            }

            // Проверяем, есть ли лайк на посте
            $like = Like::where('user_id', $user->id)
                ->where('liketable_id', $post->id)
                ->where('liketable_type', Post::class)
                ->first();
            // Если есть, то помечаем поле, как отмеченное
            if ($like) {
                $post->isLike = true;
            } // Если нету, то помечаем поле, как неотмеченное
            else {
                $post->isLike = false;
            }
        } // Если пользователь неавторизован
        else {
            $post->isFavorite = false;
            $post->isLike = false;
        }

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
            return response()->json([
                'status' => config('app.error_status'),
                'message' => $validator->errors()->all()
            ], config('app.error_status'));
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
