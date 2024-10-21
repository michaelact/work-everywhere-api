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

        User::firstOrCreate(['email' => 'admin@admin.com'], ['name' => 'Admin', 'password' => bcrypt('secretadmin')])->assignRole('admin');
    }
}
