<?php

namespace App\Http\Controllers\Post;

use App\Http\Controllers\Controller;
use App\Models\Post;

class DeleteController extends Controller {
    public function delete($id) {
        $post = Post::findOrFail($id);
        $post->delete();
        return 'Пост успешно удален';
    }
}
