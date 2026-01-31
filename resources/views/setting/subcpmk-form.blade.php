@extends('layouts.setting-form')

@push('header')
    {{ $mk->id ? 'Edit' : 'Tambah' }} Data Sub Capaian Pembelajaran Mata Kuliah (CPMK)
    <a href="{{ route('home') }}" class="btn btn-primary btn-sm float-end"><i class="bi bi-arrow-left"></i> Kembali</a>
@endpush

@push('body')

<div class="card-body">
    {{-- identitas mk --}}
    <div class="row">
        <div class="col-md-3">Mata Kuliah</div>
        <div class="col"><strong>{{ $mk->nama }}</strong></div>
    </div>
    <div class="row">
        <div class="col-md-3">Kurikulum</div>
        <div class="col"><strong>{{ $mk->kurikulum->nama }}</strong></div>
    </div>
    <div class="row">
        <div class="col-md-3">Program Studi</div>
        <div class="col"><strong>{{ $mk->kurikulum->prodi->jenjang }} {{ $mk->kurikulum->prodi->nama }}</strong></div>
    </div>
    <hr>
    {{-- form BK --}}
    <form id="formAction" action="{{ $subcpmk->id ? route('mks.subcpmks.update',[$mk->id,$subcpmk->id]) : route('mks.subcpmks.store', $mk) }}" method="post">
        @csrf
        @if ($subcpmk->id)
            @method('PUT')
        @endif
        <input type="hidden" name="mk_id" value="{{ $mk->id }}">

        {{-- kode cpmk --}}
        <div class="row mb-3">
            <div class="col">
                <label for="join_cpl_cpmk_id" class="form-label"><strong>CPMK</strong><span class="text-danger">(*)</span></label>
                <select name="join_cpl_cpmk_id" class="form-control" id="join_cpl_cpmk_id">
                    <option value="">Pilih CPMK</option>
                    @foreach ($join_cpl_cpmks as $join_cpl_cpmk)
                        <option value="{{ $join_cpl_cpmk->id }}" @selected($subcpmk->join_cpl_cpmk_id == $join_cpl_cpmk->id)>{{ $join_cpl_cpmk->cpmk->kode }} - {{ $join_cpl_cpmk->cpmk->nama }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- kode subcpmk --}}
        <div class="row mb-3">
            <div class="col">
                <label for="kode" class="form-label"><strong>Kode</strong> Sub CPMK <span class="text-danger">(*)</span></label>
                <input type="text" placeholder="" value="{{ $subcpmk->kode }}" name="kode" class="form-control" id="kode">
            </div>
        </div>
        {{-- nama --}}
        <div class="row mb-3">
            <div class="col">
                <label for="nama" class="form-label"><strong>Nama</strong> Sub CPMK <span class="text-danger">(*)</span></label>
                <textarea name="nama" rows="3" class="form-control" id="nama" required>{{ $subcpmk->nama }}</textarea>
            </div>
        </div>
        {{-- kompetensi --}}
        <div class="row mb-3">
            {{-- kognitif --}}
            <div class="col">
                <label for="kompetensi_c" class="form-label">Kognitif</label>
                <select name="kompetensi_c" id="kompetensi_c" class="form-control">
                    <option value="">Pilih Kognitif</option>
                    <option value="C1" @selected($subcpmk->kompetensi_c == 'C1')>C1 - Mengingat</option>
                    <option value="C2" @selected($subcpmk->kompetensi_c == 'C2')>C2 - Memahami</option>
                    <option value="C3" @selected($subcpmk->kompetensi_c == 'C3')>C3 - Menerapkan</option>
                    <option value="C4" @selected($subcpmk->kompetensi_c == 'C4')>C4 - Menganalisis</option>
                    <option value="C5" @selected($subcpmk->kompetensi_c == 'C5')>C5 - Mengevaluasi</option>
                    <option value="C6" @selected($subcpmk->kompetensi_c == 'C6')>C6 - Menciptakan</option>
                </select>
            </div>
            {{-- kognitif --}}
            <div class="col">
                <label for="kompetensi_a" class="form-label">Afektif</label>
                <select name="kompetensi_a" id="kompetensi_a" class="form-control">
                    <option value="">Pilih Afektif</option>
                    <option value="A1" @selected($subcpmk->kompetensi_a == 'A1')>A1 - Menerima</option>
                    <option value="A2" @selected($subcpmk->kompetensi_a == 'A2')>A2 - Merespon</option>
                    <option value="A3" @selected($subcpmk->kompetensi_a == 'A3')>A3 - Menghargai</option>
                    <option value="A4" @selected($subcpmk->kompetensi_a == 'A4')>A4 - Mengorganisasikan</option>
                    <option value="A5" @selected($subcpmk->kompetensi_a == 'A5')>A5 - Karakterisasi Menurut Nilai</option>
                </select>
            </div>
            {{-- psikomotor --}}
            <div class="col">
                <label for="kompetensi_p" class="form-label">Psikomotor</label>
                <select name="kompetensi_p" id="kompetensi_p" class="form-control">
                    <option value="">Pilih Psikomotor</option>
                    <option value="P1" @selected($subcpmk->kompetensi_p == 'P1')>P1 - Meniru</option>
                    <option value="P2" @selected($subcpmk->kompetensi_p == 'P2')>P2 - Memanipulasi</option>
                    <option value="P3" @selected($subcpmk->kompetensi_p == 'P3')>P3 - Presisi</option>
                    <option value="P4" @selected($subcpmk->kompetensi_p == 'P4')>P4 - Artikulasi</option>
                    <option value="P5" @selected($subcpmk->kompetensi_p == 'P5')>P5 - Naturalisasi</option>
                </select>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col">
                <div class="accordion" id="accordionExample">
                    <div class="accordion-item bg-primary bg-opacity-10">
                        <h2 class="accordion-header">
                        <button class="accordion-button collapsed bg-primary bg-opacity-10" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="false" aria-controls="collapseOne">
                            Keterangan Kognitif
                        </button>
                        </h2>
                        <div id="collapseOne" class="accordion-collapse collapse" data-bs-parent="#accordionExample">
                        <div class="accordion-body">
                            <strong>C1 - Mengingat</strong>: <span class="text-primary">termasuk</span> Mengetahui ...... Misalnya: istilah, fakta, aturan, urutan, metoda, Melafalkan/melafazkan, Membaca, Memberi definisi, Memberi nama pada, Memilih, Menemukenali (identifikasi), Mengambil, Mengenali, Menggarisbawahi, Menghafal, Mengidentifikasikan, Mengingat kembali, Menjodohkan, Menuliskan, Menunjukkan, Menyatakan, Menyebutkan, Menyusun daftar
                            <br><strong>C2 - Memahami</strong>: <span class="text-primary">termasuk</span> Menterjemahkan, Menafsirkan, Memperkirakan, Menentukan ... Misalnya: metode, prosedur Memahami .... misalnya: konsep, kaidah, prinsip, kaitan antara, fakta, isi pokok. Mengartikan Menginterpretasikan ... misalnya: tabel, grafik, bagan, Membandingkan, Membedakan, Memberi contoh tentang, Membuat model, Membuktikan, Memetakan, Memparafrasakan, Memperkirakan, Memprediksi, Menafsirkan, Menampilkan, Menarik kesimpulan, Menceritakan, Mencocokkan, Mencontohkan, Menerangkan, Menerjemahkan, Mengabstraksi, Mengartikan, Mengategorikan, Mengekstrapolasi, Mengelompokkan, Mengembangkan, Menggantikan, Menggeneralisasi, Mengilustrasikan, Menginterpolasi, Menginterpretasikan, Mengklasifikasikan, Mengontraskan, Mengubah, Menguraikan, Menjelaskan, Menunjukkan, Menyadur, Menyarikan, Menyimpulkan, Meramalkan, Merangkum, Merepresentasi, Meringkas, Merumuskan
                            <br><strong>C3 - Menerapkan</strong>: <span class="text-primary">termasuk</span> Memecahkan masalah, Membuat bagan/grafik, Menggunakan .. misalnya: metoda, prosedur, konsep, kaidah, prinsip, Melaksanakan, Melakukan, Melengkapi, Membuktikan, Memperagakan, Memperhitungkan, Memproseskan, Mendemonstrasikan, Menemukan, Menentukan, Mengeksekusi, Menggunakan, Menghasilkan, Menghitung, Menghubungkan, Mengimplementasikan, Mengonsepkan, Menunjukkan, Menyesuaikan
                            <br><strong>C4 - Menganalisis</strong>: <span class="text-primary">termasuk</span> Mengenali kesalahan Memberikan .... misalnya: fakta-fakta, Menganalisis ... misalnya: struktur, bagian, hubungan, Memadukan, Membagi, Membandingkan, Membedakan, Membuat diagram/skema, Membuat garis besar, Memecahkan, Memerinci, Memfokuskan, Memilah, Memilih, Memisahkan, Mempertentangkan, Mendekonstruksi, Mendeskripsikan peran, Mendeteksi, Mendiagnosis, Mendiferensiasikan, Mendistribusikan, Menelaah, Menemukan koherensi, Menerima pendapat, Mengaitkan, Menganalisis, Mengatribusikan, Menghubungkan, Mengorganisasikan, Menguraikan, Menstrukturkan, Menunjukkan hubungan antara, Menyeleksi, Menyendirikan, Menyisihkan
                            <br><strong>C5 - Mengevaluasi</strong>: <span class="text-primary">termasuk</span> Menilai berdasarkan norma internal .... misalnya: hasil karya, mutu karangan, dll., Melukiskan, Membahas, Membedakan, Memberi argumentasi, Memberi saran, Membuktikan, Memeriksa, Memilih antara, Memonitor, Memperbandingkan, Mempertahankan, Memproyeksikan, Memutuskan, Memvalidasi, Menafsirkan, Mendeteksi, Mendukung, Mengecek, Mengevaluasi, Mengevaluir, Mengkritik, Mengoordinasi, Menguji, Menguraikan, Menilai, Menolak, Menyimpulkan, Menyokong, Merekomendasi
                            <br><strong>C6 - Menciptakan</strong>: <span class="text-primary">termasuk</span> Menghasilkan ... misalnya: klasifikasi, karangan, teori Menyusun .... misalnya: laporan, rencana, skema, program, proposal, Membangun, Membuat, Membuat Hipotesis, Membuat pola, Memproduksi, Menciptakan, Mendesain, Mengabstraksi, Mengarang, Mengatur, Mengkategorikan, Mengkombinasikan, Mengonstruksi, Menyimpulkan, Menyusun kembali, Merancang, Merangkaikan, Merekonstruksi, Merencanakan, Merumuskan
                        </div>
                        </div>
                    </div>
                    <div class="accordion-item bg-success bg-opacity-10">
                        <h2 class="accordion-header">
                        <button class="accordion-button collapsed bg-success bg-opacity-10" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                            Keterangan Afektif
                        </button>
                        </h2>
                        <div id="collapseTwo" class="accordion-collapse collapse" data-bs-parent="#accordionExample">
                        <div class="accordion-body">
                            <strong>A1 - Menerima</strong>: <span class="text-primary">termasuk</span>Menunjukkan ....... Misalnya: kesadaran, kemauan, perhatian. Mengakui ......, misalnya: perbedaan, kepentingan, Melanjutkan, Memberi, Memilih, Menanyakan, Menempatkan, Mengikuti, Menjawab, Menyatakan
                            <br><strong>A2 - Merespon</strong>: <span class="text-primary">termasuk</span>Mematuhi ........ mis.: peraturan, tuntutan, perintah. Berperan aktif ....., mis: di laboratorium, dalam diskusi, dalam kelompok, dalam organisasi, dalam kegiatan., Berlatih, Melaksanakan, Melaporkan, Membantu, Membawakan, Mempraktekkan, Menampilkan, Menawarkan diri, Mendatangi, Mendiskusikan, Menolong, Menyambut, Menyatakan setuju, Menyelesaikan, Menyesuaikan diri, Menyumbangkan
                            <br><strong>A3 - Menghargai</strong>: <span class="text-primary">termasuk</span>Menerima suatu nilai, menyukai, menyepakati. Menghargai ......... misal: karya seni, sumbangan ilmu, pendapt, gagasan dan saran, Ikut serta, Melaksanakan, Membedakan, Membela, Membenarkan, Membimbing, Memilih, Mengajak, Mengambil prakarsa, Menggabungkan diri, Mengikuti, Mengundang, Mengusulkan, Menolak, Menunjukkan, Menuntun, Menyatakan pendapat
                            <br><strong>A4 - Mengorganisasikan</strong>: <span class="text-primary">termasuk</span>Membentuk sistem nilai. Menangkap relasi antar nilai. Bertanggung jawab. Mengintegrasikan nilai., Berpegang pada, Melengkapi, Memodifikasi, Memperbandingkan, Mempertahankan, Mengaitkan, Mengatur, Menghubungkan, Mengintegrasikan, Mengkoordinir, Mengorganisasi, Mengubah, Menyamakan, Menyempurnakan, Menyesuaikan, Menyusun, Merangkai, Merumuskan
                            <br><strong>A5 - Karakterisasi Menurut Nilai</strong>: <span class="text-primary">termasuk</span>Menunjukkan ..... mis.: kepercayaan diri, disiplin pribadi, kesadaran moral. Mempertimbangkan. Melibatkan diri., Bertahan, Bertindak, Melayani, Membuktikan, Memperhatikan, Mempersoalkan, Mempertimbangkan, Mempraktekkan, Mengundurkan diri, Menunjukkan, Menyatakan
                        </div>
                        </div>
                    </div>
                    <div class="accordion-item bg-danger bg-opacity-10">
                        <h2 class="accordion-header">
                        <button class="accordion-button collapsed bg-danger bg-opacity-10" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                            Keterangan Psikomotor
                        </button>
                        </h2>
                        <div id="collapseThree" class="accordion-collapse collapse" data-bs-parent="#accordionExample">
                        <div class="accordion-body">
                            <strong>P1 - Meniru</strong>: <span class="text-primary">termasuk</span>Menafsirkan rangsangan (stimulus). Kepekaan terhadap rangsangan, Mematuhi, Membedakan, Membuat, Mempersiapkan, Mempraktekkan, Mencetak dengan pola, Mengikuti, Mengulangi, Menirukan, Menjiplak, Menunjukkan, Menyalin, Merakit, Mereplikasi
                            <br><strong>P2 - Memanipulasi</strong>: <span class="text-primary">termasuk</span>Menyiapkan diri secara fisik, Bereaksi, Kembali membuat, Melaksanakan,, Melakukan,, Memainkan, Memasang, Membangun, Membongkar, Memiaio, Memperbaiki, Mempersiapkan, Mempertunjukkan, Memprakarsai, Menanggapi, Mendemonstrasikan, Menerapkan, Mengawali, Menggunakan’, Mengoperasikan, Menyusun, Merakit, Merangkai
                            <br><strong>P3 - Presisi</strong>: <span class="text-primary">termasuk</span>Berkonsentrasi untuk menghasilkan ketepatan, Melakukan gerak dengan benar, Melakukan gerak dengan teliti, Melakukan gerak dengan terukur, Melengkapi, Memainkan, Membuat, Memposisikan, Mempraktekkan, Mencoba, Mengendalikan, Mengerjakan, Mengkalibrasi, Menunjukkan, Menyempurnakan
                            <br><strong>P4 - Artikulasi</strong>: <span class="text-primary">termasuk</span>Mengkaitkan berbagai ketrampilan. Bekerja berdasarkan pola, Beradaptasi, Koordinat,, Memasang, Membangun, Membongkar, Membuat variasi, Memodifikasi, Mempolakan, Mengadaptasikan berbagai gerak, Mengatasi, Mengatur, Mengembangkan, Menggabungkan, Mengintegrasikan, Mengombinasikan gerak, Merangkaikan, Merumuskan,
                            <br><strong>P5 - Naturalisasi</strong>: <span class="text-primary">termasuk</span>Menghasilkan karya cipta. Melakukan sesuatu dengan ketepatan tinggi, Melaksananakan, Melakukan, Melakukan gerak dengan cepat, Melakukan gerak dengan wajar, Melakukan gerak spontan, Memainkan, Membangun, Membuat, Mencipta menghasilkan karya, Menciptakan, Mendesain, Menentukan, Mengatasi, Mengelola, Mengerjakan, Menggunakan, Mengoperasikan, Mengorganisasi gerak, Menyelesaikan
                        </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        {{-- indikator --}}
        <div class="row mb-3">
            <div class="col">
                <label for="indikator" class="form-label">Indikator <span class="text-danger">(*)</span></label>
                <textarea name="indikator" rows="3" class="form-control" id="indikator" required>{{ $subcpmk->indikator }}</textarea>
            </div>
        </div>
        {{-- evaluasi --}}
        <div class="row mb-3">
            <div class="col">
                <label for="evaluasi" class="form-label">Evaluasi <span class="text-danger">(*)</span></label>
                <textarea name="evaluasi" rows="3" class="form-control" id="evaluasi" required>{{ $subcpmk->evaluasi }}</textarea>
            </div>
        </div>
        <div class="row mb-3">
            {{-- bobot --}}
            <div class="col">
                <label for="bobot" class="form-label">Bobot (%) <span class="text-danger">(*)</span></label>
                <input type="text" placeholder="" value="{{ $subcpmk->bobot }}" name="bobot" class="form-control" id="bobot" required>
            </div>
            {{-- waktu_penagihan --}}
            <div class="col">
                <label for="waktu_penagihan" class="form-label">Waktu Penagihan</label>
                <select name="waktu_penagihan" id="waktu_penagihan" class="form-control">
                    <option value="">Pilih Waktu Penagihan</option>
                    <option value="1" @selected($subcpmk->waktu_penagihan == '1')>Pertemuan ke-1</option>
                    <option value="2" @selected($subcpmk->waktu_penagihan == '2')>Pertemuan ke-2</option>
                    <option value="3" @selected($subcpmk->waktu_penagihan == '3')>Pertemuan ke-3</option>
                    <option value="4" @selected($subcpmk->waktu_penagihan == '4')>Pertemuan ke-4</option>
                    <option value="5" @selected($subcpmk->waktu_penagihan == '5')>Pertemuan ke-5</option>
                    <option value="6" @selected($subcpmk->waktu_penagihan == '6')>Pertemuan ke-6</option>
                    <option value="7" @selected($subcpmk->waktu_penagihan == '7')>Pertemuan ke-7</option>
                    <option value="8" @selected($subcpmk->waktu_penagihan == '8')>Pertemuan ke-8</option>
                    <option value="9" @selected($subcpmk->waktu_penagihan == '9')>Pertemuan ke-9</option>
                    <option value="10" @selected($subcpmk->waktu_penagihan == '10')>Pertemuan ke-10</option>
                    <option value="11" @selected($subcpmk->waktu_penagihan == '11')>Pertemuan ke-11</option>
                    <option value="12" @selected($subcpmk->waktu_penagihan == '12')>Pertemuan ke-12</option>
                    <option value="13" @selected($subcpmk->waktu_penagihan == '13')>Pertemuan ke-13</option>
                    <option value="14" @selected($subcpmk->waktu_penagihan == '14')>Pertemuan ke-14</option>
                    <option value="15" @selected($subcpmk->waktu_penagihan == '15')>Pertemuan ke-15</option>
                </select>
            </div>
        </div>
        <hr>
        {{-- submit Button --}}
        <div class="row mb-0">
            <div class="col">
                <button type="submit" for="formAction" class="btn btn-success btn-sm"><i class="bi bi-save"></i> Save</button>
                <a href="{{ route('mks.subcpmks.index', $mk) }}" class="btn btn-outline-secondary btn-sm float-end"><i class="bi bi-x-circle"></i> Close</a>
            </div>
        </div>
    </form>
</div>

@if ($subcpmk->id)
<form id="delete-form" action="{{ route('mks.subcpmks.destroy',[$mk->id,$subcpmk->id]) }}" method="POST">
    @csrf
    @method('DELETE')
    <hr>
    <button type="submit" for="delete-form" class="btn btn-outline-danger btn-sm float-end" onclick="return confirm('Yakin akan menghapus {{ $mk->name }}?');">
        <i class="bi bi-trash"></i>
    </button>
</form>
@endif
<span class="text-danger">(*) Wajib diisi.</span></label>
@endpush
