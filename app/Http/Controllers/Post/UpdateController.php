<?php

namespace App\Http\Controllers\Post;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;

class UpdateController extends Controller {
    public function update(Request $req, $id) {
        $post = Post::findOrFail($id);
        $post->update($req->all());
        return $post;
    }
}
