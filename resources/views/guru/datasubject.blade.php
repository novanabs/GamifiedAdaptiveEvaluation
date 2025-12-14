@extends('layouts.main')

@section('dataSubject', request()->is('datamatapelajaran') ? 'active' : '')
@section('content')
    <div class="container mt-4">
        <div class="d-flex align-items-center gap-2">
            <h3 class="fw-bold mb-0">Data Mata Pelajaran Berdasarkan Kelas</h3>

            <button type="button" class="btn btn-sm btn-outline-secondary rounded-circle" style="width:32px;height:32px"
                data-bs-toggle="modal" data-bs-target="#modalInfoSubject" title="Informasi Mata Pelajaran">
                <i class="bi bi-info-lg"></i>
            </button>
        </div>

        {{-- Pesan sukses --}}
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        {{-- Form tambah subject --}}
        <div class="card mb-4 mt-3">
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
                                    <td class="align-middle">
                                        {{ $item->kelas->semester_human ?? ($item->kelas->semester === 'odd' ? 'Ganjil' : 'Genap') }}
                                    </td> <!-- new -->
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
                                                class="d-inline form-delete-subject">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" class="btn btn-danger btn-sm btn-delete-subject">
                                                    Hapus
                                                </button>
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

    <div class="modal fade" id="modalInfoSubject" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content rounded-4">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-info-circle me-1"></i>
                        Panduan Data Mata Pelajaran
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    {{-- Tambah Mata Pelajaran --}}
                    <section class="mb-4">
                        <h6 class="fw-semibold text-primary">
                            <i class="bi bi-plus-circle me-1"></i>
                            Menambah Mata Pelajaran
                        </h6>
                        <ol class="small text-muted">
                            <li>Isi <strong>Nama Mata Pelajaran</strong></li>
                            <li>Pilih <strong>Kelas</strong> yang akan menggunakan mata pelajaran tersebut</li>
                            <li>Klik tombol <strong>Tambah</strong></li>
                            <li>Mata pelajaran akan langsung muncul di tabel</li>
                        </ol>
                    </section>

                    {{-- Edit Mata Pelajaran --}}
                    <section class="mb-4">
                        <h6 class="fw-semibold text-success">
                            <i class="bi bi-pencil-square me-1"></i>
                            Mengedit Mata Pelajaran
                        </h6>
                        <ol class="small text-muted">
                            <li>Klik tombol <strong>Edit</strong> pada kolom Aksi</li>
                            <li>Ubah nama mata pelajaran atau kelas</li>
                            <li>Klik <strong>Simpan</strong> untuk menyimpan perubahan</li>
                        </ol>
                    </section>

                    {{-- Hapus Mata Pelajaran --}}
                    <section class="mb-4">
                        <h6 class="fw-semibold text-danger">
                            <i class="bi bi-trash me-1"></i>
                            Menghapus Mata Pelajaran
                        </h6>
                        <ol class="small text-muted">
                            <li>Klik tombol <strong>Hapus</strong></li>
                            <li>Konfirmasi penghapusan</li>
                            <li>
                                <strong>Perhatian:</strong> Data yang dihapus tidak dapat dikembalikan
                            </li>
                        </ol>
                    </section>

                    {{-- Info Tambahan --}}
                    <section>
                        <h6 class="fw-semibold text-secondary">
                            <i class="bi bi-diagram-3 me-1"></i>
                            Catatan Penting
                        </h6>
                        <ul class="small text-muted">
                            <li>Satu kelas dapat memiliki banyak mata pelajaran</li>
                            <li>Mata pelajaran terkait langsung dengan <strong>Topik</strong> dan <strong>Soal</strong></li>
                            <li>Gunakan fitur pencarian untuk menemukan data lebih cepat</li>
                        </ul>
                    </section>

                </div>

                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
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

    <!-- sweatalert -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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
    <script>
        document.addEventListener('DOMContentLoaded', function () {

            // Delegasi klik tombol hapus
            document.querySelectorAll('.btn-delete-subject').forEach(btn => {
                btn.addEventListener('click', function (e) {
                    e.preventDefault();

                    const form = this.closest('form');
                    const subjectName = this.closest('tr')
                        ?.querySelector('[class^="subject-name-"]')
                        ?.innerText ?? 'mata pelajaran ini';

                    Swal.fire({
                        title: 'Yakin ingin menghapus?',
                        html: `
                            <div class="text-start">
                                <p>
                                    Mata pelajaran <strong>${subjectName}</strong> akan dihapus.
                                </p>
                                <small class="text-danger">
                                    Data terkait (topik & soal) bisa ikut terdampak.
                                </small>
                            </div>
                        `,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#dc3545',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Ya, hapus',
                        cancelButtonText: 'Batal',
                        reverseButtons: true
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // loading state
                            Swal.fire({
                                title: 'Menghapus...',
                                text: 'Mohon tunggu',
                                allowOutsideClick: false,
                                didOpen: () => {
                                    Swal.showLoading();
                                }
                            });

                            form.submit();
                        }
                    });
                });
            });

        });
    </script>

@endpush