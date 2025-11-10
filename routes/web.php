<?php

use App\Http\Controllers\aktivitasController;
use App\Http\Controllers\loginController;
use App\Http\Controllers\siswaController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('landing');
});

Route::get('/dashboardsiswa', [siswaController::class, 'dashboardSiswa'])
    ->name('dashboard.siswa')
    ->middleware('auth');

Route::get('/aktivitassiswa', [aktivitasController::class, 'aktivitasSiswa'])
    ->middleware('auth')
    ->name('siswa.aktivitas');

Route::get('/activity/{id}', [aktivitasController::class, 'show'])->name('activity.show');
Route::post('/activity/saveResult', [aktivitasController::class, 'saveResult'])->name('activity.saveResult');


//route login dan logut
Route::get('/login', [loginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [loginController::class, 'login'])->name('login.process');
Route::post('/logout', [loginController::class, 'logout'])->name('logout');


Route::get('/registrasi', function () {
    return view('home.registrasi');
});

Route::get('/tentang', function () {
    return view('home.tentang');
});