@extends('layouts.main')
@section('dataAktivitas', request()->is('dataaktivitas') ? 'active' : '')
@section('content')
    <div class="container mt-4">
        <h3 class="fw-bold mb-4">Data Aktivitas Berdasarkan Topik</h3>

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

                    <div class="row g-3 align-items-end">
                        <div class="col-md-2">
                            <label class="form-label fw-semibold">Judul Aktivitas</label>
                            <input type="text" name="title" class="form-control shadow-sm"
                                   placeholder="Masukkan judul aktivitas..." required>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Deadline</label>
                            <input type="datetime-local" name="deadline" class="form-control shadow-sm">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Topik</label>
                            <select name="id_topic" class="form-select shadow-sm" required>
                                <option value="">Pilih Topik</option>
                                @foreach($data as $topic)
                                    <option value="{{ $topic->id }}">
                                        {{ $topic->title }} ({{ $topic->subject->name ?? 'Tanpa Subject' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label fw-semibold">Adaptive</label>
                            <div class="form-check">
                                <input type="hidden" name="addaptive" value="no">
                                <input class="form-check-input" type="checkbox" name="addaptive" value="yes">
                                <label class="form-check-label">Aktifkan adaptive</label>
                            </div>
                        </div>

                        <div class="col-md-2 d-flex justify-content-end">
                            <button class="btn btn-success px-4 py-2 shadow-sm mt-2 w-100">
                                Simpan Aktivitas
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
                    <span>{{ $topic->title }}</span>
                    <small class="text-muted">({{ $topic->subject->name ?? 'Tanpa Subject' }})</small>
                </div>

                <div class="card-body">
                    @if($topic->activities->isEmpty())
                        <p class="text-muted fst-italic mb-0">Belum ada aktivitas untuk topik ini.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-bordered align-middle">
                                <thead class="table-secondary text-center">
                                    <tr>
                                        <th style="width: 50px;">No</th>
                                        <th>Judul</th>
                                        <th>Deadline</th>
                                        <th>Status</th>
                                        <th style="width: 220px;">Aksi</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @foreach($topic->activities as $aktivitas)
                                        @php
                                            $selectedIds = $activityQuestions->where('id_activity', $aktivitas->id)
                                                ->pluck('id_question')
                                                ->toArray();
                                            $selectedQuestions = $questions->whereIn('id', $selectedIds);
                                        @endphp

                                        <tr>
                                            <td class="text-center">{{ $loop->iteration }}</td>

                                            {{-- Judul & Update --}}
                                            <td>
                                                <form action="{{ route('guru.aktivitas.ubah', $aktivitas->id) }}"
                                                      method="POST" class="d-flex">
                                                    @csrf
                                                    @method('PUT')

                                                    <input type="text" name="title"
                                                           value="{{ $aktivitas->title }}"
                                                           class="form-control me-2">
                                            </td>

                                            {{-- Deadline --}}
                                            <td>
                                                <input type="datetime-local" name="deadline"
                                                       value="{{ $aktivitas->deadline ? date('Y-m-d\TH:i', strtotime($aktivitas->deadline)) : '' }}"
                                                       class="form-control">
                                            </td>

                                            {{-- Adaptive --}}
                                            <td>
                                                <div class="form-check mt-2">
                                                    <input type="hidden" name="addaptive" value="no">
                                                    <input class="form-check-input" name="addaptive" type="checkbox"
                                                           value="yes" {{ $aktivitas->addaptive === 'yes' ? 'checked' : '' }}>
                                                    <label class="form-check-label">Adaptive</label>
                                                </div>
                                            </td>

                                            {{-- Aksi --}}
                                            <td class="text-center">
                                                <div class="d-flex justify-content-center gap-1 flex-wrap">
                                                    <button type="submit" class="btn btn-sm btn-success">
                                                        <i class="bi bi-check-circle me-1"></i> Perbarui
                                                    </button>
                                                </form>

                                                <form action="{{ route('guru.aktivitas.hapus', $aktivitas->id) }}"
                                                      method="POST"
                                                      onsubmit="return confirm('Yakin ingin menghapus aktivitas ini?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger">
                                                        <i class="bi bi-trash me-1"></i> Hapus
                                                    </button>
                                                </form>

                                                <button type="button" class="btn btn-sm btn-primary"
                                                        onclick="window.location.href='{{ url('/guru/aktivitas/' . $aktivitas->id . '/atur-soal?topic=' . $aktivitas->id_topic) }}'">
                                                    <i class="bi bi-gear me-1"></i> Atur Soal
                                                </button>

                                                <button type="button" class="btn btn-sm btn-info text-white"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#lihatSoal{{ $aktivitas->id }}">
                                                    <i class="bi bi-eye me-1"></i> Lihat
                                                </button>
                                                </div>
                                            </td>
                                        </tr>

                                        @push('modals')
                                            {{-- Modal Lihat Soal --}}
                                            <div class="modal fade" id="lihatSoal{{ $aktivitas->id }}" tabindex="-1"
                                                 aria-hidden="true">
                                                <div class="modal-dialog modal-xl modal-dialog-scrollable">
                                                    <div class="modal-content border-0 shadow">

                                                        <div class="modal-header bg-info text-white">
                                                            <h5 class="modal-title">
                                                                <i class="bi bi-list-check me-2"></i>
                                                                Daftar Soal – {{ $aktivitas->title }}
                                                            </h5>
                                                            <button type="button" class="btn-close btn-close-white"
                                                                    data-bs-dismiss="modal"></button>
                                                        </div>

                                                        <div class="modal-body">
                                                            @if($selectedQuestions->isEmpty())
                                                                <div class="text-center text-muted py-4">
                                                                    <i class="bi bi-inboxes fs-1 d-block mb-2"></i>
                                                                    Belum ada soal untuk aktivitas ini.
                                                                </div>
                                                            @else
                                                                <div class="table-responsive">
                                                                    <table class="table table-striped table-bordered align-middle">
                                                                        <thead class="table-secondary text-center">
                                                                            <tr>
                                                                                <th width="5%">No</th>
                                                                                <th width="12%">Tipe</th>
                                                                                <th width="12%">Kesulitan</th>
                                                                                <th>Pertanyaan</th>
                                                                            </tr>
                                                                        </thead>

                                                                        <tbody>
                                                                            @foreach ($selectedQuestions as $s)
                                                                                @php $sData = json_decode($s->question); @endphp
                                                                                <tr>
                                                                                    <td class="text-center fw-bold">{{ $loop->iteration }}</td>
                                                                                    <td class="text-center">
                                                                                        <span class="badge bg-primary">{{ $s->type }}</span>
                                                                                    </td>
                                                                                    <td class="text-center">
                                                                                        @if($s->difficulty == 'mudah')
                                                                                            <span class="badge bg-success">Mudah</span>
                                                                                        @elseif($s->difficulty == 'sedang')
                                                                                            <span class="badge bg-warning text-dark">Sedang</span>
                                                                                        @else
                                                                                            <span class="badge bg-danger">Sulit</span>
                                                                                        @endif
                                                                                    </td>
                                                                                    <td>{{ $sData->text ?? '-' }}</td>
                                                                                </tr>
                                                                            @endforeach
                                                                        </tbody>
                                                                    </table>
                                                                </div>
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
