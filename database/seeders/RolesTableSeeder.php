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
        Role::create(['value' => config('app.roles.moderator.name')]);
        Role::create(['value' => config('app.roles.admin.name')]);
        Role::create(['value' => config('app.roles.user.name')]);
    }
}
