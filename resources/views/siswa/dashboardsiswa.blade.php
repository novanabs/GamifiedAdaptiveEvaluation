@extends('layouts.main')

@section('dashboard')
    @if(request()->is('*dashboard*')) active @endif
@endsection


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

        .table>thead>tr>th {
            color: white !important
        }
    </style>

    <div class="container mt-3">

        <!-- 🔹 Profile + Statistik -->
        <div class="row g-3 mb-4">

            <!-- COMBINED: Profile + Stats + Badge (gabungan jadi 1 card) -->
            <div class="col-12 col-md-6">
                <div class="card shadow-sm h-100 status-card border-start-primary">
                    <div class="card-body">
                        <div class="d-flex gap-3 align-items-center">
                            {{-- Profile Image + Name --}}
                            <div class="text-center" style="min-width:120px">
                                <img src="https://cdn.pixabay.com/photo/2023/02/18/11/00/icon-7797704_640.png"
                                    alt="Foto Profile" class="rounded-circle profile-img mb-2">
                                <h6 class="fw-bold mb-0 text-primary" style="font-size:1rem">{{ $user->name }}</h6>
                                <small class="text-muted d-block">{{ $user->email }}</small>
                            </div>

                            {{-- Right: stats and badge --}}
                            <div class="flex-fill">
                                <div class="d-flex gap-2 mb-3 flex-wrap">
                                    <div class="card p-2 flex-fill shadow-sm border-start-success" style="min-width:120px">
                                        <div class="small text-muted">Jumlah Aktivitas</div>
                                        <div class="fw-bold text-success" style="font-size:1.25rem">{{ $jumlahAktivitas }}
                                        </div>
                                    </div>

                                    <div class="card p-2 flex-fill shadow-sm border-start-danger" style="min-width:120px">
                                        <div class="small text-muted">Jumlah Remedial</div>
                                        <div class="fw-bold text-danger" style="font-size:1.25rem">{{ $jumlahRemedial }}
                                        </div>
                                    </div>

                                    <div class="card p-2 flex-fill shadow-sm border-start-primary" style="min-width:120px">
                                        <div class="small text-muted">Kelas</div>
                                        <div class="fw-bold" style="font-size:0.95rem">
                                            @if($kelasList->isNotEmpty())
                                                {{ $kelasList->pluck('name')->implode(', ') }}
                                            @else
                                                -
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <div class="pt-2 border-top">
                                    <div class="small text-muted">Informasi Badge</div>

                                    @if(isset($userBadges) && $userBadges->isNotEmpty())
                                        <div class="d-flex flex-wrap gap-2 mt-2">
                                            @foreach($userBadges as $ub)
                                                <div class="d-flex align-items-center gap-2 p-2 border rounded"
                                                    style="min-width:160px;">
                                                    {{-- gambar badge --}}
                                                    @php
                                                        // fallback jika path_icon kosong atau file tidak ada
                                                        $icon = $ub->path_icon ? asset($ub->path_icon) : asset('img/default.png');
                                                    @endphp

                                                    <img src="{{ $icon }}" alt="{{ $ub->name }}" width="48" height="48"
                                                        style="object-fit:contain; border-radius:8px; box-shadow:0 2px 6px rgba(0,0,0,.08);">

                                                    <div>
                                                        <div class="fw-semibold" style="font-size:0.95rem;">{{ $ub->name }}</div>
                                                        <div class="small text-muted">{{ $ub->description }}</div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="mt-1 text-muted">Belum ada badge</div>
                                    @endif
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- LEADERBOARD  -->
            <!-- Leaderboard swappable per kelas -->
            <div class="col-12 col-md-6">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h6 class="fw-semibold mb-0">
                                <i class="bi bi-trophy-fill text-warning me-1"></i> Leaderboard
                            </h6>

                            @if($kelasList->count() > 1)
                                <select id="kelasSelector" class="form-select form-select-sm" style="width:200px;">
                                    @foreach($leaderboardsPerClass as $cl)
                                        <option value="{{ $cl->class_id }}">{{ $cl->class_name }}</option>
                                    @endforeach
                                </select>
                            @else
                                <small class="text-muted">Kelas: {{ $kelasList->first()->name ?? '-' }}</small>
                            @endif
                        </div>

                        <div id="leaderboardArea" style="max-height:350px; overflow-y:auto; padding-right:6px;">
                            <!-- content akan di-render oleh JS; inisialisasi ke kelas pertama -->
                        </div>

                        <div class="mt-3 text-end">
                            <a href="#" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
                        </div>
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
                    pageLength: 10,
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
        <script>
            // ambil data dari backend (array of {class_id, class_name, students})
            const leaderboardsPerClass = @json($leaderboardsPerClass);
            const myUserId = {{ $user->id }};
            // helper render
            function renderLeaderboardForClass(classId) {
                const block = leaderboardsPerClass.find(c => c.class_id == classId);
                const area = document.getElementById('leaderboardArea');

                if (!block || !block.students || block.students.length === 0) {
                    area.innerHTML = `
                                <div class="text-center py-4 text-muted">
                                    <div class="mb-2">Belum ada peringkat</div>
                                    <small>Leaderboard akan tampil setelah siswa mengerjakan aktivitas</small>
                                </div>
                            `;
                    return;
                }

                let html = '<ul class="list-group list-group-flush">';

                block.students.forEach((row, idx) => {
                    const isMe = (row.id == myUserId);
                    const score = Number(row.total_score) || 0;
                    const medal = idx === 0 ? '🥇' : (idx === 1 ? '🥈' : (idx === 2 ? '🥉' : (idx + 1)));
                    html += `
                                <li class="list-group-item d-flex align-items-center justify-content-between ${isMe ? 'bg-light' : ''}"
                                    style="${isMe ? 'border-left:4px solid #0d6efd;' : ''}">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="fw-bold text-primary" style="min-width:36px;">${medal}</div>
                                        <div>
                                            <div class="fw-semibold">${row.name}</div>
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        <div class="fw-bold">${Number(score).toLocaleString()}</div>
                                        <small class="text-muted">poin</small>
                                    </div>
                                </li>
                            `;
                });

                html += '</ul>';
                area.innerHTML = html;
            }

            // inisialisasi: gunakan kelas pertama jika ada
            if (leaderboardsPerClass.length > 0) {
                renderLeaderboardForClass(leaderboardsPerClass[0].class_id);
            }

            // swap handler
            const sel = document.getElementById('kelasSelector');
            if (sel) {
                sel.addEventListener('change', function () {
                    renderLeaderboardForClass(this.value);
                });
            }
        </script>

    @endpush
@endsection