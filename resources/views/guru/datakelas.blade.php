@extends('layouts.main')

@section('dataKelas', request()->is('datakelas') ? 'active' : '')

@section('content')
    <div class="container py-4">

        {{-- Flash messages --}}
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
        @if(session('info'))
            <div class="alert alert-info">{{ session('info') }}</div>
        @endif

        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start mb-4 gap-3">
            <div>
                <h2 class="fw-bold mb-1">Daftar Kelas Anda</h2>
            </div>

            <div class="d-flex gap-2">
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambah">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                        class="bi bi-plus-lg me-1" viewBox="0 0 16 16">
                        <path
                            d="M8 1a.5.5 0 0 1 .5.5V7.5H14a.5.5 0 0 1 0 1H8.5V14a.5.5 0 0 1-1 0V8.5H2a.5.5 0 0 1 0-1h5.5V1.5A.5.5 0 0 1 8 1z" />
                    </svg>
                    Tambah Kelas
                </button>

                <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalGabung">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                        class="bi bi-box-arrow-in-right me-1" viewBox="0 0 16 16">
                        <path fill-rule="evenodd"
                            d="M6 3.5a.5.5 0 0 1 .5-.5H13a1 1 0 0 1 1 1v7a1 1 0 0 1-1 1H6.5a.5.5 0 0 1-.5-.5V11a.5.5 0 0 1 1 0v2h6V4h-6v2a.5.5 0 0 1-1 0V3.5z" />
                        <path fill-rule="evenodd"
                            d="M1.146 8.354a.5.5 0 0 1 0-.708L3.793 5H3.5a.5.5 0 0 1 0-1h3.5a.5.5 0 0 1 .354.854L3.854 8l3.5 3.146A.5.5 0 0 1 7 12.5H3.5a.5.5 0 0 1 0-1h.293L1.146 8.354z" />
                    </svg>
                    Gabung Kelas
                </button>
            </div>
        </div>

        {{-- Jika tidak ada kelas --}}
        @if($dataKelas->isEmpty())
            <div class="alert alert-info">Anda belum mengajar kelas apa pun.</div>
        @else
            <div class="row g-4">
                @foreach($dataKelas as $data)
                    <div class="col-12 col-md-6 col-lg-4">
                        <article class="card h-100 shadow-sm border-0 rounded-4 kelas-card overflow-hidden">
                            <div class="card-header bg-gradient p-3 d-flex justify-content-between align-items-start">
                                <div class="text-primary">
                                    <h5 class="mb-1 fw-bold" style="letter-spacing: .2px;">{{ $data->kelas->name }}</h5>
                                    <div class="small opacity-85">
                                        <span class="me-2">Semester:
                                            <strong>{{ $data->kelas->semester == 'odd' ? 'Ganjil' : 'Genap' }}</strong></span>
                                    </div>
                                </div>

                                <div class="dropdown">
                                    <button class="btn btn-sm btn-light btn-icon rounded-circle" type="button"
                                        id="menuKelas{{ $loop->index }}" data-bs-toggle="dropdown" aria-expanded="false"
                                        title="Actions">
                                        ⋮
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="menuKelas{{ $loop->index }}">
                                        <li>
                                            <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#modalEdit{{ $loop->index }}">
                                                Edit Kelas
                                            </a>
                                        </li>
                                        <li><a class="dropdown-item" href="#">Lihat Detail</a></li>
                                        <li>
                                            <hr class="dropdown-divider">
                                        </li>
                                        <li>
                                            <form action="{{ route('kelas.hapus', $data->kelas->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus kelas ini? Semua data terkait mungkin akan hilang.');" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button class="dropdown-item text-danger" type="submit">Hapus Kelas</button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            </div>

                            <div class="card-body p-3">
                                {{-- Meta info vertical --}}
                                <dl class="row mb-3">
                                    <dt class="col-4 text-muted small">Jenjang</dt>
                                    <dd class="col-8 mb-1"><span
                                            class="badge bg-light text-dark border px-3 py-2">{{ $data->kelas->level }}</span></dd>

                                    <dt class="col-4 text-muted small">Kelas</dt>
                                    <dd class="col-8 mb-1"><span class="badge bg-light text-dark border px-3 py-2">Grade
                                            {{ $data->kelas->grade }}</span></dd>

                                    <dt class="col-4 text-muted small">Token</dt>
                                    <dd class="col-8 mb-0 d-flex align-items-center gap-2">
                                        <code class="px-2 py-1 rounded bg-white text-secondary border"
                                            id="tokenText{{ $loop->index }}">{{ $data->kelas->token }}</code>
                                        <button class="btn btn-sm btn-outline-secondary"
                                            onclick="copyToken('tokenText{{ $loop->index }}')" data-bs-toggle="tooltip"
                                            title="Salin token">
                                            Salin
                                        </button>
                                    </dd>
                                </dl>

                                {{-- Lists with collapse (to keep kartu ringkas) --}}
                                <div class="mb-3">
                                    <h6 class="fw-semibold text-secondary mb-1">Guru Pengajar</h6>
                                    @if($data->guru->isNotEmpty())
                                        <div class="collapse show" id="guruList{{ $loop->index }}">
                                            <ol class="ps-3 mb-0 small max-list" aria-hidden="false">
                                                @foreach($data->guru as $g)
                                                    <li>{{ $g }}</li>
                                                @endforeach
                                            </ol>
                                        </div>
                                    @else
                                        <em class="text-muted small">Belum ada guru pengajar</em>
                                    @endif
                                </div>

                                <div class="mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <h6 class="fw-semibold text-secondary mb-0">Mata Pelajaran</h6>
                                        @if($data->subjects->count() > 3)
                                            <a class="small" data-bs-toggle="collapse" href="#subjectList{{ $loop->index }}"
                                                role="button" aria-expanded="false">Lihat semua</a>
                                        @endif
                                    </div>

                                    @if($data->subjects->isNotEmpty())
                                        <div class="collapse {{ $data->subjects->count() <= 3 ? 'show' : '' }}"
                                            id="subjectList{{ $loop->index }}">
                                            <ol class="ps-3 mb-0 small max-list">
                                                @foreach($data->subjects as $s)
                                                    <li>{{ $s }}</li>
                                                @endforeach
                                            </ol>
                                        </div>
                                    @else
                                        <em class="text-muted small">Tidak ada Mata Pelajaran</em>
                                    @endif
                                </div>

                                <div class="mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <h6 class="fw-semibold text-secondary mb-0">Topik</h6>
                                        @if($data->topics->count() > 3)
                                            <a class="small" data-bs-toggle="collapse" href="#topicList{{ $loop->index }}" role="button"
                                                aria-expanded="false">Lihat semua</a>
                                        @endif
                                    </div>

                                    @if($data->topics->isNotEmpty())
                                        <div class="collapse {{ $data->topics->count() <= 3 ? 'show' : '' }}"
                                            id="topicList{{ $loop->index }}">
                                            <ol class="ps-3 mb-0 small max-list">
                                                @foreach($data->topics as $t)
                                                    <li>{{ $t }}</li>
                                                @endforeach
                                            </ol>
                                        </div>
                                    @else
                                        <em class="text-muted small">Tidak ada topic</em>
                                    @endif
                                </div>

                                <div>
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <h6 class="fw-semibold text-secondary mb-0">Aktivitas</h6>
                                        @if($data->activities->count() > 3)
                                            <a class="small" data-bs-toggle="collapse" href="#activityList{{ $loop->index }}"
                                                role="button" aria-expanded="false">Lihat semua</a>
                                        @endif
                                    </div>

                                    @if($data->activities->isNotEmpty())
                                        <div class="collapse {{ $data->activities->count() <= 3 ? 'show' : '' }}"
                                            id="activityList{{ $loop->index }}">
                                            <ol class="ps-3 mb-0 small max-list">
                                                @foreach($data->activities as $a)
                                                    <li>{{ $a }}</li>
                                                @endforeach
                                            </ol>
                                        </div>
                                    @else
                                        <em class="text-muted small">Tidak ada activity</em>
                                    @endif
                                </div>
                            </div>
                        </article>
                    </div>

                    {{-- Edit Modal per item (tanpa AJAX) --}}
                    <div class="modal fade" id="modalEdit{{ $loop->index }}" tabindex="-1">
                        <div class="modal-dialog">
                            <form action="{{ route('kelas.update', $data->kelas->id) }}" method="POST" class="modal-content">
                                @csrf
                                @method('PUT')
                                <div class="modal-header">
                                    <h5 class="modal-title fw-bold">Edit Kelas: {{ $data->kelas->name }}</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>

                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label class="form-label">Nama Kelas</label>
                                        <input type="text" name="name" class="form-control" value="{{ old('name', $data->kelas->name) }}" required>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Level (Jenjang)</label>
                                        <select name="level" class="form-control form-select" required>
                                            <option value="">Pilih Jenjang</option>
                                            @php $levels = ['SD','MI','SMP','MTs','SMA','SMK','MA','PT']; @endphp
                                            @foreach($levels as $level)
                                                <option value="{{ $level }}" {{ (old('level', $data->kelas->level) == $level) ? 'selected' : '' }}>{{ $level }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Grade (Kelas)</label>
                                        <select name="grade" class="form-control form-select" required>
                                            <option value="">Pilih Kelas</option>
                                            @foreach($grades as $g)
                                                <option value="{{ $g }}" {{ (old('grade', $data->kelas->grade) == $g) ? 'selected' : '' }}>{{ $g }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Semester</label>
                                        <select name="semester" class="form-control form-select" required>
                                            <option value="odd" {{ (old('semester', $data->kelas->semester) == 'odd') ? 'selected' : '' }}>Ganjil</option>
                                            <option value="even" {{ (old('semester', $data->kelas->semester) == 'even') ? 'selected' : '' }}>Genap</option>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Deskripsi (Opsional)</label>
                                        <textarea name="description" class="form-control" rows="3">{{ old('description', $data->kelas->description) }}</textarea>
                                    </div>
                                </div>

                                <div class="modal-footer">
                                    <button class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                    <button class="btn btn-primary" type="submit">Simpan Perubahan</button>
                                </div>
                            </form>
                        </div>
                    </div>

                @endforeach
            </div>
        @endif
    </div>

    {{-- Modal Tambah --}}
    <div class="modal fade" id="modalTambah" tabindex="-1">
        <div class="modal-dialog">
            <form action="{{ route('kelas.tambah') }}" method="POST" class="modal-content">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Tambah Kelas Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Kelas</label>
                        <input type="text" name="name" class="form-control" placeholder="kelas 7A" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Level (Jenjang)</label>
                        <select name="level" class="form-control form-select" required>
                            <option value="">Pilih Jenjang</option>
                            <option>SD</option>
                            <option>MI</option>
                            <option>SMP</option>
                            <option>MTs</option>
                            <option>SMA</option>
                            <option>SMK</option>
                            <option>MA</option>
                            <option>PT</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Grade (Kelas)</label>
                        <select name="grade" class="form-control form-select" required>
                            <option value="">Pilih Kelas</option>
                            @foreach($grades as $g)
                                <option value="{{ $g }}">{{ $g }}</option>
                            @endforeach
                        </select>

                    </div>

                    <div class="mb-3">
                        <label class="form-label">Semester</label>
                        <select name="semester" class="form-control form-select" required>
                            <option value="odd">Ganjil</option>
                            <option value="even">Genap</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Deskripsi (Opsional)</label>
                        <textarea name="description" class="form-control" rows="3"></textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button class="btn btn-primary" type="submit">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Gabung --}}
    <div class="modal fade" id="modalGabung" tabindex="-1">
        <div class="modal-dialog">
            <form action="{{ route('kelas.gabung') }}" method="POST" class="modal-content">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Gabung Kelas</h5>
                    <button class="btn-close" type="button" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <label class="form-label">Token Kelas</label>
                    <input type="text" name="token" class="form-control" required>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button class="btn btn-success" type="submit">Gabung</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Styles khusus --}}
    <style>
        .bg-gradient {
            background: linear-gradient(135deg, #0d6efd 0%, #3b82f6 100%);
        }

        .btn-icon {
            width: 36px;
            height: 36px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .kelas-card {
            transition: transform .18s ease, box-shadow .18s ease;
        }

        .kelas-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 12px 30px rgba(13, 110, 253, .12);
        }

        .max-list {
            max-height: 6.5rem;
            /* membuat list tidak memanjang */
            overflow: auto;
        }

        /* scrollbar kecil agar rapi */
        .max-list::-webkit-scrollbar {
            height: 6px;
            width: 6px;
        }

        .max-list::-webkit-scrollbar-thumb {
            background: rgba(0, 0, 0, 0.08);
            border-radius: 6px;
        }
    </style>

    {{-- Script kecil: copy token & tooltip --}}
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                // Tooltips bootstrap
                var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
                tooltipTriggerList.map(function (el) {
                    return new bootstrap.Tooltip(el)
                })
            });

            function copyToken(elementId) {
                const el = document.getElementById(elementId);
                if (!el) return;
                navigator.clipboard.writeText(el.textContent.trim()).then(function () {
                    // simple toast/feedback: gunakan alert atau bootstrap toast jika tersedia
                    const btn = event?.target;
                    if (btn) {
                        btn.setAttribute('data-bs-original-title', 'Tersalin!');
                        var t = bootstrap.Tooltip.getInstance(btn);
                        if (t) { t.show(); setTimeout(() => t.hide(), 900); }
                    } else {
                        alert('Token disalin: ' + el.textContent.trim());
                    }
                }).catch(function () {
                    alert('Gagal menyalin token. Silakan salin manual.');
                });
            }
        </script>
    @endpush
@endsection
