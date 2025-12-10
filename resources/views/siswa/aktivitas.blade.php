@extends('layouts.main')

@section('aktivitas')
    @if(request()->is('*aktivitassiswa*')) active @endif
@endsection

@section('content')
    <style>
        .activity-card {
            border-radius: 1rem;
            transition: all 0.3s ease;
            height: 100%;
        }

        .activity-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
        }

        .card-img-top {
            height: 160px;
            object-fit: cover;
            border-top-left-radius: 1rem;
            border-top-right-radius: 1rem;
        }

        .row.gx-4.gy-4>.col {
            margin-bottom: 1rem;
        }

        .container-fluid {
            padding-bottom: 2rem;
        }
    </style>

    <div class="container-fluid px-4 py-3">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h1 class="h3 fw-bold text-primary mb-1">
                    <i class="bi bi-journal-check me-2"></i> Aktivitas Kamu
                </h1>
                <p class="text-muted mb-0">Lihat dan kerjakan aktivitas pembelajaranmu di sini.</p>
            </div>
        </div>

        {{-- =======================
        1. BELUM DIKERJAKAN
        ======================= --}}
        @if($belumDikerjakan->count())
            <div class="mb-4">
                <h2 class="h5 fw-semibold mb-3 text-danger">
                    <i class="bi bi-exclamation-circle me-2"></i> Belum Dikerjakan (Deadline Terdekat)
                </h2>

                <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 gx-4 gy-4">
                    @foreach ($belumDikerjakan as $sub)
                        @php
                            $nilai = $sub->result;
                            $status = $sub->result_status;

                            if (strtolower($status) === 'remedial') {
                                $cls = 'danger';
                            } elseif (strtolower($status) === 'pass') {
                                $cls = 'success';
                            } else {
                                $cls = 'secondary';
                            }

                            $isDisabled = !is_null($nilai) && $nilai !== '-';
                        @endphp

                        <div class="col">
                            <div class="card shadow-sm border-0 activity-card border-danger">
                                <img src="https://picsum.photos/600/300?random=belum{{ $loop->iteration }}" class="card-img-top"
                                    alt="Gambar Aktivitas">

                                <div class="card-body d-flex flex-column p-3">
                                    <h5 class="card-title mb-2 text-primary fw-semibold">{{ $sub->aktivitas }}</h5>

                                    <p class="mb-2">
                                        <span class="badge bg-primary me-2 text-white">{{ $sub->mapel }}</span>
                                        <span class="badge bg-info text-white">{{ $sub->topik }}</span>
                                        <span class="badge bg-warning text-dark ms-2">Kelas {{ $sub->nama_kelas }}</span>
                                        <span class="badge bg-secondary text-white ms-2">{{ ucfirst($sub->status) }}</span>
                                    </p>

                                    <p class="text-muted mb-1">
                                        <i class="bi bi-collection me-1"></i>
                                        Status: <strong>{{ ucfirst($sub->status) }}</strong>
                                    </p>

                                    <p class="text-muted mb-2">
                                        <i class="bi bi-calendar-event me-1"></i>
                                        @php
                                            $tanggal = $sub->deadline ?? $sub->created_at;
                                        @endphp
                                        {{ \Carbon\Carbon::parse($tanggal)->format('d M Y') }}
                                    </p>

                                    <p class="mb-3">
                                        <span class="fw-semibold">Nilai:</span>
                                        {!! $nilai ? "<strong>$nilai</strong>" : '<span class="text-muted">Belum Ada</span>' !!}
                                        <span class="badge bg-{{ $cls }} ms-2 text-white">{{ ucfirst($status) }}</span>
                                    </p>

                                    <div class="mt-auto">
                                        <button class="btn btn-success w-100 fw-semibold shadow-sm"
                                            onclick="mulaiAktivitas('{{ $sub->id_activity }}')">
                                            <i class="bi bi-play-fill me-1"></i> Kerjakan Sekarang
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- =======================
        2. ACTIVITIES PER KELAS
        ======================= --}}
        @forelse ($activitiesByClass as $kelas)
            <div class="mb-5">
                {{-- Header card per kelas --}}
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="h5 mb-1 fw-semibold">
                                <i class="bi bi-people me-2"></i>
                                Kelas {{ $kelas->nama_kelas }} (Level {{ $kelas->level_kelas }})
                            </h2>
                            <p class="mb-0 text-muted">
                                Total aktivitas: {{ $kelas->list->count() }}
                            </p>
                        </div>
                    </div>
                </div>

                {{-- List aktivitas di dalam kelas --}}
                <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 gx-4 gy-4">
                    @foreach ($kelas->list as $sub)
                        @php
                            $nilai = $sub->result;
                            $status = $sub->result_status;

                            if (strtolower($status) === 'remedial') {
                                $cls = 'danger';
                            } elseif (strtolower($status) === 'pass') {
                                $cls = 'success';
                            } else {
                                $cls = 'secondary';
                            }

                            $isDisabled = !is_null($nilai) && $nilai !== '-';
                        @endphp

                        <div class="col">
                            <div class="card shadow-sm border-0 activity-card">
                                <img src="https://picsum.photos/600/300?random={{ $kelas->id_class }}{{ $loop->iteration }}"
                                    class="card-img-top" alt="Gambar Aktivitas">

                                <div class="card-body d-flex flex-column p-3">
                                    <h5 class="card-title mb-2 text-primary fw-semibold">{{ $sub->aktivitas }}</h5>

                                    <p class="mb-2">
                                        <span class="badge bg-primary me-2 text-white">{{ $sub->mapel }}</span>
                                        <span class="badge bg-info text-white">{{ $sub->topik }}</span>
                                        <span class="badge bg-warning text-dark ms-2">Kelas {{ $kelas->nama_kelas }}</span>
                                        <span class="badge bg-secondary text-white ms-2">{{ ucfirst($sub->status) }}</span>
                                    </p>

                                    <p class="text-muted mb-1">
                                        <i class="bi bi-collection me-1"></i>
                                        Status: <strong>{{ ucfirst($sub->status) }}</strong>
                                    </p>

                                    <p class="text-muted mb-2">
                                        <i class="bi bi-calendar-event me-1"></i>
                                        @php
                                            $tanggal = $sub->deadline ?? $sub->created_at;
                                        @endphp
                                        {{ \Carbon\Carbon::parse($tanggal)->format('d M Y') }}
                                    </p>

                                    <p class="mb-3">
                                        <span class="fw-semibold">Nilai:</span>
                                        {!! $nilai ? "<strong>$nilai</strong>" : '<span class="text-muted">Belum Ada</span>' !!}
                                        <span class="badge bg-{{ $cls }} ms-2 text-white">{{ ucfirst($status) }}</span>
                                    </p>

                                    <div class="mt-auto">
                                        <button
                                            class="btn btn-{{ $isDisabled ? 'secondary' : 'success' }} w-100 fw-semibold shadow-sm"
                                            {{ $isDisabled ? 'disabled' : '' }} onclick="mulaiAktivitas('{{ $sub->id_activity }}')">
                                            {!! $isDisabled
                        ? '<i class="bi bi-check2-circle me-1"></i> Sudah Dinilai'
                        : '<i class="bi bi-play-fill me-1"></i> Kerjakan Sekarang' !!}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @empty
            <div class="col-12 text-center py-5">
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
                text: 'Kamu akan memulai aktivitas dengan ID: ' + id,
                confirmButtonText: 'Oke',
                confirmButtonColor: '#0d6efd'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = `/activity/${id}`;
                }
            });
        }
    </script>

@endpush