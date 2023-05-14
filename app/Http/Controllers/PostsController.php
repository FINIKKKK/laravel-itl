<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;

class PostsController extends Controller
{
    public function create(Request $req) {
        $user = auth()->guard('api')->user();
        $post = Post::create([
            'title' => $req->title,
            'body' => json_encode($req->body),
            'user_id' => $user->id,
        ]);
        return $post;
    }

    public function getAll() {

        $posts = Post::with('user')->select('id', 'title', 'created_at', 'updated_at', 'user_id')->orderBy('created_at', 'desc')->get();
        return $posts;
    }


    public function getOne($id) {
        $post = Post::find($id);
        $post->body = json_decode($post->body, true);

        $user = User::find($post->user_id);
        $post->user = $user;
        return $post;
    }

    public function update(Request $req, $id) {
        $post = Post::findOrFail($id);
        $post->update($req->all());
        return $post;
    }

    public function delete($id) {
        $post = Post::findOrFail($id);
        Comment::where('post_id', $id)->delete();
        $post->delete();
        return response()->json(['message' => 'Пост успешно удален']);
    }
}
