@extends('layouts.main')

@section('dataSubject', request()->is('datamatapelajaran') ? 'active' : '')
@section('content')
<div class="container mt-4">
    <h3 class="fw-bold mb-3">Data Mata Pelajaran Berdasarkan Kelas</h3>

    {{-- Pesan sukses --}}
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    {{-- Form tambah subject --}}
    <div class="card mb-4">
        <div class="card-header bg-primary text-white fw-semibold">Tambah Mata Pelajaran</div>
        <div class="card-body">
            <form action="{{ route('guru.subject.tambah') }}" method="POST">
                @csrf
                <div class="row">
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
                </div>
            </form>
        </div>
    </div>

    {{-- Daftar subject per kelas --}}
    @foreach($data as $item)
        <div class="card mb-3">
            <div class="card-header bg-secondary text-white fw-semibold">Kelas: {{ $item->kelas->name }}</div>
            <div class="card-body">
                @if($item->subjects->isEmpty())
                    <p class="text-muted">Belum ada subject.</p>
                @else
                    <ul class="list-group">
                        @foreach($item->subjects as $subject)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                {{-- Form edit --}}
                                <form action="{{ route('guru.subject.update', $subject->id) }}" method="POST" class="d-flex w-100">
                                    @csrf
                                    <input type="text" name="name" class="form-control me-2" value="{{ $subject->name }}">
                                    <button type="submit" class="btn btn-success btn-sm me-2">update</button>
                                </form>

                                {{-- Form hapus --}}
                                <form action="{{ route('guru.subject.hapus', $subject->id) }}" method="POST" onsubmit="return confirm('Yakin hapus subject ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-danger btn-sm">Hapus</button>
                                </form>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    @endforeach
</div>
@endsection
