<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Favorite extends Model {
    use HasFactory;

    protected $guarded = false;

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function favoritable() {
        return $this->morphTo();
    }
}
