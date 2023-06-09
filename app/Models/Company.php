<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Company extends Model {
    use HasFactory;

    protected $guarded = false;

    // Создатель компании
    public function owner() {
        return $this->belongsTo(User::class);
    }

    // Разделы в этой компании
    public function sections() {
        return $this->hasMany(Section::class);
    }

    // Посты в этой компании
    public function posts() {
        return $this->hasMany(Post::class);
    }

    // Пользователи в компании
    public function users() {
        return $this->belongsToMany(User::class, 'user_company', 'company_id', 'user_id')->withPivot('role_id');
    }
}
