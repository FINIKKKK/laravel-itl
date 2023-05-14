<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CommentsController extends Controller
{

    public function create(Request $req) {
        $validator = Validator::make($req->all(), [
            'text' => ['required', 'string', 'max:250'],
            'post_id' => ['integer'],
            'reply_id' => ['integer'],
        ]);
        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()], 400);
        }

        $post = Post::findOrFail($req->post_id);
        if (!$post) {
            return response()->json(['message' => 'Пост не найден'], 400);
        }

        $user = auth()->guard('api')->user();
        $comment = Comment::create([
            'text' => $req->text,
            'post_id' => $req->post_id,
            'user_id' => $user->id,
            'comment_id' => $req->reply_id,
        ]);
        $comment->load('user');
        return $comment;
    }


    public function getAll(Request $req) {
        $comments = Comment::whereNull('comment_id')->where('post_id', $req->post_id)->with('user')
            ->orderBy('created_at', 'desc')->get();

        foreach ($comments as $comment) {
            $children = Comment::where('comment_id', $comment->id)->with('user')->get();
            $comment->children = $children;
        }

        return $comments;
    }


    public function update(Request $req, $id) {
        $comment = Comment::findOrFail($id);
        $comment->update($req->all());
        return $comment;
    }

    public function delete($id) {
        $comment = Comment::findOrFail($id);
        $comment->delete();
        return 'Комментарий успешно удален';
    }
}
