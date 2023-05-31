<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Favorite extends Model {
    use HasFactory;

    protected $guarded = false;

    // Пользователь, у которого есть эти избранные элементы
    public function user() {
        return $this->belongsTo(User::class);
    }

    // Избранное
    public function favoritable() {
        return $this->morphTo();
    }
}
