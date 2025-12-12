@extends('layouts.main')
@section('dataTopic', request()->is('datatopik') ? 'active' : '')
@section('content')
    <div class="container mt-4">
        <h3 class="fw-bold mb-4">Data Topik Berdasarkan Mata Pelajaran</h3>

        {{-- Pesan sukses --}}
        @if(session('success'))
            <div class="alert alert-success text-center">{{ session('success') }}</div>
        @endif

        {{-- Form tambah topik --}}
        <div class="card shadow-sm mb-4 border-0">
            <div class="card-header bg-primary text-white fw-semibold">Tambah Topik Baru</div>

            <div class="card-body px-4 py-4">
                <form action="{{ route('guru.topik.simpan') }}" method="POST" class="row g-3">
                    @csrf
                    <div class="col-md-6">
                        <label for="title" class="form-label fw-semibold">Judul Topik</label>
                        <input type="text" id="title" name="title" class="form-control"
                            placeholder="Masukkan judul topik..." required>
                    </div>

                    <div class="col-md-4">
                        <label for="subject" class="form-label fw-semibold">Mata Pelajaran</label>
                        <select name="id_subject" id="subject" class="form-select" required>
                            <option value="">Pilih Mata Pelajaran</option>
                            @foreach($data as $subject)
                                <option value="{{ $subject->id }}">
                                    {{ $subject->name }}
                                    ({{ $subject->classes ? $subject->classes->name : 'Tidak ada kelas' }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-success px-4 shadow-sm">
                            <i class="bi bi-save me-1"></i> Simpan Topik
                        </button>
                    </div>

                    <div class="col-12">
                        <label for="description" class="form-label fw-semibold">Deskripsi</label>
                        <textarea id="description" name="description" rows="3" class="form-control"
                            placeholder="Masukkan deskripsi singkat mengenai topik ini..."></textarea>
                    </div>
                </form>
            </div>
        </div>

        {{-- Tabel DataTables gabungan topik --}}
        <div class="card mb-4 border-0 shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    {{-- Hapus kelas nowrap supaya responsive bekerja benar --}}
                    <table id="topicsTable" class="table table-striped table-bordered align-middle" style="width:100%">
                        <thead class="table-secondary">
                            <tr>
                                <th style="width:60px">No</th>
                                <th>Mata Pelajaran</th>
                                <th>Kelas</th>
                                <th style="width:100px">Semester</th>
                                <th>Judul Topik</th>
                                <th>Deskripsi</th>
                                <th style="width:120px">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($data as $subject)
                                @foreach($subject->topics as $topic)
                                    <tr data-topic-id="{{ $topic->id }}" data-topic-title="{{ e($topic->title) }}"
                                        data-topic-desc="{{ e($topic->description) }}">
                                        <td class="text-center align-middle"></td>
                                        <td class="align-middle">{{ $subject->name }}</td>
                                        <td class="align-middle">{{ $subject->classes ? $subject->classes->name : '-' }}</td>

                                        {{-- kolom semester --}}
                                        <td class="align-middle">
                                            @if($subject->classes && isset($subject->classes->semester))
                                                @php $sem = $subject->classes->semester; @endphp
                                                @if($sem === 'odd')
                                                    Ganjil
                                                @elseif($sem === 'even')
                                                    Genap
                                                @else
                                                    <span>-</span>
                                                @endif
                                            @else
                                                <span>-</span>
                                            @endif
                                        </td>

                                        <td class="align-middle topic-title-cell">{{ $topic->title }}</td>
                                        <td class="align-middle topic-desc-cell">{{ $topic->description }}</td>
                                        <td class="align-middle text-center">
                                            <div class="d-flex gap-2 justify-content-center align-items-center">
                                                {{-- Edit -> buka modal --}}
                                                <button type="button" class="btn btn-success btn-sm btn-edit-topic"
                                                    data-id="{{ $topic->id }}">
                                                    Edit
                                                </button>

                                                {{-- Hapus --}}
                                                <form action="{{ route('guru.topik.hapus', $topic->id) }}" method="POST"
                                                    onsubmit="return confirm('Yakin ingin menghapus topik ini?')" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="btn btn-danger btn-sm">Hapus</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            @endforeach
                        </tbody>

                    </table>
                </div>
            </div>
        </div>

    </div>

    {{-- Modal Edit Topik --}}
    <div class="modal fade" id="editTopicModal" tabindex="-1" aria-labelledby="editTopicLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form id="editTopicForm" method="POST" action="">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title" id="editTopicLabel">Edit Topik</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="modalTopicId" name="topic_id">
                        <div class="mb-3">
                            <label for="modalTopicTitle" class="form-label"><b>Judul Topik</b></label>
                            <textarea id="modalTopicTitle" name="title" class="form-control" rows="2"
                                placeholder="Masukkan judul topik..." required></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="modalTopicDesc" class="form-label"><b>Deskripsi</b></label>
                            <textarea id="modalTopicDesc" name="description" class="form-control" rows="4"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary" id="modalTopicSave">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    {{-- DataTables CSS --}}
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">

    <style>
        /* responsive + fixed layout */
        .table-responsive {
            overflow-x: auto;
        }

        #topicsTable {
            table-layout: fixed;
            width: 100% !important;
        }

        /* allow wrap + ellipsis for long title/desc, limit max width */
        #topicsTable td.topic-title-cell,
        #topicsTable td.topic-desc-cell {
            white-space: normal;
            word-break: break-word;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 420px;
            vertical-align: middle;
        }

        /* smaller max-width on smaller screens */
        @media (max-width: 900px) {
            #topicsTable td.topic-title-cell,
            #topicsTable td.topic-desc-cell {
                max-width: 240px;
            }
        }

        /* keep small/important columns visible (indeks menyesuaikan 7 kolom) */
        #topicsTable td:nth-child(1),
        #topicsTable th:nth-child(1) {
            width: 64px;
            white-space: nowrap;
            text-align: center;
        }

        #topicsTable td:nth-child(2),
        #topicsTable th:nth-child(2) {
            width: 180px;
            white-space: nowrap;
        }

        #topicsTable td:nth-child(3),
        #topicsTable th:nth-child(3) {
            width: 140px;
            white-space: nowrap;
        }

        /* semester (kolom ke-4) */
        #topicsTable td:nth-child(4),
        #topicsTable th:nth-child(4) {
            width: 100px;
            white-space: nowrap;
            text-align: center;
        }

        /* aksi (kolom ke-7) */
        #topicsTable td:nth-child(7),
        #topicsTable th:nth-child(7) {
            width: 120px;
            white-space: nowrap;
        }

        /* spacing niceties */
        #topicsTable td {
            padding-top: .55rem;
            padding-bottom: .55rem;
        }
    </style>
