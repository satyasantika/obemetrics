<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    if (Auth::check()) {
        return to_route('home');
    }

    return view('welcome');
});

Auth::routes(['register' => false]);
Route::any('/register', function () {
    return redirect('/');
});
Route::impersonate();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::middleware('auth')->group(function () {
    Route::get('/ruang-prodi', [App\Http\Controllers\HomeController::class, 'ruangProdi'])
        ->middleware('can:access prodi dashboard')
        ->name('ruang.prodi');
    Route::get('/ruang-dosen', [App\Http\Controllers\HomeController::class, 'ruangDosen'])
        ->middleware('can:access dosen dashboard')
        ->name('ruang.dosen');

    Route::get('/password/change', [App\Http\Controllers\Auth\PasswordChangeController::class, 'showChangePasswordGet'])->name('password.change');
    Route::post('/password/change', [App\Http\Controllers\Auth\PasswordChangeController::class, 'changePasswordPost'])->name('password.change');
    // Ruang Admin
    // Deprecated candidate: no internal named-route usage found. Keep temporarily for backward compatibility.
    Route::post('/users/{user}/resetpassword', [App\Http\Controllers\Auth\PasswordChangeController::class, 'resetPasswordPost'])->name('users.resetpassword');
    Route::post('/users/{user}/activation', [App\Http\Controllers\Setting\UserController::class, 'activation'])->name('users.activation');
    Route::resource('users', App\Http\Controllers\Setting\UserController::class)->except(['show']);
    Route::resource('roles', App\Http\Controllers\Setting\RoleController::class)->except(['show']);
    Route::resource('permissions', App\Http\Controllers\Setting\PermissionController::class)->except(['show']);
    // Deprecated candidate: edit flow has been replaced by modal-based management on roles.index.
    Route::resource('rolepermissions', App\Http\Controllers\Setting\RolePermissionController::class)->only('edit', 'update');
    Route::resource('userroles', App\Http\Controllers\Setting\UserRoleController::class)->only('edit', 'update');
    Route::resource('userpermissions', App\Http\Controllers\Setting\UserPermissionController::class)->only('edit', 'update');
    Route::resource('prodis', App\Http\Controllers\Setting\ProdiController::class);
    Route::resource('prodis.joinprodiusers', App\Http\Controllers\Setting\JoinProdiUserController::class);
    Route::resource('semesters', App\Http\Controllers\Setting\SemesterController::class);
    Route::resource('evaluasis', App\Http\Controllers\Setting\EvaluasiController::class);
    Route::resource('mahasiswas', App\Http\Controllers\Setting\MahasiswaController::class);
    Route::resource('kontrakmks', App\Http\Controllers\Setting\KontrakMkController::class);

    // Ruang Prodi
    Route::middleware('ensure.kurikulum.access')->group(function () {
        Route::resource('prodis.kurikulums', App\Http\Controllers\Prodi\KurikulumController::class)->except('index','show');
        Route::resource('kurikulums.profils', App\Http\Controllers\Prodi\ProfilController::class)->except('show');
        Route::resource('kurikulums.cpls', App\Http\Controllers\Prodi\CplController::class)->except('show');
        Route::resource('kurikulums.bks', App\Http\Controllers\Prodi\BkController::class)->except('show');
        Route::resource('kurikulums.mks', App\Http\Controllers\Prodi\MkController::class)->except('show');
        Route::resource('profils.profilindikators', App\Http\Controllers\Prodi\ProfilIndikatorController::class)->except('index','show');
        // Profil >< CPL
        Route::get('kurikulums/{kurikulum}/joinprofilcpls', [App\Http\Controllers\Prodi\JoinProfilCplController::class,'index'])->name('kurikulums.joinprofilcpls.index');
        Route::put('kurikulums/{kurikulum}/joinprofilcpls/{profil}/{cpl}', [App\Http\Controllers\Prodi\JoinProfilCplController::class, 'update'])->name('kurikulums.joinprofilcpls.update');
        // CPL >< BK
        Route::get('kurikulums/{kurikulum}/joincplbks', [App\Http\Controllers\Prodi\JoinCplBkController::class,'index'])->name('kurikulums.joincplbks.index');
        Route::put('kurikulums/{kurikulum}/joincplbks/{cpl}/{bk}', [App\Http\Controllers\Prodi\JoinCplBkController::class, 'update'])->name('kurikulums.joincplbks.update');
        // CPL >< MK
        Route::get('kurikulums/{kurikulum}/joincplmks', [App\Http\Controllers\Prodi\JoinCplMkController::class,'index'])->name('kurikulums.joincplmks.index');
        Route::put('kurikulums/{kurikulum}/joincplmks/{cpl}/{mk}', [App\Http\Controllers\Prodi\JoinCplMkController::class, 'update'])->name('kurikulums.joincplmks.update');
        // Asesmen CPL
        Route::get('kurikulums/{kurikulum}/rencana-asesmen', [App\Http\Controllers\Prodi\AsesmenCplController::class,'rencanaAsesmen'])->name('kurikulums.rencana-asesmen');
        Route::get('kurikulums/{kurikulum}/analisis-asesmen', [App\Http\Controllers\Prodi\AsesmenCplController::class,'analisisAsesmen'])->name('kurikulums.analisis-asesmen');
        Route::get('kurikulums/{kurikulum}/spyderweb-cpl', [App\Http\Controllers\Prodi\AsesmenCplController::class,'spyderwebCpl'])->name('kurikulums.spyderweb-cpl');
        Route::get('kurikulums/{kurikulum}/laporan-kmahasiswa', [App\Http\Controllers\Prodi\AsesmenCplController::class,'laporanMahasiswa'])->name('kurikulums.laporan-mahasiswa');
        // Dosen >< MK
        Route::resource('mks.users', App\Http\Controllers\Prodi\JoinMkUserController::class)
            ->only('index','update');
    });

    // Ruang Dosen
    Route::middleware('ensure.mk.access')->group(function () {
        // CPMK & SubCPMK
        Route::resource('mks.cpmks', App\Http\Controllers\Dosen\CpmkController::class)->except('show');
        Route::resource('mks.subcpmks', App\Http\Controllers\Dosen\SubCpmkController::class)->except('show');
        // CPL >< BK
        Route::get('mks/{mk}/joincplcpmks', [App\Http\Controllers\Dosen\JoinCplCpmkController::class,'index'])->name('mks.joincplcpmks.index');
        Route::put('mks/{mk}/joincplcpmks/{joincplbk}/{cpmk}', [App\Http\Controllers\Dosen\JoinCplCpmkController::class, 'update'])->name('mks.joincplcpmks.update');
        // Tugas Mata Kuliah
        Route::resource('mks.penugasans', App\Http\Controllers\Dosen\PenugasanController::class)->except('show');
        Route::get('mks/{mk}/joinsubcpmkpenugasans', [App\Http\Controllers\Dosen\JoinSubcpmkPenugasanController::class,'index'])->name('mks.joinsubcpmkpenugasans.index');
        Route::put('mks/{mk}/joinsubcpmkpenugasans/{subcpmk}/{penugasan}', [App\Http\Controllers\Dosen\JoinSubcpmkPenugasanController::class, 'update'])->name('mks.joinsubcpmkpenugasans.update');
        // Penilaian Mata Kuliah
        Route::put('mks/{mk}/nilais/live-update', [App\Http\Controllers\Dosen\NilaiController::class, 'liveUpdate'])->name('mks.nilais.live-update');
        Route::resource('mks.nilais', App\Http\Controllers\Dosen\NilaiController::class)->except('show');
        Route::get('mks/{mk}/workclouds/export-kelas', [App\Http\Controllers\Dosen\WorkcloudController::class, 'exportKelas'])->name('mks.workclouds.export-kelas');
        Route::resource('mks.workclouds', App\Http\Controllers\Dosen\WorkcloudController::class)->only('index');
        Route::resource('mks.achievements', App\Http\Controllers\Dosen\AchievementController::class)->only('index');
        Route::resource('mks.ketercapaians', App\Http\Controllers\Dosen\KetercapaianController::class)->only('index');
        Route::get('mks/{mk}/spyderweb', [App\Http\Controllers\Dosen\KetercapaianController::class, 'spyderWeb'])->name('mks.spyderweb');
        Route::get('mks/{mk}/laporan/download', [App\Http\Controllers\Dosen\KetercapaianController::class, 'downloadLaporanPdf'])->name('mks.laporan.download');
        Route::get('mks/{mk}/laporan', [App\Http\Controllers\Dosen\KetercapaianController::class, 'laporan'])->name('mks.laporan');
    });

    // Bulk Upload Nilai MK
    Route::middleware('ensure.mk.access')->group(function () {
        Route::get('settings/import/nilais/{mk}', [App\Http\Controllers\Bulk\ImportNilaiController::class, 'importNilaiForm'])->name('settings.import.nilais');
        Route::post('settings/import/nilais/{mk}', [App\Http\Controllers\Bulk\ImportNilaiController::class, 'importNilai'])->name('settings.import.nilais');
        Route::post('settings/import/nilais/{mk}/commit', [App\Http\Controllers\Bulk\ImportNilaiController::class, 'commitNilai'])->name('settings.import.nilais.commit');
        Route::get('settings/import/nilais/{mk}/template', [App\Http\Controllers\Bulk\ImportNilaiController::class, 'downloadTemplate'])->name('settings.import.nilais.template');
        Route::post('settings/import/nilais/{mk}/clear', [App\Http\Controllers\Bulk\ImportNilaiController::class, 'clearPreview'])->name('settings.import.nilais.clear');
    });

    // Bulk Upload Mahasiswa
    Route::get('settings/import/mahasiswas', [App\Http\Controllers\Bulk\ImportMahasiswaController::class, 'importMahasiswaForm'])->name('settings.import.mahasiswas');
    Route::post('settings/import/mahasiswas', [App\Http\Controllers\Bulk\ImportMahasiswaController::class, 'importMahasiswa'])->name('settings.import.mahasiswas');
    Route::post('settings/import/mahasiswas/commit', [App\Http\Controllers\Bulk\ImportMahasiswaController::class, 'commitMahasiswa'])->name('settings.import.mahasiswas.commit');
    Route::get('settings/import/mahasiswas/template', [App\Http\Controllers\Bulk\ImportMahasiswaController::class, 'downloadTemplate'])->name('settings.import.mahasiswas.template');
    Route::post('settings/import/mahasiswas/clear', [App\Http\Controllers\Bulk\ImportMahasiswaController::class, 'clearPreview'])->name('settings.import.mahasiswas.clear');

    // Bulk Upload Users
    Route::get('settings/import/users', [App\Http\Controllers\Bulk\ImportUserController::class, 'importUserForm'])->name('settings.import.users');
    Route::post('settings/import/users', [App\Http\Controllers\Bulk\ImportUserController::class, 'importUser'])->name('settings.import.users');
    Route::post('settings/import/users/commit', [App\Http\Controllers\Bulk\ImportUserController::class, 'commitUser'])->name('settings.import.users.commit');
    Route::get('settings/import/users/template', [App\Http\Controllers\Bulk\ImportUserController::class, 'downloadTemplate'])->name('settings.import.users.template');
    Route::post('settings/import/users/clear', [App\Http\Controllers\Bulk\ImportUserController::class, 'clearPreview'])->name('settings.import.users.clear');

    // Bulk Upload User Prodi
    Route::get('settings/import/joinprodiusers', [App\Http\Controllers\Bulk\ImportJoinProdiUserController::class, 'importJoinProdiUserForm'])->name('settings.import.joinprodiusers');
    Route::post('settings/import/joinprodiusers', [App\Http\Controllers\Bulk\ImportJoinProdiUserController::class, 'importJoinProdiUser'])->name('settings.import.joinprodiusers');
    Route::post('settings/import/joinprodiusers/commit', [App\Http\Controllers\Bulk\ImportJoinProdiUserController::class, 'commitJoinProdiUser'])->name('settings.import.joinprodiusers.commit');
    Route::get('settings/import/joinprodiusers/template', [App\Http\Controllers\Bulk\ImportJoinProdiUserController::class, 'downloadTemplate'])->name('settings.import.joinprodiusers.template');
    Route::post('settings/import/joinprodiusers/clear', [App\Http\Controllers\Bulk\ImportJoinProdiUserController::class, 'clearPreview'])->name('settings.import.joinprodiusers.clear');

    // Bulk Upload Pengampu Mata Kuliah
    Route::middleware('ensure.kurikulum.access')->group(function () {
        Route::get('settings/import/joinmkusers', [App\Http\Controllers\Bulk\ImportJoinMkUserController::class, 'importJoinMkUserForm'])->name('settings.import.joinmkusers');
        Route::post('settings/import/joinmkusers', [App\Http\Controllers\Bulk\ImportJoinMkUserController::class, 'importJoinMkUser'])->name('settings.import.joinmkusers');
        Route::post('settings/import/joinmkusers/commit', [App\Http\Controllers\Bulk\ImportJoinMkUserController::class, 'commitJoinMkUser'])->name('settings.import.joinmkusers.commit');
        Route::get('settings/import/joinmkusers/template', [App\Http\Controllers\Bulk\ImportJoinMkUserController::class, 'downloadTemplate'])->name('settings.import.joinmkusers.template');
        Route::post('settings/import/joinmkusers/clear', [App\Http\Controllers\Bulk\ImportJoinMkUserController::class, 'clearPreview'])->name('settings.import.joinmkusers.clear');
    });

    // Bulk Upload Kontrak MK
    Route::get('settings/import/kontrakmks', [App\Http\Controllers\Bulk\ImportKontrakMkController::class, 'importKontrakMkForm'])->name('settings.import.kontrakmks');
    Route::post('settings/import/kontrakmks', [App\Http\Controllers\Bulk\ImportKontrakMkController::class, 'importKontrakMk'])->name('settings.import.kontrakmks');
    Route::post('settings/import/kontrakmks/commit', [App\Http\Controllers\Bulk\ImportKontrakMkController::class, 'commitKontrakMk'])->name('settings.import.kontrakmks.commit');
    Route::get('settings/import/kontrakmks/template', [App\Http\Controllers\Bulk\ImportKontrakMkController::class, 'downloadTemplate'])->name('settings.import.kontrakmks.template');
    Route::post('settings/import/kontrakmks/clear', [App\Http\Controllers\Bulk\ImportKontrakMkController::class, 'clearPreview'])->name('settings.import.kontrakmks.clear');

    // Bulk Upload Data Kurikulum
    Route::middleware('ensure.kurikulum.access')->group(function () {
        Route::get('settings/import/kurikulum-master/{kurikulum}', [App\Http\Controllers\Bulk\ImportKurikulumMasterController::class, 'form'])->name('settings.import.kurikulum-master');
        Route::post('settings/import/kurikulum-master/{kurikulum}', [App\Http\Controllers\Bulk\ImportKurikulumMasterController::class, 'import'])->name('settings.import.kurikulum-master.upload');
        Route::post('settings/import/kurikulum-master/{kurikulum}/commit', [App\Http\Controllers\Bulk\ImportKurikulumMasterController::class, 'commit'])->name('settings.import.kurikulum-master.commit');
        Route::get('settings/import/kurikulum-master/{kurikulum}/template', [App\Http\Controllers\Bulk\ImportKurikulumMasterController::class, 'template'])->name('settings.import.kurikulum-master.template');
        Route::post('settings/import/kurikulum-master/{kurikulum}/clear', [App\Http\Controllers\Bulk\ImportKurikulumMasterController::class, 'clear'])->name('settings.import.kurikulum-master.clear');
    });

    // Bulk Upload Data MK
    Route::middleware('ensure.mk.access')->group(function () {
        Route::get('settings/import/mk-master/{mk}', [App\Http\Controllers\Bulk\ImportMkMasterController::class, 'form'])->name('settings.import.mk-master');
        Route::post('settings/import/mk-master/{mk}', [App\Http\Controllers\Bulk\ImportMkMasterController::class, 'import'])->name('settings.import.mk-master.upload');
        Route::post('settings/import/mk-master/{mk}/commit', [App\Http\Controllers\Bulk\ImportMkMasterController::class, 'commit'])->name('settings.import.mk-master.commit');
        Route::get('settings/import/mk-master/{mk}/template', [App\Http\Controllers\Bulk\ImportMkMasterController::class, 'template'])->name('settings.import.mk-master.template');
        Route::post('settings/import/mk-master/{mk}/clear', [App\Http\Controllers\Bulk\ImportMkMasterController::class, 'clear'])->name('settings.import.mk-master.clear');
    });

    // Bulk Upload Data Admin
    Route::get('settings/import/admin-master', [App\Http\Controllers\Bulk\ImportAdminMasterController::class, 'form'])->name('settings.import.admin-master');
    Route::post('settings/import/admin-master', [App\Http\Controllers\Bulk\ImportAdminMasterController::class, 'import'])->name('settings.import.admin-master.upload');
    Route::post('settings/import/admin-master/commit', [App\Http\Controllers\Bulk\ImportAdminMasterController::class, 'commit'])->name('settings.import.admin-master.commit');
    Route::get('settings/import/admin-master/template', [App\Http\Controllers\Bulk\ImportAdminMasterController::class, 'template'])->name('settings.import.admin-master.template');
    Route::post('settings/import/admin-master/clear', [App\Http\Controllers\Bulk\ImportAdminMasterController::class, 'clear'])->name('settings.import.admin-master.clear');
});
