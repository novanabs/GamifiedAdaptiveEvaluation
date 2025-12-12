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

        /* Tambahan / pengganti styling untuk tampilan modal badge */
        .badge-card {
            border-radius: 14px;
            padding: 14px;
            min-height: 150px;
        }

        .badge-card .card-body {
            padding: 0;
        }

        .badge-card .badge-icon {
            width: 64px;
            height: 64px;
            object-fit: contain;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
        }

        .badge-card .badge-title {
            font-weight: 700;
            font-size: 1rem;
        }

        .badge-card .badge-desc {
            color: #6c757d;
            font-size: .9rem;
            margin-top: 4px;
        }

        /* badge matches: row layout (2 kolom) */
        .badge-matches-list {
            margin-top: 10px;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .badge-matches-list .list-group-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            /* left / right */
            padding: 0.45rem 0.6rem;
            border-radius: 8px;
            border: 1px solid #eef2f6;
            background: #fff;
            gap: 12px;
            min-height: 44px;
        }

        /* kiri: nama kelas, ambil sisa ruang */
        .badge-matches-list .match-left {
            display: flex;
            align-items: center;
            gap: 8px;
            min-width: 0;
            /* penting supaya text-overflow bekerja */
            flex: 1 1 auto;
            /* ambil sisa ruang */
        }

        /* class name: potong kalau terlalu panjang */
        .badge-matches-list .class-name {
            font-weight: 600;
            font-size: 0.95rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* kanan: tombol / pill, tidak mengecil */
        .badge-matches-list .match-right {
            flex: 0 0 auto;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* tombol ukuran kecil */
        .btn-claim-class {
            min-width: 120px;
            padding: 0.36rem 0.6rem;
            font-size: 0.86rem;
        }

        /* pill terklaim */
        .claimed-pill {
            background: linear-gradient(180deg, #1cc88a, #17a673);
            color: #fff;
            font-size: 0.82rem;
            padding: 0.32rem 0.6rem;
            border-radius: 999px;
            display: inline-block;
        }

        /* override cepat: pastikan pane tab & kartu badge tetap putih/transparent */
        .tab-content .tab-pane {
            background: transparent !important;
            color: inherit !important;
            padding: 0.5rem 0;
            /* beri jarak bila ingin */
        }

        /* pastikan kartu internal (badge) tidak menerima background global biru */
        .profile-badges-row .card,
        .badge-card,
        .badge-card .card-body,
        #badgeListModal .badge-card {
            background: transparent !important;
            box-shadow: none !important;
            /* optional, jika shadow ikut berpengaruh */
        }

        /* set card internal content tetap putih (jika kamu mau kotak putih di atas latar) */
        .profile-badges-row .card>.d-flex,
        .profile-badges-row .card .card-body {
            background: transparent !important;
        }

        /* jika nav-pills aktif mengubah warna tab (tombol) itu hanya tombol, bukan pane.
                               namun kalau tombol membungkus pane (struktur salah), pisahkan struktur HTML. */
        .nav-pills .nav-link.active {
            background: #0d6efd;
            /* tetap tombol biru — tidak akan mempengaruhi content */
        }

        /* kalau masih biru, coba override .bg-primary pada parent yang tidak seharusnya */
        .pt-2.border-top>.tab-content,
        .pt-2.border-top>.tab-content .tab-pane {
            background: transparent !important;
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
                                    <div>
                                        <label class="small text-muted mb-1 d-block">Aksi</label>
                                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal"
                                            data-bs-target="#modalGabungKelas">
                                            <i class="bi bi-box-arrow-in-right"></i> Gabung Kelas
                                        </button>
                                    </div>
                                </div>
                                <!-- badge siswa -->
                                <div class="pt-2 border-top">
                                    <div class="small text-muted">Informasi Badge</div>

                                    {{-- Tabs: Umum + per-kelas --}}
                                    <ul class="nav nav-pills mb-2" id="badgeTabs" role="tablist">
                                        @foreach($kelasList as $k)
                                            <li class="nav-item" role="presentation">
                                                <button class="nav-link" id="badge-tab-{{ $k->id }}" data-bs-toggle="pill"
                                                    data-bs-target="#badge-pane-{{ $k->id }}" type="button" role="tab"
                                                    aria-controls="badge-pane-{{ $k->id }}"
                                                    aria-selected="false">{{ $k->name }}</button>
                                            </li>
                                        @endforeach
                                    </ul>

                                    {{-- Panes --}}
                                    <div class="tab-content">
                                        {{-- Per-kelas panes --}}
                                        @foreach($kelasList as $k)
                                            @php $key = 'class_' . $k->id; @endphp
                                            <div class="tab-pane fade" id="badge-pane-{{ $k->id }}" role="tabpanel"
                                                aria-labelledby="badge-tab-{{ $k->id }}">
                                                <div class="row g-2 mt-2 profile-badges-row" id="profile-badges-{{ $k->id }}">
                                                    @if(!empty($badgesByClass[$key]))
                                                        @foreach($badgesByClass[$key] as $ub)
                                                            @php $icon = $ub->path_icon ? asset($ub->path_icon) : asset('img/default.png'); @endphp
                                                            <div class="col-12 col-sm-6 col-md-4" id="profile-badge-{{ $ub->id }}">
                                                                <div class="card h-100 border-0 bg-transparent p-0">
                                                                    <div class="d-flex flex-column align-items-center text-center p-2">
                                                                        <img src="{{ $icon }}" alt="{{ $ub->name }}" width="64"
                                                                            height="64"
                                                                            style="object-fit:contain; border-radius:8px; box-shadow:0 2px 6px rgba(0,0,0,.08);">
                                                                        <div class="mt-2 fw-semibold" style="font-size:0.92rem;">
                                                                            {{ $ub->name }}</div>
                                                                        @if(!empty($ub->description))
                                                                            <div class="small text-muted">{{ $ub->description }}</div>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    @else
                                                        <div class="col-12">
                                                            <div class="mt-1 text-muted">Belum ada badge untuk kelas ini.</div>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>

                                    <div class="mt-2">
                                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal"
                                            data-bs-target="#badgeListModal">
                                            <i class="bi bi-award"></i> Dapatkan Badge
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
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
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- 🔹 Daftar Nilai (dengan filter kelas) -->
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <h5 class="fw-bold mb-3 text-primary"><i class="bi bi-bar-chart-line me-1"></i> Daftar Nilai</h5>

                <div class="d-flex align-items-center mb-3 gap-2 flex-wrap">
                    <div>
                        <label class="small text-muted mb-1 d-block">Filter Kelas</label>
                        <select id="filterKelas" class="form-select form-select-sm" style="min-width:200px;">
                            <option value="">Semua Kelas</option>
                            @foreach($kelasList as $k)
                                <option value="{{ e($k->name) }}">{{ $k->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="ms-auto">
                        <small class="text-muted">Jumlah: <strong
                                id="countVisible">{{ $nilaiList->count() }}</strong></small>
                    </div>
                </div>

                <div class="table-responsive">

                    {{-- Jika data kosong, tampilkan pesan saja --}}
                    @if($nilaiList->isEmpty())
                        <div class="text-center text-muted py-4">
                            <i class="bi bi-inboxes fs-1 d-block mb-2"></i>
                            Belum ada data nilai.
                        </div>

                    @else
                        {{-- Jika ada data, tampilkan tabel --}}
                        <table id="nilaiTable" class="table table-bordered table-striped align-middle">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Tanggal</th>
                                    <th>Kelas</th>
                                    <th>Mata Pelajaran</th>
                                    <th>Topik</th>
                                    <th>Nilai Akhir</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach($nilaiList as $index => $n)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>
                                            @php
                                                $dt = $n->result_created_at ? \Carbon\Carbon::parse($n->result_created_at) : null;
                                            @endphp
                                            {{ $dt ? $dt->format('d M Y H:i') : '-' }}
                                        </td>
                                        <td>{{ $n->kelas ?? '-' }}</td>
                                        <td>{{ $n->mapel ?? '-' }}</td>
                                        <td>{{ $n->topik ?? $n->aktivitas ?? '-' }}</td>
                                        <td>
                                            {{ is_null($n->nilai_akhir) || $n->nilai_akhir === '-' ? '-' : $n->nilai_akhir }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>

            </div>
        </div>
    </div>
    <!-- Modal Daftar Semua Badge  -->
    <div class="modal fade" id="badgeListModal" tabindex="-1" aria-labelledby="badgeListModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="badgeListModalLabel">Badge Tersedia</h5>
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

                                <div class="col-12 col-sm-6 col-md-4" id="badge-card-{{ $b->id }}">
                                    <div class="card h-100 shadow-sm badge-card">
                                        <div class="card-body d-flex gap-3">
                                            <img src="{{ $icon }}" alt="{{ $b->name }}" class="badge-icon ">
                                            <div class=" min-w-0">
                                                <div class="badge-title mb-1">{{ $b->name }}</div>
                                                <div class="badge-desc small text-muted mb-2">{{ $b->description }}</div>

                                                <!-- JS akan memasukkan daftar kelas eligible di sini -->
                                                <div class="badge-matches-wrapper"></div>

                                                <div class="mt-3 text-end">
                                                    @if($isClaimed)
                                                        <span class="claimed-pill">Terklaim</span>
                                                    @endif
                                                </div>
                                            </div>
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
    <!-- modal gabung kelas -->
    <div class="modal fade" id="modalGabungKelas" tabindex="-1" aria-labelledby="modalGabungKelasLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form method="POST" action="{{ route('student.gabungKelas') }}">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalGabungKelasLabel">Gabung Kelas dengan Token</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-2">
                            <label class="form-label small">Masukkan Token Kelas</label>
                            <input type="text" name="token" class="form-control form-control-sm" placeholder="Token kelas"
                                required>
                        </div>
                        <div class="small text-muted">Token biasanya diberikan oleh guru. Pastikan memasukkan token dengan
                            benar.</div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary btn-sm">Gabung</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- DataTables --}}
    @push('scripts')
        <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/datatables.net@1.13.6/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/datatables.net-bs5@1.13.6/js/dataTables.bootstrap5.min.js"></script>
        <script>
            $(document).ready(function () {
                // inisialisasi DataTable dan simpan instance
                var table = $('#nilaiTable').DataTable({
                    responsive: true,
                    pageLength: 10,
                    language: {
                        search: "Cari:",
                        lengthMenu: "Tampilkan _MENU_ baris",
                        info: "Menampilkan _START_–_END_ dari _TOTAL_ data",
                        paginate: { previous: "← Sebelumnya", next: "Berikutnya →" },
                        zeroRecords: "Tidak ditemukan data yang sesuai."
                    },
                    // disable automatic order pada kolom No agar numbering manual
                    order: [],
                    columnDefs: [
                        { orderable: false, targets: 0 } // kolom No tidak bisa di-sort
                    ]
                });

                // fungsi escape regex untuk nilai kelas (hindari karakter regex bermasalah)
                function escapeRegex(str) {
                    return str.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
                }

                // filter by kelas (kolom index 2)
                $('#filterKelas').on('change', function () {
                    var val = $(this).val();
                    if (!val) {
                        // kosong => tampilkan semua
                        table.column(2).search('').draw();
                    } else {
                        // exact match menggunakan regex anchors ^...$
                        var regex = '^' + escapeRegex(val) + '$';
                        table.column(2).search(regex, true, false).draw();
                    }
                });

                // update numbering (kolom No) setelah setiap draw (filter/pagination/sort)
                table.on('draw.dt', function () {
                    var info = table.page.info();
                    // loop rows yang sedang tampil dan set nomor berdasar index di display (1..n)
                    table.column(0, { search: 'applied', order: 'applied', page: 'current' }).nodes().each(function (cell, i) {
                        // nomor relatif ke halaman: i + 1 + (page * length)
                        var pageInfo = table.page.info();
                        var num = pageInfo.start + i + 1;
                        cell.innerHTML = num;
                    });

                    // update count visible
                    $('#countVisible').text(table.rows({ search: 'applied' }).count());
                });

                // trigger pertama supaya count & numbering benar saat load
                table.draw();
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
                                                                                                                                                                                    <div class="text-center text-muted py-3">
                                                                                                                                                                                        Belum ada peringkat untuk kelas ini.
                                                                                                                                                                                    </div>
                                                                                                                                                                                `;
                    return;
                }

                let html = '<ul class="list-group list-group-flush">';

                block.students.forEach((row, idx) => {
                    const isMe = (row.id == myUserId);
                    const rank = idx + 1;

                    html += `
                                                                                                                                                                                    <li class="list-group-item d-flex justify-content-between align-items-center ${isMe ? 'bg-light' : ''}"
                                                                                                                                                                                        style="${isMe ? 'border-left:4px solid #0d6efd;' : ''}">

                                                                                                                                                                                        <div class="d-flex align-items-center gap-3">
                                                                                                                                                                                            <div class="fw-bold text-primary" style="width:28px">${rank}</div>

                                                                                                                                                                                            <div class="fw-semibold">${row.name}</div>
                                                                                                                                                                                        </div>

                                                                                                                                                                                        <div class="text-end">
                                                                                                                                                                                            <div class="fw-bold">${Number(row.total_score).toLocaleString()}</div>
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
                    } catch (err) {
                        console.error('eligibility fetch error', err);
                        return { eligible: false, reason: 'Gagal mengecek syarat (network).' };
                    }
                }

                async function refreshBadgeEligibility() {
                    // kosongkan semua wrapper dulu (hindari duplikat)
                    document.querySelectorAll('.badge-matches-wrapper').forEach(w => w.innerHTML = '');

                    const cards = Array.from(document.querySelectorAll('[id^="badge-card-"]'));
                    for (const card of cards) {
                        const badgeId = card.id.replace('badge-card-', '').trim();
                        const wrapper = card.querySelector('.badge-matches-wrapper');

                        if (!wrapper) continue;

                        // tampilkan loading sederhana
                        wrapper.innerHTML = '<div class="small text-muted">Memeriksa syarat…</div>';

                        const json = await checkEligibilityFor(badgeId);

                        // clear
                        wrapper.innerHTML = '';

                        // setelah menerima `json` dan sudah clear wrapper:
                        if (json.eligible && Array.isArray(json.matches) && json.matches.length) {
                            // jika semua sudah claimed -> cukup tandai footer sebagai terklaim
                            const allClaimed = json.matches.every(m => !!m.already_claimed);
                            const cardFooter = card.querySelector('.mt-3.text-end');

                            if (allClaimed) {
                                // tampilkan pesan di wrapper dan tandai footer
                                wrapper.innerHTML = '<div class="small text-muted">Sudah diklaim di semua kelas.</div>';
                                if (cardFooter) cardFooter.innerHTML = '<span class="claimed-pill">Terklaim</span>';
                                continue; // lanjut ke card berikutnya
                            }

                            // tidak semua claimed -> render daftar, tapi tetap tunjukkan pill per baris jika sudah claimed
                            const list = document.createElement('div');
                            list.className = 'badge-matches-list';

                            json.matches.forEach(m => {
                                const item = document.createElement('div');
                                item.className = 'list-group-item';

                                const left = document.createElement('div');
                                left.className = 'match-left';
                                left.innerHTML = `<div class="class-name">${escapeHtml(m.class_name)}</div>`;

                                const right = document.createElement('div');

                                if (m.already_claimed) {
                                    right.innerHTML = '<span class="claimed-pill">Terklaim</span>';
                                } else {
                                    const btn = document.createElement('button');
                                    btn.className = 'btn btn-sm btn-primary btn-claim-class';
                                    btn.dataset.badgeId = badgeId;
                                    btn.dataset.classId = m.class_id;
                                    btn.type = 'button';
                                    btn.textContent = 'Klaim';
                                    right.appendChild(btn);
                                }

                                item.appendChild(left);
                                item.appendChild(right);
                                list.appendChild(item);
                            });

                            wrapper.appendChild(list);
                        } else {
                            // tidak eligible: tampilkan alasan
                            const reason = json.reason || 'Belum memenuhi syarat.';
                            wrapper.innerHTML = `<div class="small text-muted">${escapeHtml(reason)}</div>`;
                        }

                    }
                }

                // delegated click handler untuk klaim per kelas
                document.addEventListener('click', function (e) {
                    const t = e.target;
                    if (!t) return;
                    if (t.classList.contains('btn-claim-class')) {
                        const badgeId = t.dataset.badgeId;
                        const classId = t.dataset.classId;
                        const originalText = t.innerText;
                        t.disabled = true;
                        t.innerText = 'Memproses...';

                        fetch("{{ route('badges.claim') }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: JSON.stringify({ badge_id: badgeId, class_id: classId })
                        })
                            .then(r => r.json())
                            .then(res => {
                                if (res && res.success) {
                                    if (typeof Swal !== 'undefined') Swal.fire('Sukses', res.message || 'Badge diklaim', 'success');

                                    // update baris yang diklaim
                                    const listItem = t.closest('.list-group-item');
                                    if (listItem) {
                                        const right = t.parentElement;
                                        right.innerHTML = '<span class="claimed-pill">Terklaim</span>';
                                    }


                                } else {
                                    const msg = (res && (res.message || res.reason)) || 'Gagal klaim';
                                    if (typeof Swal !== 'undefined') Swal.fire('Gagal', msg, 'error');
                                    t.disabled = false;
                                    t.innerText = originalText;
                                }
                            })
                            .catch(err => {
                                console.error('claim error', err);
                                if (typeof Swal !== 'undefined') Swal.fire('Error', 'Gagal menghubungi server', 'error');
                                t.disabled = false;
                                t.innerText = originalText;
                            });
                    }
                });

                if (badgeModal) badgeModal.addEventListener('show.bs.modal', refreshBadgeEligibility);

                function escapeHtml(str) {
                    if (typeof str !== 'string') return str || '';
                    return str.replace(/[&<>"'`=\/]/g, function (s) {
                        return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;', '/': '&#x2F;', '`': '&#x60;', '=': '&#x3D;' })[s];
                    });
                }
            });


        </script>

        <script>
            $(function () {
                // ----- helper: build HTML badge untuk profil -----
                function buildProfileBadgeHtml(badge) {
                    var icon = badge.path_icon || '{{ asset("img/default.png") }}';
                    var safeName = $('<div/>').text(badge.name || '').html();
                    var safeDesc = $('<div/>').text(badge.description || '').html();

                    return `
                                                                                        <div class="col-12 col-sm-6 col-md-4" id="profile-badge-${badge.id}">
                                                                                            <div class="card h-100 border-0 bg-transparent p-0">
                                                                                                <div class="d-flex flex-column align-items-center text-center p-2">
                                                                                                    <img src="${icon}" alt="${safeName}" width="64" height="64"
                                                                                                        style="object-fit:contain; border-radius:8px; box-shadow:0 2px 6px rgba(0,0,0,.08);">
                                                                                                    <div class="mt-2 fw-semibold" style="font-size:0.92rem;">${safeName}</div>
                                                                                                </div>
                                                                                            </div>
                                                                                        </div>`;
                }

                // ----- helper: cari / buat container badge profil -----
                function ensureProfileBadgeContainerJQ() {
                    // cari container yang sudah ada (sesuai blade: .row.g-2.mt-2 di bawah Informasi Badge)
                    var $infoSection = null;
                    $('.pt-2.border-top').each(function () {
                        if ($(this).find('.small.text-muted').first().text().trim().indexOf('Informasi Badge') !== -1) {
                            $infoSection = $(this);
                            return false;
                        }
                    });

                    if (!$infoSection || !$infoSection.length) {
                        // fallback cari berdasarkan tombol modal
                        $infoSection = $('button[data-bs-target="#badgeListModal"]').closest('.pt-2.border-top');
                        if (!$infoSection || !$infoSection.length) $infoSection = $('.col-12.col-md-6 .card-body').first();
                    }

                    // coba dapatkan row container yang ada
                    var $container = $infoSection.find('.profile-badges-row').first();
                    if (!$container || !$container.length) {
                        // jika blade sudah ada row.g-2.mt-2 gunakan itu; kalau tidak buat baru
                        var $existingRow = $infoSection.find('.row.g-2.mt-2').first();
                        if ($existingRow && $existingRow.length) {
                            $existingRow.addClass('profile-badges-row');
                            return $existingRow;
                        }
                        // buat row baru
                        $container = $('<div class="row g-2 mt-2 profile-badges-row"></div>');
                        // jika ada teks placeholder "Belum ada badge" hapus
                        $infoSection.find('.mt-1.text-muted:contains("Belum ada badge")').remove();
                        $infoSection.append($container);
                    }
                    return $container;
                }

                // ----- helper: tambahkan badge ke profil (tanpa duplikasi) -----
                function addBadgeToProfile(badge) {
                    if (!badge || !badge.id) return;
                    var $container = ensureProfileBadgeContainerJQ();
                    if ($('#profile-badge-' + badge.id).length) return; // cegah duplikat
                    var html = buildProfileBadgeHtml(badge);
                    $container.append(html);
                }

                // ----- small escape helper for fallback reads -----
                function escapeHtml(str) {
                    if (typeof str !== 'string') return str || '';
                    return str.replace(/[&<>"'`=\/]/g, function (s) {
                        return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;', '/': '&#x2F;', '`': '&#x60;', '=': '&#x3D;' })[s];
                    });
                }

                // ----- Intercept legacy form claim (global claim button) -----
                $(document).on('submit', '.badge-claim-form', function (e) {
                    e.preventDefault();
                    var $form = $(this);
                    var badgeId = $form.data('badge-id');
                    var $btn = $form.find('.claim-btn');
                    if (!$btn.length || $btn.prop('disabled')) return;

                    $btn.prop('disabled', true).text('Memproses…');
                    var token = $('meta[name="csrf-token"]').attr('content') || $form.find('input[name="_token"]').val();

                    $.ajax({
                        url: $form.attr('action'),
                        method: 'POST',
                        data: JSON.stringify({ badge_id: badgeId }),
                        contentType: 'application/json',
                        headers: { 'X-CSRF-TOKEN': token, 'X-Requested-With': 'XMLHttpRequest' },
                        success: function (res) {
                            if (res && res.success) {
                                if (typeof Swal !== 'undefined') Swal.fire('Berhasil', res.message || 'Badge diklaim', 'success');

                                // update modal footer/card
                                var $card = $('#badge-card-' + badgeId);
                                if ($card.length) $card.find('.mt-3.text-end').html('<span class="claimed-pill">Terklaim</span>');

                                // ambil badge object dari response atau fallback membaca dari DOM card
                                var badge = res.badge || {};
                                if (!badge.path_icon && $card.length) badge.path_icon = $card.find('img').first().attr('src') || '{{ asset("img/default.png") }}';
                                if (!badge.name && $card.length) badge.name = $card.find('.badge-title').first().text().trim() || 'Badge';
                                if (!badge.description && $card.length) badge.description = $card.find('.badge-desc').first().text().trim() || '';
                                badge.id = badge.id || badgeId;

                                // langsung tambahkan ke profil tanpa refresh
                                addBadgeToProfile(badge);

                                // disable duplicate buttons if ada
                                $('.claim-btn[data-badge-id="' + badgeId + '"]').prop('disabled', true).text('Terklaim');

                            } else {
                                var msg = (res && (res.message || res.reason)) || 'Gagal klaim badge.';
                                if (typeof Swal !== 'undefined') Swal.fire('Gagal', msg, 'error');
                                $btn.prop('disabled', false).text('Klaim');
                            }
                        },
                        error: function (xhr) {
                            var json = xhr.responseJSON || {};
                            var msg = json.message || json.reason || 'Terjadi kesalahan jaringan/server.';
                            if (typeof Swal !== 'undefined') Swal.fire('Error', msg, 'error');
                            $btn.prop('disabled', false).text('Klaim');
                        }
                    });
                });

                // ----- Delegated handler untuk tombol 'Klaim' per-kelas -----
                document.addEventListener('click', function (e) {
                    const t = e.target;
                    if (!t) return;
                    if (t.classList && t.classList.contains('btn-claim-class')) {
                        const badgeId = t.dataset.badgeId;
                        const classId = t.dataset.classId;
                        const originalText = t.innerText;
                        t.disabled = true;
                        t.innerText = 'Memproses...';

                        fetch("{{ route('badges.claim') }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: JSON.stringify({ badge_id: badgeId, class_id: classId })
                        })
                            .then(r => r.json().catch(() => ({ success: false, message: 'Invalid JSON' })))
                            .then(res => {
                                if (res && res.success) {
                                    if (typeof Swal !== 'undefined') Swal.fire('Sukses', res.message || 'Badge diklaim', 'success');

                                    // ubah tombol menjadi terklaim
                                    const listItem = t.closest('.list-group-item');
                                    if (listItem) {
                                        const right = t.parentElement;
                                        right.innerHTML = '<span class="claimed-pill">Terklaim</span>';
                                    }

                                    // update footer badge card
                                    const card = document.getElementById('badge-card-' + badgeId);
                                    if (card) {
                                        const footer = card.querySelector('.mt-3.text-end');
                                        if (footer) footer.innerHTML = '<span class="claimed-pill">Terklaim</span>';
                                    }

                                    // ambil badge data dari response atau fallback ke DOM card
                                    var badge = res.badge || {};
                                    if (!badge.path_icon && card) badge.path_icon = card.querySelector('img')?.getAttribute('src') || '{{ asset("img/default.png") }}';
                                    if (!badge.name && card) badge.name = card.querySelector('.badge-title')?.textContent.trim() || 'Badge';
                                    if (!badge.description && card) badge.description = card.querySelector('.badge-desc')?.textContent.trim() || '';
                                    badge.id = badge.id || badgeId;

                                    // tambahkan ke profil langsung
                                    addBadgeToProfile(badge);

                                } else {
                                    const msg = (res && (res.message || res.reason)) || 'Gagal klaim';
                                    if (typeof Swal !== 'undefined') Swal.fire('Gagal', msg, 'error');
                                    t.disabled = false;
                                    t.innerText = originalText;
                                }
                            })
                            .catch(err => {
                                console.error('claim error', err);
                                if (typeof Swal !== 'undefined') Swal.fire('Error', 'Gagal menghubungi server', 'error');
                                t.disabled = false;
                                t.innerText = originalText;
                            });
                    }
                });

            }); 
        </script>

    @endpush
@endsection