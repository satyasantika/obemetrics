<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use App\Models\Permission;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Import Data Dosen
        // $csvData = fopen(base_path('/database/seeders/csvs/lectures.csv'), 'r');
        // $transRow = true;
        // while (($data = fgetcsv($csvData, 555, ',')) !== false) {
        //     if (!$transRow) {
        //         User::create([
        //             'username'  => $data[0],
        //             'name'      => $data[1],
        //             'phone'     => $data[2],
        //             'email'     => $data[3],
        //             'password' => bcrypt($data[4]),
        //             'initial'  => $data[5],
        //         ])->assignRole('dosen')->givePermissionTo('active');
        //     }
        //     $transRow = false;
        // }
        // fclose($csvData);

        // Import Data Mahasiswa
        // $csvData = fopen(base_path('/database/seeders/csvs/students.csv'), 'r');
        // $transRow = true;
        // while (($data = fgetcsv($csvData, 555, ',')) !== false) {
        //     if (!$transRow) {
        //         User::create([
        //             'username'  => $data[0],
        //             'name'      => $data[1],
        //             'phone'     => $data[2],
        //             'address'   => $data[3],
        //             'email'     => $data[4],
        //             'password' => bcrypt($data[5]),

        //         ])->assignRole('mahasiswa')->givePermissionTo('active');
        //     }
        //     $transRow = false;
        // }
        // fclose($csvData);

        // akun Universitas
        Role::create(['name' => 'pimpinan universitas']);
        User::create([
            'username'  => 'rektor1',
            'name'      => 'rektor 01',
            'email'     => 'rektor01@unsil.ac.id',
            'password'  => bcrypt('1234'),
            ])->assignRole('pimpinan universitas');

        Role::create(['name' => 'operator universitas']);
        User::create([
            'username'  => 'operatoruniversitas1',
            'name'      => 'operatoruniversitas 01',
            'email'     => 'operatoruniversitas01@unsil.ac.id',
            'password'  => bcrypt('1234'),
            ])->assignRole('operator universitas');
        Permission::create(['name' => 'access universitas dashboard'])->syncRoles('pimpinan universitas', 'operator universitas');

        // akun Fakultas
        Role::create(['name' => 'pimpinan fakultas']);
        User::create([
            'username'  => 'dekan1',
            'name'      => 'dekan 01',
            'email'     => 'dekan01@unsil.ac.id',
            'password'  => bcrypt('1234'),
        ])->assignRole('pimpinan fakultas');

        Role::create(['name' => 'operator fakultas']);
        User::create([
            'username'  => 'operatorfakultas1',
            'name'      => 'operatorfakultas 01',
            'email'     => 'operatorfakultas01@unsil.ac.id',
            'password'  => bcrypt('1234'),
        ])->assignRole('operator fakultas');
        Permission::create(['name' => 'access fakultas dashboard'])->syncRoles('pimpinan fakultas', 'operator fakultas');

        // akun KaProdi
        Role::create(['name' => 'pimpinan prodi']);
        User::create([
            'username'  => 'kaprodi1',
            'name'      => 'kaprodi 01',
            'email'     => 'kaprodi01@unsil.ac.id',
            'password'  => bcrypt('1234'),
        ])->assignRole('pimpinan prodi');
        Role::create(['name' => 'operator prodi']);
        User::create([
            'username'  => 'operatorprodi1',
            'name'      => 'operatorprodi 01',
            'email'     => 'operatorprodi01@unsil.ac.id',
            'password'  => bcrypt('1234'),
        ])->assignRole('operator prodi');
        Permission::create(['name' => 'access prodi dashboard'])->syncRoles('pimpinan prodi', 'operator prodi');

        // akun Dosen
        Role::create(['name' => 'dosen']);
        User::create([
            'username'  => 'dosen1',
            'name'      => 'Dosen 01',
            'email'     => 'dosen01@unsil.ac.id',
            'password'  => bcrypt('1234'),
            ])->assignRole('dosen');
        Permission::create(['name' => 'access dosen dashboard'])->syncRoles('dosen');


    }
}
