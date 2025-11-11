@extends('layouts.main')

@section('dashboardGuru')
@if(request()->is('*dashboardGuru*')) active @endif
@endsection

@section('content')
<div class="container py-4">
    <div class="card shadow-sm border-0">
        <div class="card-body text-center">
            <h2 class="fw-bold text-primary mb-3">Selamat Datang, {{ Auth::user()->nama }} 👋</h2>
            <p class="text-muted mb-4">Anda sedang berada di <strong>Dashboard Guru</strong>.</p>

            <div class="row justify-content-center">
                <div class="col-md-3 mb-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <i class="fas fa-user-graduate fa-2x text-primary mb-2"></i>
                            <h6 class="fw-bold">Data Siswa</h6>
                            <a href="#" class="btn btn-sm btn-primary mt-2">Lihat</a>
                        </div>
                    </div>
                </div>

                <div class="col-md-3 mb-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <i class="fas fa-school fa-2x text-success mb-2"></i>
                            <h6 class="fw-bold">Data Kelas</h6>
                            <a href="#" class="btn btn-sm btn-success mt-2">Lihat</a>
                        </div>
                    </div>
                </div>

                <div class="col-md-3 mb-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <i class="fas fa-book fa-2x text-warning mb-2"></i>
                            <h6 class="fw-bold">Data Subject</h6>
                            <a href="#" class="btn btn-sm btn-warning mt-2 text-white">Lihat</a>
                        </div>
                    </div>
                </div>

                <div class="col-md-3 mb-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <i class="fas fa-tasks fa-2x text-info mb-2"></i>
                            <h6 class="fw-bold">Data Aktivitas</h6>
                            <a href="#" class="btn btn-sm btn-info mt-2 text-white">Lihat</a>
                        </div>
                    </div>
                </div>

                <div class="col-md-3 mb-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <i class="fas fa-question-circle fa-2x text-danger mb-2"></i>
                            <h6 class="fw-bold">Data Soal</h6>
                            <a href="#" class="btn btn-sm btn-danger mt-2">Lihat</a>
                        </div>
                    </div>
                </div>
            </div>

            <p class="mt-4 text-secondary small">
                <i class="fas fa-info-circle"></i> Gunakan menu di sidebar untuk mengelola data.
            </p>
        </div>
    </div>
</div>
@endsection
