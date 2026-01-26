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
            'password'  => bcrypt('asdf'),
            ])->syncRoles('pimpinan universitas','dosen');

        User::create([
            'username'  => 'operatoruniversitas1',
            'name'      => 'operatoruniversitas 01',
            'email'     => 'operatoruniversitas01@unsil.ac.id',
            'password'  => bcrypt('asdf'),
            ])->assignRole('operator universitas');
        Permission::create(['name' => 'access universitas dashboard'])->syncRoles('pimpinan universitas', 'operator universitas');

        // akun Fakultas
        User::create([
            'username'  => 'dekan1',
            'name'      => 'dekan 01',
            'email'     => 'dekan01@unsil.ac.id',
            'password'  => bcrypt('asdf'),
        ])->syncRoles('pimpinan fakultas','dosen');

        User::create([
            'username'  => 'operatorfakultas1',
            'name'      => 'operatorfakultas 01',
            'email'     => 'operatorfakultas01@unsil.ac.id',
            'password'  => bcrypt('asdf'),
        ])->assignRole('operator fakultas');
        Permission::create(['name' => 'access fakultas dashboard'])->syncRoles('pimpinan fakultas', 'operator fakultas');

        // akun KaProdi
        $kaprodiuser = User::create([
            'username'  => 'kaprodi1',
            'name'      => 'kaprodi 01',
            'email'     => 'kaprodi01@unsil.ac.id',
            'password'  => bcrypt('asdf'),
        ])->syncRoles('pimpinan prodi','dosen');

        User::create([
            'username'  => 'operatorprodi1',
            'name'      => 'operatorprodi 01',
            'email'     => 'operatorprodi01@unsil.ac.id',
            'password'  => bcrypt('asdf'),
        ])->assignRole('operator prodi');
        Permission::create(['name' => 'access prodi dashboard'])->syncRoles('pimpinan prodi', 'operator prodi');

        // akun Dosen
        $dosenuser = User::create([
            'username'  => 'dosen1',
            'name'      => 'Dosen 01',
            'email'     => 'dosen01@unsil.ac.id',
            'password'  => bcrypt('asdf'),
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

        // data kurikulum
        $kurikulum = $prodimat->kurikulums()->create([
            'nama'=>'Kurikulum OBE 2025',
            'kode'=>'mat-OBE-2025',
            'deskripsi'=>'Kurikulum berbasis OBE untuk program studi Pendidikan Matematika Universitas Siliwangi Tahun 2025',
            'status_aktif'=>true,
        ]);
        $kurikulum1 = $prodimat->kurikulums()->create([
            'nama'=>'Kurikulum OBE 2023',
            'kode'=>'mat-OBE-2023',
            'deskripsi'=>'Kurikulum berbasis OBE untuk program studi Pendidikan Matematika Universitas Siliwangi Tahun 2025',
            'status_aktif'=>false,
        ]);

        // data profil lulusan
        $profil1 = $kurikulum->profils()->create([
            'nama'=>'Pendidik Matematika',
            'deskripsi'=>'Orang yang melakukan proses pengubahan sikap dan perilaku seseorang atau kelompok orang dalam usaha mendewasakan manusia melalui upaya pengajaran, bimbingan dan latihan di bidang matematika dengan menguasai materi matematika (Content Knowledge), pedagogik (Pedagogical Knowledge) dan teknologi (Technological Knowledge)',
        ]);
        $profil2 = $kurikulum->profils()->create([
            'nama'=>'Peneliti',
            'deskripsi'=>'Orang yang menguasai konsep teoritis penelitian pendidikan matematika dan terampil dalam menyelesaikan masalah secara prosedural dalam kehidupan sehari-hari',
        ]);
        $profil3 = $kurikulum->profils()->create([
            'nama'=>'Edupreneur',
            'deskripsi'=>'Orang yang memiliki jiwa kewirausahaan untuk memecahkan permasalahan dan mampu beradaptasi, baik dalam pembelajaran maupun dalam kehidupan sehari-hari',
        ]);

        // data indikator dari profil
        $profil1->profil_indikators()->create([
            'nama'=>'Menguasai konsep teoritis tentang konsep-konsep dasar matematika'
        ]);
        $profil1->profil_indikators()->create([
            'nama'=>'Menguasai dan mengaplikasikan strategi dan metode pembelajaran dasar yang efektif untuk menyampaikan materi matematika'
        ]);
        $profil1->profil_indikators()->create([
            'nama'=>'Mampu memanfaatkan teknologi untuk mendukung proses dan evaluasi pembelajaran'
        ]);
        $profil1->profil_indikators()->create([
            'nama'=>'Mampu mendesain pengelolaan kelas yang baik untuk terciptanya lingkungan belajar yang kondusif'
        ]);
        $profil2->profil_indikators()->create([
            'nama'=>'Menguasai konsep pengumpulan, pengolahan, analisis, penyajian, dan interpretasi data yang dilakukan secara sistematis dan objektif'
        ]);
        $profil2->profil_indikators()->create([
            'nama'=>'Memiliki keterampilan literasi informasi untuk mendukung topik penelitiannya'
        ]);
        $profil2->profil_indikators()->create([
            'nama'=>'Mampu menyusun laporan penelitian dan artikel ilmiah yang mendukung penerapan teori-teori pendidikan matematika'
        ]);
        $profil3->profil_indikators()->create([
            'nama'=>'Menguasai konsep teoritis tentang konsep-konsep dasar kewirausahaan untuk menghasilkan ide-ide kreatif dan inovatif dalam pembelajaran'
        ]);
        $profil3->profil_indikators()->create([
            'nama'=>'Mampu merancang produk atau layanan pendidikan yang inovatif'
        ]);
        $profil3->profil_indikators()->create([
            'nama'=>'Mampu mengomunikasikan hasil rancangan produk atau layanan pendidikan secara sistematis untuk perbaikan pembelajaran'
        ]);
    }
}
