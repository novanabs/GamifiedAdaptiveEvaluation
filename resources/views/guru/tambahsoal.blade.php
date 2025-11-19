@extends('layouts.main')
@section('dataSoal', request()->is('soal/tambah') ? 'active' : '')

@section('content')
    <div class="container py-4">
        <div class="card shadow-lg border-0 rounded-4">
            <div class="card-body p-4">
                <h3 class="fw-bold text-primary mb-4">
                    <i class="bi bi-plus-circle me-2"></i> Tambah Soal
                </h3>
                <div class="container">
                    <form action="{{ route('simpanSoal') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <!-- baris 1 -->
                        <div class="row mt-3">
                            <!-- tipe soal -->
                            <div class="col-6">
                                <label class="form-label fw-semibold">Tipe Soal</label>
                                <select name="type" class="form-select" id="tipeSoal" required>
                                    <option value="">-- Pilih Tipe --</option>
                                    <option value="MultipleChoice">Pilihan Ganda</option>
                                    <option value="ShortAnswer">Isian Singkat</option>
                                </select>
                            </div>
                            <!-- tingkat kesulitan -->
                            <div class="col-6">
                                <label class="form-label fw-semibold">Tingkat Kesulitan</label>
                                <select name="difficulty" class="form-select" required>
                                    <option value="">-- Pilih Kesulitan --</option>
                                    <option value="Mudah">Mudah</option>
                                    <option value="Sedang">Sedang</option>
                                    <option value="Sulit">Sulit</option>
                                </select>

                            </div>
                        </div>
                        <!-- baris 2 -->
                        <div class="row mt-3">
                            <div class="col-6">
                                <label class="form-label fw-semibold">Teks Pertanyaan</label>
                                <textarea name="question_text" class="form-control" rows="3"
                                    placeholder="Tulis teks soal di sini..." required></textarea>
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-semibold">Gambar Soal (opsional)</label>
                                <div class="input-group mb-2">
                                    <input type="file" name="question_image" class="form-control" accept="image/*"
                                        id="questionImageInput">
                                </div>
                                <input type="text" name="question_url" class="form-control mb-2"
                                    placeholder="Atau masukkan URL gambar">
                                <div id="previewQuestionImage" class="mt-2 text-center"></div>
                            </div>
                        </div>
                        <!-- baris 3 -->
                        <div class="row mt-3">
                            <!-- 🔹 Pilihan Ganda -->
                            <div id="opsiPilihanGanda" style="display:none;">
                                <hr>
                                <h5 class="fw-bold text-secondary mb-3">
                                    <i class="bi bi-list-check me-2"></i> Pilihan Jawaban
                                </h5>

                                <!-- ========================= -->
                                <!-- 🔵 BARIS 1: Opsi A, B, C -->
                                <!-- ========================= -->
                                <div class="row">
                                    @foreach(['a', 'b', 'c'] as $opt)
                                        <div class="col-md-4 mb-3">
                                            <div class="card shadow-sm border-0 h-100">
                                                <div class="card-body">
                                                    <label class="fw-semibold mb-2">Opsi {{ strtoupper($opt) }}</label>
                                                    <input type="text" name="option_text[]" class="form-control mb-2"
                                                        placeholder="Teks opsi {{ strtoupper($opt) }}">

                                                    <div class="row g-2">
                                                        <div class="col-6">
                                                            <input type="file" name="option_image[]" class="form-control"
                                                                accept="image/*">
                                                        </div>
                                                        <div class="col-6">
                                                            <input type="text" name="option_url[]" class="form-control"
                                                                placeholder="URL gambar (opsional)">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                <!-- ========================= -->
                                <!-- 🔵 BARIS 2: Opsi D, E + Jawaban -->
                                <!-- ========================= -->
                                <div class="row">
                                    @foreach(['d', 'e'] as $opt)
                                        <div class="col-md-4 mb-3">
                                            <div class="card shadow-sm border-0 h-100">
                                                <div class="card-body">
                                                    <label class="fw-semibold mb-2">Opsi {{ strtoupper($opt) }}</label>
                                                    <input type="text" name="option_text[]" class="form-control mb-2"
                                                        placeholder="Teks opsi {{ strtoupper($opt) }}">

                                                    <div class="row g-2">
                                                        <div class="col-6">
                                                            <input type="file" name="option_image[]" class="form-control"
                                                                accept="image/*">
                                                        </div>
                                                        <div class="col-6">
                                                            <input type="text" name="option_url[]" class="form-control"
                                                                placeholder="URL gambar (opsional)">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach

                                    <!-- 🔹 Jawaban Benar -->
                                    <div class="col-md-4 mb-3">
                                        <div class="card shadow-sm border-0 h-100">
                                            <div class="card-body">
                                                <label class="form-label fw-semibold">Jawaban Benar</label>
                                                <select name="mc_answer" class="form-select" required>
                                                    <option value="">-- Pilih Jawaban --</option>
                                                    @foreach(['a', 'b', 'c', 'd', 'e'] as $opt)
                                                        <option value="{{ $opt }}">{{ strtoupper($opt) }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>

                    </form>
                </div>



                <!-- 🔹 Isian Singkat -->
                <div id="opsiIsianSingkat" style="display:none;">
                    <hr>
                    <h5 class="fw-bold text-secondary mb-3">
                        <i class="bi bi-pencil-square me-2"></i> Jawaban Benar
                    </h5>
                    <div id="jawabanContainer">
                        <input type="text" name="sa_answer[]" class="form-control mb-2"
                            placeholder="Masukkan jawaban singkat">
                    </div>
                    <button type="button" id="tambahJawaban" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-plus-circle"></i> Tambah Jawaban
                    </button>
                </div>

                <div class="text-end mt-4">
                    <button type="submit" class="btn btn-success px-4">
                        <i class="bi bi-save me-1"></i> Simpan Soal
                    </button>
                    <a href="{{ route('tampilanSoal') }}" class="btn btn-secondary px-4">
                        <i class="bi bi-arrow-left-circle me-1"></i> Kembali
                    </a>
                </div>

            </div>
        </div>
    </div>

    <!-- 🔹 Script -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const tipeSoal = document.getElementById('tipeSoal');
            const opsiPG = document.getElementById('opsiPilihanGanda');
            const opsiSA = document.getElementById('opsiIsianSingkat');
            const tambahJawaban = document.getElementById('tambahJawaban');
            const jawabanContainer = document.getElementById('jawabanContainer');
            const questionImageInput = document.getElementById('questionImageInput');
            const previewQuestionImage = document.getElementById('previewQuestionImage');

            // 🔸 Toggle tipe soal
            tipeSoal.addEventListener('change', function () {
                opsiPG.style.display = this.value === 'MultipleChoice' ? 'block' : 'none';
                opsiSA.style.display = this.value === 'ShortAnswer' ? 'block' : 'none';
            });

            // 🔸 Tambah field jawaban singkat
            tambahJawaban.addEventListener('click', function () {
                const input = document.createElement('input');
                input.type = 'text';
                input.name = 'sa_answer[]';
                input.classList.add('form-control', 'mb-2');
                input.placeholder = 'Masukkan jawaban singkat';
                jawabanContainer.appendChild(input);
            });

            // 🔸 Preview gambar soal saat upload
            questionImageInput.addEventListener('change', function (e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function (event) {
                        previewQuestionImage.innerHTML = `
                                                                        <img src="${event.target.result}" alt="Preview Gambar Soal" class="img-fluid rounded shadow-sm" style="max-height: 200px;">
                                                                    `;
                    };
                    reader.readAsDataURL(file);
                } else {
                    previewQuestionImage.innerHTML = '';
                }
            });
        });
    </script>
@endsection