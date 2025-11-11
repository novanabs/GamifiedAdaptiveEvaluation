<?php

use App\Http\Controllers\aktivitasController;
use App\Http\Controllers\guruController;
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


//guru
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboardguru', [guruController::class, 'dashboardGuru'])->name('dashboardGuru');
    //manajemen siswa
    Route::get('/datasiswa', [guruController::class, 'dataSiswa'])->name('dataSiswa');
    Route::get('/dataSiswa/export', [guruController::class, 'exportSiswa'])->name('dataSiswa.export');
    //manajemen kelas
    Route::get('/kelas-guru', [guruController::class, 'kelasGuru'])->name('kelasGuru');
    Route::post('/kelas/tambah', [guruController::class, 'tambahKelas'])->name('kelas.tambah');
    Route::post('/kelas/gabung', [guruController::class, 'gabungKelas'])->name('kelas.gabung');
    //manajemen subject
    Route::get('/guru/data-subject', [guruController::class, 'dataSubject'])->name('guru.dataSubject');
    Route::post('/guru/subject/tambah', [guruController::class, 'tambahSubject'])->name('guru.subject.tambah');
    Route::post('/guru/subject/update/{id}', [guruController::class, 'updateSubject'])->name('guru.subject.update');
    Route::delete('/guru/subject/hapus/{id}', [guruController::class, 'hapusSubject'])->name('guru.subject.hapus');
    // manajemen topik
    Route::get('/data-topik', [guruController::class, 'tampilanTopik'])->name('guru.topik.tampilan');
    Route::post('/simpan-topik', [guruController::class, 'simpanTopik'])->name('guru.topik.simpan');
    Route::post('/ubah-topik/{id}', [guruController::class, 'ubahTopik'])->name('guru.topik.ubah');
    Route::delete('/hapus-topik/{id}', [guruController::class, 'hapusTopik'])->name('guru.topik.hapus');
    //manajemen aktivitas
    Route::get('/dataaktivitas', [guruController::class, 'tampilAktivitas'])->name('guru.aktivitas.tampil');
    Route::post('/aktivitas/simpan', [guruController::class, 'simpanAktivitas'])->name('guru.aktivitas.simpan');
    Route::put('/aktivitas/ubah/{id}', [guruController::class, 'ubahAktivitas'])->name('guru.aktivitas.ubah');
    Route::delete('/aktivitas/hapus/{id}', [guruController::class, 'hapusAktivitas'])->name('guru.aktivitas.hapus');
    //manajemen soal
    Route::get('/soal', [guruController::class, 'tampilanSoal'])->name('tampilanSoal');
    Route::get('/soal/tambah', [guruController::class, 'tambahSoal'])->name('tambahSoal');
    Route::post('/soal/simpan', [guruController::class, 'simpanSoal'])->name('simpanSoal');
    Route::get('/soal/edit/{id}', [guruController::class, 'editSoal'])->name('editSoal');
    Route::post('/soal/update/{id}', [guruController::class, 'updateSoal'])->name('updateSoal');
    Route::delete('/soal/hapus/{id}', [guruController::class, 'hapusSoal'])->name('hapusSoal');
    //tambah aktivitas soal
    Route::post('/guru/aktivitas/{id}/atur-soal', [guruController::class, 'simpanAturSoal'])->name('guru.simpanAturSoal');
});

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