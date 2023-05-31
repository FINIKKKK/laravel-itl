<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Like extends Model {
    use HasFactory;

    protected $guarded = false;

    // Поьзователь, у которого есть эти понравившиеся элементы
    public function user() {
        return $this->belongsTo(User::class);
    }

    // Понравившееся
    public function likeable() {
        return $this->morphTo();
    }
}
