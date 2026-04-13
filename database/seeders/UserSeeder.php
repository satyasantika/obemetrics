<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use App\Models\Prodi;
use App\Models\Evaluasi;
use App\Models\Semester;
use App\Models\Permission;
use App\Models\ProdiUser;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // akun Universitas
        Role::create(['name' => 'pimpinan universitas']);
        Role::create(['name' => 'operator universitas']);
        Role::create(['name' => 'pimpinan fakultas']);
        Role::create(['name' => 'operator fakultas']);
        Role::create(['name' => 'pimpinan prodi']);
        Role::create(['name' => 'operator prodi']);
        Role::create(['name' => 'koordinator mk']);
        Role::create(['name' => 'dosen']);

        Permission::create(['name' => 'access fakultas dashboard'])->syncRoles('pimpinan fakultas', 'operator fakultas');

        Permission::create(['name' => 'access prodi dashboard'])->syncRoles('pimpinan prodi', 'operator prodi');

        Permission::create(['name' => 'access dosen dashboard'])->syncRoles('dosen');

        // data semester
        $semester20201 = Semester::create([
            'nama'=>'Semester Ganjil 2020/2021',
            'kode'=>'20201',
        ]);
        $semester20202 = Semester::create([
            'nama'=>'Semester Genap 2020/2021',
            'kode'=>'20202',
        ]);
        $semester20211 = Semester::create([
            'nama'=>'Semester Ganjil 2021/2022',
            'kode'=>'20211',
        ]);
        $semester20212 = Semester::create([
            'nama'=>'Semester Genap 2021/2022',
            'kode'=>'20212',
        ]);
        $semester20221 = Semester::create([
            'nama'=>'Semester Ganjil 2022/2023',
            'kode'=>'20221',
        ]);
        $semester20222 = Semester::create([
            'nama'=>'Semester Genap 2022/2023',
            'kode'=>'20222',
        ]);
        $semester20231 = Semester::create([
            'nama'=>'Semester Ganjil 2023/2024',
            'kode'=>'20231',
        ]);
        $semester20232 = Semester::create([
            'nama'=>'Semester Genap 2023/2024',
            'kode'=>'20232',
        ]);
        $semester20241 = Semester::create([
            'nama'=>'Semester Ganjil 2024/2025',
            'kode'=>'20241',
        ]);
        $semester20242 = Semester::create([
            'nama'=>'Semester Genap 2024/2025',
            'kode'=>'20242',
        ]);
        $semester20251 = Semester::create([
            'nama'=>'Semester Ganjil 2025/2026',
            'kode'=>'20251',
        ]);
        $semester20252 = Semester::create([
            'nama'=>'Semester Genap 2025/2026',
            'kode'=>'20252',
            'status_aktif'=>true,
        ]);

        // data evaluasi
        $evaluasi_uts = Evaluasi::create([
            'kode'=>'uts',
            'kategori'=>'Pengetahuan/Kognitif',
            'workcloud'=>'UTS',
            'nama'=>'Ujian Tengah Semester (UTS)',
        ]);
        $evaluasi_uas = Evaluasi::create([
            'kode'=>'uas',
            'kategori'=>'Pengetahuan/Kognitif',
            'workcloud'=>'UAS',
            'nama'=>'Ujian Akhir Semester (UAS)',
        ]);
        $evaluasi_quiz = Evaluasi::create([
            'kode'=>'quiz',
            'kategori'=>'Pengetahuan/Kognitif',
            'workcloud'=>'Quiz',
            'nama'=>'Quiz',
        ]);
        $evaluasi_tugas = Evaluasi::create([
            'kode'=>'tugas',
            'kategori'=>'Pengetahuan/Kognitif',
            'workcloud'=>'Tugas',
            'nama'=>'Tugas',
        ]);
        $evaluasi_proyek_individu = Evaluasi::create([
            'kode'=>'proyek_individu',
            'kategori'=>'Hasil Proyek/Studi Kasus',
            'workcloud'=>'Hasil Proyek/Studi Kasus',
            'nama'=>'Hasil Proyek Individu',
        ]);
        $evaluasi_proyek_kelompok = Evaluasi::create([
            'kode'=>'proyek_kelompok',
            'kategori'=>'Hasil Proyek/Studi Kasus',
            'workcloud'=>'Hasil Proyek/Studi Kasus',
            'nama'=>'Hasil Proyek Kelompok',
        ]);
        $evaluasi_studi_kasus = Evaluasi::create([
            'kode'=>'studi_kasus',
            'kategori'=>'Hasil Proyek/Studi Kasus',
            'workcloud'=>'Hasil Proyek/Studi Kasus',
            'nama'=>'Studi Kasus',
        ]);
        $evaluasi_partisipasi_individu = Evaluasi::create([
            'kode'=>'partisipasi_individu',
            'kategori'=>'Aktivitas Partisipatif',
            'workcloud'=>'Aktivitas Partisipatif',
            'nama'=>'Partisipasi Individu',
        ]);
        $evaluasi_partisipasi_kelompok = Evaluasi::create([
            'kode'=>'partisipasi_kelompok',
            'kategori'=>'Aktivitas Partisipatif',
            'workcloud'=>'Aktivitas Partisipatif',
            'nama'=>'Partisipasi Kelompok',
        ]);

    }
}
