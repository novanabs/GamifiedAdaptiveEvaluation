@extends('layouts.main')

@section('dataSubject', request()->is('datamatapelajaran') ? 'active' : '')
@section('content')
    <div class="container mt-4">
        <h3 class="fw-bold mb-3">Data Mata Pelajaran Berdasarkan Kelas</h3>

        {{-- Pesan sukses --}}
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        {{-- Form tambah subject --}}
        <div class="card mb-4">
            <div class="card-header bg-primary text-white fw-semibold">Tambah Mata Pelajaran</div>
            <div class="card-body">
                <form action="{{ route('guru.subject.tambah') }}" method="POST" class="row g-2">
                    @csrf
                    <div class="col-md-5">
                        <input type="text" name="name" class="form-control" placeholder="Nama Mata Pelajaran" required>
                    </div>
                    <div class="col-md-4">
                        <select name="id_class" class="form-control" required>
                            <option value="">Pilih Kelas</option>
                            @foreach($data as $item)
                                <option value="{{ $item->kelas->id }}">{{ $item->kelas->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary w-100">Tambah</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Tabel DataTables --}}
        <div class="card">
            <div class="card-body">
                <table id="subjectsTable" class="table table-striped table-bordered nowrap" style="width:100%">
                    <thead>
                        <tr>
                            <th style="width:12px !important">No</th>
                            <th style="width:50px !important">Kelas</th>
                            <th style="width:150px !important">Mata Pelajaran</th>
                            <th style="width:180px !important">Aksi</th>

                        </tr>
                    </thead>
                    <tbody>
                        @php $no = 1; @endphp
                        @foreach($data as $item)
                            @foreach($item->subjects as $subject)
                                <tr>
                                    <td class="align-middle text-center">{{ $no++ }}</td>
                                    <td class="align-middle">{{ $item->kelas->name }}</td>
                                    <td class="align-middle">
                                        {{-- Nama subject (tetap teks) --}}
                                        <span class="subject-name-{{ $subject->id }}">{{ $subject->name }}</span>
                                    </td>
                                    <td class="align-middle">
                                        <div class="d-flex gap-2">
                                            {{-- Form update: memakai input kecil + submit --}}
                                            <form action="{{ route('guru.subject.update', $subject->id) }}" method="POST"
                                                class="d-flex ">
                                                @csrf
                                                <input type="text" name="name" value="{{ $subject->name }}" 
                                                    class="form-control form-control-sm" required>
                                                <button type="submit" class="btn btn-success btn-sm ms-2">Update</button>
                                            </form>

                                            {{-- Form hapus --}}
                                            <form action="{{ route('guru.subject.hapus', $subject->id) }}" method="POST"
                                                onsubmit="return confirm('Yakin hapus subject ini?')" class="d-inline">
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
@endsection

@push('styles')
    {{-- DataTables CSS (Bootstrap 5 + Responsive) --}}
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
    <style>
        /* Supaya input update kecil rapi */
        table#subjectsTable .form-control-sm {
            padding: .25rem .5rem;
            font-size: .85rem;
        }
    </style>
@endpush

@push('scripts')
    {{-- jQuery (DataTables dependency) --}}
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    {{-- DataTables JS --}}
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Inisialisasi DataTable
            $('#subjectsTable').DataTable({
                responsive: true,
                lengthChange: true,
                pageLength: 10,
                columnDefs: [
                    { orderable: false, targets: [2, 3] }, // non-sortable columns
                    { searchable: false, targets: 0 }     // nomor tidak ikut search
                ],
                order: [[1, 'asc']], // urut berdasarkan Kelas
                // language optional: kamu bisa sesuaikan teks ke Bahasa Indonesia
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Cari mata pelajaran atau kelas...",
                    lengthMenu: "Tampilkan _MENU_ entri",
                    paginate: {
                        previous: "Sebelumnya",
                        next: "Selanjutnya"
                    }
                }
            });

            // Tooltip bootstrap (jika ada)
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            tooltipTriggerList.map(function (el) {
                return new bootstrap.Tooltip(el)
            })
        });
    </script>
@endpush