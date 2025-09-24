<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;



//Route::get('/index', [PublicController::class, 'index'])->name('home');

// Halaman publik
Route::get('/', [PublicController::class, 'index']);
Route::get('/tentang', [PublicController::class, 'tentang']);
Route::get('/kontak', [PublicController::class, 'kontak']);
//Route::get('/index', [PublicController::class, 'index'])->name('index');

// Auth
Route::get('/login', [AuthController::class, 'login'])->name('login');
Route::get('/register', [AuthController::class, 'register']);

// Admin
Route::prefix('admin')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard']);
    Route::get('/users', [AdminController::class, 'manageUsers']);
    Route::get('/pengumuman', [AdminController::class, 'managePengumuman']);
    Route::get('/publikasi', [AdminController::class, 'managePublikasi']);
    Route::get('/kegiatan', [AdminController::class, 'manageKegiatan']);
});


Route::get('/', [PublicController::class, 'index'])->name('home');

// Halaman Publik
Route::get('/pengumuman', [PublicController::class, 'pengumuman'])->name('pengumuman');
Route::get('/publikasi', [PublicController::class, 'publikasi'])->name('publikasi');
Route::get('/kegiatan', [PublicController::class, 'kegiatan'])->name('kegiatan');
Route::get('/ketentuan', [PublicController::class, 'ketentuan'])->name('ketentuan');
Route::get('/tentang', [PublicController::class, 'tentang'])->name('tentang');
Route::get('/kontak', [PublicController::class, 'kontak'])->name('kontak');

Route::get('/', function () {
    return view('pages.home');
});


Route::get('/pengumuman', function () {
    return view('pengumuman');
});

// ... rute lainnya

// Auth routes (jika menggunakan Laravel Auth)
