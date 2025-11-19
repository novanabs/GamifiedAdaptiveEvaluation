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
            <form action="{{ route('guru.topik.simpan') }}" method="POST">
                @csrf
                <div class="row mb-3">
                    <div class="col-md-6">
                        {{-- Judul Topik --}}
                        <label for="title" class="form-label fw-semibold">Judul Topik</label>
                        <input type="text" id="title" name="title" class="form-control "
                            placeholder="Masukkan judul topik..." required>
                    </div>
                    {{-- Mata Pelajaran --}}
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
                        {{-- Tombol Simpan --}}
                        <button type="submit" class="btn btn-success px-4 shadow-sm">
                            <i class="bi bi-save me-1"></i> Simpan Topik
                        </button>
                    </div>
                </div>


                {{-- Deskripsi --}}
                <div class="mb-4">
                    <label for="description" class="form-label fw-semibold">Deskripsi</label>
                    <textarea id="description" name="description" rows="4" class="form-control "
                        placeholder="Masukkan deskripsi singkat mengenai topik ini..."></textarea>
                </div>
            </form>
        </div>
    </div>


    {{-- Daftar topik per subject --}}
    @foreach($data as $subject)
        <div class="card mb-4 border-0 shadow-sm">
            <div class="card-header bg-secondary text-white fw-semibold">
                {{ $subject->name }}
                <small class="text-white">
                    ({{ $subject->classes ? $subject->classes->name : 'Tidak ada kelas' }})
                </small>
            </div>
            <div class="card-body">
                @if($subject->topics->isEmpty())
                    <p class="text-muted fst-italic mb-0">Belum ada topik untuk subject ini.</p>
                @else
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle">
                            <thead class="table-secondary">
                                <tr class="text-center">
                                    <th style="width: 5%;">No</th>
                                    <th style="width: 25%;">Judul Topik</th>
                                    <th style="width: 40%;">Deskripsi</th>
                                    <th style="width: 30%;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($subject->topics as $index => $topik)
                                    <tr>
                                        <td class="text-center">{{ $loop->iteration }}</td>
                                        <td>
                                            <form action="{{ route('guru.topik.ubah', $topik->id) }}" method="POST"
                                                class="d-flex flex-column flex-md-row align-items-md-center gap-2">
                                                @csrf
                                                <input type="text" name="title" value="{{ $topik->title }}" class="form-control">
                                        </td>
                                        <td>
                                            <input type="text" name="description" value="{{ $topik->description }}"
                                                class="form-control">
                                        </td>
                                        <td class="text-center">
                                            <button class="btn btn-sm btn-success me-2">Perbarui</button>
                                            </form>

                                            <form action="{{ route('guru.topik.hapus', $topik->id) }}" method="POST"
                                                onsubmit="return confirm('Yakin ingin menghapus topik ini?')" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-sm btn-danger">Hapus</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    @endforeach
    </div>
@endsection