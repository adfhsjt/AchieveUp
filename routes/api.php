<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BidangController as ApiBidang;
use App\Http\Controllers\Api\BimbinganController as ApiBimbingan;
use App\Http\Controllers\Api\DashboardAdminController as ApiDashboardAdmin;
use App\Http\Controllers\Api\DashboardDosenController as ApiDashboardDosen;
use App\Http\Controllers\Api\LandingController as ApiLanding;
use App\Http\Controllers\Api\LombaController as ApiLomba;
use App\Http\Controllers\Api\LombaDosenController as ApiLombaDosen;
use App\Http\Controllers\Api\LombaMahasiswaController as ApiLombaMahasiswa;
use App\Http\Controllers\Api\NotifikasiAdminController as ApiNotifikasiAdmin;
use App\Http\Controllers\Api\NotifikasiDosenPembimbingController as ApiNotifikasiDosenPembimbing;
use App\Http\Controllers\Api\NotifikasiMahasiswaController as ApiNotifikasiMahasiswa;
use App\Http\Controllers\Api\PeriodeController as ApiPeriode;
use App\Http\Controllers\Api\PrestasiMahasiswaController as ApiPrestasiMahasiswa;
use App\Http\Controllers\Api\ProdiController as ApiProdi;
use App\Http\Controllers\Api\ProfilAdminController as ApiProfilAdmin;
use App\Http\Controllers\Api\ProfilDosenPembimbingController as ApiProfilDosenPembimbing;
use App\Http\Controllers\Api\ProfilMahasiswaController as ApiProfilMahasiswa;
use App\Http\Controllers\Api\RekomendasiLombaController as ApiRekomendasi;
use App\Http\Controllers\Api\UserController as ApiUser;
use App\Http\Controllers\Api\VerifikasiPrestasiController as ApiVerifikasiPrestasi;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| API versions can mirror web routes. Controllers for API are expected
| to live under ...
|
*/

