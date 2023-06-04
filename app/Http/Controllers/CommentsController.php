<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Favorite;
use App\Models\Like;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CommentsController extends BaseController {
    /**
     * Создание комментария
     */
    public function create(Request $req) {
        // Проверяем данные запроса
        $validator = Validator::make($req->all(), [
            'text' => 'required|string|min:5|max:250',
            'post_id' => 'required|integer',
            'replyUser' => 'integer',
        ]);
        // Прокидываем ошибки, если данные не прошли валидацию
        if ($validator->fails()) {
            return $this->validationErrors($validator);
        }

        // Проверяем есть ли пост
        $post = Post::find($req->get('post_id'));
        if (!$post) {
            return $this->response('Пост не найден', true, true);
        }

        $replyUser = User::find($req->get('replyUser'));
        if ($req->get('replyUser') && !$replyUser) {
            return $this->response('Пользователь не найден', true, true);
        }

        // Создаем комментарий и подгружаем информацию об авторе комментария
        $comment = Comment::create([
            'text' => $req->get('text'),
            'post_id' => $req->get('post_id'),
            'user_id' => $req->user()->id,
            'reply_user_id' => $req->get('replyUser')
        ])->load('author');

        // Возвращаем комментарий
        return $this->response($comment, false, false);
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
            return $this->validationErrors($validator);
        }

        // Получаем пользователя
        $user = $req->user();

        // Получаем только те комментарии, которые являются родителями
        // + Определенного поста
        // + Привязываем информацио об авторе
        // + Сортируем по дате (сначала новые)
        $comments = Comment::where('post_id', $req->get('post_id'))
            ->where('reply_user_id')
            ->with([
                'author:id,firstName,lastName,avatar',
                'liked' => function ($query) use ($user) {
                    if ($user) {
                        $query->where('users.id', $user->id);
                    }
                },
            ])
            ->orderBy('created_at', 'desc')
            ->get();

        // Пробегаймся по комментриям
        foreach ($comments as $comment) {
            // Проверяем, есть ли лайк на посте
            $comment->isLike = ($user && $comment->liked->count()) ? true : false;

            // Добавляем поле количество лайков
            $likesCount = Like::where('likeable_id', $comment->id)
                ->where('likeable_type', $comment::class)
                ->count();
            $comment->likesCount = $likesCount;
        }

        // Убираем поле likes
        $comments->makeHidden(['liked']);

        // Возвращаем комментарии
        return $this->response($comments, false, false);
    }


    /**
     * Обновление комментария по id
     */
    public function update(Request $req, $id) {
        // Проверяем есть ли комментарий
        $comment = Comment::find($id);
        if (!$comment) {
            return $this->response('Комментарий не найден', true, true);
        }

        // Проверяем данные запроса
        $validator = Validator::make($req->all(), [
            'text' => 'string|min:5|max:250',
        ]);
        // Прокидываем ошибки, если данные не прошли валидацию
        if ($validator->fails()) {
            return $this->validationErrors($validator);
        }

        // Обновляем комментарий
        $comment->update([
            'text' => $req->get('text'),
        ]);

        // Возвращаем обновленный комментарий
        return $this->response($comment, false, false);
    }


    /**
     * Удаление комментария по id
     */
    public function delete($id) {
        // Проверяем есть ли комментарий
        $comment = Comment::find($id);
        if (!$comment) {
            return $this->response('Комментарий не найден', true, true);
        }

        // Удаляем комментарий
        $comment->delete();

        // Возвращаем сообщение об успешном удалении комментария
        return $this->response('Комментарий успешно удален', false, true);
    }
}
