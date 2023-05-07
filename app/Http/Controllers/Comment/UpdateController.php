<?php

namespace App\Http\Controllers\Comment;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use Illuminate\Http\Request;

class UpdateController extends Controller {
    public function update(Request $req, $id) {
        $comment = Comment::findOrFail($id);
        $comment->update($req->all());
        return $comment;
    }
}
