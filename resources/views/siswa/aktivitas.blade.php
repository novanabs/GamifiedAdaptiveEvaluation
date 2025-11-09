@extends('layouts.main')

@section('content')
    <style>
        /* ✅ Kustomisasi Card dan Spacing */
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

        /* ✅ Atur jarak antar card (gap) seperti contoh HTML aslimu */
        .row.gx-4.gy-4>.col {
            margin-bottom: 1rem;
        }

        /* ✅ Tambah padding di bawah halaman agar tidak terlalu rapat */
        .container-fluid {
            padding-bottom: 2rem;
        }
    </style>

    <div class="container-fluid px-4 py-3">

        <!-- 🔹 Judul Halaman -->
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h1 class="h3 fw-bold text-primary mb-1">
                    <i class="bi bi-journal-check me-2"></i> Aktivitas Kamu
                </h1>
                <p class="text-muted mb-0">Lihat dan kerjakan aktivitas pembelajaranmu di sini.</p>
            </div>
        </div>

        <!-- 🔹 Daftar Aktivitas -->
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 gx-4 gy-4">
            @forelse ($activities as $act)
                <div class="col">
                    <div class="card shadow-sm border-0 activity-card">

                        {{-- Gambar Dummy --}}
                        <img src="https://picsum.photos/600/300?random={{ $loop->iteration }}" class="card-img-top"
                            alt="Gambar Aktivitas">

                        <div class="card-body d-flex flex-column p-3">
                            {{-- Judul Aktivitas --}}
                            <h5 class="card-title mb-2 text-primary fw-semibold">{{ $act->aktivitas }}</h5>

                            {{-- Mapel dan Topik --}}
                            <p class="mb-2">
                                <span class="badge bg-primary me-2 text-white">{{ $act->mapel }}</span>
                                <span class="badge bg-info text-white">{{ $act->topik }}</span>
                            </p>

                            {{-- Jenis Aktivitas --}}
                            <p class="text-muted mb-1">
                                <i class="bi bi-collection me-1"></i>
                                Jenis: <strong>{{ ucfirst($act->status) }}</strong>
                            </p>

                            {{-- Tanggal --}}
                            <p class="text-muted mb-2">
                                <i class="bi bi-calendar-event me-1"></i>
                                {{ \Carbon\Carbon::parse($act->created_at)->format('d M Y') }}
                            </p>

                            {{-- Nilai & Status --}}
                            @php
                                $nilai = $act->result;
                                $status = $nilai ? ($act->result_status ?? 'Sudah Dinilai') : 'Belum Dinilai';
                                $cls = $nilai ? 'success' : 'secondary';
                                $isDisabled = !is_null($nilai);
                            @endphp

                            <p class="mb-3">
                                <span class="fw-semibold">Nilai:</span>
                                {!! $nilai ? "<strong>$nilai</strong>" : '<span class="text-muted">Belum Ada</span>' !!}
                                <span class="badge bg-{{ $cls }} ms-2 text-white">{{ ucfirst($status) }}</span>
                            </p>

                            {{-- Tombol Aksi --}}
                            <div class="mt-auto">
                                <button class="btn btn-{{ $isDisabled ? 'secondary' : 'success' }} w-100 fw-semibold shadow-sm"
                                    {{ $isDisabled ? 'disabled' : '' }} onclick="mulaiAktivitas('{{ $act->id_aktivitas }}')">
                                    {!! $isDisabled
                ? '<i class="bi bi-check2-circle me-1"></i> Sudah Dinilai'
                : '<i class="bi bi-play-fill me-1"></i> Kerjakan Sekarang' !!}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center py-5">
                    <i class="bi bi-emoji-frown display-6 text-muted"></i>
                    <p class="mt-2 text-muted">Belum ada aktivitas untukmu.</p>
                </div>
            @endforelse
        </div>

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
            });
        }
    </script>
@endpush