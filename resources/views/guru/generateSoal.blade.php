@extends('layouts.main')
@section('dataSoal', request()->is('generate-soal') ? 'active' : '')
@section('content')
    <div class="container py-4">
        <div class="d-flex align-items-center gap-2 mb-3">
            <h3 class="fw-bold mb-0">Generator Soal Otomatis</h3>

            <button type="button"
                class="btn btn-sm btn-outline-secondary rounded-circle d-flex align-items-center justify-content-center"
                style="width:32px;height:32px" data-bs-toggle="modal" data-bs-target="#modalInfoGenerateSoal"
                title="Informasi Generator Soal">
                <i class="bi bi-info-lg"></i>
            </button>
        </div>


        <div class="card shadow-sm border-0 rounded-4">
            <div class="card-body">
                <form method="POST" action="{{ route('generateSoal.post') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Topik Soal</label>
                        <select name="topic" class="form-select" required>
                            <option value="">-- Pilih Topik --</option>

                            @foreach($topics as $t)
                                <option value="{{ $t->id }}" {{ isset($selectedTopic) && $selectedTopic == $t->id ? 'selected' : '' }}>{{ $t->title }}
                                </option>
                            @endforeach
                        </select>

                    </div>


                    <div class="mb-3">
                        <label class="form-label fw-semibold">Jenjang</label>
                        <select name="jenjang" class="form-select" required>
                            <option value="">-- Pilih Jenjang --</option>

                            @foreach(['SD', 'MI', 'SMP', 'MTS', 'SMA', 'SMK', 'MA', 'PT'] as $j)
                                <option value="{{ $j }}" {{ isset($selectedJenjang) && $selectedJenjang == $j ? 'selected' : '' }}>{{ $j }}
                                </option>
                            @endforeach
                        </select>

                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Jumlah Soal</label>
                        <input type="number" name="jumlah" class="form-control" min="3" max="30"
                            value="{{ $jumlahInput ?? 9 }}" required>

                    </div>

                    <button class="btn btn-success w-100 py-2 fw-bold">
                        <i class="bi bi-rocket-takeoff"></i>
                        Buat Prompt AI
                    </button>
                </form>
            </div>
        </div>

        @isset($prompt)
            <div class="mt-4">
                <h5 class="fw-bold mb-2 text-primary">Prompt AI yang Dihasilkan:</h5>
                <textarea class="form-control bg-light p-3" rows="12" readonly>{{ $prompt }}</textarea>
                <div class="text-end mt-2">
                    <button class="btn btn-outline-primary" onclick="navigator.clipboard.writeText(`{{ trim($prompt) }}`)">
                        Salin Prompt
                    </button>
                </div>
            </div>
        @endisset

        <hr class="my-4">

        <h5 class="fw-bold text-primary">Import Soal dari JSON</h5>
        <p class="text-muted mb-2">Pilih metode input:</p>

        <!-- ========== NAV TABS ========== -->
        <ul class="nav nav-tabs" id="importTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active fw-bold" id="paste-tab" data-bs-toggle="tab" data-bs-target="#paste-json"
                    type="button" role="tab">
                    <i class="bi bi-clipboard"> Tempel Kode JSON</i>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-bold" id="upload-tab" data-bs-toggle="tab" data-bs-target="#upload-json"
                    type="button" role="tab">
                    <i class="bi bi-upload"> Upload File JSON</i>
                </button>
            </li>
        </ul>

        <!-- ========== TAB CONTENT ========== -->
        <div class="tab-content mt-3">

            <!-- TAB 1: PASTE JSON -->
            <div class="tab-pane show active" id="paste-json" role="tabpanel">
                <div class="card">
                    <div class="card-body">

                        <h6 class="fw-bold mb-2">Tempel JSON di bawah ini:</h6>

                        <form action="{{ route('importQuestionJson') }}" method="POST">
                            @csrf

                            <textarea name="json_text" class="form-control" rows="10"
                                placeholder='Tempel JSON di sini...'></textarea>

                            <input type="hidden" name="upload_mode" value="paste">

                            <button type="submit" class="btn btn-primary w-100 mt-3 fw-bold">
                                <i class="bi bi-cloud-upload"></i> Simpan JSON ke Database
                            </button>
                        </form>

                    </div>
                </div>
            </div>

            <!-- TAB 2: UPLOAD FILE -->
            <div class="tab-pane fade" id="upload-json" role="tabpanel">
                <div class="card">
                    <div class="card-body">

                        <h6 class="fw-bold mb-2">Upload File JSON</h6>

                        <form action="{{ route('importQuestionJson') }}" method="POST" enctype="multipart/form-data">
                            @csrf

                            <div class="input-group">
                                <input type="file" name="file" accept=".json,.txt" class="form-control" required>
                                <button type="submit" class="btn btn-success fw-bold">
                                    <i class="bi bi-upload"></i> Simpan ke Database
                                </button>
                            </div>

                            <input type="hidden" name="upload_mode" value="file">
                        </form>

                    </div>
                </div>
            </div>

        </div>

        @if(session('success'))
            <div class="alert alert-success mt-3">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger mt-3">{{ session('error') }}</div>
        @endif

        <div class="text-end mt-4">
            <a href="{{ route('tampilanSoal') }}" class="btn btn-secondary px-4">
                <i class="bi bi-arrow-left-circle me-1"></i> Kembali
            </a>
        </div>

    </div>
    {{-- MODAL INFO GENERATOR SOAL --}}
    <div class="modal fade" id="modalInfoGenerateSoal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content rounded-4 shadow">

                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-info-circle me-2"></i>
                        Panduan Generator Soal Otomatis
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <p>
                        Halaman <strong>Generator Soal Otomatis</strong> digunakan untuk membantu guru
                        menghasilkan soal secara cepat menggunakan bantuan AI dan mengimpor soal dalam format JSON.
                    </p>

                    <hr>

                    <h6 class="fw-bold text-primary">
                        <i class="bi bi-lightbulb me-1"></i>
                        Generator Prompt AI
                    </h6>
                    <ul>
                        <li>Pilih <strong>Topik Soal</strong> yang ingin dibuatkan pertanyaan.</li>
                        <li>Tentukan <strong>Jenjang Pendidikan</strong> agar tingkat bahasa dan kompleksitas sesuai.</li>
                        <li>Masukkan <strong>Jumlah Soal</strong> yang diinginkan.</li>
                        <li>Sistem akan menghasilkan <b>prompt AI</b> yang siap digunakan.</li>
                    </ul>

                    <hr>

                    <h6 class="fw-bold text-success">
                        <i class="bi bi-robot me-1"></i>
                        Prompt AI
                    </h6>
                    <ul>
                        <li>Prompt yang dihasilkan dapat langsung disalin.</li>
                        <li>Gunakan prompt tersebut pada AI (misalnya ChatGPT) untuk menghasilkan soal.</li>
                        <li>Hasil AI diharapkan berbentuk <b>JSON</b> sesuai struktur sistem.</li>
                    </ul>

                    <hr>

                    <h6 class="fw-bold text-warning">
                        <i class="bi bi-braces me-1"></i>
                        Import Soal dari JSON
                    </h6>
                    <ul>
                        <li>Soal hasil AI dapat dimasukkan ke sistem menggunakan format JSON.</li>
                        <li>Tersedia dua metode impor:
                            <ul>
                                <li><b>Tempel JSON</b> langsung ke textarea</li>
                                <li><b>Upload file JSON</b> dari perangkat</li>
                            </ul>
                        </li>
                        <li>JSON yang valid akan otomatis disimpan ke bank soal.</li>
                    </ul>

                    <hr>

                    <h6 class="fw-bold text-info">
                        <i class="bi bi-shield-check me-1"></i>
                        Validasi & Keamanan Data
                    </h6>
                    <ul>
                        <li>Sistem akan memeriksa struktur JSON sebelum menyimpan.</li>
                        <li>Jika format tidak sesuai, proses impor akan dibatalkan.</li>
                        <li>Soal yang berhasil diimpor dapat langsung digunakan dalam aktivitas.</li>
                    </ul>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Tutup
                    </button>
                </div>

            </div>
        </div>
    </div>

@endsection