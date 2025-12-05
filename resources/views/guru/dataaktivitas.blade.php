@extends('layouts.main')
@section('dataAktivitas', request()->is('dataaktivitas') ? 'active' : '')
@section('content')
    <div class="container mt-4">
        <h3 class="fw-bold mb-4">Data Aktivitas Berdasarkan Topik</h3>

        @if(session('success'))
            <div class="alert alert-success text-center shadow-sm">{{ session('success') }}</div>
        @endif

        <div class="card shadow-sm mb-4 border-0">
            <div class="card-header bg-primary text-white fw-semibold">
                <i class="bi bi-plus-circle me-2"></i> Tambah Aktivitas
            </div>
            <div class="card-body">
                <form action="{{ route('guru.aktivitas.simpan') }}" method="POST">
                    @csrf

                    <div class="row g-3">
                        {{-- KIRI: judul / deadline / topik (stacked) --}}
                        <div class="col-lg-8">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Judul Aktivitas</label>
                                <input type="text" name="title" class="form-control shadow-sm"
                                    placeholder="Masukkan judul aktivitas..." required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Deadline</label>
                                <input type="datetime-local" name="deadline" class="form-control shadow-sm">
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Topik</label>
                                <select name="id_topic" class="form-select shadow-sm" required>
                                    <option value="">Pilih Topik</option>
                                    @foreach(\App\Models\Topic::with('subject')->where('created_by', Auth::id())->get() as $topicOption)
                                        <option value="{{ $topicOption->id }}">
                                            {{ $topicOption->title }} ({{ $topicOption->subject->name ?? 'Tanpa Subject' }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- KANAN: grid 2 kolom x 3 baris --}}
                        <div class="col-lg-4">
                            <div class="row gx-2 gy-2">

                                {{-- Row 1: Durasi (kiri) & Adaptive (kanan) --}}
                                <div class="col-6">
                                    <label class="form-label fw-semibold">Durasi (menit)</label>
                                    <input type="number" name="durasi_pengerjaan" class="form-control shadow-sm" min="1"
                                        placeholder="30">
                                </div>

                                <div class="col-6 d-flex flex-column">
                                    <label class="form-label fw-semibold">Adaptive</label>
                                    <div class="form-check mt-1">
                                        <input type="hidden" name="addaptive" value="no">
                                        <input class="form-check-input" type="checkbox" name="addaptive" value="yes"
                                            id="adaptiveToggle">
                                        <label class="form-check-label" for="adaptiveToggle">Aktifkan</label>
                                    </div>
                                </div>

                                {{-- Row 2: (kosong / spacing) --}}
                                <div class="col-6">
                                    {{-- could put an extra small field / info if needed; left intentionally blank for
                                    spacing --}}
                                </div>
                                <div class="col-6">
                                    {{-- space --}}
                                </div>

                                {{-- Row 3: tombol Simpan (span 2 kolom) --}}
                                <div class="col-12 d-grid">
                                    <button class="btn btn-success shadow-sm py-2">
                                        Simpan
                                    </button>
                                </div>

                            </div><!-- /.row (kanan) -->
                        </div><!-- /.col-kanan -->

                    </div><!-- /.row utama -->
                </form>
            </div>


        </div>

        {{-- DataTables --}}
        <div class="card mb-4 border-0 shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="activitiesTable" class="table table-striped table-bordered nowrap" style="width:100%">
                        <thead class="table-secondary text-center">
                            <tr>
                                <th style="width:60px">No</th>
                                <th>Judul</th>
                                <th>Deadline</th>
                                <th>Adaptive</th>
                                <th>Topik</th>
                                <th>Subject</th>
                                <th>Kelas</th>
                                <th style="width:260px">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($rows as $r)
                                <tr>
                                    {{-- nomor dikosongkan, DataTables isi --}}
                                    <td class="text-center align-middle"></td>

                                    <td class="align-middle">{{ $r->title }}</td>
                                    <td class="align-middle">
                                        {{ $r->deadline ? date('Y-m-d H:i', strtotime($r->deadline)) : '-' }}
                                    </td>

                                    <td class="align-middle text-center">
                                        @if($r->addaptive === 'yes')
                                            <span class="badge bg-success">Ya</span>
                                        @else
                                            <span class="badge bg-secondary">Tidak</span>
                                        @endif
                                    </td>

                                    <td class="align-middle">{{ $r->topic_title }}</td>
                                    <td class="align-middle">{{ $r->subject_name ?? '-' }}</td>
                                    <td class="align-middle">{{ $r->class_name ?? '-' }}</td>

                                    <td class="align-middle text-center">
                                        <div class="action-group">
                                            {{-- Edit --}}
                                            <button class="btn btn-primary btn-sm" data-bs-toggle="modal"
                                                data-bs-target="#modalEdit{{ $r->id }}" data-bs-toggle="tooltip" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                                <span class="btn-text d-none d-sm-inline"> Edit</span>
                                            </button>

                                            {{-- Atur Soal --}}
                                            <a href="{{ url('/guru/aktivitas/' . $r->id . '/atur-soal?topic=' . $r->topic_id) }}"
                                                class="btn btn-warning btn-sm" data-bs-toggle="tooltip" title="Atur Soal">
                                                <i class="bi bi-gear"></i>
                                                <span class="btn-text d-none d-sm-inline"> Atur Soal</span>
                                            </a>

                                            {{-- Lihat --}}
                                            <button type="button" class="btn btn-info btn-sm text-white" data-bs-toggle="modal"
                                                data-bs-target="#lihatSoal{{ $r->id }}" data-bs-toggle="tooltip"
                                                title="Lihat Soal">
                                                <i class="bi bi-eye"></i>
                                                <span class="btn-text d-none d-sm-inline"> Lihat</span>
                                            </button>

                                            {{-- Hapus --}}
                                            <form action="{{ route('guru.aktivitas.hapus', $r->id) }}" method="POST"
                                                onsubmit="return confirm('Yakin ingin menghapus aktivitas ini?')"
                                                class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-danger btn-sm" data-bs-toggle="tooltip" title="Hapus">
                                                    <i class="bi bi-trash"></i>
                                                    <span class="btn-text d-none d-sm-inline"> Hapus</span>
                                                </button>
                                            </form>
                                        </div><!-- /.action-group -->
                                    </td>
                                </tr>

                                {{-- Pushing modals supaya tidak berada di dalam DOM table (DataTables safe) --}}
                                @push('modals')
                                    {{-- Modal Edit --}}
                                    <div class="modal fade" id="modalEdit{{ $r->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content">
                                                <form action="{{ route('guru.aktivitas.ubah', $r->id) }}" method="POST">
                                                    @csrf
                                                    @method('PUT')
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Edit Aktivitas — {{ $r->title }}</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="mb-3">
                                                            <label class="form-label">Judul</label>
                                                            <input type="text" name="title" class="form-control"
                                                                value="{{ $r->title }}" required>
                                                        </div>

                                                        <div class="mb-3">
                                                            <label class="form-label">Deadline</label>
                                                            <input type="datetime-local" name="deadline" class="form-control"
                                                                value="{{ $r->deadline ? date('Y-m-d\TH:i', strtotime($r->deadline)) : '' }}">
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">Durasi (menit)</label>
                                                            <input type="number" name="durasi_pengerjaan" class="form-control"
                                                                value="{{ $r->durasi_pengerjaan ?? '' }}" min="1" placeholder="30">
                                                        </div>

                                                        <div class="mb-3">
                                                            <label class="form-label">Topik</label>
                                                            <select name="id_topic" class="form-select" required>
                                                                @foreach(\App\Models\Topic::with('subject')->where('created_by', Auth::id())->get() as $topicOpt)
                                                                    <option value="{{ $topicOpt->id }}" {{ $topicOpt->id === $r->topic_id ? 'selected' : '' }}>
                                                                        {{ $topicOpt->title }}
                                                                        ({{ $topicOpt->subject->name ?? 'Tanpa Subject' }})
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>

                                                        <div class="form-check mb-1">
                                                            <input type="hidden" name="addaptive" value="no">
                                                            <input class="form-check-input" type="checkbox" name="addaptive"
                                                                value="yes" {{ $r->addaptive === 'yes' ? 'checked' : '' }}>
                                                            <label class="form-check-label">Adaptive</label>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary"
                                                            data-bs-dismiss="modal">Batal</button>
                                                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Modal Lihat Soal --}}
                                    <div class="modal fade" id="lihatSoal{{ $r->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-xl modal-dialog-scrollable">
                                            <div class="modal-content border-0 shadow">
                                                <div class="modal-header bg-info text-white">
                                                    <h5 class="modal-title">
                                                        <i class="bi bi-list-check me-2"></i> Daftar Soal – {{ $r->title }}
                                                    </h5>
                                                    <button type="button" class="btn-close btn-close-white"
                                                        data-bs-dismiss="modal"></button>
                                                </div>

                                                <div class="modal-body">
                                                    @php
                                                        $selectedQuestions = $questionsMap[$r->id] ?? collect();
                                                    @endphp

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
                                                                            <td class="text-center"><span
                                                                                    class="badge bg-primary">{{ $s->type }}</span></td>
                                                                            <td class="text-center">
                                                                                @if(in_array($s->difficulty, ['easy', 'mudah']))
                                                                                    <span class="badge bg-success">Mudah</span>
                                                                                @elseif(in_array($s->difficulty, ['medium', 'sedang']))
                                                                                    <span class="badge bg-warning text-dark">Sedang</span>
                                                                                @else
                                                                                    <span class="badge bg-danger">Sulit</span>
                                                                                @endif
                                                                            </td>
                                                                            <td>{!! nl2br(e($sData->text ?? '-')) !!}</td>
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

                {{-- render semua modal yang di-push --}}
                @stack('modals')
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">

    <style>
        /* kolom nomor & aksi */
        #activitiesTable th:nth-child(1),
        #activitiesTable td:nth-child(1) {
            width: 60px;
        }

        #activitiesTable th:nth-child(8),
        #activitiesTable td:nth-child(8) {
            width: 260px;
            white-space: nowrap;
        }

        /* vertical align semua sel */
        #activitiesTable td,
        #activitiesTable th {
            vertical-align: middle;
        }

        /* action group: rapi dan responsif */
        .action-group {
            display: flex;
            gap: .45rem;
            flex-wrap: wrap;
            align-items: center;
            justify-content: center;
        }

        .action-group .btn {
            padding: .35rem .6rem;
            font-size: .88rem;
            min-width: 44px;
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            border-radius: .35rem;
        }

        /* sembunyikan teks tombol pada layar sangat kecil, tampilkan hanya icon */
        .action-group .btn-text {
            margin-left: .2rem;
        }

        @media (max-width: 520px) {
            .action-group .btn-text {
                display: none;
            }

            .action-group {
                gap: .3rem;
            }
        }

        /* sedikit spacing antar elemen form */
        .card .form-control,
        .card .form-select {
            box-shadow: none;
        }
    </style>
@endpush

@push('scripts')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var table = $('#activitiesTable').DataTable({
                responsive: { details: false },
                autoWidth: false,
                lengthChange: true,
                pageLength: 10,
                order: [[1, 'asc']],
                columnDefs: [
                    { orderable: false, targets: [0, 7] },
                    { searchable: false, targets: 0 }
                ],
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Cari aktivitas, topik, subject, atau kelas...",
                    lengthMenu: "Tampilkan _MENU_ entri",
                    paginate: { previous: "Sebelumnya", next: "Selanjutnya" }
                }
            });

            // isi nomor dinamis
            table.on('order.dt search.dt draw.dt', function () {
                table.column(0, { search: 'applied', order: 'applied' }).nodes().each(function (cell, i) {
                    cell.innerHTML = i + 1;
                });
            }).draw();

            // tooltip bootstrap
            var tlist = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tlist.map(function (el) { return new bootstrap.Tooltip(el) });
        });
    </script>
@endpush