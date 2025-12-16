@extends('layouts.main')
@section('dataNilai', 'active')

@section('head')
    <style>
        /* ===== CARD & LAYOUT ===== */
        .page-header {
            margin-bottom: 2rem;
        }

        .class-card {
            border-radius: 14px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.05);
            transition: transform .15s ease, box-shadow .15s ease;
        }

        .class-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 14px 32px rgba(0, 0, 0, .08);
        }

        .class-card .card-body {
            padding: 1.25rem 1.25rem 1.5rem;
        }

        /* ===== TEXT ===== */
        .muted-small {
            font-size: .85rem;
            color: #6c757d;
        }

        .subject-badge {
            font-size: .8rem;
            margin-right: .4rem;
        }

        .topic-title {
            font-weight: 600;
            color: #333;
            margin-bottom: .5rem;
        }

        /* ===== GROUPING ===== */
        .subject-block {
            margin-bottom: 1.4rem;
        }

        .topic-block {
            margin-bottom: .9rem;
        }

        /* ===== ACTIVITY ===== */
        .activity-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: .75rem;
            padding: .75rem 1rem;
            border-radius: 10px;
            background: #f8f9fa;
            border: 1px solid rgba(0, 0, 0, 0.04);
        }

        .activity-row+.activity-row {
            margin-top: .6rem;
        }

        .activity-title {
            font-weight: 600;
            line-height: 1.35;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* ===== SCROLL AREA ===== */
        .card-scroll {
            max-height: 420px;
            overflow-y: auto;
            padding-right: .25rem;
        }

        /* ===== BUTTON ===== */
        .btn-sm-primary {
            padding: .4rem .7rem;
            white-space: nowrap;
        }
    </style>
@endsection

@section('content')
    <div class="container py-4">

        {{-- HEADER --}}
        <div class="page-header d-flex justify-content-between align-items-start flex-wrap gap-3 mb-3">
            <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <h1 class="h4 mb-0">Data Nilai</h1>

                    <button type="button"
                        class="btn btn-sm btn-outline-secondary rounded-circle d-flex align-items-center justify-content-center"
                        style="width:32px;height:32px" data-bs-toggle="modal" data-bs-target="#modalInfoDataNilai"
                        title="Informasi Data Nilai">
                        <i class="bi bi-info-lg"></i>
                    </button>
                </div>

                <div class="muted-small mb-2">
                    Daftar kelas, mata pelajaran, topik, dan aktivitas yang Anda ampu.
                </div>

                <div class="d-flex gap-2">
                    <a href="{{ route('guru.datanilai.export') }}" class="btn btn-success btn-sm">
                        <i class="fas fa-file-excel"></i> Export Nilai Semua Kelas
                    </a>

                    <a href="{{ url()->current() }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-sync-alt"></i>
                    </a>
                </div>
            </div>
        </div>

        {{-- CONTENT --}}
        @if ($grouped->isEmpty())
            <div class="alert alert-info">Belum ada kelas atau aktivitas untuk Anda.</div>
        @else
            <div class="row g-4">
                @foreach($grouped as $class)
                    <div class="col-12 col-md-6 col-lg-4">
                        <div class="card class-card h-100">
                            <div class="card-body d-flex flex-column">

                                {{-- HEADER KELAS --}}
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div>
                                        <h5 class="mb-1">{{ $class['class_name'] }}</h5>
                                        <div class="muted-small">
                                            Siswa: <strong>{{ count($class['students'] ?? []) }} orang</strong>
                                        </div>
                                    </div>

                                    <div class="text-end">
                                        <span class="badge bg-primary mb-2">
                                            {{  collect($class['subjects'])->sum(function ($s) {
                        return collect($s['topics'])->sum(fn($t) => count($t['activities']));
                    })
                                                                }} aktivitas
                                        </span>
                                        <br>
                                        <a href="{{ route('guru.datanilai.exportClass', $class['class_id']) }}"
                                            class="btn btn-success btn-sm">
                                            <i class="fas fa-file-excel"></i> Export Nilai {{ $class['class_name'] }}
                                        </a>
                                    </div>
                                </div>

                                {{-- BODY --}}
                                <div class="card-scroll">
                                    @forelse ($class['subjects'] as $subject)
                                        <div class="subject-block">
                                            <div class="d-flex align-items-center mb-2">
                                                <span class="badge bg-info text-dark subject-badge">
                                                    {{ $subject['name'] }}
                                                </span>
                                                <small class="muted-small">
                                                    Topik: <strong>{{ count($subject['topics']) }}</strong>
                                                </small>
                                            </div>

                                            @foreach ($subject['topics'] as $topic)
                                                <div class="topic-block">
                                                    <div class="topic-title small">
                                                        {{ $topic['title'] }}
                                                    </div>

                                                    @forelse ($topic['activities'] as $act)
                                                        @php
                                                            $cnt = $act['results_count'] ?? 0;
                                                            $badgeClass = $cnt > 0 ? 'bg-success' : 'bg-secondary';
                                                        @endphp

                                                        <div class="activity-row">
                                                            <div class="me-3">
                                                                <div class="activity-title" title="{{ $act['title'] }}">
                                                                    {{ $act['title'] }}
                                                                </div>
                                                            </div>

                                                            <div class="d-flex align-items-center gap-2">
                                                                <span class="badge {{ $badgeClass }}">
                                                                    {{ $cnt }} Nilai
                                                                </span>

                                                                <a href="{{ route('detail.nilai', $act['id']) }}"
                                                                    class="btn btn-sm btn-primary btn-sm-primary">
                                                                    <i class="fas fa-eye"></i> Lihat
                                                                </a>
                                                            </div>
                                                        </div>
                                                    @empty
                                                        <div class="text-muted small">
                                                            Belum ada aktivitas pada topik ini.
                                                        </div>
                                                    @endforelse
                                                </div>
                                            @endforeach
                                        </div>
                                    @empty
                                        <div class="text-muted small">
                                            Tidak ada mata pelajaran untuk kelas ini.
                                        </div>
                                    @endforelse
                                </div>

                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- MODAL INFO --}}
    <div class="modal fade" id="modalInfoDataNilai" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content rounded-4 shadow">

                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-info-circle me-2"></i> Informasi Data Nilai
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <p class="mb-3">
                        Halaman <strong>Data Nilai</strong> digunakan untuk melihat dan mengelola
                        hasil penilaian siswa dari berbagai <strong>kelas</strong> yang Anda ampu.
                        Data disusun secara bertingkat agar mudah dipahami dan dipantau.
                    </p>

                    <hr class="my-3">

                    <h6 class="fw-bold text-primary mb-2">
                        <i class="bi bi-diagram-3 me-1"></i>
                        Struktur Tampilan Data
                    </h6>
                    <ul class="mb-3">
                        <li>
                            <strong>Kelas</strong> → menampilkan jumlah siswa dan total aktivitas evaluasi.
                        </li>
                        <li>
                            <strong>Mata Pelajaran</strong> → dikelompokkan di dalam setiap kelas.
                        </li>
                        <li>
                            <strong>Topik</strong> → berisi kumpulan aktivitas penilaian.
                        </li>
                        <li>
                            <strong>Aktivitas</strong> → tempat siswa mengerjakan soal dan memperoleh nilai.
                        </li>
                    </ul>

                    <hr class="my-3">

                    <h6 class="fw-bold text-success mb-2">
                        <i class="bi bi-check2-square me-1"></i>
                        Informasi Nilai
                    </h6>
                    <ul class="mb-3">
                        <li>
                            Setiap aktivitas menampilkan <strong>jumlah siswa yang telah mengerjakan</strong>.
                        </li>
                        <li>
                            Badge <span class="badge bg-success">Hijau</span> menandakan sudah ada siswa yang mengerjakan.
                        </li>
                        <li>
                            Badge <span class="badge bg-secondary">Abu-abu</span> menandakan belum ada yang mengerjakan.
                        </li>
                        <li>
                            Tombol <strong>Lihat</strong> digunakan untuk membuka detail nilai siswa.
                        </li>
                    </ul>

                    <hr class="my-3">

                    <h6 class="fw-bold text-info mb-2">
                        <i class="bi bi-file-earmark-excel me-1"></i>
                        Export Data Nilai
                    </h6>
                    <ul class="mb-0">
                        <li>
                            <strong>Export XLSX</strong> → mengunduh seluruh data nilai dari semua kelas.
                        </li>
                        <li>
                            <strong>Export Kelas</strong> → mengunduh nilai berdasarkan kelas tertentu.
                        </li>
                        <li>
                            File hasil export dapat digunakan untuk laporan atau arsip administrasi.
                        </li>
                    </ul>

                </div>


                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
    @if (session('swal'))
        <script>
            Swal.fire({
                icon: "{{ session('swal.icon') }}",
                title: "{{ session('swal.title') }}",
                text: "{{ session('swal.text') }}",
                confirmButtonColor: '#4e73df'
            });
        </script>
    @endif


@endsection