<?php

namespace App\Http\Controllers\Comment;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use Illuminate\Http\Request;

class GetAllController extends Controller {
    public function getAll(Request $req) {
        $comments = Comment::whereNull('comment_id')->where('post_id', $req->post_id)->with('user')
            ->orderBy('created_at', 'desc')->get();

        foreach ($comments as $comment) {
            $children = Comment::where('comment_id', $comment->id)->with('user')->get();
            $comment->children = $children;
        }

        return $comments;
    }
}