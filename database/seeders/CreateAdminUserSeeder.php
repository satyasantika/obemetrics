<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use App\Models\Permission;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class CreateAdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::create([
                'name' => 'Admin',
                'username' => 'admin',
                'email' => 'admin@gmail.com',
                'password' => bcrypt('12341234')
            ]);

        $role = Role::create(['name' => 'admin']);
        $user->assignRole('admin');

        Permission::create(['name' => 'access admin dashboard'])->assignRole('admin');

        Permission::create(['name' => 'create users'])->assignRole('admin');
        Permission::create(['name' => 'read users'])->assignRole('admin');
        Permission::create(['name' => 'update users'])->assignRole('admin');
        Permission::create(['name' => 'delete users'])->assignRole('admin');

        Permission::create(['name' => 'create roles'])->assignRole('admin');
        Permission::create(['name' => 'read roles'])->assignRole('admin');
        Permission::create(['name' => 'update roles'])->assignRole('admin');
        Permission::create(['name' => 'delete roles'])->assignRole('admin');

        Permission::create(['name' => 'create permissions'])->assignRole('admin');
        Permission::create(['name' => 'read permissions'])->assignRole('admin');
        Permission::create(['name' => 'update permissions'])->assignRole('admin');
        Permission::create(['name' => 'delete permissions'])->assignRole('admin');

        Role::create(['name' => 'active-user']);
    }
}
