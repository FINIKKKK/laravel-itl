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

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function comments() {
        return $this->hasMany(Comment::class);
    }

    public function section() {
        return $this->belongsTo(Section::class);
    }

    public function favorites() {
        return $this->morphToMany(User::class, 'favoritable', 'favorites');
    }

    public function likes() {
        return $this->morphToMany(User::class, 'liketable', 'likes');
    }
}
