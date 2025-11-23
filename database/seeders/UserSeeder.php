<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use App\Models\Prodi;
use App\Models\JoinProdiUser;
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
        Role::create(['name' => 'operator universitas']);
        Role::create(['name' => 'pimpinan fakultas']);
        Role::create(['name' => 'operator fakultas']);
        Role::create(['name' => 'pimpinan prodi']);
        Role::create(['name' => 'operator prodi']);
        Role::create(['name' => 'dosen']);

        User::create([
            'username'  => 'rektor1',
            'name'      => 'rektor 01',
            'email'     => 'rektor01@unsil.ac.id',
            'password'  => bcrypt('1234'),
            ])->syncRoles('pimpinan universitas','dosen');

        User::create([
            'username'  => 'operatoruniversitas1',
            'name'      => 'operatoruniversitas 01',
            'email'     => 'operatoruniversitas01@unsil.ac.id',
            'password'  => bcrypt('1234'),
            ])->assignRole('operator universitas');
        Permission::create(['name' => 'access universitas dashboard'])->syncRoles('pimpinan universitas', 'operator universitas');

        // akun Fakultas
        User::create([
            'username'  => 'dekan1',
            'name'      => 'dekan 01',
            'email'     => 'dekan01@unsil.ac.id',
            'password'  => bcrypt('1234'),
        ])->syncRoles('pimpinan fakultas','dosen');

        User::create([
            'username'  => 'operatorfakultas1',
            'name'      => 'operatorfakultas 01',
            'email'     => 'operatorfakultas01@unsil.ac.id',
            'password'  => bcrypt('1234'),
        ])->assignRole('operator fakultas');
        Permission::create(['name' => 'access fakultas dashboard'])->syncRoles('pimpinan fakultas', 'operator fakultas');

        // akun KaProdi
        $kaprodiuser = User::create([
            'username'  => 'kaprodi1',
            'name'      => 'kaprodi 01',
            'email'     => 'kaprodi01@unsil.ac.id',
            'password'  => bcrypt('1234'),
        ])->syncRoles('pimpinan prodi','dosen');

        User::create([
            'username'  => 'operatorprodi1',
            'name'      => 'operatorprodi 01',
            'email'     => 'operatorprodi01@unsil.ac.id',
            'password'  => bcrypt('1234'),
        ])->assignRole('operator prodi');
        Permission::create(['name' => 'access prodi dashboard'])->syncRoles('pimpinan prodi', 'operator prodi');

        // akun Dosen
        $dosenuser = User::create([
            'username'  => 'dosen1',
            'name'      => 'Dosen 01',
            'email'     => 'dosen01@unsil.ac.id',
            'password'  => bcrypt('1234'),
            ])->assignRole('dosen');
        Permission::create(['name' => 'access dosen dashboard'])->syncRoles('dosen');

        // sampel data Fakultas
        Prodi::create([
            'kode_unsil'=>'00',
            'kode_prodi'=>'21',
            'pt'=>'Universitas Siliwangi',
            'fakultas'=>'Fakultas Keguruan dan Ilmu Pendidikan',
            'nama'=>'FKIP',
            'alamat'=>'Jl. Siliwangi no. 24 Kota Tasikmalaya',
            'email'=>'fkip@unsil.ac.id',
            'website'=>'fkip.unsil.ac.id',
        ]);

        // sampel data Prodi
        $prodimat = Prodi::create([
            'kode_unsil'=>'2151',
            'pt'=>'Universitas Siliwangi',
            'fakultas'=>'Fakultas Keguruan dan Ilmu Pendidikan',
            'nama'=>'Pendidikan Matematika',
            'kode_prodi'=>'84202',
            'visi_misi'=>'Mengembangkan Keilmuan Pendidikan dan pembelajaran Matematika Melalui Transformative Learning dan Edupreneur Matematika untuk Menghasilkan Lulusan Unggul yang Berwawasan Kebangsaan dan Berkarakter Wirausaha',
            'jenjang'=>'S1',
            'gelar_lulusan'=>'Sarjana Pendidikan (S.Pd.)',
            'alamat'=>'Jl. Siliwangi no. 24 Kota Tasikmalaya',
            'email'=>'mat@unsil.ac.id',
            'website'=>'matematika.unsil.ac.id',
            'tahun_pendirian'=>'2014',
            'sk_pendirian'=>'Perpres No. 24 Tahun 2014',
            'tahun_akreditasi'=>'2024',
            'sk_akreditasi'=>'1552/SK/LAMDIK/Ak/S/X/2024',
        ]);

        JoinProdiUser::create([
            'prodi_id' => $prodimat->id,
            'user_id' => $kaprodiuser->id,
            'status' => 'Kaprodi Matematika',
        ]);
        JoinProdiUser::create([
            'prodi_id' => $prodimat->id,
            'user_id' => $dosenuser->id,
            'status' => 'Dosen matematika',
        ]);
    }
}
