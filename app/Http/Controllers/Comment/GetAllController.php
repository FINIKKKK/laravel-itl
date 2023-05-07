<?php

namespace App\Http\Controllers\Comment;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use Illuminate\Http\Request;

class GetAllController extends Controller {
    public function getAll(Request $req) {
        if ($req->post_id) {
            $comments = Comment::where('post_id', $req->post_id)->get();
        } else {
            $comments = Comment::all();
        }
        return $comments;
    }
}