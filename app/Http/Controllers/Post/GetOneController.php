<?php

namespace App\Http\Controllers\Post;

use App\Models\Post;
use App\Models\User;
use App\Http\Controllers\Controller;

class GetOneController extends Controller {
    public function getOne($id) {
        $post = Post::find($id);
        $post->body = json_decode($post->body, true);

        $user = User::find($post->user_id);
        $post->user = $user;
        return $post;
    }
}
