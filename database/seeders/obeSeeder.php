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

        Permission::create(['name' => 'create prodiusers'])->assignRole('admin');
        Permission::create(['name' => 'read prodiusers'])->assignRole('admin');
        Permission::create(['name' => 'update prodiusers'])->assignRole('admin');
        Permission::create(['name' => 'delete prodiusers'])->assignRole('admin');

        // sampel data Prodi
        Prodi::create([
            'kode_unsil'=>'21',
            'pt'=>'Universitas Siliwangi',
            'fakultas'=>'Fakultas Keguruan dan Ilmu Pendidikan',
            'nama'=>'FKIP',
            'alamat'=>'Jl. Siliwangi no. 24 Kota Tasikmalaya',
            'email'=>'fkip@unsil.ac.id',
            'website'=>'fkip.unsil.ac.id',
        ]);
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
    }
}
