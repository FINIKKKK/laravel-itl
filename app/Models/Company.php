<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Company extends Model
{
    use HasFactory;

    protected $guarded = false;

    public function owner() {
        return $this->belongsTo(User::class);
    }

    public function users() {
        return $this->belongsToMany(User::class, 'user_company', 'company_id', 'user_id')->withPivot('role_id');
    }
}
