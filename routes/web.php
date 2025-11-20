<?php

use App\Http\Controllers\aktivitasController;
use App\Http\Controllers\aturAktivitasController;
use App\Http\Controllers\guruController;
use App\Http\Controllers\loginController;
use App\Http\Controllers\siswaController;
use App\Http\Controllers\SoalController;
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
Route::get('/activity/{id}/start', [aktivitasController::class, 'start']);
Route::get('/activity/{id}/question', [aktivitasController::class, 'getQuestion']);
Route::post('/activity/{id}/submit', [aktivitasController::class, 'submitAnswer']);
Route::post('/activity/{id}/finish', [aktivitasController::class, 'finishTest']);



Route::post('/activity/saveResult', [aktivitasController::class, 'saveResult'])->name('activity.saveResult');


//guru
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboardguru', [guruController::class, 'dashboardGuru'])->name('dashboardGuru');
    //manajemen siswa
    Route::get('/datasiswa', [guruController::class, 'dataSiswa'])->name('dataSiswa');
    Route::get('/dataSiswa/export', [guruController::class, 'exportSiswa'])->name('dataSiswa.export');
    Route::post('/dataSiswa/update', [GuruController::class, 'updateSiswa'])->name('dataSiswa.update');
    //manajemen kelas
    Route::get('/datakelas', [guruController::class, 'kelasGuru'])->name('kelasGuru');
    Route::post('/kelas/tambah', [guruController::class, 'tambahKelas'])->name('kelas.tambah');
    Route::post('/kelas/gabung', [guruController::class, 'gabungKelas'])->name('kelas.gabung');
    //manajemen subject
    Route::get('/datamatapelajaran', [guruController::class, 'dataSubject'])->name('guru.dataSubject');
    Route::post('/guru/subject/tambah', [guruController::class, 'tambahSubject'])->name('guru.subject.tambah');
    Route::post('/guru/subject/update/{id}', [guruController::class, 'updateSubject'])->name('guru.subject.update');
    Route::delete('/guru/subject/hapus/{id}', [guruController::class, 'hapusSubject'])->name('guru.subject.hapus');
    // manajemen topik
    Route::get('/datatopik', [guruController::class, 'tampilanTopik'])->name('guru.topik.tampilan');
    Route::post('/simpan-topik', [guruController::class, 'simpanTopik'])->name('guru.topik.simpan');
    Route::post('/ubah-topik/{id}', [guruController::class, 'ubahTopik'])->name('guru.topik.ubah');
    Route::delete('/hapus-topik/{id}', [guruController::class, 'hapusTopik'])->name('guru.topik.hapus');
    //manajemen aktivitas
    Route::get('/dataaktivitas', [guruController::class, 'tampilAktivitas'])->name('guru.aktivitas.tampil');
    Route::post('/aktivitas/simpan', [guruController::class, 'simpanAktivitas'])->name('guru.aktivitas.simpan');
    Route::put('/aktivitas/ubah/{id}', [guruController::class, 'ubahAktivitas'])->name('guru.aktivitas.ubah');
    Route::delete('/aktivitas/hapus/{id}', [guruController::class, 'hapusAktivitas'])->name('guru.aktivitas.hapus');
    // =============================
// 🟦 1. Halaman Atur Soal (GET)
// =============================
    Route::get(
        '/guru/aktivitas/{id}/atur-soal',
        [aturAktivitasController::class, 'halamanAturSoal']
    )->name('guru.aktivitas.aturSoal');

    // =============================
// 🟩 2. AJAX Ambil Soal (POST)
// =============================
    Route::post(
        '/guru/ambil-soal/{id}',
        [aturAktivitasController::class, 'ambilSoalAjax']
    );

    // =============================
// 🟧 3. Simpan Pilihan Soal (POST)
// =============================
    Route::post(
        '/guru/simpan-atur-soal/{id}',
        [aturAktivitasController::class, 'simpanAturSoal']
    )->name('guru.simpanAturSoal');
    // =============================
// ➕ Tambah 1 Soal ke Terpilih (POST)
// =============================
    Route::post(
        '/guru/tambah-soal-manual/{id}',
        [aturAktivitasController::class, 'tambahSoalManual']
    );

    // =============================
// ❌ Hapus 1 Soal dari Terpilih (POST)
// =============================
    Route::post(
        '/guru/hapus-soal-manual/{id}',
        [aturAktivitasController::class, 'hapusSoalManual']
    );
    Route::get('/get-question/{id}', [aturAktivitasController::class, 'getQuestion']);


    //manajemen soal
    Route::get('/datasoal', [guruController::class, 'tampilanSoal'])->name('tampilanSoal');
    Route::get('/soal/tambah', [guruController::class, 'tambahSoal'])->name('tambahSoal');
    Route::post('/soal/simpan', [guruController::class, 'simpanSoal'])->name('simpanSoal');
    Route::get('/soal/edit/{id}', [guruController::class, 'editSoal'])->name('editSoal');
    Route::post('/soal/update/{id}', [guruController::class, 'updateSoal'])->name('updateSoal');
    Route::delete('/soal/hapus/{id}', [guruController::class, 'hapusSoal'])->name('hapusSoal');
    Route::post('/edit-topik-soal/{id}', [guruController::class, 'editTopikSoal'])->name('editTopikSoal');


    //generate soal otomatis
    Route::get('/generate-soal', [SoalController::class, 'showGenerator'])->name('generateSoal');
    Route::post('/generate-soal', [SoalController::class, 'generateAI'])->name('generateSoal.post');
    Route::post('/import-question-json', [SoalController::class, 'importQuestionJson'])->name('importQuestionJson');

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