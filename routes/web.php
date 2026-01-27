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
    return view('welcome');
});

Auth::routes();
Route::impersonate();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::middleware('auth')->group(function () {
    Route::get('/mypassword/change', [App\Http\Controllers\Auth\PasswordChangeController::class, 'showChangePasswordGet'])->name('mypassword.change');
    Route::post('/mypassword/change', [App\Http\Controllers\Auth\PasswordChangeController::class, 'changePasswordPost'])->name('mypassword.change.post');
    // Admin
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

    // Prodi
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

});
