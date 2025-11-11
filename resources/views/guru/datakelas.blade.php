@extends('layouts.main')

@section('dataKelas')
    @if(request()->is('*dataKelas*')) active @endif
@endsection

@section('content')
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold text-primary mb-0">Daftar Kelas yang Anda Ajar</h2>
                <p class="text-muted small mb-0">Menampilkan kelas beserta subject, topic, dan activity</p>
            </div>
            <div>
                <!-- Tombol tambah & gabung kelas -->
                <button class="btn btn-primary me-2" data-bs-toggle="modal" data-bs-target="#modalTambah">
                    Tambah Kelas
                </button>
                <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalGabung">
                    Gabung Kelas
                </button>
            </div>
        </div>

        {{-- DAFTAR KELAS --}}
        @if($dataKelas->isEmpty())
            <div class="alert alert-info shadow-sm border-0 rounded-3">
                Belum ada kelas yang Anda ajar.
            </div>
        @else
            <div class="row">
                @foreach($dataKelas as $data)
                    <div class="col-md-4 mb-4">
                        <div class="card border-0 shadow-lg rounded-4 h-100 kelas-card" style="transition: transform 0.2s;">
                            <div class="card-body p-4">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h4 class="fw-bold text-primary mb-0">{{ $data->kelas->name }}</h4>
                                    <span class="badge bg-light text-secondary px-3 py-2">
                                        🎓 Level: {{ ucfirst($data->kelas->level) }}
                                    </span>
                                </div>

                                <div class="mb-3">
                                    <h6 class="fw-semibold text-secondary mb-1">Guru Pengajar</h6>
                                    @if($data->guru->isNotEmpty())
                                        <ol class="ps-3 mb-0">
                                            @foreach($data->guru as $g)
                                                <li>{{ $g }}</li>
                                            @endforeach
                                        </ol>
                                    @else
                                        <em class="text-muted">Belum ada guru pengajar</em>
                                    @endif
                                </div>

                                <div class="mb-3">
                                    <h6 class="fw-semibold text-secondary mb-1">Subject</h6>
                                    @if($data->subjects->isNotEmpty())
                                        <ol class="ps-3 mb-0">
                                            @foreach($data->subjects as $s)
                                                <li>{{ $s }}</li>
                                            @endforeach
                                        </ol>
                                    @else
                                        <em class="text-muted">Tidak ada subject</em>
                                    @endif
                                </div>

                                <div class="mb-3">
                                    <h6 class="fw-semibold text-secondary mb-1">Topic</h6>
                                    @if($data->topics->isNotEmpty())
                                        <ol class="ps-3 mb-0">
                                            @foreach($data->topics as $t)
                                                <li>{{ $t }}</li>
                                            @endforeach
                                        </ol>
                                    @else
                                        <em class="text-muted">Tidak ada topic</em>
                                    @endif
                                </div>

                                <div>
                                    <h6 class="fw-semibold text-secondary mb-1">Activity</h6>
                                    @if($data->activities->isNotEmpty())
                                        <ol class="ps-3 mb-0">
                                            @foreach($data->activities as $a)
                                                <li>{{ $a }}</li>
                                            @endforeach
                                        </ol>
                                    @else
                                        <em class="text-muted">Tidak ada activity</em>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Modal Tambah Kelas -->
    <div class="modal fade" id="modalTambah" tabindex="-1" aria-labelledby="modalTambahLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form action="{{ route('kelas.tambah') }}" method="POST" class="modal-content">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="modalTambahLabel">Tambah Kelas Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Kelas</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Level</label>
                        <input type="text" name="level" class="form-control" placeholder="Contoh: X, XI, XII" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Deskripsi (opsional)</label>
                        <textarea name="description" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button class="btn btn-primary" type="submit">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Gabung Kelas -->
    <div class="modal fade" id="modalGabung" tabindex="-1" aria-labelledby="modalGabungLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form action="{{ route('kelas.gabung') }}" method="POST" class="modal-content">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="modalGabungLabel">Gabung ke Kelas</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <label class="form-label">Masukkan Token Kelas</label>
                    <input type="text" name="token" class="form-control" required>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button class="btn btn-success" type="submit">Gabung</button>
                </div>
            </form>
        </div>
    </div>

    <style>
        .kelas-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.12);
        }
    </style>
@endsection