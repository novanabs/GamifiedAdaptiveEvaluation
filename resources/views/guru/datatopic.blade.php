@extends('layouts.main')
@section('dataTopic', request()->is('datatopik') ? 'active' : '')
@section('content')
    <div class="container mt-4">
        <h3 class="fw-bold mb-4">Data Topik Berdasarkan Mata Pelajaran</h3>

        {{-- Pesan sukses --}}
        @if(session('success'))
            <div class="alert alert-success text-center">{{ session('success') }}</div>
        @endif

        {{-- Form tambah topik --}}
        <div class="card shadow-sm mb-4 border-0">
            <div class="card-header bg-primary text-white fw-semibold">Tambah Topik Baru</div>

            <div class="card-body px-4 py-4">
                <form action="{{ route('guru.topik.simpan') }}" method="POST" class="row g-3">
                    @csrf
                    <div class="col-md-6">
                        <label for="title" class="form-label fw-semibold">Judul Topik</label>
                        <input type="text" id="title" name="title" class="form-control" placeholder="Masukkan judul topik..." required>
                    </div>

                    <div class="col-md-4">
                        <label for="subject" class="form-label fw-semibold">Mata Pelajaran</label>
                        <select name="id_subject" id="subject" class="form-select" required>
                            <option value="">Pilih Mata Pelajaran</option>
                            @foreach($data as $subject)
                                <option value="{{ $subject->id }}">
                                    {{ $subject->name }}
                                    ({{ $subject->classes ? $subject->classes->name : 'Tidak ada kelas' }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-success px-4 shadow-sm">
                            <i class="bi bi-save me-1"></i> Simpan Topik
                        </button>
                    </div>

                    <div class="col-12">
                        <label for="description" class="form-label fw-semibold">Deskripsi</label>
                        <textarea id="description" name="description" rows="3" class="form-control" placeholder="Masukkan deskripsi singkat mengenai topik ini..."></textarea>
                    </div>
                </form>
            </div>
        </div>

        {{-- Tabel DataTables gabungan topik --}}
        <div class="card mb-4 border-0 shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="topicsTable" class="table table-striped table-bordered nowrap align-middle" style="width:100%">
                        <thead class="table-secondary">
                            <tr>
                                <th style="width:60px">No</th>
                                <th>Mata Pelajaran</th>
                                <th>Kelas</th>
                                <th>Judul Topik</th>
                                <th>Deskripsi</th>
                                <th style="width:220px">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $no = 1; @endphp
                            @foreach($data as $subject)
                                @foreach($subject->topics as $topic)
                                    <tr>
                                        <td class="text-center align-middle">{{ $no++ }}</td>
                                        <td class="align-middle">{{ $subject->name }}</td>
                                        <td class="align-middle">{{ $subject->classes ? $subject->classes->name : '-' }}</td>

                                        {{-- FORM dimulai di sini (membungkus Judul + Deskripsi + tombol Perbarui) --}}
                                        <form action="{{ route('guru.topik.ubah', $topic->id) }}" method="POST" class="row-edit-form">
                                            @csrf

                                            <td class="align-middle topic-title-cell">
                                                <input type="text"
                                                       name="title"
                                                       value="{{ $topic->title }}"
                                                       class="form-control form-control-sm topic-edit-title"
                                                       required>

                                                {{-- hidden span indexed oleh DataTables untuk pencarian --}}
                                                <span class="dt-search d-none">{{ $topic->title }}</span>
                                            </td>

                                            <td class="align-middle topic-desc-cell">
                                                <input type="text"
                                                       name="description"
                                                       value="{{ $topic->description }}"
                                                       class="form-control form-control-sm topic-edit-desc"
                                                       placeholder="Deskripsi singkat">

                                                <span class="dt-search d-none">{{ $topic->description }}</span>
                                            </td>

                                            <td class="align-middle text-center">
                                                <div class="d-flex gap-2 justify-content-center align-items-center">
                                                    <button class="btn btn-success btn-sm" type="submit">Perbarui</button>
                                                    </form>

                                                    {{-- Form hapus terpisah --}}
                                                    <form action="{{ route('guru.topik.hapus', $topic->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus topik ini?')" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button class="btn btn-danger btn-sm">Hapus</button>
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

    </div>
@endsection

@push('styles')
    {{-- DataTables CSS --}}
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">

    <style>
        /* column width rules */
        #topicsTable th:nth-child(1), #topicsTable td:nth-child(1) {
            width: 64px !important;
            text-align: center;
            white-space: nowrap;
        }
        #topicsTable th:nth-child(2), #topicsTable td:nth-child(2) {
            width: 180px !important;
            white-space: nowrap;
        }
        #topicsTable th:nth-child(3), #topicsTable td:nth-child(3) {
            width: 160px !important;
            white-space: nowrap;
        }
        #topicsTable th:nth-child(4), #topicsTable td:nth-child(4) {
            min-width: 220px;
            max-width: 520px;
            overflow: hidden;
        }
        #topicsTable th:nth-child(5), #topicsTable td:nth-child(5) {
            min-width: 220px;
            max-width: 520px;
            overflow: hidden;
        }
        #topicsTable th:nth-child(6), #topicsTable td:nth-child(6) {
            width: 220px !important;
            white-space: nowrap;
        }

        /* input di kolom judul/deskripsi dibuat lega agar tidak mepet */
        .topic-edit-title, .topic-edit-desc {
            width: 100%;
            min-width: 160px;
            padding: .45rem .6rem;
            font-size: .95rem;
            border-radius: .35rem;
        }

        /* tombol lebih seragam */
        #topicsTable td .btn {
            padding: .32rem .6rem;
            font-size: .88rem;
        }

        /* responsive tweaks */
        @media (max-width: 900px) {
            #topicsTable th:nth-child(2), #topicsTable td:nth-child(2) { width: 140px !important; }
            #topicsTable th:nth-child(4), #topicsTable td:nth-child(4) { min-width: 160px; }
            #topicsTable th:nth-child(5), #topicsTable td:nth-child(5) { min-width: 160px; }
        }
        @media (max-width: 520px) {
            .topic-edit-title, .topic-edit-desc { width: 100%; max-width: none; }
            /* tombol full width agar mudah ditekan */
            #topicsTable td .btn { width: 100%; }
            /* pastikan sel tetap rapih */
            #topicsTable td { vertical-align: top; }
        }
    </style>
