<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    use HasFactory;

    protected $guarded = false;


    public function company() {
        return $this->belongsTo(Company::class);
    }

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function posts() {
        return $this->hasMany(Post::class);
    }

    public function sections() {
        return $this->hasMany(Section::class);
    }
}
