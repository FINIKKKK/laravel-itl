<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RolesTableSeeder extends Seeder {
    /**
     * Run the database seeds.
     */
    public function run(): void {
//        Role::create(['value' => config('app.roles.moderator.value'), 'name' => config('app.roles.moderator.name')]);
//        Role::create(['value' => config('app.roles.admin.value'), 'name' => config('app.roles.admin.name')]);
//        Role::create(['value' => config('app.roles.user.value'), 'name' => config('app.roles.user.name')]);
        Role::create(['value' => 'moderator', 'name' => 'Модератор']);
        Role::create(['value' => 'admin', 'name' => 'Администратор']);
        Role::create(['value' => 'user', 'name' => 'Пользователь']);
    }
}
