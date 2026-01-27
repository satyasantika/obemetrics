<?php

namespace Database\Seeders;

use App\Models\Prodi;
use App\Models\Permission;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class obeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Permission::create(['name' => 'create prodis'])->assignRole('admin');
        Permission::create(['name' => 'read prodis'])->syncRoles('admin','operator prodi','pimpinan prodi');
        Permission::create(['name' => 'update prodis'])->syncRoles('admin','operator prodi','pimpinan prodi');
        Permission::create(['name' => 'delete prodis'])->assignRole('admin');

        Permission::create(['name' => 'create join prodi users'])->assignRole('admin');
        Permission::create(['name' => 'read join prodi users'])->assignRole('admin');
        Permission::create(['name' => 'update join prodi users'])->assignRole('admin');
        Permission::create(['name' => 'delete join prodi users'])->assignRole('admin');

        Permission::create(['name' => 'create kurikulums'])->assignRole('pimpinan prodi');
        Permission::create(['name' => 'read kurikulums'])->assignRole('pimpinan prodi');
        Permission::create(['name' => 'update kurikulums'])->assignRole('pimpinan prodi');
        Permission::create(['name' => 'delete kurikulums'])->assignRole('pimpinan prodi');

        Permission::create(['name' => 'create profils'])->assignRole('pimpinan prodi');
        Permission::create(['name' => 'read profils'])->assignRole('pimpinan prodi');
        Permission::create(['name' => 'update profils'])->assignRole('pimpinan prodi');
        Permission::create(['name' => 'delete profils'])->assignRole('pimpinan prodi');

        Permission::create(['name' => 'create profil indikators'])->assignRole('pimpinan prodi');
        Permission::create(['name' => 'read profil indikators'])->assignRole('pimpinan prodi');
        Permission::create(['name' => 'update profil indikators'])->assignRole('pimpinan prodi');
        Permission::create(['name' => 'delete profil indikators'])->assignRole('pimpinan prodi');

        Permission::create(['name' => 'create cpls'])->assignRole('pimpinan prodi');
        Permission::create(['name' => 'read cpls'])->assignRole('pimpinan prodi');
        Permission::create(['name' => 'update cpls'])->assignRole('pimpinan prodi');
        Permission::create(['name' => 'delete cpls'])->assignRole('pimpinan prodi');

        Permission::create(['name' => 'create bks'])->assignRole('pimpinan prodi');
        Permission::create(['name' => 'read bks'])->assignRole('pimpinan prodi');
        Permission::create(['name' => 'update bks'])->assignRole('pimpinan prodi');
        Permission::create(['name' => 'delete bks'])->assignRole('pimpinan prodi');

        Permission::create(['name' => 'read join profil cpls'])->assignRole('pimpinan prodi');
        Permission::create(['name' => 'update join profil cpls'])->assignRole('pimpinan prodi');

    }
}
