@extends('layouts.main')

@section('dataSiswa', request()->is('datasiswa') ? 'active' : '')

@section('content')
    <div class="container py-4">
        <div class="d-flex align-items-center gap-2 mb-3">
            <h2 class="fw-bold mb-0 text-black">Data Siswa per Kelas</h2>

            <button type="button"
                class="btn btn-sm btn-outline-secondary rounded-circle d-flex align-items-center justify-content-center"
                style="width:32px;height:32px" data-bs-toggle="modal" data-bs-target="#modalInfoDataSiswa"
                title="Informasi Data Siswa">
                <i class="bi bi-info-lg"></i>
            </button>
        </div>


        <!-- Filter Kelas -->
        <form action="{{ route('dataSiswa') }}" method="GET" class="mb-4">
            <div class="row g-2 align-items-center">
                <div class="col-auto">
                    <label for="token" class="col-form-label fw-semibold">Pilih Kelas:</label>
                </div>
                <div class="col-md-4">
                    <select name="token" id="token" class="form-select shadow-sm" onchange="this.form.submit()">
                        <option value="">-- Pilih Nama Kelas --</option>
                        @foreach($kelas as $k)
                            <option value="{{ $k->token }}" {{ ($token ?? '') == $k->token ? 'selected' : '' }}>
                                {{ $k->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </form>

        <!-- Info kelas atau info default -->
        @if($kelasTerpilih)
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h5 class="mb-0">Nama Kelas:
                        <span class="fw-semibold">{{ $kelasTerpilih->name }}</span>
                    </h5>
                    <small class="text-primary">Token Kelas: {{ $kelasTerpilih->token }}</small>
                </div>

                <!-- Export per kelas -->
                <a href="{{ route('dataSiswa.export', ['token' => $kelasTerpilih->token]) }}" class="btn btn-success shadow-sm">
                    <i class="bi bi-file-earmark-excel-fill me-1"></i> Export Data Siswa Kelas {{$kelasTerpilih->name}}
                </a>
            </div>
        @else
            <div class="d-flex justify-content-end align-items-center mb-3">
                <!-- Export semua siswa -->
                <a href="{{ route('dataSiswa.export') }}" class="btn btn-success shadow-sm">
                    <i class="bi bi-file-earmark-excel-fill me-1"></i> Export Semua Siswa
                </a>
            </div>
        @endif

        @if($siswa->isNotEmpty())

            {{-- ===== DESKTOP & TABLET (TABLE) ===== --}}
            <div class="d-none d-md-block">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <table id="tabelSiswa" class="table table-bordered table-striped align-middle">
                            <thead class="table-primary text-center">
                                <tr>
                                    <th>No</th>
                                    <th>Nama Siswa</th>
                                    <th>Email</th>
                                    <th>Kelas</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($siswa as $index => $s)
                                    <tr>
                                        <td class="text-center">{{ $index + 1 }}</td>
                                        <td>{{ $s->name }}</td>
                                        <td>{{ $s->email }}</td>
                                        <td>{{ $s->kelas ?? '-' }}</td>
                                        <td class="text-center">
                                            <button class="btn btn-warning btn-sm" data-bs-toggle="modal"
                                                data-bs-target="#editModal" data-id="{{ $s->id }}" data-name="{{ $s->name }}"
                                                data-email="{{ $s->email }}">
                                                <i class="bi bi-pencil-square"></i> Edit
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- ===== MOBILE (CARD VIEW) ===== --}}
            <div class="d-block d-md-none">
                @foreach($siswa as $s)
                    <div class="card mb-3 shadow-sm border-0">
                        <div class="card-body">

                            <h6 class="fw-bold mb-1">
                                <i class="bi bi-person-fill me-1 text-primary"></i>
                                {{ $s->name }}
                            </h6>

                            <p class="mb-1 text-muted small">
                                <i class="bi bi-envelope me-1"></i>
                                {{ $s->email }}
                            </p>

                            <span class="badge bg-info mb-2">
                                {{ $s->kelas ?? 'Tanpa Kelas' }}
                            </span>

                            <div class="d-grid mt-3">
                                <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editModal"
                                    data-id="{{ $s->id }}" data-name="{{ $s->name }}" data-email="{{ $s->email }}">
                                    <i class="bi bi-pencil-square me-1"></i> Edit Siswa
                                </button>
                            </div>

                        </div>
                    </div>
                @endforeach
            </div>

        @else
            <div class="alert alert-warning">Belum ada siswa yang terdaftar.</div>
        @endif

    </div>

    <!-- Modal Edit Siswa -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header bg-primary text-white rounded-top-4">
                    <h5 class="modal-title" id="editModalLabel"><i class="bi bi-pencil-square me-2"></i>Edit Data Siswa</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('dataSiswa.update') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" name="id" id="edit_id">

                        <div class="mb-3">
                            <label for="edit_name" class="form-label fw-semibold">Nama Siswa</label>
                            <input type="text" class="form-control" id="edit_name" name="name" required>
                        </div>

                        <div class="mb-3">
                            <label for="edit_email" class="form-label fw-semibold">Email</label>
                            <input type="email" class="form-control" id="edit_email" name="email" required>
                        </div>

                        <div class="mb-3">
                            <label for="edit_password" class="form-label fw-semibold">Password (opsional)</label>
                            <input type="password" class="form-control" id="edit_password" name="password"
                                placeholder="Biarkan kosong jika tidak diubah">
                        </div>
                    </div>
                    <div class="modal-footer bg-light rounded-bottom-4">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    {{-- MODAL INFO DATA SISWA --}}
    <div class="modal fade" id="modalInfoDataSiswa" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content rounded-4 shadow">

                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-info-circle me-2"></i>
                        Informasi Data Siswa
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <p>
                        Halaman <strong>Data Siswa per Kelas</strong> digunakan untuk mengelola
                        daftar siswa yang tergabung dalam setiap kelas.
                    </p>

                    <hr>

                    <h6 class="fw-bold text-primary">
                        <i class="bi bi-diagram-3 me-1"></i>
                        Filter Kelas
                    </h6>
                    <ul>
                        <li>Pilih kelas untuk menampilkan siswa berdasarkan kelas tersebut.</li>
                        <li>Jika tidak memilih kelas, sistem akan menampilkan seluruh siswa.</li>
                        <li>Perubahan pilihan kelas akan langsung memuat ulang data.</li>
                    </ul>

                    <hr>

                    <h6 class="fw-bold text-success">
                        <i class="bi bi-table me-1"></i>
                        Tabel Data Siswa
                    </h6>
                    <ul>
                        <li>Menampilkan nama siswa, email, dan kelas.</li>
                        <li>Dilengkapi fitur pencarian, pagination, dan responsive.</li>
                        <li>Nomor baris akan menyesuaikan jumlah data yang tampil.</li>
                    </ul>

                    <hr>

                    <h6 class="fw-bold text-warning">
                        <i class="bi bi-pencil-square me-1"></i>
                        Edit Data Siswa
                    </h6>
                    <ul>
                        <li>Gunakan tombol <strong>Edit</strong> untuk memperbarui data siswa.</li>
                        <li>Password bersifat opsional dan dapat dikosongkan jika tidak ingin diubah.</li>
                        <li>Perubahan akan langsung tersimpan ke database.</li>
                    </ul>

                    <hr>

                    <h6 class="fw-bold text-info">
                        <i class="bi bi-file-earmark-excel me-1"></i>
                        Export Data Siswa
                    </h6>
                    <ul>
                        <li>Export dapat dilakukan untuk seluruh siswa.</li>
                        <li>Jika kelas dipilih, export hanya berisi siswa dari kelas tersebut.</li>
                        <li>File hasil export menggunakan format Excel.</li>
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


    <!-- JS -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/datatables.net@1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/datatables.net-bs5@1.13.6/js/dataTables.bootstrap5.min.js"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            if (document.getElementById("tabelSiswa")) {
                new DataTable('#tabelSiswa', {
                    responsive: true,
                    language: {
                        search: "Cari:",
                        lengthMenu: "Tampilkan _MENU_ data",
                        info: "Menampilkan _START_ - _END_ dari _TOTAL_ siswa",
                        zeroRecords: "Tidak ditemukan data yang cocok"
                    }
                });
            }

            // Isi modal edit
            const editModal = document.getElementById('editModal');
            editModal.addEventListener('show.bs.modal', event => {
                const button = event.relatedTarget;
                document.getElementById('edit_id').value = button.getAttribute('data-id');
                document.getElementById('edit_name').value = button.getAttribute('data-name');
                document.getElementById('edit_email').value = button.getAttribute('data-email');
                document.getElementById('edit_password').value = '';
            });
        });
    </script>
@endsection