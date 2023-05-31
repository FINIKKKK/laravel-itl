<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Section extends Model {
    use HasFactory;

    protected $guarded = false;

    // Автор раздела
    public function author() {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Компания, в которой находиться этот раздел
    public function company() {
        return $this->belongsTo(Company::class);
    }

    // Посты внутри этого раздела
    public function posts() {
        return $this->hasMany(Post::class);
    }

    // Дочерние разделы внутри этого раздлела
    public function sections() {
        return $this->hasMany(Section::class);
    }

    // Родительский раздел, к которому принадлежит этот раздел
    public function parentSection() {
        return $this->belongsTo(Section::class, 'parent_id');
    }

    public function favorites() {
        return $this->morphMany(Favorite::class, 'favoritable');
    }
}
