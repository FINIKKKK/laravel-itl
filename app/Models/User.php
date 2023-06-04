<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Comment;
use App\Models\Company;
use App\Models\Post;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject {
    use HasApiTokens, HasFactory, Notifiable;

    protected $guarded = false;

    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function getJWTIdentifier() {
        return $this->getKey();
    }

    public function getJWTCustomClaims() {
        return [];
    }

    public function hasRoleInCompany($roleIds, $companyId) {
        return $this->companies()->where('company_id', $companyId)
            ->wherePivotIn('role_id', $roleIds)
            ->exists();
    }

    public function roles() {
        return $this->belongsToMany(Role::class, 'user_company', 'user_id', 'role_id')
            ->withPivot('company_id')
            ->withTimestamps();
    }


    // Компании пользователя
    public function companies() {
        return $this->belongsToMany(Company::class, 'user_company', 'user_id', 'company_id')->withPivot('role_id');
    }

    // Разделы пользователя
    public function sections() {
        return $this->hasMany(Section::class);
    }

    // Посты пользователя
    public function posts() {
        return $this->hasMany(Post::class);
    }

    // Комментарии пользователя
    public function comments() {
        return $this->hasMany(Comment::class);
    }
}