@endpush

@push('scripts')
    {{-- jQuery (DataTables dependency) --}}
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    {{-- DataTables --}}
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>

    {{-- Pastikan layout Anda memuat Bootstrap 5 JS (bootstrap.bundle.min.js). Jika belum, tambahkan di layouts.main:
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    --}}

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Inisialisasi DataTable dengan responsive details
            var table = $('#topicsTable').DataTable({
                responsive: {
                    details: {
                        type: 'column',
                        target: -1
                    }
                },
                autoWidth: false,
                lengthChange: true,
                pageLength: 10,
                columnDefs: [
                    { orderable: false, targets: [6] }, // kolom Aksi non-sortable (kolom ke-7 => index 6)
                    { searchable: false, targets: 0 },  // No tidak ikut search

                    // responsive priority: smaller = more important (hilang paling akhir)
                    { responsivePriority: 1, targets: 0 }, // No
                    { responsivePriority: 2, targets: 1 }, // Mata Pelajaran
                    { responsivePriority: 3, targets: 2 }, // Kelas
                    { responsivePriority: 4, targets: 3 }, // Semester
                    { responsivePriority: 5, targets: 4 }, // Judul Topik
                    { responsivePriority: 6, targets: 6 }, // Aksi
                    { responsivePriority: 200, targets: 5 } // Deskripsi paling mudah di-collapse
                ],
                order: [[1, 'asc']],
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Cari topik, subject, atau kelas...",
                    lengthMenu: "Tampilkan _MENU_ entri",
                    paginate: { previous: "Sebelumnya", next: "Selanjutnya" }
                },
                drawCallback: function () {
                    // nomor otomatis sesuai filter & urut
                    this.api().column(0, { search: 'applied', order: 'applied' }).nodes().each(function (cell, i) {
                        cell.innerHTML = i + 1;
                    });
                }
            });

            // Setup modal (pastikan Bootstrap tersedia)
            var editModalEl = document.getElementById('editTopicModal');
            var editModal = (typeof bootstrap !== 'undefined' && editModalEl) ? new bootstrap.Modal(editModalEl) : null;
            var updateUrlTemplate = "{{ route('guru.topik.ubah', ['id' => ':id']) }}";

            // Klik Edit -> isi modal lalu tampilkan
            $(document).on('click', '.btn-edit-topic', function () {
                var $tr = $(this).closest('tr');
                var id = $tr.data('topic-id');
                var title = $tr.data('topic-title') || $tr.find('.topic-title-cell').text().trim();
                var desc = $tr.data('topic-desc') || $tr.find('.topic-desc-cell').text().trim();

                $('#modalTopicId').val(id);
                $('#modalTopicTitle').val(title);
                $('#modalTopicDesc').val(desc);

                var action = updateUrlTemplate.replace(':id', id);
                $('#editTopicForm').attr('action', action);

                if (editModal) {
                    editModal.show();
                } else {
                    alert('Editor tidak tersedia — pastikan Bootstrap JS (bootstrap.bundle.min.js) dimuat di layout.');
                }
            });

            // Disable submit button on submit to prevent double-post
            $('#editTopicForm').on('submit', function () {
                $('#modalTopicSave').attr('disabled', true).text('Menyimpan...');
            });
        });
    </script>
@endpush
