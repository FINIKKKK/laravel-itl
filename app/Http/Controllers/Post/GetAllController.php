<?php

namespace App\Http\Controllers\Post;

use App\Http\Controllers\Controller;
use App\Models\Post;

class GetAllController extends Controller {
    public function getAll() {

        $posts = Post::with('user')->select('id', 'title', 'created_at', 'updated_at', 'user_id')->orderBy('created_at', 'desc')->get();
        return $posts;
    }
}