Route::get('/', function () {
    return redirect('/landing');
});

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']); // optional
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/simpan-password', [AuthController::class, 'simpanPassword']);
Route::get('/landing', [ApiLanding::class, 'index']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    // Admin area (keep role middleware if available)
    Route::middleware('dosen:admin')->prefix('admin')->name('api.admin.')->group(function () {
        // Bidang
        Route::get('bidang', [ApiBidang::class, 'index']);
        Route::get('bidang/getall', [ApiBidang::class, 'getall']);
        Route::get('bidang/{id}', [ApiBidang::class, 'show']);
        Route::post('bidang', [ApiBidang::class, 'store']);
        Route::put('bidang/{id}', [ApiBidang::class, 'update']);
        Route::delete('bidang/{id}', [ApiBidang::class, 'destroy']);

        // Dashboard
        Route::get('dashboard', [ApiDashboardAdmin::class, 'index']);
        Route::get('dashboard/entropy', [ApiDashboardAdmin::class, 'entropy']);
        Route::get('dashboard/electre', [ApiDashboardAdmin::class, 'electre']);
        Route::get('dashboard/aras', [ApiDashboardAdmin::class, 'aras']);
        Route::get('dashboard/test', [ApiDashboardAdmin::class, 'test']);

        // Lomba
        Route::get('lomba', [ApiLomba::class, 'index']);
        Route::get('lomba/{id}', [ApiLomba::class, 'show']);
        Route::post('lomba', [ApiLomba::class, 'store']);
        Route::put('lomba/{id}', [ApiLomba::class, 'update']);
        Route::delete('lomba/{id}', [ApiLomba::class, 'destroy']);

        Route::get('lomba/pengajuan', [ApiLomba::class, 'getPengajuan']);
        Route::get('lomba/pengajuan/{id}', [ApiLomba::class, 'showPengajuan']);
        Route::post('lomba/pengajuan/{id}/approve', [ApiLomba::class, 'approvePengajuan']);
        Route::post('lomba/pengajuan/{id}/reject', [ApiLomba::class, 'rejectPengajuan']);

        //Notifikasi
        Route::get('notifikasi', [ApiNotifikasiAdmin::class, 'index']);
        Route::get('notifikasi/getAll', [ApiNotifikasiAdmin::class, 'getAllNotifikasi']);
        Route::get('notifikasi/{type}/{id}', [ApiNotifikasiAdmin::class, 'show']);
        Route::delete('notifikasi/{type}/{id}', [ApiNotifikasiAdmin::class, 'destroy']);

        Route::post('notifikasi/markAsRead', [ApiNotifikasiAdmin::class, 'markAsRead']);
        Route::post('notifikasi/markAllAsRead', [ApiNotifikasiAdmin::class, 'markAllAsRead']);
        Route::post('notifikasi/destroyIsAcceptedMessage', [ApiNotifikasiAdmin::class, 'destroyIsAcceptedMessege']);

        // Periode
        Route::get('periode', [ApiPeriode::class, 'index']);
        Route::get('periode/getall', [ApiPeriode::class, 'getall']);
        Route::post('periode', [ApiPeriode::class, 'store']);
        Route::get('periode/{id}', [ApiPeriode::class, 'show'])->whereNumber('id');
        Route::put('periode/{id}', [ApiPeriode::class, 'update'])->whereNumber('id');
        Route::delete('periode/{id}', [ApiPeriode::class, 'destroy'])->whereNumber('id');
        Route::put('periode/{id}/activate', [ApiPeriode::class, 'activate'])->whereNumber('id');

        // Prodi
        Route::get('prodi', [ApiProdi::class, 'index']);
        Route::get('prodi/getall', [ApiProdi::class, 'getall']);
        Route::post('prodi', [ApiProdi::class, 'store']);
        Route::get('prodi/{id}', [ApiProdi::class, 'show'])->whereNumber('id');
        Route::put('prodi/{id}', [ApiProdi::class, 'update'])->whereNumber('id');
        Route::delete('prodi/{id}', [ApiProdi::class, 'destroy'])->whereNumber('id');

        // Profil Admin
        Route::get('profil', [ApiProfilAdmin::class, 'show']);
        Route::put('profil/{id}', [ApiProfilAdmin::class, 'update'])->whereNumber('id');

        // Rekomendasi Lomba
        Route::get('rekomendasi', [ApiRekomendasi::class, 'index']);
        Route::post('rekomendasi', [ApiRekomendasi::class, 'store']);
        Route::get('rekomendasi/all', [ApiRekomendasi::class, 'getAll']);
        Route::get('rekomendasi/{id}', [ApiRekomendasi::class, 'show'])->whereNumber('id');
        Route::delete('rekomendasi/{id}', [ApiRekomendasi::class, 'destroy'])->whereNumber('id');
        Route::get('rekomendasi/lomba/{id}', [ApiRekomendasi::class, 'showLomba'])->whereNumber('id');

        //User
        Route::get('users', [ApiUser::class, 'index']); // optional query ?type=mahasiswa|dosen
        Route::get('users/mahasiswa', [ApiUser::class, 'getMahasiswaData']);
        Route::get('users/dosen', [ApiUser::class, 'getDosenData']);
        Route::post('users', [ApiUser::class, 'store']); // body: type=mahasiswa|dosen + fields
        Route::put('users/mahasiswa/{id}', [ApiUser::class, 'updateMahasiswa'])->whereNumber('id');
        Route::put('users/dosen/{id}', [ApiUser::class, 'updateDosen'])->whereNumber('id');
        Route::delete('users/mahasiswa/{id}', [ApiUser::class, 'destroyMahasiswa'])->whereNumber('id');
        Route::delete('users/dosen/{id}', [ApiUser::class, 'destroyDosen'])->whereNumber('id');
        Route::get('users/prodi', [ApiUser::class, 'prodiList']);

        // Prestasi
        Route::get('prestasi', [ApiVerifikasiPrestasi::class, 'index']);
        Route::get('prestasi/data', [ApiVerifikasiPrestasi::class, 'getData']);
        Route::get('prestasi/{id}', [ApiVerifikasiPrestasi::class, 'show'])->whereNumber('id');
        Route::post('prestasi/{id}/approve', [ApiVerifikasiPrestasi::class, 'approve']);
        Route::post('prestasi/{id}/reject', [ApiVerifikasiPrestasi::class, 'reject']);
        Route::get('prestasi/export', [ApiVerifikasiPrestasi::class, 'export']);

        
    });

    // Dosen pembimbing area
    Route::middleware('dosen:dosen pembimbing')->prefix('dosen_pembimbing')->group(function () {
        Route::get('bimbingan', [ApiBimbingan::class, 'index']);
        Route::get('bimbingan/{id}', [ApiBimbingan::class, 'detail']);

        Route::get('dashboard', [ApiDashboardDosen::class, 'index']);

        Route::get('lomba', [ApiLombaDosen::class, 'getAll']);
        Route::get('lomba/{id}', [ApiLombaDosen::class, 'show']);
        Route::post('lomba', [ApiLombaDosen::class, 'store']);

        Route::get('notifikasi', [ApiNotifikasiDosenPembimbing::class, 'index']);
        Route::get('notifikasi/getAll', [ApiNotifikasiDosenPembimbing::class, 'getAll']);
        Route::get('notifikasi/{id}', [ApiNotifikasiDosenPembimbing::class, 'show'])->whereNumber('id');
        Route::post('notifikasi/markAsRead', [ApiNotifikasiDosenPembimbing::class, 'markAsRead']);
        Route::post('notifikasi/markAllAsRead', [ApiNotifikasiDosenPembimbing::class, 'markAllAsRead']);
        Route::delete('notifikasi/{id}', [ApiNotifikasiDosenPembimbing::class, 'destroy'])->whereNumber('id');
        Route::post('notifikasi/destroyIsAcceptedMessage', [ApiNotifikasiDosenPembimbing::class, 'destroyIsAcceptedMessage']);

        Route::get('profil', [ApiProfilDosenPembimbing::class, 'show']);
        Route::put('profil/{id}', [ApiProfilDosenPembimbing::class, 'update'])->whereNumber('id');
    });

    // Mahasiswa area
    Route::middleware('mahasiswa')->prefix('mahasiswa')->group(function () {
        // Lomba
        Route::get('lomba', [ApiLombaMahasiswa::class, 'getAll']);
        Route::get('lomba/{id}', [ApiLombaMahasiswa::class, 'show'])->whereNumber('id');
        Route::post('lomba', [ApiLombaMahasiswa::class, 'store']); // submit pengajuan (multipart)
        Route::get('lomba/pengajuan', [ApiLombaMahasiswa::class, 'getPengajuan']);
        Route::get('lomba/pengajuan/{id}', [ApiLombaMahasiswa::class, 'showPengajuan']);
        Route::delete('lomba/pengajuan/{id}', [ApiLombaMahasiswa::class, 'destroyPengajuan']);

        Route::get('notifikasi', [ApiNotifikasiMahasiswa::class, 'index']);
        Route::get('notifikasi/getAll', [ApiNotifikasiMahasiswa::class, 'getAll']);
        Route::get('notifikasi/{type}/{id}', [ApiNotifikasiMahasiswa::class, 'show']);
        Route::post('notifikasi/markAsRead', [ApiNotifikasiMahasiswa::class, 'markAsRead']);
        Route::post('notifikasi/markAllAsRead', [ApiNotifikasiMahasiswa::class, 'markAllAsRead']);
        Route::delete('notifikasi/{type}/{id}', [ApiNotifikasiMahasiswa::class, 'destroy']);
        Route::post('notifikasi/destroyIsAcceptedMessage', [ApiNotifikasiMahasiswa::class, 'destroyIsAcceptedMessage']);

        Route::get('prestasi', [ApiPrestasiMahasiswa::class, 'index']);
        Route::get('prestasi/data', [ApiPrestasiMahasiswa::class, 'getData']);
        Route::post('prestasi', [ApiPrestasiMahasiswa::class, 'store']); // multipart/form-data
        Route::get('prestasi/{id}', [ApiPrestasiMahasiswa::class, 'show'])->whereNumber('id');
        Route::put('prestasi/{id}', [ApiPrestasiMahasiswa::class, 'update'])->whereNumber('id');
        Route::delete('prestasi/{id}', [ApiPrestasiMahasiswa::class, 'destroy'])->whereNumber('id');

        Route::get('profil', [ApiProfilMahasiswa::class, 'show']);
        Route::put('profil/{id}', [ApiProfilMahasiswa::class, 'update'])->whereNumber('id');

    });
});
