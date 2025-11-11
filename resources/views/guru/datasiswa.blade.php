@extends('layouts.main')
@section('dataSiswa')
    @if(request()->is('*dataSiswa*')) active @endif
@endsection
@section('content')
    <div class="container py-4">
        <h2 class="fw-bold mb-3">Data Siswa per Kelas</h2>

        <!-- Filter berdasarkan token -->
        <form action="{{ route('dataSiswa') }}" method="GET" class="mb-4">
            <div class="row g-2 align-items-center">
                <div class="col-auto">
                    <label for="token" class="col-form-label">Pilih Kelas:</label>
                </div>
                <div class="col-md-4">
                    <select name="token" id="token" class="form-select" onchange="this.form.submit()">
                        <option value="">-- Pilih Token Kelas --</option>
                        @foreach($kelas as $k)
                            <option value="{{ $k->token }}" {{ $token == $k->token ? 'selected' : '' }}>
                                {{ $k->name }} ({{ $k->token }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </form>

        <!-- Jika kelas dipilih -->
        @if($kelasTerpilih)
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h5>Kelas: {{ $kelasTerpilih->name }}</h5>
                    <p class="text-muted">Token: {{ $kelasTerpilih->token }}</p>
                </div>
                <a href="{{ route('dataSiswa.export', ['token' => $kelasTerpilih->token]) }}" class="btn btn-success">
                    Export Excel
                </a>
            </div>

            @if($siswa->isEmpty())
                <div class="alert alert-warning">Belum ada siswa di kelas ini.</div>
            @else
                <table id="tabelSiswa" class="table table-bordered table-striped">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Nama Siswa</th>
                            <th>Email</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($siswa as $index => $s)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $s->name }}</td>
                                <td>{{ $s->email }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        @else
            <div class="alert alert-info">Silakan pilih kelas untuk melihat daftar siswa.</div>
        @endif
    </div>

    <!-- DataTables CDN -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/datatables.net@1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/datatables.net-bs5@1.13.6/js/dataTables.bootstrap5.min.js"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            let table = document.getElementById("tabelSiswa");
            if (table) {
                new DataTable('#tabelSiswa', {
                    responsive: true,
                    language: {
                        search: "Cari:",
                        lengthMenu: "Tampilkan _MENU_ data",
                        info: "Menampilkan _START_ - _END_ dari _TOTAL_ siswa",
                        zeroRecords: "Tidak ditemukan data yang cocok"
                    }
                });
            }
        });
    </script>
@endsection