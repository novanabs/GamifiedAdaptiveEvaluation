@extends('layouts.main')

@section('content')
    <style>
        .card {
            border-radius: 1rem;
            transition: all 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 24px rgba(0, 0, 0, 0.1);
        }

        .profile-img {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border: 4px solid #4e73df;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        th {
            background-color: #4e73df !important;
            color: white;
            text-align: center;
            vertical-align: middle !important;
        }

        td {
            vertical-align: middle !important;
        }

        .nilai-box {
            border-radius: 0.4rem;
            background-color: #f8f9fc;
            padding: 0.3rem 0.5rem;
        }

        .nilai-title {
            font-weight: 600;
            color: #4e73df;
            font-size: 0.8rem;
        }

        .nilai-score {
            font-size: 0.9rem;
            font-weight: bold;
        }

        .badge {
            font-size: 0.85rem;
            padding: 0.4em 0.7em;
        }

        .status-card {
            border-left-width: 5px !important;
        }

        .border-start-primary {
            border-left-color: #4e73df !important;
        }

        .border-start-success {
            border-left-color: #1cc88a !important;
        }

        .border-start-danger {
            border-left-color: #e74a3b !important;
        }

        .bg-soft-danger {
            background-color: rgba(231, 74, 59, 0.1);
            color: #e74a3b;
        }

        .bg-soft-success {
            background-color: rgba(28, 200, 138, 0.1);
            color: #1cc88a;
        }

        .table th,
        .table td {
            font-size: 0.9rem;
        }
    </style>

    <div class="container mt-3">

        <!-- 🔹 Profile + Statistik -->
        <div class="row g-3 mb-4">
            <!-- Profile -->
            <div class="col-12 col-sm-6 col-md-3">
                <div class="card shadow-sm text-center h-100 status-card border-start-primary">
                    <div class="card-body d-flex flex-column align-items-center">
                        <img src="https://cdn.pixabay.com/photo/2023/02/18/11/00/icon-7797704_640.png" alt="Foto Profile"
                            class="rounded-circle profile-img mb-3">
                        <h5 class="fw-bold mb-1 text-primary">{{ $user->name }}</h5>
                        <p class="text-muted mb-0">Email: {{ $user->email }}</p>
                        <small class="text-muted">
                            Kelas:
                            @if($kelasList->isNotEmpty())
                                {{ $kelasList->pluck('name')->implode(', ') }}
                            @else
                                -
                            @endif
                        </small>

                    </div>
                </div>
            </div>

            <!-- Jumlah Aktivitas -->
            <div class="col-12 col-sm-6 col-md-3">
                <div class="card shadow-sm text-center h-100 status-card border-start-success">
                    <div class="card-body d-flex flex-column justify-content-center">
                        <h6 class="text-uppercase text-muted fw-semibold mb-2">Jumlah Aktivitas</h6>
                        <h2 class="fw-bold text-success mb-0">{{ $jumlahAktivitas }}</h2>
                    </div>
                </div>
            </div>

            <!-- Jumlah Remedial -->
            <div class="col-12 col-sm-6 col-md-3">
                <div class="card shadow-sm text-center h-100 status-card border-start-danger">
                    <div class="card-body d-flex flex-column justify-content-center">
                        <h6 class="text-uppercase text-muted fw-semibold mb-2">Jumlah Remedial</h6>
                        <h2 class="fw-bold text-danger mb-0">{{ $jumlahRemedial }}</h2>
                    </div>
                </div>
            </div>

            <!-- Informasi Badge -->
            <div class="col-12 col-sm-6 col-md-3">
                <div class="card shadow-sm h-100 status-card border-start-primary">
                    <div class="card-body d-flex flex-column justify-content-center">
                        <h6 class="text-uppercase text-muted fw-semibold mb-2">Informasi</h6>
                        @if($badge)
                            <p class="text-dark mb-0">
                                🎖️ {{ $badge->name }} <br>
                                <small class="text-muted">{{ $badge->description }}</small>
                            </p>
                        @else
                            <p class="text-dark mb-0">Belum ada badge 📬</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- 🔹 Daftar Nilai -->
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <h5 class="fw-bold mb-3 text-primary"><i class="bi bi-bar-chart-line me-1"></i> Daftar Nilai</h5>
                <div class="table-responsive">
                    <table id="nilaiTable" class="table table-bordered table-striped align-middle">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Tanggal</th>
                                <th>Mata Pelajaran</th>
                                <th>Topik</th>
                                <th>Nilai Dasar</th>
                                <th>Nilai Tambahan</th>
                                <th>Nilai Remedial</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $no = 1; @endphp
                            @forelse($activities as $act)
                                <tr>
                                    <td class="text-center">{{ $no++ }}</td>
                                    <td class="text-center">{{ \Carbon\Carbon::parse($act->tanggal)->format('Y-m-d') }}</td>
                                    <td>{{ $act->mapel }}</td>
                                    <td>{{ $act->topik }}</td>

                                    {{-- ✅ Nilai Dasar --}}
                                    <td>
                                        @if($act->basic)
                                            <div class="nilai-box d-flex justify-content-between align-items-center">
                                                <div>
                                                    <div class="nilai-title">{{ $act->basic->aktivitas }}</div>
                                                    <small class="text-muted">{{ ucfirst($act->basic->status) }}</small>
                                                </div>
                                                <div class="text-end">
                                                    <div
                                                        class="nilai-score {{ $act->basic->result < 60 ? 'text-danger' : 'text-success' }}">
                                                        {{ $act->basic->result ?? '-' }}
                                                    </div>
                                                    <span
                                                        class="badge bg-soft-{{ $act->basic->result_status == 'pass' ? 'danger' : 'success' }} border border-{{ $act->basic->result_status == 'pass' ? 'danger' : 'success' }}">
                                                        {{ $act->basic->result_status == 'pass' ? 'Remedial' : 'Lulus' }}
                                                    </span>
                                                </div>
                                            </div>
                                        @else
                                            <div class="text-center text-muted">-</div>
                                        @endif
                                    </td>

                                    {{-- ✅ Nilai Tambahan --}}
                                    <td>
                                        @if($act->additional)
                                            <div class="nilai-box d-flex justify-content-between align-items-center">
                                                <div>
                                                    <div class="nilai-title">{{ $act->additional->aktivitas }}</div>
                                                    <small class="text-muted">{{ ucfirst($act->additional->status) }}</small>
                                                </div>
                                                <div class="text-end">
                                                    <div class="nilai-score text-success">{{ $act->additional->result ?? '-' }}
                                                    </div>
                                                    <span
                                                        class="badge bg-soft-{{ $act->additional->result_status == 'pass' ? 'danger' : 'success' }} border border-{{ $act->additional->result_status == 'pass' ? 'danger' : 'success' }}">
                                                        {{ $act->additional->result_status == 'pass' ? 'Remedial' : 'Lulus' }}
                                                    </span>
                                                </div>
                                            </div>
                                        @else
                                            <div class="text-center text-muted">-</div>
                                        @endif
                                    </td>

                                    {{-- ✅ Nilai Remedial --}}
                                    <td>
                                        @if($act->remedial)
                                            <div class="nilai-box d-flex justify-content-between align-items-center">
                                                <div>
                                                    <div class="nilai-title">{{ $act->remedial->aktivitas }}</div>
                                                    <small class="text-muted">{{ ucfirst($act->remedial->status) }}</small>
                                                </div>
                                                <div class="text-end">
                                                    <div
                                                        class="nilai-score {{ $act->remedial->result >= 60 ? 'text-success' : 'text-danger' }}">
                                                        {{ $act->remedial->result ?? '-' }}
                                                    </div>
                                                    <span
                                                        class="badge bg-soft-{{ $act->remedial->result_status == 'pass' ? 'danger' : 'success' }} border border-{{ $act->remedial->result_status == 'pass' ? 'danger' : 'success' }}">
                                                        {{ $act->remedial->result_status == 'pass' ? 'Remedial' : 'Lulus' }}
                                                    </span>
                                                </div>
                                            </div>
                                        @else
                                            <div class="text-center text-muted">-</div>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted">Belum ada aktivitas</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                </div>
            </div>
        </div>
    </div>

    {{-- DataTables --}}
    @push('scripts')
        <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/datatables.net@1.13.6/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/datatables.net-bs5@1.13.6/js/dataTables.bootstrap5.min.js"></script>
        <script>
            $(document).ready(function () {
                $('#nilaiTable').DataTable({
                    responsive: true,
                    pageLength: 5,
                    language: {
                        search: "Cari:",
                        lengthMenu: "Tampilkan _MENU_ baris",
                        info: "Menampilkan _START_–_END_ dari _TOTAL_ data",
                        paginate: { previous: "← Sebelumnya", next: "Berikutnya →" },
                        zeroRecords: "Tidak ditemukan data yang sesuai."
                    }
                });
            });
        </script>
    @endpush
@endsection