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
            $table->timestamps();
        });

        // jenis_kurikulum
        Schema::create('kurikulums', function (Blueprint $table) {
            $table->uuid('id')->primary('id');
            $table->string('nama')->nullable();
            $table->string('kode')->nullable();
            $table->text('deskripsi')->nullable();
            $table->integer('status_aktif')->default(0);
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
            $table->integer('waktu_penagihan')->nullable();
            $table->foreignUuid('join_cpl_cpmk_id')->nullable()->constrained()->onUpdate('cascade')->nullOnDelete();
            $table->timestamps();
        });
        // // pertemuan
        // Schema::create('pertemuans', function (Blueprint $table) {
        //     $table->uuid('id')->primary('id');
        //     $table->integer('ke')->nullable();
        //     $table->foreignUuid('subcpmk_id')->nullable()->constrained()->onUpdate('cascade')->nullOnDelete();
        //     $table->text('materi')->nullable();
        //     $table->date('tanggal')->nullable();
        //     $table->time('jam_mulai')->nullable();
        //     $table->time('jam_selesai')->nullable();
        //     $table->text('dokumen')->nullable();
        //     $table->text('keterangan')->nullable();
        //     $table->timestamps();
        // });
        // // dosen pada pertemuan tertentu
        // Schema::create('join_pertemuan_dosens', function (Blueprint $table) {
        //     $table->uuid('id')->primary('id');
        //     $table->foreignUuId('pertemuan_id')->nullable()->constrained()->onUpdate('cascade')->nullOnDelete();
        //     $table->foreignUuid('user_id')->nullable()->constrained()->onUpdate('cascade')->nullOnDelete(); // dosen pengajar
        //     $table->timestamps();
        // });
        // // sub materi
        // Schema::create('join_pertemuan_submateris', function (Blueprint $table) {
        //     $table->uuid('id')->primary('id');
        //     $table->text('nama')->nullable();
        //     $table->foreignUuId('pertemuan_id')->nullable()->constrained()->onUpdate('cascade')->nullOnDelete();
        //     $table->timestamps();
        // });
        // // bentuk pembelajaran
        // Schema::create('bentuk_kuliahs', function (Blueprint $table) {
        //     $table->uuid('id')->primary('id');
        //     $table->string('nama')->nullable();
        //     $table->text('deskripsi')->nullable();
        //     $table->timestamps();
        // });
        // // bentuk pembelajaran satu pertemuan
        // Schema::create('join_pertemuan_bentuk_kuliahs', function (Blueprint $table) {
        //     $table->uuid('id')->primary('id');
        //     $table->foreignUuId('bentuk_kuliah_id')->nullable()->constrained()->onUpdate('cascade')->nullOnDelete();
        //     $table->foreignUuId('pertemuan_id')->nullable()->constrained()->onUpdate('cascade')->nullOnDelete();
        //     $table->text('deskripsi')->nullable();
        //     $table->timestamps();
        // });
        // // bentuk pembelajaran
        // Schema::create('bentuk_evaluasis', function (Blueprint $table) {
        //     $table->uuid('id')->primary('id');
        //     $table->string('nama')->nullable();
        //     $table->text('deskripsi')->nullable();
        //     $table->timestamps();
        // });
        // // evaluasi pembelajaran
        // Schema::create('join_pertemuan_evaluasis', function (Blueprint $table) {
        //     $table->uuid('id')->primary('id');
        //     $table->foreignUuId('bentuk_evaluasi_id')->nullable()->constrained()->onUpdate('cascade')->nullOnDelete();
        //     $table->foreignUuId('pertemuan_id')->nullable()->constrained()->onUpdate('cascade')->nullOnDelete();
        //     $table->text('deskripsi')->nullable();
        //     $table->timestamps();
        // });
        // // berkas pembelajaran
        // Schema::create('join_pertemuan_berkass', function (Blueprint $table) {
        //     $table->uuid('id')->primary('id');
        //     $table->text('nama')->nullable();
        //     $table->foreignUuId('pertemuan_id')->nullable()->constrained()->onUpdate('cascade')->nullOnDelete();
        //     $table->timestamps();
        // });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // // berkas pembelajaran
        // Schema::table('join_kuliah_berkass', function (Blueprint $table) {
        //     $table->dropForeign('join_kuliah_berkass_kuliah_id_foreign');
        // });
        // Schema::dropIfExists('join_kuliah_berkass');
        // // evaluasi pembelajaran
        // Schema::table('join_kuliah_evaluasis', function (Blueprint $table) {
        //     $table->dropForeign('join_kuliah_evaluasis_kuliah_id_foreign');
        //     $table->dropForeign('join_kuliah_evaluasis_bentuk_evaluasi_id_foreign');
        // });
        // Schema::dropIfExists('join_kuliah_evaluasis');
        // Schema::dropIfExists('bentuk_evaluasis');
        // // bentuk pembelajaran
        // Schema::table('join_kuliah_bentuks', function (Blueprint $table) {
        //     $table->dropForeign('join_kuliah_bentuks_kuliah_id_foreign');
        //     $table->dropForeign('join_kuliah_bentuks_bentuk_kuliah_id_foreign');
        // });
        // Schema::dropIfExists('join_kuliah_bentuks');
        // Schema::dropIfExists('bentuk_kuliahs');
        // // sub materi
        // Schema::table('join_kuliah_submateris', function (Blueprint $table) {
        //     $table->dropForeign('join_kuliah_submateris_kuliah_id_foreign');
        // });
        // Schema::dropIfExists('join_kuliah_submateris');
        // // dosen pertemuan
        // Schema::table('join_kuliah_dosens', function (Blueprint $table) {
        //     $table->dropForeign('join_kuliah_dosens_kuliah_id_foreign');
        // });
        // Schema::dropIfExists('join_kuliah_dosens');
        // // kuliah
        // Schema::table('kuliahs', function (Blueprint $table) {
        //     $table->dropForeign('kuliahs_subcpmk_id_foreign');
        // });
        // Schema::dropIfExists('kuliahs');
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
