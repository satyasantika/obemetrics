<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // identitas program studi
        Schema::create('prodis', function (Blueprint $table) {
            $table->uuid('id')->primary('id');
            $table->string('kode_unsil')->nullable();
            $table->string('nama')->nullable();
            $table->string('singkat')->nullable();
            $table->string('mapel')->nullable();
            $table->string('pt')->nullable();
            $table->string('fakultas')->nullable();
            $table->string('kode_prodi')->nullable();
            $table->text('visi_misi')->nullable();
            $table->string('jenjang')->nullable();
            $table->string('gelar_lulusan')->nullable();
            $table->text('alamat')->nullable();
            $table->string('no_telepon')->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->string('tahun_pendirian')->nullable();
            $table->string('sk_pendirian')->nullable();
            $table->string('tahun_akreditasi')->nullable();
            $table->string('sk_akreditasi')->nullable();
            $table->string('tahun_internasional')->nullable();
            $table->string('sk_internasional')->nullable();
            $table->timestamps();
        });
        // user pada prodi
        Schema::create('join_prodi_users', function (Blueprint $table) {
            $table->uuid('id')->primary('id');
            $table->foreignUuid('prodi_id')->nullable()->constrained()->onUpdate('cascade')->nullOnDelete();
            $table->foreignUuid('user_id')->nullable()->constrained()->onUpdate('cascade')->nullOnDelete();
            $table->string('status')->nullable();
            $table->timestamps();
        });

        // semester
        Schema::create('semesters', function (Blueprint $table) {
            $table->uuid('id')->primary('id');
            $table->string('kode')->nullable();
            $table->string('nama')->nullable();
            $table->text('deskripsi')->nullable();
            $table->boolean('status_aktif')->default(0);
            $table->timestamps();
        });

        // jenis_kurikulum
        Schema::create('kurikulums', function (Blueprint $table) {
            $table->uuid('id')->primary('id');
            $table->string('nama')->nullable();
            $table->string('kode')->nullable();
            $table->text('deskripsi')->nullable();
            $table->boolean('status_aktif')->default(0);
            $table->foreignUuid('prodi_id')->nullable()->constrained()->onUpdate('cascade')->nullOnDelete();
            $table->timestamps();
        });

        // profil lulusan
        Schema::create('profils', function (Blueprint $table) {
            $table->uuid('id')->primary('id');
            $table->string('nama')->nullable();
            $table->text('deskripsi')->nullable();
            $table->foreignUuid('kurikulum_id')->nullable()->constrained()->onUpdate('cascade')->nullOnDelete();
            $table->timestamps();
        });

        // indikator profil lulusan
        Schema::create('profil_indikators', function (Blueprint $table) {
            $table->uuid('id')->primary('id');
            $table->text('nama')->nullable();
            $table->text('deskripsi')->nullable();
            $table->foreignUuid('profil_id')->nullable()->constrained()->onUpdate('cascade')->nullOnDelete();
            $table->timestamps();
        });

        // CPL
        Schema::create('cpls', function (Blueprint $table) {
            $table->uuid('id')->primary('id');
            $table->string('kode')->nullable();
            $table->text('nama')->nullable();
            $table->string('cakupan')->nullable();
            $table->foreignUuid('kurikulum_id')->nullable()->constrained()->onUpdate('cascade')->nullOnDelete();
            $table->timestamps();
        });
        // interaksi profil-cpl
        Schema::create('join_profil_cpls', function (Blueprint $table) {
            $table->uuid('id')->primary('id');
            $table->foreignUuid('profil_id')->nullable()->constrained()->onUpdate('cascade')->nullOnDelete();
            $table->foreignUuid('cpl_id')->nullable()->constrained()->onUpdate('cascade')->nullOnDelete();
            $table->foreignUuid('kurikulum_id')->nullable()->constrained()->onUpdate('cascade')->nullOnDelete();
            $table->timestamps();
        });
        // bahan kajian
        Schema::create('bks', function (Blueprint $table) {
            $table->uuid('id')->primary('id');
            $table->string('kode')->nullable();
            $table->text('nama')->nullable();
            $table->text('deskripsi')->nullable();
            $table->foreignUuid('kurikulum_id')->nullable()->constrained()->onUpdate('cascade')->nullOnDelete();
            $table->timestamps();
        });
        // interaksi cpl-bk
        Schema::create('join_cpl_bks', function (Blueprint $table) {
            $table->uuid('id')->primary('id');
            $table->foreignUuid('cpl_id')->nullable()->constrained()->onUpdate('cascade')->nullOnDelete();
            $table->foreignUuid('bk_id')->nullable()->constrained()->onUpdate('cascade')->nullOnDelete();
            $table->foreignUuid('kurikulum_id')->nullable()->constrained()->onUpdate('cascade')->nullOnDelete();
            $table->timestamps();
        });
        // mata kuliah
        Schema::create('mks', function (Blueprint $table) {
            $table->uuid('id')->primary('id');
            $table->string('kodemk')->nullable();
            $table->string('nama')->nullable();
            $table->integer('semester')->default(0);
            $table->integer('sks')->default(0);
            $table->integer('sks_teori')->default(0);
            $table->integer('sks_praktik')->default(0);
            $table->integer('sks_lapangan')->default(0);
            $table->text('deskripsi')->nullable();
            $table->foreignUuid('kurikulum_id')->nullable()->constrained()->onUpdate('cascade')->nullOnDelete();
            $table->timestamps();
        });
        // interaksi bk-mk
        Schema::create('join_bk_mks', function (Blueprint $table) {
            $table->uuid('id')->primary('id');
            $table->foreignUuid('bk_id')->nullable()->constrained()->onUpdate('cascade')->nullOnDelete();
            $table->foreignUuid('mk_id')->nullable()->constrained()->onUpdate('cascade')->nullOnDelete();
            $table->foreignUuid('kurikulum_id')->nullable()->constrained()->onUpdate('cascade')->nullOnDelete();
            $table->timestamps();
        });
        // interaksi mk-dosen
        Schema::create('join_mk_users', function (Blueprint $table) {
            $table->uuid('id')->primary('id');
            $table->foreignUuid('mk_id')->nullable()->constrained()->onUpdate('cascade')->nullOnDelete();
            $table->foreignUuid('user_id')->nullable()->constrained()->onUpdate('cascade')->nullOnDelete();
            $table->foreignUuid('kurikulum_id')->nullable()->constrained()->onUpdate('cascade')->nullOnDelete();
            $table->foreignUuid('semester_id')->nullable()->constrained()->onUpdate('cascade')->nullOnDelete();
            $table->boolean('koordinator')->default(0);
            $table->timestamps();
        });
        // cpmk
        Schema::create('cpmks', function (Blueprint $table) {
            $table->uuid('id')->primary('id');
            $table->foreignUuid('mk_id')->nullable()->constrained()->onUpdate('cascade')->nullOnDelete();
            $table->string('kode')->nullable();
            $table->text('nama')->nullable();
            $table->text('deskripsi')->nullable();
            $table->timestamps();
        });
        // interaksi cpl-cpmk (diambil dari tabel join_cpl_bks dan join_bk_mks)
        Schema::create('join_cpl_cpmks', function (Blueprint $table) {
            $table->uuid('id')->primary('id');
            $table->foreignUuid('join_cpl_bk_id')->nullable()->constrained()->onUpdate('cascade')->nullOnDelete();
            $table->foreignUuid('cpmk_id')->nullable()->constrained()->onUpdate('cascade')->nullOnDelete();
            $table->foreignUuid('mk_id')->nullable()->constrained()->onUpdate('cascade')->nullOnDelete();
            $table->timestamps();
        });
        // sub cpmk
        Schema::create('subcpmks', function (Blueprint $table) {
            $table->uuid('id')->primary('id');
            $table->string('kode')->nullable();
            $table->string('kompetensi_c')->nullable();
            $table->string('kompetensi_a')->nullable();
            $table->string('kompetensi_p')->nullable();
            $table->text('nama')->nullable();
            $table->text('indikator')->nullable();
            $table->text('evaluasi')->nullable();
            $table->double('bobot')->nullable();
            $table->foreignUuid('join_cpl_cpmk_id')->nullable()->constrained()->onUpdate('cascade')->nullOnDelete();
            $table->foreignUuid('semester_id')->nullable()->constrained()->onUpdate('cascade')->nullOnDelete();
            $table->timestamps();
        });
        // evaluasi
        Schema::create('evaluasis', function (Blueprint $table) {
            $table->uuid('id')->primary('id');
            $table->string('kode')->nullable();
            $table->string('nama')->nullable();
            $table->string('kategori')->nullable();
            $table->string('workcloud')->nullable();
            $table->text('deskripsi')->nullable();
            $table->timestamps();
        });
        // asesmen (penilaian) pada sub cpmk
        Schema::create('penugasans', function (Blueprint $table) {
            $table->uuid('id')->primary('id');
            $table->string('kode')->nullable();
            $table->string('nama')->nullable();
            $table->double('bobot')->nullable();
            $table->foreignUuId('mk_id')->nullable()->constrained()->onUpdate('cascade')->nullOnDelete();
            $table->foreignUuId('evaluasi_id')->nullable()->constrained()->onUpdate('cascade')->nullOnDelete();
            $table->foreignUuId('semester_id')->nullable()->constrained()->onUpdate('cascade')->nullOnDelete();
            $table->text('deskripsi')->nullable();
            $table->timestamps();
        });
        // SubCPMK pada tugas mata kuliah
        Schema::create('join_subcpmk_penugasans', function (Blueprint $table) {
            $table->uuid('id')->primary('id');
            $table->foreignUuid('subcpmk_id')->nullable()->constrained()->onUpdate('cascade')->nullOnDelete();
            $table->foreignUuid('penugasan_id')->nullable()->constrained()->onUpdate('cascade')->onDelete('cascade');
            $table->foreignUuid('mk_id')->nullable()->constrained()->onUpdate('cascade')->nullOnDelete();
            $table->double('bobot')->nullable()->default(100);
            $table->timestamps();
        });
        // identitas mahasiswa
        Schema::create('mahasiswas', function (Blueprint $table) {
            $table->uuid('id')->primary('id');
            $table->string('nim');
            $table->index('nim');
            $table->string('nama')->nullable();
            $table->string('angkatan')->nullable();
            $table->foreignUuid('prodi_id')->nullable()->constrained()->onUpdate('cascade')->nullOnDelete();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->timestamps();
        });
        // kontrak mata kuliah mahasiswa
        Schema::create('kontrak_mks', function (Blueprint $table) {
            $table->uuid('id')->primary('id');
            $table->string('kelas')->nullable();
            $table->foreignUuid('mahasiswa_id')->nullable()->constrained()->onUpdate('cascade')->nullOnDelete();
            $table->foreignUuid('mk_id')->nullable()->constrained()->onUpdate('cascade')->nullOnDelete();
            $table->foreignUuid('user_id')->nullable()->constrained()->onUpdate('cascade')->nullOnDelete(); // dosen pengampu
            $table->foreignUuid('semester_id')->nullable()->constrained()->onUpdate('cascade')->nullOnDelete();
            $table->double('nilai_angka')->nullable();
            $table->string('nilai_huruf')->nullable();
            $table->timestamps();
        });

         // penilaian mahasiswa pada penugasan
        Schema::create('nilais', function (Blueprint $table) {
            $table->uuid('id')->primary('id');
            $table->foreignUuid('mk_id')->nullable()->constrained()->onUpdate('cascade')->nullOnDelete();
            $table->foreignUuid('penugasan_id')->nullable()->constrained()->onUpdate('cascade')->nullOnDelete();
            $table->foreignUuid('mahasiswa_id')->nullable()->constrained()->onUpdate('cascade')->nullOnDelete();
            $table->foreignUuid('semester_id')->nullable()->constrained()->onUpdate('cascade')->nullOnDelete();
            $table->double('nilai')->nullable();
            $table->text('komentar')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // penilaian mahasiswa pada penugasan
        Schema::table('nilais', function (Blueprint $table) {
            $table->dropForeign('nilais_mk_id_foreign');
            $table->dropForeign('nilais_penugasan_id_foreign');
            $table->dropForeign('nilais_mahasiswa_id_foreign');
            $table->dropForeign('nilais_semester_id_foreign');
        });
        Schema::dropIfExists('nilais');
        // kontrak mata kuliah mahasiswa
        Schema::table('kontrak_mks', function (Blueprint $table) {
            $table->dropForeign('kontrak_mks_mahasiswa_id_foreign');
            $table->dropForeign('kontrak_mks_mk_id_foreign');
            $table->dropForeign('kontrak_mks_user_id_foreign');
        });
        Schema::dropIfExists('kontrak_mks');
        // mahasiswa
        Schema::table('mahasiswas', function (Blueprint $table) {
            $table->dropForeign('mahasiswas_prodi_id_foreign');
        });
        Schema::dropIfExists('mahasiswas');
        // set subcpmk pada penugasan
        Schema::table('join_subcpmk_penugasans', function (Blueprint $table) {
            $table->dropForeign('join_subcpmk_penugasans_penugasan_id_foreign');
            $table->dropForeign('join_subcpmk_penugasans_subcpmk_id_foreign');
            $table->dropForeign('join_subcpmk_penugasans_mk_id_foreign');
        });
        Schema::dropIfExists('join_subcpmk_penugasans');
        // penugasan
        Schema::table('penugasans', function (Blueprint $table) {
            $table->dropForeign('penugasans_mk_id_foreign');
            $table->dropForeign('penugasans_evaluasi_id_foreign');
            $table->dropForeign('penugasans_semester_id_foreign');
        });
        Schema::dropIfExists('penugasans');
        // evaluasi
        Schema::dropIfExists('evaluasis');
        // sub cpmk
        Schema::table('subcpmks', function (Blueprint $table) {
            $table->dropForeign('subcpmks_join_cpl_cpmk_id_foreign');
        });
        Schema::dropIfExists('subcpmks');
        // interaksi cpl-cpmk
        Schema::table('join_cpl_cpmks', function (Blueprint $table) {
            $table->dropForeign('join_cpl_cpmks_join_cpl_bk_id_foreign');
            $table->dropForeign('join_cpl_cpmks_cpmk_id_foreign');
            $table->dropForeign('join_cpl_cpmks_mk_id_foreign');
        });
        Schema::dropIfExists('join_cpl_cpmks');
        // cpmk
        Schema::table('cpmks', function (Blueprint $table) {
            $table->dropForeign('cpmks_mk_id_foreign');
        });
        Schema::dropIfExists('cpmks');
        // interaksi mk-user
        Schema::table('join_mk_users', function (Blueprint $table) {
            $table->dropForeign('join_mk_users_user_id_foreign');
            $table->dropForeign('join_mk_users_mk_id_foreign');
            $table->dropForeign('join_mk_users_kurikulum_id_foreign');
            $table->dropForeign('join_mk_users_semester_id_foreign');
        });
        Schema::dropIfExists('join_mk_users');
        // interaksi bk-mk
        Schema::table('join_bk_mks', function (Blueprint $table) {
            $table->dropForeign('join_bk_mks_bk_id_foreign');
            $table->dropForeign('join_bk_mks_mk_id_foreign');
            $table->dropForeign('join_bk_mks_kurikulum_id_foreign');
        });
        Schema::dropIfExists('join_bk_mks');
        // mata kuliah
        Schema::table('mks', function (Blueprint $table) {
            $table->dropForeign('mks_kurikulum_id_foreign');
        });
        Schema::dropIfExists('mks');
        // interaksi cpl-bk
        Schema::table('join_cpl_bks', function (Blueprint $table) {
            $table->dropForeign('join_cpl_bks_cpl_id_foreign');
            $table->dropForeign('join_cpl_bks_bk_id_foreign');
            $table->dropForeign('join_cpl_bks_kurikulum_id_foreign');
        });
        Schema::dropIfExists('join_cpl_bks');
        // bahan kajian
        Schema::table('bks', function (Blueprint $table) {
            $table->dropForeign('bks_kurikulum_id_foreign');
        });
        Schema::dropIfExists('bks');
        // interaksi profil-cpl
        Schema::table('join_profil_cpls', function (Blueprint $table) {
            $table->dropForeign('join_profil_cpls_profil_id_foreign');
            $table->dropForeign('join_profil_cpls_cpl_id_foreign');
            $table->dropForeign('join_profil_cpls_kurikulum_id_foreign');
        });
        Schema::dropIfExists('join_profil_cpls');
        // CPL
        Schema::table('cpls', function (Blueprint $table) {
            $table->dropForeign('cpls_kurikulum_id_foreign');
        });
        Schema::dropIfExists('cpls');
        // indikator profil lulusan
        Schema::table('profil_indikators', function (Blueprint $table) {
            $table->dropForeign('profil_indikators_profil_id_foreign');
        });
        Schema::dropIfExists('profil_indikators');
        // profil lulusan
        Schema::table('profils', function (Blueprint $table) {
            $table->dropForeign('profils_kurikulum_id_foreign');
        });
        Schema::dropIfExists('profils');
        // jenis_kurikulum
        Schema::table('kurikulums', function (Blueprint $table) {
            $table->dropForeign('kurikulums_prodi_id_foreign');
        });
        Schema::dropIfExists('kurikulums');
        // semester
        Schema::dropIfExists('semesters');
        // prodi_users
        Schema::table('join_prodi_users', function (Blueprint $table) {
            $table->dropForeign('join_prodi_users_prodi_id_foreign');
            $table->dropForeign('join_prodi_users_user_id_foreign');
        });
        Schema::dropIfExists('join_prodi_users');
        // identitas program studi
        Schema::dropIfExists('prodis');
    }
};
