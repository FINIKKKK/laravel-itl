<?php

namespace App\Models;

use App\Models\Post;
use App\Models\User;
use App\Models\Like;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model {
    use HasFactory;

    protected $guarded = false;

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function post() {
        return $this->belongsTo(Post::class);
    }

    public function parentComment() {
        return $this->belongsTo(Comment::class);
    }

    public function likes() {
        return $this->morphMany(Like::class, 'liketable');
    }
}
