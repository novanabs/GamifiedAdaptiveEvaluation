@extends('layouts.main')
@section('dataAktivitas', request()->is('dataaktivitas') ? 'active' : '')
@section('content')
    <div class="container mt-4">
        <div class="d-flex align-items-center gap-2 mb-4">
            <h3 class="fw-bold mb-0">Data Evaluasi Berdasarkan Topik</h3>

            <button type="button"
                class="btn btn-sm btn-outline-secondary rounded-circle d-flex align-items-center justify-content-center"
                style="width:32px;height:32px" data-bs-toggle="modal" data-bs-target="#modalInfoAktivitas"
                title="Informasi Aktivitas">
                <i class="bi bi-info-lg"></i>
            </button>
        </div>

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
                                <!-- kkm -->
                                <div class="col-6">
                                    <label class="form-label fw-semibold">KKM</label>
                                    <input type="number" name="kkm" class="form-control shadow-sm" min="0" max="100"
                                        placeholder="70" required>
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
                    <table id="activitiesTable" class="table table-striped table-bordered " style="width:100%">
                        <thead class="table-secondary text-center">
                            <tr>
                                <th style="width:60px">No</th>
                                <th>Judul</th>
                                <th>Deadline</th>
                                <th>Adaptive</th>
                                <th>Topik</th>
                                <th>Mapel</th>
                                <th>Kelas</th>
                                <th>Semester</th>
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

                                    <td class="align-middle col-title">
                                        <div class="cell-inner" title="{{ $r->title }}">{{ $r->title }}</div>
                                    </td>

                                    <td class="align-middle col-subject hide-sm">
                                        <div class="cell-inner" title="{{ $r->subject_name ?? '-' }}">
                                            {{ $r->subject_name ?? '-' }}
                                        </div>
                                    </td>

                                    <td class="align-middle col-class hide-sm">
                                        <div class="cell-inner" title="{{ $r->class_name ?? '-' }}">{{ $r->class_name ?? '-' }}
                                        </div>
                                    </td>
                                    <td class="align-middle">
                                        @if($r->semester === 'odd')
                                            <span class="badge bg-info text-dark">Ganjil</span>
                                        @elseif($r->semester === 'even')
                                            <span class="badge bg-secondary">Genap</span>
                                        @else
                                            <span>-</span>
                                        @endif
                                    </td>


                                    <td class="align-middle text-center">
                                        <div class="action-group" role="group" aria-label="Aksi aktivitas">
                                            <button class="btn btn-primary btn-sm" data-bs-toggle="modal"
                                                data-bs-target="#modalEdit{{ $r->id }}" title="Edit" aria-label="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </button>

                                            <button type="button" class="btn btn-success btn-sm btn-create-package"
                                                data-url="{{ route('activity.package.create', $r->id) }}"
                                                title="Buat Paket Soal">
                                                <i class="bi bi-archive"></i>
                                            </button>


                                            <a href="{{ url('/guru/aktivitas/' . $r->id . '/atur-soal?topic=' . $r->topic_id) }}"
                                                class="btn btn-warning btn-sm" title="Atur Soal" aria-label="Atur Soal">
                                                <i class="bi bi-gear"></i>
                                            </a>

                                            <button class="btn btn-info btn-sm text-white" data-bs-toggle="modal"
                                                data-bs-target="#lihatSoal{{ $r->id }}" title="Lihat Soal"
                                                aria-label="Lihat Soal">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                            <form action="{{ route('guru.aktivitas.hapus', $r->id) }}" method="POST"
                                                class="d-inline delete-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" class="btn btn-danger btn-sm btn-delete" title="Hapus"
                                                    aria-label="Hapus">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>

                                        </div>
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
                                                            <label class="form-label">KKM</label>
                                                            <input type="number" name="kkm" class="form-control"
                                                                value="{{ $r->kkm ?? 70 }}" min="0" max="100" required>
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
                {{-- MODAL INFO AKTIVITAS --}}
                <div class="modal fade" id="modalInfoAktivitas" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                        <div class="modal-content shadow rounded-4">

                            <div class="modal-header bg-primary text-white">
                                <h5 class="modal-title">
                                    <i class="bi bi-info-circle me-2"></i>Informasi Data Evaluasi
                                </h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>

                            <div class="modal-body">

                                <p>
                                    Halaman <strong>Data Evaluasi Berdasarkan Topik</strong> digunakan untuk
                                    membuat, mengelola, dan mendistribusikan aktivitas evaluasi (kuis/tes)
                                    kepada siswa berdasarkan topik pembelajaran.
                                </p>

                                <hr>

                                <h6 class="fw-bold text-primary">
                                    <i class="bi bi-plus-circle me-1"></i> Tambah Aktivitas
                                </h6>
                                <ul>
                                    <li>Digunakan untuk membuat evaluasi baru.</li>
                                    <li>Guru wajib mengisi:
                                        <ul>
                                            <li>Judul aktivitas</li>
                                            <li>Topik</li>
                                            <li>Deadline (opsional)</li>
                                            <li>Durasi pengerjaan</li>
                                        </ul>
                                    </li>
                                </ul>

                                <hr>

                                <h6 class="fw-bold text-success">
                                    <i class="bi bi-shuffle me-1"></i> Adaptive
                                </h6>
                                <ul>
                                    <li>Jika diaktifkan, soal dapat menyesuaikan tingkat kesulitan siswa.</li>
                                    <li>Jika tidak diaktifkan, soal ditampilkan secara statis.</li>
                                </ul>

                                <hr>

                                <h6 class="fw-bold text-warning">
                                    <i class="bi bi-gear me-1"></i> Aksi Aktivitas
                                </h6>
                                <ul>
                                    <li>
                                        <i class="bi bi-pencil text-primary"></i>
                                        <strong>Edit</strong> – Mengubah data aktivitas.
                                    </li>
                                    <li>
                                        <i class="bi bi-archive text-success"></i>
                                        <strong>Buat Paket Soal</strong> – Mengemas seluruh soal dalam topik menjadi paket.
                                    </li>
                                    <li>
                                        <i class="bi bi-gear text-warning"></i>
                                        <strong>Atur Soal</strong> – Menentukan soal mana saja yang digunakan.
                                    </li>
                                    <li>
                                        <i class="bi bi-eye text-info"></i>
                                        <strong>Lihat Soal</strong> – Melihat daftar soal dalam aktivitas.
                                    </li>
                                    <li>
                                        <i class="bi bi-trash text-danger"></i>
                                        <strong>Hapus</strong> – Menghapus aktivitas secara permanen.
                                    </li>
                                </ul>

                                <hr>

                                <h6 class="fw-bold text-secondary">
                                    <i class="bi bi-calendar-event me-1"></i> Informasi Tambahan
                                </h6>
                                <ul>
                                    <li>Kolom <strong>Semester</strong> menunjukkan periode pembelajaran.</li>
                                    <li>Kolom <strong>Kelas</strong> menunjukkan target siswa.</li>
                                    <li>Kolom <strong>Mapel</strong> menunjukkan mata pelajaran terkait.</li>
                                </ul>

                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                    Tutup
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                @stack('modals')
@endsection
            @push('styles')
                <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
                <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">

                <style>
                    /* truncate dengan ellipsis */
                    .text-ellipsis {
                        overflow: hidden;
                        text-overflow: ellipsis;
                        white-space: nowrap;
                        /* ubah ke normal untuk wrap */
                    }

                    /* bila ingin boleh wrap (multi-line) gunakan kelas ini */
                    .text-wrap {
                        white-space: normal;
                        word-wrap: break-word;
                    }

                    /* batas lebar kolom agar tidak memanjangkan layout */
                    td.col-title {
                        max-width: 220px;
                    }

                    /* Judul */
                    td.col-topic {
                        max-width: 180px;
                    }

                    /* Topik */
                    td.col-subject {
                        max-width: 140px;
                    }

                    /* Subject */
                    td.col-class {
                        max-width: 120px;
                    }

                    /* Kelas */

                    /* buat cell truncate (multi size) */
                    td.col-title>.cell-inner,
                    td.col-topic>.cell-inner,
                    td.col-subject>.cell-inner,
                    td.col-class>.cell-inner {
                        display: block;
                        overflow: hidden;
                        text-overflow: ellipsis;
                        white-space: nowrap;
                    }

                    /* make action buttons stay on one line and allow horizontal scroll if cell too narrow */
                    .action-group {
                        display: flex;
                        gap: .35rem;
                        align-items: center;
                        white-space: nowrap;
                        /* prevent icons text wrapping */
                        overflow-x: auto;
                        -webkit-overflow-scrolling: touch;
                        padding: .15rem 0;
                    }

                    /* small visual tweak: keep consistent button sizing */
                    .action-group .btn {
                        flex: 0 0 auto;
                    }



                    /* responsive: sembunyikan kolom subject & class di xs */
                    @media (max-width: 768px) {
                        .hide-sm {
                            display: none !important;
                        }
                    }

                    /* agar table responsive horizontal */
                    .dt-scroll-wrapper {
                        overflow-x: auto;
                    }
                </style>
            @endpush


            @push('scripts')
                <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
                <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
                <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
                <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
                <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
                <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
                <script>
                    document.addEventListener('DOMContentLoaded', function () {

                        // semua tombol delete
                        document.querySelectorAll('.btn-delete').forEach(btn => {

                            btn.addEventListener('click', function (e) {
                                e.preventDefault();

                                let form = this.closest('form');

                                Swal.fire({
                                    title: 'Yakin hapus aktivitas ini?',
                                    text: "Data yang dihapus tidak bisa dikembalikan!",
                                    icon: 'warning',
                                    showCancelButton: true,
                                    confirmButtonColor: '#d33',
                                    cancelButtonColor: '#6c757d',
                                    confirmButtonText: 'Ya, hapus!',
                                    cancelButtonText: 'Batal'
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        form.submit();
                                    }
                                });

                            });

                        });
                    });
                </script>


                <script>
                    document.addEventListener('DOMContentLoaded', function () {
                        // DataTable with horizontal scroll and responsive details
                        var table = $('#activitiesTable').DataTable({
                            responsive: {
                                details: {
                                    type: 'column',
                                    target: -1 // fallback: last column toggles detail
                                }
                            },
                            scrollX: true,
                            autoWidth: false,
                            lengthChange: true,
                            pageLength: 10,
                            order: [[1, 'asc']],
                            columnDefs: [
                                { orderable: false, targets: [0, 8] },
                                { searchable: false, targets: 0 },
                                // make the last column (Aksi) not responsive-detail toggler
                                { responsivePriority: 1, targets: 1 }, // Judul paling penting
                                { responsivePriority: 2, targets: 7 }  // Aksi tetap penting
                            ],
                            language: {
                                search: "_INPUT_",
                                searchPlaceholder: "Cari aktivitas, topik, subject, atau kelas...",
                                lengthMenu: "Tampilkan _MENU_ entri",
                                paginate: { previous: "Sebelumnya", next: "Selanjutnya" }
                            },
                            drawCallback: function () {
                                // Aktifkan tooltip untuk semua cell yang punya title
                                var tlist = [].slice.call(document.querySelectorAll('[title]'));
                                tlist.map(function (el) { return new bootstrap.Tooltip(el); });
                            }
                        });

                        // nomor dinamis
                        table.on('order.dt search.dt draw.dt', function () {
                            table.column(0, { search: 'applied', order: 'applied' }).nodes().each(function (cell, i) {
                                cell.innerHTML = i + 1;
                            });
                        }).draw();

                        // optional: tombol Lihat -> modal show detail row
                        // delegasi: ketika tombol lihat diklik ambil data baris dan tampilkan
                        $(document).on('click', '.btn-view-row', function (e) {
                            e.preventDefault();
                            var $btn = $(this);
                            var $tr = $btn.closest('tr');
                            if ($tr.hasClass('child')) { // jika responsive membuat row child, ambil parent
                                $tr = $tr.prev();
                            }
                            var rowData = table.row($tr).data(); // array cells
                            // rowData indeks: 0=No,1=Judul,2=Deadline,3=Adaptive,4=Topik,5=Subject,6=Kelas,7=Aksi
                            var html = '<dl class="row">';
                            html += '<dt class="col-sm-3">Judul</dt><dd class="col-sm-9">' + $('<div>').text(rowData[1]).html() + '</dd>';
                            html += '<dt class="col-sm-3">Deadline</dt><dd class="col-sm-9">' + $('<div>').text(rowData[2]).html() + '</dd>';
                            html += '<dt class="col-sm-3">Adaptive</dt><dd class="col-sm-9">' + $('<div>').text(rowData[3]).html() + '</dd>';
                            html += '<dt class="col-sm-3">Topik</dt><dd class="col-sm-9">' + $('<div>').text(rowData[4]).html() + '</dd>';
                            html += '<dt class="col-sm-3">Subject</dt><dd class="col-sm-9">' + $('<div>').text(rowData[5]).html() + '</dd>';
                            html += '<dt class="col-sm-3">Kelas</dt><dd class="col-sm-9">' + $('<div>').text(rowData[6]).html() + '</dd>';
                            html += '</dl>';
                            $('#rowDetailModal .modal-body').html(html);
                            var modal = new bootstrap.Modal(document.getElementById('rowDetailModal'));
                            modal.show();
                        });

                    });
                    document.addEventListener('DOMContentLoaded', function () {
                        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'));
                        tooltipTriggerList.map(function (el) { return new bootstrap.Tooltip(el); });
                    });

                </script>
                <script>
                    document.addEventListener('DOMContentLoaded', function () {

                        document.querySelectorAll('.btn-create-package').forEach(btn => {
                            btn.addEventListener('click', function () {

                                let url = this.dataset.url;

                                Swal.fire({
                                    title: 'Buat paket soal?',
                                    text: 'Paket akan berisi semua soal dalam topik.',
                                    icon: 'question',
                                    showCancelButton: true,
                                    confirmButtonText: 'Ya, buat',
                                    cancelButtonText: 'Batal'
                                }).then((result) => {

                                    if (!result.isConfirmed) return;

                                    Swal.fire({
                                        title: 'Memproses...',
                                        text: 'Sedang membuat paket soal',
                                        allowOutsideClick: false,
                                        didOpen: () => Swal.showLoading()
                                    });

                                    fetch(url, {
                                        method: 'POST',
                                        headers: {
                                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                            'Accept': 'application/json'
                                        }
                                    })
                                        .then(res => res.json())
                                        .then(res => {

                                            if (!res.success) {
                                                throw new Error(res.message ?? 'Gagal membuat paket');
                                            }

                                            Swal.fire({
                                                icon: 'success',
                                                title: 'Berhasil',
                                                text: 'Paket soal berhasil dibuat',
                                            });

                                        })
                                        .catch(err => {
                                            Swal.fire('Error', err.message, 'error');
                                        });

                                });
                            });
                        });

                    });
                </script>
            @endpush