@extends('layouts.main')

@section('dataSubject', request()->is('datamatapelajaran') ? 'active' : '')
@section('content')
    <div class="container mt-4">
        <h3 class="fw-bold mb-3">Data Mata Pelajaran Berdasarkan Kelas</h3>

        {{-- Pesan sukses --}}
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        {{-- Form tambah subject --}}
        <div class="card mb-4">
            <div class="card-header bg-primary text-white fw-semibold">Tambah Mata Pelajaran</div>
            <div class="card-body">
                <form action="{{ route('guru.subject.tambah') }}" method="POST" class="row g-2">
                    @csrf
                    <div class="col-md-5">
                        <input type="text" name="name" class="form-control" placeholder="Nama Mata Pelajaran" required>
                    </div>
                    <div class="col-md-4">
                        <select name="id_class" class="form-control" required>
                            <option value="">Pilih Kelas</option>
                            @foreach($data as $item)
                                <option value="{{ $item->kelas->id }}">{{ $item->kelas->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary w-100">Tambah</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Tabel DataTables --}}
        <div class="card">
            <div class="card-body">
                <table id="subjectsTable" class="table table-striped table-bordered nowrap" style="width:100%">
                    <thead>
                        <tr>
                            <th style="width:12px !important">No</th>
                            <th style="width:140px !important">Kelas</th>
                            <th style="width:80px !important">Semester</th> 
                            <th style="width:150px !important">Mata Pelajaran</th>
                            <th style="width:120px !important">Dibuat Oleh</th>
                            <th style="width:180px !important">Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($data as $item)
                            @foreach($item->subjects as $subject)
                                <tr data-subject-id="{{ $subject->id }}" data-subject-name="{{ e($subject->name) }}"
                                    data-subject-class="{{ $item->kelas->id }}">
                                    <td class="align-middle text-center"></td>
                                    <td class="align-middle">{{ $item->kelas->name }}</td>
                                    <td class="align-middle">{{ $item->kelas->semester_human ?? ($item->kelas->semester === 'odd' ? 'Ganjil' : 'Genap') }}</td> <!-- new -->
                                    <td class="align-middle">
                                        <span class="subject-name-{{ $subject->id }}">{{ $subject->name }}</span>
                                    </td>
                                    <td class="align-middle">{{ $subject->creator_name ?? '—' }}</td>
                                    <td class="align-middle">
                                        <div class="d-flex gap-2">
                                            {{-- Tombol Edit -> buka modal --}}
                                            <button type="button" class="btn btn-success btn-sm btn-edit-subject"
                                                data-id="{{ $subject->id }}" data-name="{{ $subject->name }}"
                                                data-class="{{ $item->kelas->id }}">
                                                Edit
                                            </button>

                                            {{-- Form hapus --}}
                                            <form action="{{ route('guru.subject.hapus', $subject->id) }}" method="POST"
                                                onsubmit="return confirm('Yakin hapus subject ini?')" class="d-inline">
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

    {{-- Modal Edit Subject --}}
    <div class="modal fade" id="editSubjectModal" tabindex="-1" aria-labelledby="editSubjectLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form id="editSubjectForm" method="POST" action="">
                    @csrf
                    @method('PUT') {{-- method spoofing; pastikan route menerima PUT --}}
                    <div class="modal-header">
                        <h5 class="modal-title" id="editSubjectLabel">Edit Mata Pelajaran</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="subject_id" id="modalSubjectId">
                        <div class="mb-3">
                            <label for="modalSubjectName" class="form-label">Nama Mata Pelajaran</label>
                            <input type="text" name="name" id="modalSubjectName" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label for="modalSubjectClass" class="form-label">Kelas</label>
                            <select name="id_class" id="modalSubjectClass" class="form-control" required>
                                <option value="">Pilih Kelas</option>
                                @foreach($data as $item)
                                    <option value="{{ $item->kelas->id }}">{{ $item->kelas->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary" id="modalSaveBtn">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@push('styles')
    {{-- DataTables CSS (Bootstrap 5 + Responsive) --}}
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
    <style>
        /* Supaya tombol & spacing rapi */
        table#subjectsTable .form-control-sm {
            padding: .25rem .5rem;
            font-size: .85rem;
        }
    </style>
@endpush

@push('scripts')
    {{-- jQuery (DataTables dependency) --}}
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    {{-- DataTables JS --}}
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>

    {{-- Pastikan layout Anda sudah menyertakan Bootstrap 5 JS (bootstrap.bundle.min.js).
    Jika belum, uncomment baris berikut atau tambahkan di layout utama:
    --}}
    {{--
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script> --}}

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Inisialisasi DataTable
            var table = $('#subjectsTable').DataTable({
                responsive: true,
                lengthChange: true,
                pageLength: 10,
                columnDefs: [
                    { orderable: false, targets: [0, 5] }, // nomor dan aksi non-sortable
                    { searchable: false, targets: 0 }     // nomor tidak ikut search
                ],
                order: [[1, 'asc']],
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Cari mata pelajaran atau kelas...",
                    lengthMenu: "Tampilkan _MENU_ entri",
                    paginate: {
                        previous: "Sebelumnya",
                        next: "Selanjutnya"
                    }
                },
                drawCallback: function (settings) {
                    var api = this.api();
                    api.column(0, { search: 'applied', order: 'applied' }).nodes().each(function (cell, i) {
                        cell.innerHTML = i + 1; // numbering otomatis sesuai sorting
                    });
                }
            });

            // --- Modal setup (pastikan Bootstrap JS sudah dimuat di layout) ---
            var editModalEl = document.getElementById('editSubjectModal');

            // safety check: jika bootstrap belum tersedia, jangan lanjutkan (hindari error)
            var editModal = null;
            if (typeof bootstrap !== 'undefined' && editModalEl) {
                editModal = new bootstrap.Modal(editModalEl);
            } else {
                console.warn('Bootstrap modal tidak tersedia. Pastikan bootstrap.bundle.min.js dimuat di layout.');
            }

            // Template URL untuk update; :id akan diganti dengan subject id
            var updateUrlTemplate = "{{ route('guru.subject.update', ['id' => ':id']) }}";

            // Klik tombol Edit -> buka modal dan isi data
            $(document).on('click', '.btn-edit-subject', function () {
                var id = $(this).data('id');
                var name = $(this).data('name');
                var cls = $(this).data('class');

                // isi field modal
                $('#modalSubjectId').val(id);
                $('#modalSubjectName').val(name);
                $('#modalSubjectClass').val(cls);

                // set form action (ganti :id)
                var action = updateUrlTemplate.replace(':id', id);
                $('#editSubjectForm').attr('action', action);

                // tampilkan modal jika inisialisasi berhasil
                if (editModal) {
                    editModal.show();
                } else {
                    // fallback: jika modal tidak tersedia, alert sederhana
                    alert('Editor tidak tersedia — periksa apakah Bootstrap JS dimuat.');
                }
            });

            // Optional: saat form modal disubmit, disable tombol supaya user tidak klik ganda
            $('#editSubjectForm').on('submit', function () {
                $('#modalSaveBtn').attr('disabled', true).text('Menyimpan...');
            });

            // Tooltip bootstrap (jika ada)
            if (typeof bootstrap !== 'undefined') {
                var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                tooltipTriggerList.map(function (el) {
                    return new bootstrap.Tooltip(el);
                });
            }

            // Debug help: tunjukkan error 404 pada plugin yang hilang (opsional)
            // Jika kamu melihat console error 404 untuk jquery.easing.min.js, hapus referensi file itu
            // di layout atau letakkan versi yang valid.
        });
    </script>
@endpush