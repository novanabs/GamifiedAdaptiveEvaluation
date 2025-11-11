@extends('layouts.main')

@section('content')
    <div class="container mt-4">
        <h3 class="fw-bold mb-4 text-center text-primary">📘 Data Aktivitas Berdasarkan Topik</h3>

        {{-- 🔹 Pesan sukses --}}
        @if(session('success'))
            <div class="alert alert-success text-center shadow-sm">{{ session('success') }}</div>
        @endif

        {{-- 🔹 Form tambah aktivitas --}}
        <div class="card shadow-sm mb-4 border-0">
            <div class="card-header bg-primary text-white fw-semibold">
                <i class="bi bi-plus-circle me-2"></i> Tambah Aktivitas
            </div>
            <div class="card-body">
                <form action="{{ route('guru.aktivitas.simpan') }}" method="POST">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Judul Aktivitas</label>
                            <input type="text" name="title" class="form-control" placeholder="Masukkan judul" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold">Status</label>
                            <select name="status" class="form-select" required>
                                <option value="basic">Basic</option>
                                <option value="additional">Additional</option>
                                <option value="remedial">Remedial</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold">Tipe</label>
                            <select name="type" class="form-select" required>
                                <option value="task">Tugas</option>
                                <option value="quiz">Kuis</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold">Deadline</label>
                            <input type="datetime-local" name="deadline" class="form-control">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold">Topik</label>
                            <select name="id_topic" class="form-select" required>
                                <option value="">Pilih Topik</option>
                                @foreach($data as $topic)
                                    <option value="{{ $topic->id }}">
                                        {{ $topic->title }} ({{ $topic->subject->name ?? 'Tanpa Subject' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-1 d-flex align-items-end">
                            <button class="btn btn-success w-100">
                                <i class="bi bi-save me-1"></i> Simpan
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- 🔹 Daftar aktivitas per topik --}}
        @foreach($data as $topic)
            <div class="card mb-4 border-0 shadow-sm">
                <div class="card-header bg-light fw-bold">
                    <span class="text-primary">{{ $topic->title }}</span>
                    <small class="text-muted">({{ $topic->subject->name ?? 'Tanpa Subject' }})</small>
                </div>
                <div class="card-body">
                    @if($topic->activities->isEmpty())
                        <p class="text-muted fst-italic mb-0">Belum ada aktivitas untuk topik ini.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-bordered align-middle">
                                <thead class="table-primary text-center">
                                    <tr>
                                        <th style="width: 50px;">No</th>
                                        <th>Judul</th>
                                        <th>Status</th>
                                        <th>Tipe</th>
                                        <th>Deadline</th>
                                        <th style="width: 220px;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($topic->activities as $aktivitas)
                                        @php
                                            $selectedIds = $activityQuestions->where('id_activity', $aktivitas->id)->pluck('id_question')->toArray();
                                            $selectedQuestions = $questions->whereIn('id', $selectedIds);
                                        @endphp
                                        <tr>
                                            <td class="text-center">{{ $loop->iteration }}</td>

                                            {{-- Judul & Update --}}
                                            <td>
                                                <form action="{{ route('guru.aktivitas.ubah', $aktivitas->id) }}" method="POST"
                                                    class="d-flex">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="text" name="title" value="{{ $aktivitas->title }}"
                                                        class="form-control me-2">
                                            </td>

                                            {{-- Status --}}
                                            <td>
                                                <select name="status" class="form-select">
                                                    <option value="basic" {{ $aktivitas->status == 'basic' ? 'selected' : '' }}>Basic
                                                    </option>
                                                    <option value="additional" {{ $aktivitas->status == 'additional' ? 'selected' : '' }}>
                                                        Additional</option>
                                                    <option value="remedial" {{ $aktivitas->status == 'remedial' ? 'selected' : '' }}>
                                                        Remedial</option>
                                                </select>
                                            </td>

                                            {{-- Tipe --}}
                                            <td>
                                                <select name="type" class="form-select">
                                                    <option value="task" {{ $aktivitas->type == 'task' ? 'selected' : '' }}>Tugas</option>
                                                    <option value="quiz" {{ $aktivitas->type == 'quiz' ? 'selected' : '' }}>Kuis</option>
                                                </select>
                                            </td>

                                            {{-- Deadline --}}
                                            <td>
                                                <input type="datetime-local" name="deadline"
                                                    value="{{ $aktivitas->deadline ? date('Y-m-d\TH:i', strtotime($aktivitas->deadline)) : '' }}"
                                                    class="form-control">
                                            </td>

                                            {{-- Aksi --}}
                                            <td class="text-center">
                                                <div class="d-flex justify-content-center gap-1 flex-wrap">
                                                    <button type="submit" class="btn btn-sm btn-success">
                                                        <i class="bi bi-check-circle me-1"></i> Perbarui
                                                    </button>
                                                    </form>

                                                    <form action="{{ route('guru.aktivitas.hapus', $aktivitas->id) }}" method="POST"
                                                        onsubmit="return confirm('Yakin ingin menghapus aktivitas ini?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger">
                                                            <i class="bi bi-trash me-1"></i> Hapus
                                                        </button>
                                                    </form>

                                                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal"
                                                        data-bs-target="#aturSoal{{ $aktivitas->id }}">
                                                        <i class="bi bi-gear me-1"></i> Atur Soal
                                                    </button>

                                                    <button type="button" class="btn btn-sm btn-info text-white" data-bs-toggle="modal"
                                                        data-bs-target="#lihatSoal{{ $aktivitas->id }}">
                                                        <i class="bi bi-eye me-1"></i> Lihat
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>

                                        {{-- 🔹 Simpan ID untuk modal nanti --}}
                                        @push('modals')
                                            {{-- Modal Atur Soal --}}
                                            <div class="modal fade" id="aturSoal{{ $aktivitas->id }}" tabindex="-1" aria-hidden="true">
                                                <div class="modal-dialog modal-lg modal-dialog-scrollable">
                                                    <div class="modal-content">
                                                        <form method="POST" action="{{ route('guru.simpanAturSoal', $aktivitas->id) }}">
                                                            @csrf
                                                            <div class="modal-header bg-primary text-white">
                                                                <h5 class="modal-title">
                                                                    <i class="bi bi-list-task me-2"></i> Atur Soal: {{ $aktivitas->title }}
                                                                </h5>
                                                                <button type="button" class="btn-close btn-close-white"
                                                                    data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <table class="table table-bordered align-middle">
                                                                    <thead class="table-light text-center">
                                                                        <tr>
                                                                            <th>Pilih</th>
                                                                            <th>ID</th>
                                                                            <th>Tipe</th>
                                                                            <th>Pertanyaan</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        @foreach ($questions as $q)
                                                                            @php
                                                                                $checked = in_array($q->id, $selectedIds) ? 'checked' : '';
                                                                                $qData = json_decode($q->question);
                                                                            @endphp
                                                                            <tr>
                                                                                <td class="text-center">
                                                                                    <input type="checkbox" name="id_question[]"
                                                                                        value="{{ $q->id }}" {{ $checked }}>
                                                                                </td>
                                                                                <td class="text-center">{{ $q->id }}</td>
                                                                                <td class="text-center">
                                                                                    {{ $q->type == 'MultipleChoice' ? 'Pilihan Ganda' : 'Isian Singkat' }}
                                                                                </td>
                                                                                <td>{{ $qData->text ?? 'Teks kosong' }}</td>
                                                                            </tr>
                                                                        @endforeach
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary"
                                                                    data-bs-dismiss="modal">Tutup</button>
                                                                <button type="submit" class="btn btn-success">
                                                                    <i class="bi bi-save me-1"></i> Simpan
                                                                </button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- Modal Lihat Soal --}}
                                            <div class="modal fade" id="lihatSoal{{ $aktivitas->id }}" tabindex="-1" aria-hidden="true">
                                                <div class="modal-dialog modal-lg modal-dialog-scrollable">
                                                    <div class="modal-content">
                                                        <div class="modal-header bg-info text-white">
                                                            <h5 class="modal-title">
                                                                <i class="bi bi-eye me-2"></i> Daftar Soal: {{ $aktivitas->title }}
                                                            </h5>
                                                            <button type="button" class="btn-close btn-close-white"
                                                                data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            @if($selectedQuestions->isEmpty())
                                                                <p class="text-center text-muted mb-0">Belum ada soal untuk aktivitas ini.</p>
                                                            @else
                                                                <ul class="list-group">
                                                                    @foreach ($selectedQuestions as $s)
                                                                        @php $sData = json_decode($s->question); @endphp
                                                                        <li class="list-group-item">{{ $sData->text ?? '-' }}</li>
                                                                    @endforeach
                                                                </ul>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endpush
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        @endforeach

        {{-- 🔹 Render semua modal di bawah konten --}}
        @stack('modals')
    </div>
@endsection