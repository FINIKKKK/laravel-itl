<?php

namespace App\Http\Controllers\Comment;

use App\Http\Controllers\Controller;
use App\Models\Comment;

class DeleteController extends Controller {
    public function delete($id) {
        $comment = Comment::findOrFail($id);
        $comment->delete();
        return 'Комментарий успешно удален';
    }
}
