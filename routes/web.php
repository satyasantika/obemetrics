<?php

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
    return to_route('home');
});

Auth::routes();
Route::impersonate();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::middleware('auth')->group(function () {
    Route::get('/mypassword/change', [App\Http\Controllers\Auth\PasswordChangeController::class, 'showChangePasswordGet'])->name('mypassword.change');
    Route::post('/mypassword/change', [App\Http\Controllers\Auth\PasswordChangeController::class, 'changePasswordPost'])->name('mypassword.change.post');
    // Ruang Admin
    Route::post('/users/{user}/resetpassword', [App\Http\Controllers\Auth\PasswordChangeController::class, 'resetPasswordPost'])->name('users.resetpassword');
    Route::post('/users/{user}/activation', [App\Http\Controllers\Setting\UserController::class, 'activation'])->name('users.activation');
    Route::resource('users', App\Http\Controllers\Setting\UserController::class)->except(['show']);
    Route::resource('roles', App\Http\Controllers\Setting\RoleController::class)->except(['show']);
    Route::resource('permissions', App\Http\Controllers\Setting\PermissionController::class)->except(['show']);
    Route::resource('rolepermissions', App\Http\Controllers\Setting\RolePermissionController::class)->only('edit', 'update');
    Route::resource('userroles', App\Http\Controllers\Setting\UserRoleController::class)->only('edit', 'update');
    Route::resource('userpermissions', App\Http\Controllers\Setting\UserPermissionController::class)->only('edit', 'update');
    Route::resource('prodis', App\Http\Controllers\Setting\ProdiController::class);
    Route::resource('prodis.joinprodiusers', App\Http\Controllers\Setting\JoinProdiUserController::class);
    Route::resource('semesters', App\Http\Controllers\Setting\SemesterController::class);
    Route::resource('metodes', App\Http\Controllers\Setting\MetodeController::class);
    Route::resource('evaluasis', App\Http\Controllers\Setting\EvaluasiController::class);
    Route::resource('mahasiswas', App\Http\Controllers\Setting\MahasiswaController::class);
    Route::resource('kontrakmks', App\Http\Controllers\Setting\KontrakMkController::class);

    // Ruang Prodi
    Route::resource('prodis.kurikulums', App\Http\Controllers\Prodi\KurikulumController::class)->except('index','show');
    Route::resource('kurikulums.profils', App\Http\Controllers\Prodi\ProfilController::class)->except('show');
    Route::resource('profils.profilindikators', App\Http\Controllers\Prodi\ProfilIndikatorController::class)->except('index','show');
    Route::resource('kurikulums.cpls', App\Http\Controllers\Prodi\CplController::class)->except('show');
    Route::resource('kurikulums.bks', App\Http\Controllers\Prodi\BkController::class)->except('show');
    Route::resource('kurikulums.mks', App\Http\Controllers\Prodi\MkController::class)->except('show');
    // Profil >< CPL
    Route::get('kurikulums/{kurikulum}/joinprofilcpls', [App\Http\Controllers\Prodi\JoinProfilCplController::class,'index'])->name('kurikulums.joinprofilcpls.index');
    Route::put('joinprofilcpls/{profil}/{cpl}', [App\Http\Controllers\Prodi\JoinProfilCplController::class, 'update'])->name('joinprofilcpls.update');
    // CPL >< BK
    Route::get('kurikulums/{kurikulum}/joincplbks', [App\Http\Controllers\Prodi\JoinCplBkController::class,'index'])->name('kurikulums.joincplbks.index');
    Route::put('joincplbks/{cpl}/{bk}', [App\Http\Controllers\Prodi\JoinCplBkController::class, 'update'])->name('joincplbks.update');
    // BK >< MK
    Route::get('kurikulums/{kurikulum}/joinbkmks', [App\Http\Controllers\Prodi\JoinBkMkController::class,'index'])->name('kurikulums.joinbkmks.index');
    Route::put('joinbkmks/{bk}/{mk}', [App\Http\Controllers\Prodi\JoinBkMkController::class, 'update'])->name('joinbkmks.update');
    // Dosen >< MK
    Route::resource('mks.users', App\Http\Controllers\Prodi\JoinMkUserController::class)->only('index','update');

    // Ruang Dosen
    Route::resource('mks.cpmks', App\Http\Controllers\Dosen\CpmkController::class)->except('show');
    Route::resource('mks.subcpmks', App\Http\Controllers\Dosen\SubCpmkController::class)->except('show');
    // CPL >< BK
    Route::get('mks/{mk}/joincplcpmks', [App\Http\Controllers\Dosen\JoinCplCpmkController::class,'index'])->name('mks.joincplcpmks.index');
    Route::put('joincplcpmks/{joincplbk}/{cpmk}', [App\Http\Controllers\Dosen\JoinCplCpmkController::class, 'update'])->name('joincplcpmks.update');
    // Tugas Mata Kuliah
    Route::resource('mks.penugasans', App\Http\Controllers\Dosen\PenugasanController::class)->except('show');
    Route::get('mks/{mk}/joinsubcpmkpenugasans', [App\Http\Controllers\Dosen\JoinSubcpmkPenugasanController::class,'index'])->name('mks.joinsubcpmkpenugasans.index');
    Route::put('joinsubcpmkpenugasans/{subcpmk}/{penugasan}', [App\Http\Controllers\Dosen\JoinSubcpmkPenugasanController::class, 'update'])->name('joinsubcpmkpenugasans.update');
    // Penilaian Mata Kuliah
    Route::resource('mks.penilaians', App\Http\Controllers\Dosen\PenilaianController::class)->except('show');

    // Bulk Upload Mahasiswa
    Route::get('setting/import/mahasiswas', [App\Http\Controllers\Bulk\ImportMahasiswaController::class, 'importMahasiswaForm'])->name('setting.import.mahasiswas');
    Route::post('setting/import/mahasiswas', [App\Http\Controllers\Bulk\ImportMahasiswaController::class, 'importMahasiswa'])->name('setting.import.mahasiswas');
    Route::post('setting/import/mahasiswas/commit', [App\Http\Controllers\Bulk\ImportMahasiswaController::class, 'commitMahasiswa'])->name('setting.import.mahasiswas.commit');
    Route::get('setting/import/mahasiswas/template', [App\Http\Controllers\Bulk\ImportMahasiswaController::class, 'downloadTemplate'])->name('setting.import.mahasiswas.template');
    Route::post('setting/import/mahasiswas/clear', [App\Http\Controllers\Bulk\ImportMahasiswaController::class, 'clearPreview'])->name('setting.import.mahasiswas.clear');

    // Bulk Upload Users
    Route::get('setting/import/users', [App\Http\Controllers\Bulk\ImportUserController::class, 'importUserForm'])->name('setting.import.users');
    Route::post('setting/import/users', [App\Http\Controllers\Bulk\ImportUserController::class, 'importUser'])->name('setting.import.users');
    Route::post('setting/import/users/commit', [App\Http\Controllers\Bulk\ImportUserController::class, 'commitUser'])->name('setting.import.users.commit');
    Route::get('setting/import/users/template', [App\Http\Controllers\Bulk\ImportUserController::class, 'downloadTemplate'])->name('setting.import.users.template');
    Route::post('setting/import/users/clear', [App\Http\Controllers\Bulk\ImportUserController::class, 'clearPreview'])->name('setting.import.users.clear');

    // Bulk Upload Kontrak MK
    Route::get('setting/import/kontrakmks', [App\Http\Controllers\Bulk\ImportKontrakMkController::class, 'importKontrakMkForm'])->name('setting.import.kontrakmks');
    Route::post('setting/import/kontrakmks', [App\Http\Controllers\Bulk\ImportKontrakMkController::class, 'importKontrakMk'])->name('setting.import.kontrakmks');
    Route::post('setting/import/kontrakmks/commit', [App\Http\Controllers\Bulk\ImportKontrakMkController::class, 'commitKontrakMk'])->name('setting.import.kontrakmks.commit');
    Route::get('setting/import/kontrakmks/template', [App\Http\Controllers\Bulk\ImportKontrakMkController::class, 'downloadTemplate'])->name('setting.import.kontrakmks.template');
    Route::post('setting/import/kontrakmks/clear', [App\Http\Controllers\Bulk\ImportKontrakMkController::class, 'clearPreview'])->name('setting.import.kontrakmks.clear');
});
