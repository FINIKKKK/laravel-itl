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

    // Автор комментария
    public function author() {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Пост, у которого был оставлен этот комментарий
    public function post() {
        return $this->belongsTo(Post::class);
    }

    // Родтельский комментарий, у которого был оставлен этот комментарий
    public function replyUser() {
        return $this->belongsTo(User::class);
    }

    // Лайки у этого комментария
    public function liked() {
        return $this->morphToMany(User::class, 'likeable', 'likes');
    }
}
