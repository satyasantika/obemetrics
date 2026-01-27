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

        // data cpl prodi
        $cpl01 = $kurikulum->cpls()->create([
            'kode'=>'CPL01',
            'nama'=>"Menunjukkan sikap bertakwa kepada Tuhan Yang Maha Esa, menjunjung tinggi dan menginternalisasi nilai, norma dan etika akademik, menjadi warga negara yang baik, bertanggungjawab atas pekerjaan di bidang keahliannya secara mandiri serta unggul dalam aspek softskill, semangat kemandirian, kejuangan, dan kewirausahaan pada perannya di kehidupan bermasyarakat berbangsa dan bernegara.",
            'cakupan'=>'Universitas',
        ]);
        $cpl02 = $kurikulum->cpls()->create([
            'kode'=>'CPL02',
            'nama'=>'Mampu menerapkan pemikiran logis, kritis, sistematis, dan inovatif dalam konteks pengembangan atau implementasi ilmu pengetahuan dan teknologi yang memperhatikan dan menerapkan nilai humaniora yang sesuai dengan bidang keahliannya berdasarkan kaidah, tata cara dan etika ilmiah dalam rangka menghasilkan solusi, gagasan, desain atau kritik seni, menyusun deskripsi saintifik hasil kajiannya dalam bentuk skripsi atau laporan tugas akhir dan mengunggahnya dalam laman perguruan tinggi.',
            'cakupan'=>'Universitas',
        ]);
        $cpl03 = $kurikulum->cpls()->create([
            'kode'=>'CPL03',
            'nama'=>'Mampu mengambil keputusan secara tepat dalam konteks penyelesaian masalah di bidang keahliannya, berdasarkan hasil analisis informasi dan data secara mandiri, bermutu, dan terukur.',
            'cakupan'=>'Universitas',
        ]);
        $cpl04 = $kurikulum->cpls()->create([
            'kode'=>'CPL04',
            'nama'=>'Menunjukkan pemahaman mendalam tentang teori dan praktik merancang, melaksanakan, dan mengevaluasi pembelajaran berbasis Technological Pedagogical And Content Knowledge (TPACK) untuk mengembangkan keterampilan abad 21.',
            'cakupan'=>'Fakultas',
        ]);
        $cpl05 = $kurikulum->cpls()->create([
            'kode'=>'CPL05',
            'nama'=>'Menguasai dan mengaplikasikan konsep teoritis dengan memanfaatkan IPTEKS tentang keilmuan matematika yang diperlukan dalam meningkatkan kemampuan intelektual untuk berpikir secara mandiri dan kritis yang berkontribusi dalam peningkatan mutu kehidupan bermasyarakat, berbangsa, bernegara, dan kemajuan peradaban berdasarkan Pancasila.',
            'cakupan'=>'Program Studi',
        ]);
        $cpl06 = $kurikulum->cpls()->create([
            'kode'=>'CPL06',
            'nama'=>'Menguasai dan mengaplikasikan konsep teoritis serta mampu mendesain pembelajaran matematika yang diperlukan untuk merencanakan, melaksanakan, dan mengevaluasi pembelajaran yang inovatif dengan menginternalisasi nilai, norma, dan etika akademik.',
            'cakupan'=>'Program Studi',
        ]);
        $cpl07 = $kurikulum->cpls()->create([
            'kode'=>'CPL07',
            'nama'=>'Menguasai dan mengaplikasikan konsep teoritis untuk penguatan kompetensi lanjutan yang diperlukan untuk melanjutkan studi atau keahlian tambahan dalam peningkatan mutu kehidupan bermasyarakat.',
            'cakupan'=>'Program Studi',
        ]);
        $cpl08 = $kurikulum->cpls()->create([
            'kode'=>'CPL08',
            'nama'=>'Menguasai, mengkaji, dan mengaplikasikan konsep teoritis serta mendesain penelitian dan publikasi yang diperlukan untuk menyelesaikan masalah baik dalam pembelajaran maupun kehidupan sehari-hari dalam menginternalisasi nilai, norma, dan etika akademik.',
            'cakupan'=>'Program Studi',
        ]);
        $cpl09 = $kurikulum->cpls()->create([
            'kode'=>'CPL09',
            'nama'=>'Menguasai dan mengaplikasikan konsep teoritis tentang pengembangan jiwa kewirausahaan untuk menyelesaikan masalah baik dalam pembelajaran maupun kehidupan sehari-hari dengan bertanggungjawab dan menginternalisasi semangat kemandirian, kejuangan, serta kewirausahaan.',
            'cakupan'=>'Program Studi',
        ]);

        // data bk prodi
        $bk01 = $kurikulum->bks()->create([
            'kode'=>'BK01',
            'nama'=>"Sikap, nilai, pengetahuan, dan keterampilan kognitif",
            'deskripsi'=>"Menginternalisasi ajaran agama dalam setiap aspek kehidupan, baik dalam sikap, perilaku, maupun keputusan akademik, dengan menjadikan nilai-nilai spiritual sebagai landasan moral, serta menjunjung tinggi integritas akademik dan menghormati aturan serta norma yang berlaku. Sikap ini mencerminkan etika akademik yang baik dan tanggung jawab sebagai warga negara yang aktif berkontribusi bagi masyarakat. Tanggung jawab mandiri dan profesional dalam menjalankan profesi sesuai bidang keahlian menjadi keharusan, diiringi dengan penguasaan soft skill seperti komunikasi, kerja sama tim, kepemimpinan, dan manajemen waktu. Semua ini didukung oleh semangat kemandirian, kejuangan, dan kewirausahaan dalam menghadapi tantangan kehidupan bermasyarakat. Menerapkan pemikiran logis, kritis, sistematis, dan inovatif penting untuk mengembangkan kemampuan berpikir terstruktur dalam menganalisis, menyelesaikan masalah, serta menghasilkan solusi atau inovasi sesuai bidang keahlian. Pengintegrasian nilai-nilai humaniora dalam pengembangan ilmu pengetahuan dan teknologi memastikan setiap keputusan ilmiah mempertimbangkan aspek etika, sosial, dan budaya. Penguasaan metode ilmiah, termasuk penerapan kaidah, tata cara, dan etika ilmiah dalam penelitian, sangat esensial untuk menghasilkan deskripsi ilmiah terstruktur dalam bentuk skripsi atau laporan tugas akhir. Publikasi hasil penelitian di laman perguruan tinggi menjadi langkah penting sebagai komitmen terhadap penyebaran ilmu pengetahuan yang bermanfaat bagi publik.",
        ]);
        $bk02 = $kurikulum->bks()->create([
            'kode'=>'BK02',
            'nama'=>"Teori dan Praktik Pendidikan Inovatif Berbasis Teknologi",
            'deskripsi'=>"Materi tentang aljabar linear dan aplikasinya.",
        ]);
        $bk03 = $kurikulum->bks()->create([
            'kode'=>'BK03',
            'nama'=>"Perancangan, Pelaksanaan, dan Evaluasi Pembelajaran Digital",
            'deskripsi'=>"Desain Pembelajaran Digital dan Interaktif memadukan elemen multimedia dan alat interaktif dengan strategi komunikasi serta evaluasi berbasis teknologi untuk menciptakan pembelajaran yang dinamis dan partisipatif.",
        ]);
        $bk04 = $kurikulum->bks()->create([
            'kode'=>'BK04',
            'nama'=>"Pengembangan Keterampilan Abad 21",
            'deskripsi'=>"Penerapan keterampilan berpikir kritis dan kreatif untuk menganalisis dan memecahkan masalah faktual melalui serangkaian metode ilmiah guna menarik kesimpulan dan menemukan solusi inovatif",
        ]);
        $bk05 = $kurikulum->bks()->create([
            'kode'=>'BK05',
            'nama'=>"Keilmuan Matematika",
            'deskripsi'=>"Keilmuan Matematika mencakup konsep, teori, dan metode yang menjadi dasar dalam memahami, menganalisis, serta menyelesaikan berbagai permasalahan di bidang sains, teknik, ekonomi, dan bidang lainnya. Keilmuan ini berfokus pada struktur, pola, hubungan, dan perubahan yang dapat dimodelkan secara kuantitatif dan logis.",
        ]);
        $bk06 = $kurikulum->bks()->create([
            'kode'=>'BK06',
            'nama'=>"Pembelajaran Matematika",
            'deskripsi'=>"Pembelajaran Matematika mempelajari bagaimana konsep, teori, dan keterampilan matematika diajarkan dan dipelajari secara efektif, mencakup teori pendidikan, strategi pengajaran, serta pendekatan pedagogis yang bertujuan untuk meningkatkan pemahaman dan penerapan matematika dalam berbagai konteks pendidikan.",
        ]);
        $bk07 = $kurikulum->bks()->create([
            'kode'=>'BK07',
            'nama'=>"Penguatan Kompetensi Lanjutan",
            'deskripsi'=>"Penguatan Kompetensi Lanjutan memperdalam pemahaman teori matematika dan struktur matematika yang lebih kompleks serta mengembangkan strategi pembelajaran yang inovatif, berbasis riset, dan selaras dengan perkembangan teknologi.",
        ]);
        $bk08 = $kurikulum->bks()->create([
            'kode'=>'BK08',
            'nama'=>"Penelitian dan Publikasi",
            'deskripsi'=>"Berfokus pada pengembangan keterampilan dalam merancang, melaksanakan, dan mendiseminasikan hasil penelitian melalui publikasi ilmiah. Kajian ini bertujuan untuk membekali mahasiswa dengan pemahaman mendalam tentang metode penelitian, analisis data, serta strategi penulisan ilmiah yang berkualitas untuk dipublikasikan dalam jurnal atau konferensi ilmiah.",
        ]);
        $bk09 = $kurikulum->bks()->create([
            'kode'=>'BK09',
            'nama'=>"Pengembangan Jiwa Kewirausahaan",
            'deskripsi'=>"Membekali individu dengan keterampilan, mindset, dan strategi dalam membangun serta mengelola usaha secara inovatif dan berkelanjutan. Kajian ini mencakup aspek teoritis dan praktis kewirausahaan, termasuk identifikasi peluang bisnis, manajemen usaha, inovasi, serta penerapan teknologi dalam dunia usaha.",
        ]);

    }
}