@endpush

@push('scripts')
    {{-- jQuery (DataTables dependency) --}}
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    {{-- DataTables --}}
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>

    <script>
        // simple debounce utility
        function debounce(fn, wait) {
            let t;
            return function(...args) {
                clearTimeout(t);
                t = setTimeout(() => fn.apply(this, args), wait);
            };
        }

        document.addEventListener('DOMContentLoaded', function () {
            const table = $('#topicsTable').DataTable({
                responsive: true,
                autoWidth: false,
                lengthChange: true,
                pageLength: 10,
                columnDefs: [
                    { orderable: false, targets: [5] }, // kolom Aksi non-sortable
                    { searchable: false, targets: 0 }   // No tidak ikut search
                ],
                order: [[1, 'asc']],
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Cari topik, subject, atau kelas...",
                    lengthMenu: "Tampilkan _MENU_ entri",
                    paginate: { previous: "Sebelumnya", next: "Selanjutnya" }
                }
            });

            // update hidden dt-search when user edits inputs (debounced)
            const updateAndDraw = debounce(function($td, value) {
                $td.find('.dt-search').text(value);
                // redraw without resetting pagination
                table.draw(false);
            }, 300);

            // delegated event binding for title and description inputs
            $('#topicsTable').on('input', '.topic-edit-title', function () {
                const $td = $(this).closest('td');
                updateAndDraw($td, this.value);
            });

            $('#topicsTable').on('input', '.topic-edit-desc', function () {
                const $td = $(this).closest('td');
                updateAndDraw($td, this.value);
            });


        });
    </script>
@endpush
