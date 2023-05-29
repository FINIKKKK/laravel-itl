<?php

namespace App\Models;

use App\Models\Comment;
use App\Models\User;
use App\Models\Section;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model {
    use HasFactory;

    protected $guarded = false;

    // Автор поста
    public function author() {
        return $this->belongsTo(User::class);
    }

    // Раздел у поста
    public function section() {
        return $this->belongsTo(Section::class);
    }

    // Комментарии у поста
    public function comments() {
        return $this->hasMany(Comment::class);
    }

    public function favorites() {
        return $this->morphToMany(User::class, 'favoritable', 'favorites');
    }

    public function likes() {
        return $this->morphToMany(User::class, 'liketable', 'likes');
    }
}
