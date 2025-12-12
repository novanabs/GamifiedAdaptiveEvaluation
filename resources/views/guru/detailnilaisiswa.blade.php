@extends('layouts.main')
@section('dataNilai', 'active')

@section('head')
    {{-- jika layout punya section head, DataTables CSS --}}
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <style>
        /* sedikit styling custom agar rapi */
        .meta-key {
            font-weight: 600;
            color: #495057;
        }

        .meta-value {
            color: #212529;
        }

        .card-activity {
            border-radius: 12px;
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.04);
        }

        .badge-nilai {
            font-size: .85rem;
            padding: .35rem .6rem;
            border-radius: .35rem;
        }

        .no-data {
            color: #6c757d;
            font-style: italic;
        }
    </style>
@endsection

@section('content')
    <div class="container py-4">
        <a href="{{ route('data.nilai') }}" class="btn btn-outline-primary mb-3">
            <i class="fas fa-arrow-left me-1"></i> Kembali
        </a>


        {{-- header / card info aktivitas --}}
        <div class="card card-activity mb-4">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h4 class="mb-1">{{ $activity->title ?? 'Aktivitas' }}</h4>
                        <div class="text-muted mb-2">
                            {{-- Subject, Topic, Class (menggunakan relasi yang ada jika tersedia) --}}
                            <span class="me-3"><span class="meta-key">Mata Pelajaran:</span>
                                <span
                                    class="meta-value">{{ optional(optional($activity->topic)->subject)->name ?? '-' }}</span>
                            </span>
                            <span class="me-3"><span class="meta-key">Topik:</span>
                                <span class="meta-value">{{ optional($activity->topic)->title ?? '-' }}</span>
                            </span>
                            <span class="me-3"><span class="meta-key">Kelas:</span>
                                <span class="meta-value">
                                    {{ optional(optional($activity->topic)->subject)->id_class
        ? (optional(optional($activity->topic)->subject)->classes->name ?? 'Kelas ' . optional(optional($activity->topic)->subject)->id_class)
        : '-' }}
                                </span>
                            </span>
                        </div>

                        <div class="small text-muted">
                            <span class="me-3"><i class="far fa-calendar-alt me-1"></i>
                                Dibuat: {{ optional($activity->created_at)->format('d M Y H:i') ?? '-' }}
                            </span>
                            <span><i class="far fa-clock me-1"></i>
                                Deadline: {{ optional($activity->deadline)->format('d M Y H:i') ?? '-' }}
                            </span>
                        </div>
                    </div>

                    <div class="col-md-4 text-md-end mt-3 mt-md-0">
                        {{-- ringkasan angka --}}
                        @php
                            // students: koleksi/array dari controller: setiap elemen ['id','name','nilai']
                            $countStudents = isset($students) ? count($students) : 0;
                            $countWithNilai = 0;
                            $sumNilai = 0;
                            if ($countStudents) {
                                foreach ($students as $st) {
                                    if (isset($st['nilai']) && $st['nilai'] !== null && $st['nilai'] !== '') {
                                        $countWithNilai++;
                                        // pastikan numeric
                                        $sumNilai += is_numeric($st['nilai']) ? (float) $st['nilai'] : 0;
                                    }
                                }
                            }
                            $avg = $countWithNilai ? round($sumNilai / $countWithNilai, 2) : null;
                        @endphp

                        <div class="d-inline-block text-start">
                            <div class="small text-muted">Siswa</div>
                            <div class="h5 mb-0">{{ $countStudents }}</div>
                        </div>

                        <div class="d-inline-block text-start ms-3">
                            <div class="small text-muted">Tercatat Nilai</div>
                            <div class="h5 mb-0">{{ $countWithNilai }}</div>
                        </div>

                        <div class="d-inline-block text-start ms-3">
                            <div class="small text-muted">Rata-rata</div>
                            <div class="h5 mb-0">{{ $avg ?? '-' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- tabel nilai --}}
        <div class="card">
            <div class="card-body">
                @if(empty($students) || count($students) === 0)
                    <div class="alert alert-info mb-0">Tidak ada siswa di kelas ini.</div>
                @else
                    <div class="table-responsive">
                        <table id="nilaiTable" class="table table-striped table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:60px">No</th>
                                    <th>Nama Siswa</th>
                                    <th style="width:160px">Nilai Akhir</th>
                                    <th style="width:120px">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($students as $i => $s)
                                    @php
                                        $nilai = $s['nilai'] ?? null;
                                        $status = '-';
                                        if ($nilai !== null && $nilai !== '') {
                                            // contoh logika status (bisa disesuaikan)
                                            $status = (is_numeric($nilai) && $nilai >= 75) ? 'Lulus' : 'Remedial';
                                        } else {
                                            $status = 'Belum Mengerjakan';
                                        }
                                    @endphp
                                    <tr>
                                        <td>{{ $i + 1 }}</td>
                                        <td>{{ $s['name'] ?? ('Siswa ' . ($s['id'] ?? '')) }}</td>
                                        <td>
                                            @if($nilai === null || $nilai === '')
                                                <span class="no-data">-</span>
                                            @else
                                                <span class="text-dark">{{ $nilai }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($status === 'Lulus')
                                                <span class="badge bg-success">{{ $status }}</span>
                                            @elseif($status === 'Remedial')
                                                <span class="badge bg-warning text-dark">{{ $status }}</span>
                                            @else
                                                <span class="badge bg-secondary">{{ $status }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- tombol export sederhana --}}
                    <div class="mt-3">
                        <a href="{{ route('detail.nilai', $activity->id) }}?export=xlsx"
                            class="btn btn-sm btn-outline-success">
                            <i class="fas fa-file-excel me-1"></i> Export Excel (XLSX)
                        </a>

                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@section('scripts')
{{-- jQuery + DataTables (CDN) - jika layout sudah include jQuery, yang ini tidak perlu --}}
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>