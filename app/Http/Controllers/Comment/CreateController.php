<?php

namespace App\Http\Controllers\Comment;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CreateController extends Controller {
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
}
