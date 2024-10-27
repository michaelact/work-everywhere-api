<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AdminPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::firstOrCreate(['name' => 'admin']);
        User::firstOrCreate(['email' => 'admin@example.com'], ['name' => 'Admin', 'password' => bcrypt('ApalagiSaya150')])->assignRole('admin');

        Role::firstOrCreate(['name' => 'member']);
        User::firstOrCreate(['email' => 'michael.4ct@gmail.com'], ['name' => 'Michael Act', 'password' => bcrypt('SangatTerlindungi150')])->assignRole('member');
    }
}
