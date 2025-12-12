@extends('layouts.main')

@section('aktivitas')
    @if(request()->is('*aktivitassiswa*')) active @endif
@endsection

@section('content')
    <style>
        /* ---------- overall ---------- */
        .page-title { margin-bottom: 1rem; }
        .section-title { margin-bottom: 0.75rem; }

        /* ========== GRID / EQUAL HEIGHT SETUP ========== */
        .row.gx-4.gy-4 > .col {
            display: flex;
            align-items: stretch;
        }

        /* ---------- activity card ---------- */
        .activity-card {
            border-radius: 12px;
            overflow: hidden;
            transition: transform .20s ease, box-shadow .20s ease;
            display: flex;
            flex-direction: column;
            background: #ffffff;
            flex: 1 1 auto;
            min-height: 340px;
        }
        .activity-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 18px 40px rgba(13,110,253,0.08);
        }

        /* image fixed height */
        .activity-card .card-img-top {
            height: 150px;
            object-fit: cover;
            display:block;
            width:100%;
            flex: 0 0 150px;
        }

        /* card body flexible */
        .activity-card .card-body {
            padding: 1rem;
            display:flex;
            flex-direction: column;
            gap: .5rem;
            min-height: 0;
            flex: 1 1 auto;
        }

        /* title: clamp to 2 lines */
        .activity-card .card-body h5,
        .activity-card .card-body .card-title {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
            line-height: 1.15;
            height: calc(1.15em * 2);
            margin-bottom: .25rem;
        }

        /* badges: keep concise with ellipsis */
        .badges {
            display: flex;
            gap: .4rem;
            align-items: center;
            overflow: hidden;
        }
        .badges .badge {
            max-width: 120px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            display:inline-block;
            padding: .35rem .6rem;
            line-height: 1;
        }

        /* meta */
        .meta-line { font-size: .92rem; color:#6c757d; overflow:hidden; text-overflow:ellipsis; }
        .meta-strong { font-weight:600; color:#212529; }

        /* action */
        .action-btn { border-radius: 8px; padding:.52rem .8rem; }

        /* class header */
        .class-header {
            display:inline-block;
            background: linear-gradient(90deg,#f8fafc,#ffffff);
            border:1px solid rgba(0,0,0,0.04);
            padding:.55rem .8rem;
            border-radius:10px;
            box-shadow: 0 6px 18px rgba(2,6,23,0.03);
        }
        .class-header h6 { margin:0; font-size:1rem; font-weight:700; color:#0d6efd; }
        .class-sub { font-size:.86rem; color:#6c757d; }

        /* keep button stuck to bottom */
        .card .mt-auto { margin-top: auto; }

        /* responsive */
        @media (max-width: 576px) {
            .activity-card { min-height: 260px; }
            .activity-card .card-img-top { height:120px; flex: 0 0 120px; }
            .badges .badge { max-width:90px; font-size:.82rem; }
        }
    </style>

    <div class="container-fluid px-4 py-4">
        <div class="d-flex align-items-start justify-content-between mb-4 page-title">
            <div>
                <h1 class="h3 fw-bold text-primary mb-1">
                    <i class="bi bi-journal-check me-2"></i> Aktivitas Kamu
                </h1>
                <p class="text-muted mb-0">Lihat dan kerjakan aktivitas pembelajaranmu di sini.</p>
            </div>

            <div class="d-none d-md-block">
                <a href="#" class="btn btn-outline-secondary btn-sm"><i class="bi bi-funnel-fill me-1"></i> Filter</a>
                <a href="#" class="btn btn-outline-primary btn-sm ms-2"><i class="bi bi-plus-lg me-1"></i> Baru</a>
            </div>
        </div>

        {{-- BELUM DIKERJAKAN --}}
        @if(!empty($belumDikerjakan) && $belumDikerjakan->count())
            <section class="mb-5">
                <h2 class="h5 fw-semibold text-danger section-title">
                    <i class="bi bi-exclamation-circle me-2"></i>
                    Belum Dikerjakan — Deadline Terdekat
                </h2>

                <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 gx-4 gy-4">
                    @foreach ($belumDikerjakan as $sub)
                        @php
                            // nilai & status
                            $nilai = $sub->result ?? null;
                            $status = $sub->result_status ?? '-';

                            // color untuk status
                            $cls = (strtolower($status) === 'remedial') ? 'danger' : ((strtolower($status) === 'pass') ? 'success' : 'secondary');

                            // apakah sudah dinilai
                            $isAlreadyGraded = !is_null($nilai) && $nilai !== '-';

                            // cek deadline (anggap null = tidak ada deadline)
                            $isPastDeadline = false;
                            if (!empty($sub->deadline)) {
                                try {
                                    $isPastDeadline = \Carbon\Carbon::parse($sub->deadline)->isPast();
                                } catch (\Exception $e) {
                                    $isPastDeadline = false;
                                }
                            }

                            // final: tidak bisa mulai jika sudah dinilai atau lewat deadline
                            $cannotStart = $isAlreadyGraded || $isPastDeadline;
                        @endphp

                        <div class="col">
                            <article class="card activity-card">
                                <img class="card-img-top" src="https://picsum.photos/800/400?random=belum{{ $loop->iteration }}" alt="Gambar Aktivitas">
                                <div class="card-body">
                                    <h5 class="mb-1 text-primary fw-bold" title="{{ $sub->aktivitas }}">{{ $sub->aktivitas }}</h5>

                                    <div class="badges">
                                        <span class="badge bg-primary text-white" title="{{ $sub->mapel ?? '' }}">{{ $sub->mapel ?? '-' }}</span>
                                        <span class="badge bg-info text-white" title="{{ $sub->topik ?? '' }}">{{ $sub->topik ?? '-' }}</span>
                                        <span class="badge bg-warning text-dark" title="Kelas {{ $sub->nama_kelas ?? '' }}">Kls {{ $sub->nama_kelas ?? '-' }}</span>
                                        <span class="badge bg-secondary text-white" title="{{ ucfirst($status) }}">{{ ucfirst($status) }}</span>
                                    </div>

                                    <div class="meta-line">
                                        <i class="bi bi-collection me-1"></i>
                                        Status: <span class="meta-strong">{{ ucfirst($status) }}</span>
                                    </div>

                                    <div class="meta-line">
                                        <i class="bi bi-calendar-event me-1"></i>
                                        @php $tanggal = $sub->deadline ?? $sub->created_at; @endphp
                                        {{ $tanggal ? \Carbon\Carbon::parse($tanggal)->format('d M Y H:i') : '-' }}
                                        @if($isPastDeadline)
                                            <small class="text-danger ms-2">— deadline lewat</small>
                                        @endif
                                    </div>

                                    {{-- Nilai + status + tombol (disabled jika perlu) --}}
                                    <div class="d-flex flex-column gap-2 mt-2">
                                        <div class="d-flex align-items-center">
                                            <div>
                                                <div class="text-muted" style="font-size:.92rem;"><strong>Nilai:</strong></div>
                                                <div class="fw-bold" style="font-size:1rem;">
                                                    {!! $nilai !== null ? "<span>$nilai</span>" : '<span class="text-muted">Belum Ada</span>' !!}
                                                </div>
                                            </div>

                                            <div class="ms-auto d-flex align-items-center gap-2">
                                                @if($isPastDeadline)
                                                    <span class="badge bg-danger text-white py-2 px-3" title="Deadline sudah lewat">Deadline Lewat</span>
                                                @endif
                                                <span class="badge bg-{{ $cls }} text-white py-2 px-3">{{ ucfirst($status) }}</span>
                                            </div>
                                        </div>

                                        <div class="meta-line">
                                            {{-- tambahan ringkasan waktu --}}
                                            <small class="text-muted">
                                                Dibuat: {{ $sub->created_at ? \Carbon\Carbon::parse($sub->created_at)->format('d M Y') : '-' }}
                                            </small>
                                        </div>
                                    </div>

                                    <div class="mt-auto">
                                        @if($cannotStart)
                                            <button class="btn btn-secondary w-100 action-btn" disabled
                                                    title="{{ $isAlreadyGraded ? 'Sudah dinilai' : ($isPastDeadline ? 'Deadline sudah lewat' : '') }}">
                                                <i class="bi bi-x-circle me-1"></i>
                                                {{ $isAlreadyGraded ? 'Sudah Dinilai' : 'Tidak Bisa Dikerjakan' }}
                                            </button>
                                        @else
                                            <button class="btn btn-success w-100 action-btn"
                                                    onclick="mulaiAktivitas('{{ $sub->id_activity }}')">
                                                <i class="bi bi-play-fill me-1"></i> Kerjakan Sekarang
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </article>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- ACTIVITIES PER KELAS --}}
        @forelse ($activitiesByClass as $kelas)
            <section class="mb-5">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div>
                        <div class="class-header">
                            <h6 class="mb-0"> <i class="bi bi-people me-2"></i> Kelas {{ $kelas->nama_kelas }}</h6>
                            <div class="class-sub">Level {{ $kelas->level_kelas }} • {{ $kelas->list->count() }} aktivitas</div>
                        </div>
                    </div>
                </div>

                <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 gx-4 gy-4">
                    @foreach ($kelas->list as $sub)
                        @php
                            $nilai = $sub->result ?? null;
                            $status = $sub->result_status ?? '-';
                            $cls = (strtolower($status) === 'remedial') ? 'danger' : ((strtolower($status) === 'pass') ? 'success' : 'secondary');
                            $isAlreadyGraded = !is_null($nilai) && $nilai !== '-';
                            $isPastDeadline = false;
                            if (!empty($sub->deadline)) {
                                try {
                                    $isPastDeadline = \Carbon\Carbon::parse($sub->deadline)->isPast();
                                } catch (\Exception $e) {
                                    $isPastDeadline = false;
                                }
                            }
                            $cannotStart = $isAlreadyGraded || $isPastDeadline;
                        @endphp

                        <div class="col">
                            <article class="card activity-card">
                                <img class="card-img-top" src="https://picsum.photos/800/400?random={{ $kelas->id_class }}{{ $loop->iteration }}" alt="Gambar Aktivitas">
                                <div class="card-body">
                                    <h5 class="mb-1 text-primary fw-bold" title="{{ $sub->aktivitas }}">{{ $sub->aktivitas }}</h5>

                                    <div class="badges">
                                        <span class="badge bg-primary text-white" title="{{ $sub->mapel ?? '' }}">{{ $sub->mapel ?? '-' }}</span>
                                        <span class="badge bg-info text-white" title="{{ $sub->topik ?? '' }}">{{ $sub->topik ?? '-' }}</span>
                                        <span class="badge bg-warning text-dark" title="Kelas {{ $kelas->nama_kelas ?? '' }}">Kls {{ $kelas->nama_kelas ?? '-' }}</span>
                                        <span class="badge bg-secondary text-white" title="{{ ucfirst($status) }}">{{ ucfirst($status) }}</span>
                                    </div>

                                    <div class="meta-line">
                                        <i class="bi bi-collection me-1"></i>
                                        Status: <span class="meta-strong">{{ ucfirst($status) }}</span>
                                    </div>

                                    <div class="meta-line">
                                        <i class="bi bi-calendar-event me-1"></i>
                                        @php $tanggal = $sub->deadline ?? $sub->created_at; @endphp
                                        {{ $tanggal ? \Carbon\Carbon::parse($tanggal)->format('d M Y H:i') : '-' }}
                                        @if($isPastDeadline)
                                            <small class="text-danger ms-2">— deadline lewat</small>
                                        @endif
                                    </div>

                                    <div class="d-flex flex-column gap-2 mt-2">
                                        <div class="d-flex align-items-center">
                                            <div>
                                                <div class="text-muted" style="font-size:.92rem;"><strong>Nilai:</strong></div>
                                                <div class="fw-bold" style="font-size:1rem;">
                                                    {!! $nilai !== null ? "<span>$nilai</span>" : '<span class="text-muted">Belum Ada</span>' !!}
                                                </div>
                                            </div>

                                            <div class="ms-auto d-flex align-items-center gap-2">
                                                @if($isPastDeadline)
                                                    <span class="badge bg-danger text-white py-2 px-3" title="Deadline sudah lewat">Deadline Lewat</span>
                                                @endif
                                                <span class="badge bg-{{ $cls }} text-white py-2 px-3">{{ ucfirst($status) }}</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mt-auto">
                                        @if($cannotStart)
                                            <button class="btn btn-secondary w-100 action-btn" disabled
                                                    title="{{ $isAlreadyGraded ? 'Sudah dinilai' : ($isPastDeadline ? 'Deadline sudah lewat' : '') }}">
                                                <i class="bi bi-x-circle me-1"></i>
                                                {{ $isAlreadyGraded ? 'Sudah Dinilai' : 'Tidak Bisa Dikerjakan' }}
                                            </button>
                                        @else
                                            <button class="btn btn-success w-100 action-btn"
                                                    onclick="mulaiAktivitas('{{ $sub->id_activity }}')">
                                                <i class="bi bi-play-fill me-1"></i> Kerjakan Sekarang
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </article>
                        </div>
                    @endforeach
                </div>
            </section>
        @empty
            <div class="text-center py-5">
                <i class="bi bi-emoji-frown display-6 text-muted"></i>
                <p class="mt-2 text-muted">Belum ada aktivitas untukmu.</p>
            </div>
        @endforelse
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function mulaiAktivitas(id) {
            Swal.fire({
                icon: 'info',
                title: 'Mulai Aktivitas',
                html: 'Kamu akan memulai aktivitas dengan ID: <strong>' + id + '</strong>',
                showCancelButton: true,
                confirmButtonText: 'Lanjut',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#198754'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = `/activity/${id}`;
                }
            });
        }
    </script>
@endpush
