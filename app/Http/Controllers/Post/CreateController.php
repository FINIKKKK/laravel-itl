<?php

namespace App\Http\Controllers\Post;

use App\Models\Post;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CreateController extends Controller
{
    public function create(Request $req)
    {
        $user = auth()->guard('api')->user();
        $post = Post::create([
            'title' => $req->title,
            'body' => json_encode($req->body),
            'user_id' => $user->id,
        ]);
        return $post;
    }
}
