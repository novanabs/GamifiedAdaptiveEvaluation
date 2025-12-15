@extends('layouts.main')

@section('dashboardGuru', request()->is('dashboardguru') ? 'active' : '')

@section('content')
<style>
    .stat-card {
        border-radius: 16px;
        transition: all .25s ease;
        cursor: pointer;
    }

    .stat-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 12px 30px rgba(0,0,0,.08);
    }

    .stat-icon {
        width: 56px;
        height: 56px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.6rem;
        color: #fff;
    }

    .quick-btn {
        border-radius: 14px;
        padding: 1rem;
        transition: all .2s ease;
        background: #f8f9fa;
    }

    .quick-btn:hover {
        background: #eef2f7;
        transform: translateY(-3px);
    }
</style>

<div class="container py-4">

    {{-- HERO --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body text-center py-4">
            <h2 class="fw-bold text-primary mb-1">
                Selamat Datang, {{ Auth::user()->nama }} 👋
            </h2>
            <p class="text-muted mb-0">
                Anda berada di <strong>Dashboard Guru</strong>. Kelola kelas, soal, dan aktivitas dengan mudah.
            </p>
        </div>
    </div>

    {{-- STATISTIK --}}
    <div class="row g-4 mb-4">

        <div class="col-md-4 col-lg-3">
            <a href="{{ route('dataSiswa') }}" class="text-decoration-none text-dark">
                <div class="card stat-card border-0 shadow-sm h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="stat-icon bg-primary">
                            <i class="fas fa-user-graduate"></i>
                        </div>
                        <div>
                            <div class="fw-bold">Data Siswa</div>
                            <small class="text-muted">Kelola siswa</small>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-4 col-lg-3">
            <a href="{{ route('kelasGuru') }}" class="text-decoration-none text-dark">
                <div class="card stat-card border-0 shadow-sm h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="stat-icon bg-success">
                            <i class="fas fa-school"></i>
                        </div>
                        <div>
                            <div class="fw-bold">Data Kelas</div>
                            <small class="text-muted">Manajemen kelas</small>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-4 col-lg-3">
            <a href="{{ route('guru.dataSubject') }}" class="text-decoration-none text-dark">
                <div class="card stat-card border-0 shadow-sm h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="stat-icon bg-warning">
                            <i class="fas fa-book"></i>
                        </div>
                        <div>
                            <div class="fw-bold">Mata Pelajaran</div>
                            <small class="text-muted">Subject & topik</small>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-4 col-lg-3">
            <a href="{{ route('tampilanSoal') }}" class="text-decoration-none text-dark">
                <div class="card stat-card border-0 shadow-sm h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="stat-icon bg-danger">
                            <i class="fas fa-question-circle"></i>
                        </div>
                        <div>
                            <div class="fw-bold">Bank Soal</div>
                            <small class="text-muted">Kelola soal</small>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-4 col-lg-3">
            <a href="{{ route('guru.aktivitas.tampil') }}" class="text-decoration-none text-dark">
                <div class="card stat-card border-0 shadow-sm h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="stat-icon bg-info">
                            <i class="fas fa-tasks"></i>
                        </div>
                        <div>
                            <div class="fw-bold">Aktivitas</div>
                            <small class="text-muted">Ujian & tugas</small>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-4 col-lg-3">
            <a href="{{ route('data.nilai') }}" class="text-decoration-none text-dark">
                <div class="card stat-card border-0 shadow-sm h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="stat-icon bg-secondary">
                            <i class="fas fa-chart-bar"></i>
                        </div>
                        <div>
                            <div class="fw-bold">Data Nilai</div>
                            <small class="text-muted">Rekap nilai</small>
                        </div>
                    </div>
                </div>
            </a>
        </div>

    </div>

    {{-- QUICK ACTION --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <h5 class="fw-bold mb-3">
                <i class="fas fa-bolt text-warning me-1"></i> Aksi Cepat
            </h5>

            <div class="row g-3">

                <div class="col-md-4">
                    <a href="{{ route('tambahSoal') }}" class="text-decoration-none text-dark">
                        <div class="quick-btn shadow-sm h-100">
                            <i class="bi bi-plus-circle text-primary fs-3 mb-2"></i>
                            <div class="fw-semibold">Tambah Soal Manual</div>
                            <small class="text-muted">Buat soal sendiri</small>
                        </div>
                    </a>
                </div>

                <div class="col-md-4">
                    <a href="{{ route('generateSoal') }}" class="text-decoration-none text-dark">
                        <div class="quick-btn shadow-sm h-100">
                            <i class="bi bi-lightbulb text-success fs-3 mb-2"></i>
                            <div class="fw-semibold">Buat Soal Semi-Otomatis</div>
                            <small class="text-muted">Cepat & efisien</small>
                        </div>
                    </a>
                </div>

                <div class="col-md-4">
                    <a href="{{ route('guru.aktivitas.tampil') }}" class="text-decoration-none text-dark">
                        <div class="quick-btn shadow-sm h-100">
                            <i class="bi bi-journal-check text-info fs-3 mb-2"></i>
                            <div class="fw-semibold">Buat Aktivitas</div>
                            <small class="text-muted">Ujian / latihan</small>
                        </div>
                    </a>
                </div>

            </div>
        </div>
    </div>

</div>
@endsection
