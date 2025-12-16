@extends('layouts.main')

@section('dataKelas', request()->is('datakelas') ? 'active' : '')

@section('content')
    <div class="container py-4">
        {{-- PENTING: pastikan di layouts.main ada:
        <meta name="csrf-token" content="{{ csrf_token() }}">
        --}}
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
            <div class="d-flex align-items-center gap-2">
                <h2 class="fw-bold mb-0">Daftar Kelas Anda</h2>

                <button type="button" class="btn btn-sm btn-outline-secondary rounded-circle" style="width:32px;height:32px"
                    data-bs-toggle="modal" data-bs-target="#modalInfoKelas" title="Informasi Pengelolaan Kelas">
                    <i class="bi bi-info-lg"></i>
                </button>
            </div>


            <div class="d-flex gap-2">
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambah">
                    <!-- svg omitted for brevity -->
                    Tambah Kelas
                </button>

                <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalGabung">
                    <!-- svg omitted for brevity -->
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
                                            <a class="dropdown-item" href="#" data-bs-toggle="modal"
                                                data-bs-target="#modalEdit{{ $loop->index }}">
                                                Edit Kelas
                                            </a>
                                        </li>
                                        <li><a class="dropdown-item" href="#">Lihat Detail</a></li>
                                        <li>
                                            <hr class="dropdown-divider">
                                        </li>
                                        <li>
                                            <form action="{{ route('kelas.hapus', $data->kelas->id) }}" method="POST"
                                                onsubmit="return confirm('Yakin ingin menghapus kelas ini? Semua data terkait mungkin akan hilang.');"
                                                class="d-inline">
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
                                <!-- klaim paket -->
                                <div class="mt-2">
                                    <button class="btn btn-outline-primary btn-sm btn-open-claim-modal"
                                        data-class-id="{{ $data->kelas->id }}" data-class-name="{{ $data->kelas->name }}">
                                        Klaim Paket
                                    </button>
                                </div>

                                {{-- Lists with collapse --}}
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

                    {{-- Edit Modal per item --}}
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
                                        <input type="text" name="name" class="form-control"
                                            value="{{ old('name', $data->kelas->name) }}" required>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Level (Jenjang)</label>
                                        <select name="level" class="form-control form-select" required>
                                            <option value="">Pilih Jenjang</option>
                                            @php $levels = ['SD', 'MI', 'SMP', 'MTs', 'SMA', 'SMK', 'MA', 'PT']; @endphp
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
                                        <textarea name="description" class="form-control"
                                            rows="3">{{ old('description', $data->kelas->description) }}</textarea>
                                    </div>
                                </div>

                                <div class="modal-footer">
                                    <button class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                    <button class="btn btn-primary" type="submit">Simpan Perubahan</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    <!-- modal info -->
                    <div class="modal fade" id="modalInfoKelas" tabindex="-1">
                        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                            <div class="modal-content rounded-4">
                                <div class="modal-header">
                                    <h5 class="modal-title fw-bold">
                                        <i class="bi bi-info-circle me-1"></i>
                                        Panduan Pengelolaan Kelas
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>

                                <div class="modal-body">

                                    <!-- Tambah Kelas -->
                                    <section class="mb-4">
                                        <h6 class="fw-semibold text-primary">
                                            <i class="bi bi-plus-circle me-1"></i> Menambah Kelas
                                        </h6>
                                        <ol class="small text-muted">
                                            <li>Klik tombol <strong>Tambah Kelas</strong></li>
                                            <li>Isi nama kelas, jenjang, grade, dan semester</li>
                                            <li>Klik <strong>Simpan</strong></li>
                                            <li>Kelas akan muncul di daftar kelas Anda</li>
                                        </ol>
                                        <img src="{{ asset('img/info/tambah-kelas.png') }}" class="img-fluid rounded border"
                                            alt="Tambah Kelas">
                                    </section>

                                    <!-- Gabung Kelas -->
                                    <section class="mb-4">
                                        <h6 class="fw-semibold text-success">
                                            <i class="bi bi-link-45deg me-1"></i> Gabung Kelas
                                        </h6>
                                        <ol class="small text-muted">
                                            <li>Klik tombol <strong>Gabung Kelas</strong></li>
                                            <li>Masukkan <strong>Token Kelas</strong> dari guru</li>
                                            <li>Klik <strong>Gabung</strong></li>
                                            <li>Anda akan otomatis terdaftar di kelas tersebut</li>
                                        </ol>
                                        <img src="{{ asset('img/info/gabung-kelas.png') }}" class="img-fluid rounded border"
                                            alt="Gabung Kelas">
                                    </section>

                                    <!-- Edit Kelas -->
                                    <section>
                                        <h6 class="fw-semibold text-warning">
                                            <i class="bi bi-pencil-square me-1"></i> Mengedit Kelas
                                        </h6>
                                        <ol class="small text-muted">
                                            <li>Klik ikon <strong>⋮</strong> pada kartu kelas</li>
                                            <li>Pilih <strong>Edit Kelas</strong></li>
                                            <li>Ubah data yang diperlukan</li>
                                            <li>Klik <strong>Simpan Perubahan</strong></li>
                                        </ol>
                                        <img src="{{ asset('img/info/edit-kelas.png') }}" class="img-fluid rounded border"
                                            alt="Edit Kelas">
                                    </section>

                                </div>

                                <div class="modal-footer">
                                    <button class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                </div>
                            </div>
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
    <!-- Modal Klaim Paket (global) -->
    <div class="modal fade" id="modalClaimPackage" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Klaim Paket — <span id="claimClassName"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="packagesList" class="list-group">
                        <div class="text-center text-muted py-4">Memuat paket...</div>
                    </div>
                </div>
            </div>
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
            overflow: auto;
        }

        .max-list::-webkit-scrollbar {
            height: 6px;
            width: 6px;
        }

        .max-list::-webkit-scrollbar-thumb {
            background: rgba(0, 0, 0, 0.08);
            border-radius: 6px;
        }
    </style>

    {{-- Script: gabungkan semua logic CSRF + fetch + swal --}}
    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

        <script>
            (function () {
                'use strict';

                function getCsrfToken() {
                    const meta = document.querySelector('meta[name="csrf-token"]');
                    if (meta && meta.content) return meta.content;
                    const cookieVal = getCookie('XSRF-TOKEN');
                    if (cookieVal) {
                        try { return decodeURIComponent(cookieVal); } catch (e) { return cookieVal; }
                    }
                    return null;
                }

                function getCookie(name) {
                    const v = document.cookie.match('(^|;)\\s*' + name + '\\s*=\\s*([^;]+)');
                    return v ? v.pop() : null;
                }

                async function csrfFetch(url, opts = {}) {
                    const csrf = getCsrfToken();
                    const defaultHeaders = { 'X-Requested-With': 'XMLHttpRequest' };

                    if (opts.body && typeof opts.body === 'object' && !(opts.body instanceof FormData)) {
                        defaultHeaders['Content-Type'] = 'application/json';
                        opts.body = JSON.stringify(opts.body);
                    }

                    if (csrf) defaultHeaders['X-CSRF-TOKEN'] = csrf;
                    opts.headers = Object.assign({}, defaultHeaders, opts.headers || {});
                    opts.credentials = opts.credentials || 'same-origin';

                    const res = await fetch(url, opts);
                    const contentType = res.headers.get('content-type') || '';
                    if (contentType.includes('application/json')) {
                        const json = await res.json();
                        if (!res.ok) {
                            const err = new Error('HTTP Error: ' + res.status);
                            err.response = res;
                            err.data = json;
                            throw err;
                        }
                        return json;
                    } else {
                        if (!res.ok) throw new Error('HTTP Error: ' + res.status);
                        return res;
                    }
                }

                document.addEventListener('DOMContentLoaded', function () {
                    // Buat paket (export)
                    document.querySelectorAll('.btn-create-package').forEach(btn => {
                        btn.addEventListener('click', async function (e) {
                            e.preventDefault();
                            const activityId = this.dataset.activityId;
                            const title = this.dataset.title || '';

                            const result = await Swal.fire({
                                title: 'Buat paket aktivitas?',
                                text: 'Paket akan berisi aktivitas & soal terkait.',
                                icon: 'question',
                                showCancelButton: true,
                                confirmButtonText: 'Buat Paket',
                                showLoaderOnConfirm: true,
                                preConfirm: async () => {
                                    try {
                                        return await csrfFetch(`/activity/${activityId}/package/create`, {
                                            method: 'POST',
                                            body: { title }
                                        });
                                    } catch (err) {
                                        throw err.data ?? err.message ?? err;
                                    }
                                }
                            });

                            if (result.isConfirmed) {
                                const data = result.value;
                                if (data && data.success) {
                                    Swal.fire('Sukses', 'Paket dibuat. Anda dapat mengunduh atau klaim paket.', 'success');
                                } else {
                                    Swal.fire('Gagal', (data && data.message) ? data.message : 'Gagal membuat paket', 'error');
                                }
                            }
                        });
                    });

                    // open claim modal for a class
                    document.querySelectorAll('.btn-open-claim-modal').forEach(btn => {
                        btn.addEventListener('click', async function (e) {
                            const classId = this.dataset.classId;
                            const className = this.dataset.className;
                            document.getElementById('claimClassName').textContent = className;
                            var modal = new bootstrap.Modal(document.getElementById('modalClaimPackage'));
                            modal.show();

                            const listEl = document.getElementById('packagesList');
                            listEl.innerHTML = `<div class="text-center py-4 text-muted">Memuat paket...</div>`;

                            try {
                                const json = await csrfFetch(`/activity-packages`, { method: 'GET' });
                                const data = json.data || json;
                                if (!data || data.length === 0) {
                                    listEl.innerHTML = `<div class="text-center py-4 text-muted">Tidak ada paket.</div>`;
                                    return;
                                }

                                const frag = document.createDocumentFragment();
                                data.forEach(p => {
                                    const item = document.createElement('div');
                                    item.className = 'list-group-item d-flex justify-content-between align-items-start';
                                    item.innerHTML = `
                                                            <div>
                                                                <div class="fw-semibold">${escapeHtml(p.title)}</div>
                                                                <div class="small text-muted">Sumber activity: ${escapeHtml(p.activity_title ?? '-')} — Kelas: ${escapeHtml(p.class_name ?? '-')}</div>
                                                            </div>
                                                            <div class="text-end">
                                                                                                                    <button class="btn btn-sm btn-primary btn-claim-package" data-pkg-id="${p.id}" data-class-id="${classId}">Klaim</button>
                                                                </div>
                                                            </div>`;
                                    frag.appendChild(item);
                                });
                                listEl.innerHTML = '';
                                listEl.appendChild(frag);
                            } catch (err) {
                                console.error(err);
                                listEl.innerHTML = `<div class="text-center py-4 text-danger">Gagal memuat paket.</div>`;
                            }
                        });
                    });

                    // delegate claim button
                    document.getElementById('packagesList').addEventListener('click', function (e) {
                        const btn = e.target.closest('.btn-claim-package');
                        if (!btn) return;
                        const pkgId = btn.dataset.pkgId;
                        const targetClassId = btn.dataset.classId;

                        Swal.fire({
                            title: 'Klaim paket ke kelas ini?',
                            html: `<div class="form-check text-start">
                                                        <input class="form-check-input" type="checkbox" id="duplicateCheck">
                                                        <label class="form-check-label" for="duplicateCheck">Duplicate soal jika belum ada (create new questions)</label>
                                                   </div>`,
                            showCancelButton: true,
                            confirmButtonText: 'Klaim',
                            preConfirm: async () => {
                                const duplicate = document.getElementById('duplicateCheck').checked;
                                try {
                                    return await csrfFetch(`/activity-package/${pkgId}/claim`, {
                                        method: 'POST',
                                        body: { target_class_id: targetClassId, duplicate: duplicate }
                                    });
                                } catch (err) {
                                    throw err.data ?? err.message ?? err;
                                }
                            }
                        }).then(result => {
                            if (result.isConfirmed) {
                                const resp = result.value;
                                if (resp && resp.success) {
                                    Swal.fire('Berhasil', 'Paket berhasil diklaim; aktivitas baru dibuat.', 'success')
                                        .then(() => { location.reload(); });
                                } else {
                                    Swal.fire('Gagal', (resp && resp.message) ? resp.message : 'Gagal klaim paket', 'error');
                                }
                            }
                        });
                    });

                    // Tooltips bootstrap
                    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
                    tooltipTriggerList.map(function (el) {
                        return new bootstrap.Tooltip(el)
                    });

                    // copy token
                    window.copyToken = function (elementId) {
                        const el = document.getElementById(elementId);
                        if (!el) return;
                        navigator.clipboard.writeText(el.textContent.trim()).then(function () {
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
                    };

                    function escapeHtml(unsafe) {
                        if (unsafe === null || unsafe === undefined) return '';
                        return String(unsafe)
                            .replace(/&/g, '&amp;')
                            .replace(/</g, '&lt;')
                            .replace(/>/g, '&gt;')
                            .replace(/"/g, '&quot;')
                            .replace(/'/g, '&#039;');
                    }
                });
            })();
        </script>
    @endpush
@endsection