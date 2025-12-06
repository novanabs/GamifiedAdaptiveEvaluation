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
                            <div class="text-center" style="min-width:150px">
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
                                <!-- badge siswa -->
                                <div class="pt-2 border-top">
                                    <div class="small text-muted">Informasi Badge</div>
                                    @if(isset($userBadges) && $userBadges->isNotEmpty())
                                        <div class="d-flex flex-wrap gap-2 mt-2">
                                            @foreach($userBadges as $ub)
                                                <div class="d-flex align-items-center gap-2 p-2 border rounded"
                                                    style="min-width:160px;">
                                                    {{-- gambar badge --}}
                                                    @php
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
                                    <div class="mt-2">
                                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal"
                                            data-bs-target="#badgeListModal">
                                            <i class="bi bi-award"></i> Lihat Badge
                                        </button>
                                    </div>

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
    <!-- Modal Daftar Semua Badge  -->
    <div class="modal fade" id="badgeListModal" tabindex="-1" aria-labelledby="badgeListModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="badgeListModalLabel">Semua Badge</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>

                <div class="modal-body">
                    @if(!isset($allBadges) || $allBadges->isEmpty())
                        <div class="text-center text-muted py-4">Belum ada data badge di sistem.</div>
                    @else
                        <div class="row g-3">
                            @foreach($allBadges as $b)
                                @php
                                    $icon = $b->path_icon ? asset($b->path_icon) : asset('img/default.png');
                                    $isClaimed = in_array($b->id, $claimedBadgeIds ?? []);
                                @endphp

                                <div class="col-md-4" id="badge-card-{{ $b->id }}">
                                    <div class="card h-100 shadow-sm p-3">
                                        <div class="d-flex gap-3 align-items-start">
                                            <img src="{{ $icon }}" width="64" height="64" alt="{{ $b->name }}"
                                                style="object-fit:contain; border-radius:8px;">
                                            <div class="grow">
                                                <div class="fw-bold">{{ $b->name }}</div>
                                                <div class="small text-muted mb-2">{{ $b->description }}</div>
                                            </div>
                                        </div>

                                        <div class="mt-3 text-end">
                                            @if($isClaimed)
                                                <span class="badge bg-success">Terklaim</span>
                                            @else
                                                <form method="POST" action="{{ route('badges.claim') }}"
                                                    class="d-inline badge-claim-form" data-badge-id="{{ $b->id }}">
                                                    @csrf
                                                    <input type="hidden" name="badge_id" value="{{ $b->id }}">
                                                    <button type="submit" class="btn btn-primary btn-sm claim-btn"
                                                        data-badge-id="{{ $b->id }}" disabled>
                                                        Klaim
                                                    </button>
                                                    <div class="small text-muted mt-1 reason-text" id="reason-{{ $b->id }}"
                                                        style="display:none;"></div>
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                    @endif
                </div>

                <div class="modal-footer">
                    <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
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
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const badgeModal = document.getElementById('badgeListModal');

                async function checkEligibilityFor(badgeId) {
                    try {
                        const res = await fetch("{{ url('/badges') }}/" + badgeId + "/eligibility", {
                            headers: { 'X-Requested-With': 'XMLHttpRequest' },
                            credentials: 'same-origin'
                        });
                        return await res.json();
                    } catch (e) {
                        console.error('eligibility fetch error', e);
                        return { eligible: false, reason: 'Gagal mengecek syarat (network).' };
                    }
                }

                async function refreshBadgeEligibility() {
                    document.querySelectorAll('.claim-btn').forEach(async btn => {
                        const badgeId = btn.dataset.badgeId;
                        // reset
                        btn.disabled = true;
                        const reasonEl = document.getElementById('reason-' + badgeId);
                        if (reasonEl) { reasonEl.style.display = 'none'; reasonEl.textContent = ''; }

                        const json = await checkEligibilityFor(badgeId);

                        if (json.claimed === true) {
                            // ganti UI: sudah diklaim
                            const card = document.getElementById('badge-card-' + badgeId);
                            if (card) card.querySelector('.mt-3.text-end').innerHTML = '<span class="badge bg-success">Terklaim</span>';
                            return;
                        }

                        if (json.eligible === true) {
                            btn.disabled = false;
                            if (reasonEl) reasonEl.style.display = 'none';
                        } else {
                            btn.disabled = true;
                            if (reasonEl) {
                                reasonEl.textContent = json.reason || 'Belum memenuhi syarat.';
                                reasonEl.style.display = 'block';
                            }
                        }
                    });
                }

                // ketika modal dibuka (Bootstrap 5 event)
                if (badgeModal) {
                    badgeModal.addEventListener('show.bs.modal', function () {
                        refreshBadgeEligibility();
                    });
                }

                // AJAX submit: intercept form submit untuk klaim
                document.querySelectorAll('.badge-claim-form').forEach(form => {
                    form.addEventListener('submit', async function (e) {
                        e.preventDefault();
                        const badgeId = this.dataset.badgeId;
                        const btn = this.querySelector('.claim-btn');
                        if (!btn || btn.disabled) return;

                        // disable button sementara
                        btn.disabled = true;
                        btn.innerText = 'Memproses…';

                        // ambil CSRF token dari meta atau field
                        const token = document.querySelector('meta[name="csrf-token"]')?.content
                            || document.querySelector('input[name="_token"]')?.value;

                        try {
                            const res = await fetch("{{ route('badges.claim') }}", {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': token,
                                    'X-Requested-With': 'XMLHttpRequest'
                                },
                                body: JSON.stringify({ badge_id: badgeId }),
                                credentials: 'same-origin'
                            });

                            const json = await res.json();

                            if (res.ok && json.success) {
                                // sukses klaim -> ubah UI jadi Terklaim
                                const card = document.getElementById('badge-card-' + badgeId);
                                if (card) card.querySelector('.mt-3.text-end').innerHTML = '<span class="badge bg-success">Terklaim</span>';
                                // optional: tampil notifikasi
                                Swal.fire('Berhasil', json.message || 'Badge diklaim', 'success');
                            } else {
                                // gagal -> tampil pesan dan re-enable tombol (jika bukan already claimed)
                                const msg = json.message || json.reason || 'Gagal klaim badge.';
                                Swal.fire('Gagal', msg, 'error');
                                // show reason text if ada
                                const reasonEl = document.getElementById('reason-' + badgeId);
                                if (reasonEl) { reasonEl.textContent = msg; reasonEl.style.display = 'block'; }
                                btn.disabled = false;
                                btn.innerText = 'Klaim';
                            }
                        } catch (err) {
                            console.error(err);
                            Swal.fire('Error', 'Terjadi kesalahan jaringan.', 'error');
                            btn.disabled = false;
                            btn.innerText = 'Klaim';
                        }
                    });
                });
            });
        </script>


    @endpush
@endsection