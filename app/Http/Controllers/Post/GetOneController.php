<?php

namespace App\Http\Controllers\Post;

use App\Http\Controllers\Controller;
use App\Models\Post;

class GetOneController extends Controller {
    public function getOne($id) {
        $post = Post::find($id);
        return $post;
    }
}
