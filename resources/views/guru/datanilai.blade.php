@extends('layouts.main')
@section('dataNilai', 'active')

@section('head')
    {{-- styling kecil untuk tampilan --}}
    <style>
        .class-card {
            border-radius: 12px;
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.04);
        }

        .subject-badge {
            font-size: .82rem;
            margin-right: .35rem;
        }

        .topic-title {
            font-weight: 600;
            color: #333;
        }

        .activity-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: .75rem;
            padding: .6rem .75rem;
            border-radius: 8px;
            background: #f8f9fa;
            border: 1px solid rgba(0, 0, 0, 0.03);
        }

        .activity-row+.activity-row {
            margin-top: .5rem;
        }

        .muted-small {
            font-size: .85rem;
            color: #6c757d;
        }

        .no-data {
            color: #6c757d;
            font-style: italic;
        }

        .activity-title {
            font-weight: 600;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .activity-meta {
            font-size: .85rem;
            color: #6c757d;
        }

        .btn-sm-primary {
            padding: .38rem .6rem;
        }
    </style>
@endsection

@section('content')
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h4 mb-0">Data Nilai</h1>
                <div class="muted-small">Daftar kelas, mata pelajaran, topik, dan aktivitas yang Anda ampu.</div>
                <a href="{{ route('guru.datanilai.export') }}" class="btn btn-success btn-sm me-2"
                    title="Export Semua Kelas ke XLSX">
                    <i class="fas fa-file-excel"></i> Export XLSX
                </a>

            </div>
            <div>
                <a href="{{ url()->current() }}" class="btn btn-outline-secondary btn-sm me-2" title="Refresh">
                    <i class="fas fa-sync-alt"></i>
                </a>
            </div>
        </div>

        @if ($grouped->isEmpty())
            <div class="alert alert-info">Belum ada kelas atau aktivitas untuk Anda.</div>
        @else
            <div class="row g-3">
                @foreach($grouped as $class)
                    <div class="col-12 col-md-6 col-lg-4">
                        <div class="card class-card h-100">
                            <div class="card-body d-flex flex-column">
                                {{-- header kelas --}}
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <h5 class="mb-1">{{ $class['class_name'] ?? 'Kelas ' . $class['class_id'] }}</h5>
                                        <div class="muted-small">Siswa: <strong>{{ count($class['students'] ?? []) }} orang</strong>
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge bg-primary">{{ collect($class['subjects'])->sum(function ($s) {
                        return collect($s['topics'])->sum(fn($t) => count($t['activities'])); }) }}
                                            aktivitas</span>
                                        <a href="{{ route('guru.datanilai.exportClass', $class['class_id']) }}"
                                            class="btn btn-success btn-sm mt-2" title="Export Kelas Ini">
                                            <i class="fas fa-file-excel"></i> Export Kelas
                                        </a>
                                    </div>
                                </div>

                                {{-- body: daftar subject -> topic -> activities (satu baris per aktivitas) --}}
                                <div class="overflow-auto" style="max-height:340px;">
                                    @forelse ($class['subjects'] as $subject)
                                        <div class="mb-3">
                                            <div class="d-flex align-items-center mb-1">
                                                <span class="badge bg-info text-dark subject-badge">{{ $subject['name'] }}</span>
                                                <small class="muted-small ms-2">Topik: <strong>{{ count($subject['topics'] ?? []) }}
                                                        Buah Topik</strong></small>
                                            </div>

                                            @foreach ($subject['topics'] as $topic)
                                                <div class="mb-2">
                                                    <div class="topic-title small mb-2">{{ $topic['title'] }}</div>

                                                    {{-- LIST aktivitas: satu baris = satu aktivitas --}}
                                                    @forelse ($topic['activities'] as $act)
                                                        <div class="activity-row">
                                                            <div style="flex:1; min-width:0; margin-right:.75rem;">
                                                                <div class="activity-title" title="{{ $act['title'] }}">
                                                                    <b>{{ $act['title'] }}</b>
                                                                </div>

                                                            </div>

                                                            <div class="d-flex align-items-center">
                                                                {{-- badge warna sesuai jumlah nilai (opsional) --}}
                                                                @php
                                                                    $cnt = $act['results_count'] ?? 0;
                                                                    $badgeClass = $cnt > 0 ? 'bg-success' : 'bg-secondary';
                                                                @endphp
                                                                <span class="badge {{ $badgeClass }} me-2">Jumlah Mengerjakan : {{ $cnt }}
                                                                    orang</span>

                                                                <a href="{{ route('detail.nilai', $act['id']) }}"
                                                                    class="btn btn-sm btn-primary btn-sm-primary">
                                                                    <i class="fas fa-eye me-1"></i> Lihat
                                                                </a>
                                                            </div>
                                                        </div>
                                                    @empty
                                                        <div class="text-muted small">Belum ada aktivitas pada topik ini.</div>
                                                    @endforelse

                                                </div>
                                            @endforeach

                                        </div>
                                    @empty
                                        <div class="text-muted small">Tidak ada mata pelajaran untuk kelas ini.</div>
                                    @endforelse
                                </div>

                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
@endsection