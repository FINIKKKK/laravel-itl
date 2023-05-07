<?php

namespace App\Http\Controllers\Post;

use App\Http\Controllers\Controller;
use App\Models\Post;

class GetAllController extends Controller {
    public function getAll() {
        $posts = Post::all();
        return $posts;
    }
}
