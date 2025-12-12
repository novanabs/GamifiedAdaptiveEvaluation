@extends('layouts.main')
@section('dataSoal', request()->is('soal/tambah') ? 'active' : '')

@section('content')
    <div class="container py-4">
        <div class="card shadow-lg border-0 rounded-4">
            <div class="card-body p-4">
                <h3 class="fw-bold text-primary mb-4">
                    <i class="bi bi-plus-circle me-2"></i> Tambah Soal
                </h3>

                {{-- FORM START --}}
                <form id="soalForm" action="{{ route('simpanSoal') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <!-- baris 1 -->
                    <div class="row mt-3">
                        <!-- tipe soal -->
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Tipe Soal</label>
                            <select name="type" class="form-select" id="tipeSoal" required>
                                <option value="">-- Pilih Tipe --</option>
                                <option value="MultipleChoice">Pilihan Ganda</option>
                                <option value="ShortAnswer">Isian Singkat</option>
                            </select>
                        </div>

                        <!-- tingkat kesulitan -->
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Tingkat Kesulitan</label>
                            <select name="difficulty" class="form-select" id="difficulty" required>
                                <option value="">-- Pilih Kesulitan --</option>
                                <option value="mudah">Mudah</option>
                                <option value="sedang">Sedang</option>
                                <option value="sulit">Sulit</option>
                            </select>
                        </div>

                        <!-- pilih topik -->
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Topik (opsional)</label>
                            <select name="id_topic" class="form-select" id="id_topic">
                                <option value="">-- Pilih Topik --</option>
                                @if(isset($topics) && $topics->count())
                                    @foreach($topics as $t)
                                        <option value="{{ $t->id }}">{{ $t->title }}</option>
                                    @endforeach
                                @endif
                            </select>
                            <div class="form-text">Topik muncul berdasarkan mata pelajaran/kls yang Anda ajar.</div>
                        </div>
                    </div>

                    <!-- baris 2 -->
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Teks Pertanyaan</label>
                            <textarea name="question_text" id="question_text" class="form-control" rows="4"
                                placeholder="Tulis teks soal di sini..." required></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Gambar Soal (opsional)</label>
                            <div class="input-group mb-2">
                                <input type="file" name="question_image" class="form-control" accept="image/*"
                                    id="questionImageInput">
                            </div>
                            <input type="text" name="question_url" id="question_url" class="form-control mb-2"
                                placeholder="Atau masukkan URL gambar">
                            <div id="previewQuestionImage" class="mt-2 text-center"></div>
                        </div>
                    </div>

                    <!-- baris 3 (Pilihan Ganda area) -->
                    <div class="row mt-3">
                        <div id="opsiPilihanGanda" style="display:none; width:100%;">
                            <hr>
                            <h5 class="fw-bold text-secondary mb-3">
                                <i class="bi bi-list-check me-2"></i> Pilihan Jawaban
                            </h5>

                            <div class="row">
                                @foreach(['a','b','c','d','e'] as $i => $opt)
                                    <div class="col-md-4 mb-3">
                                        <div class="card shadow-sm border-0 h-100">
                                            <div class="card-body">
                                                <label class="fw-semibold mb-2">Opsi {{ strtoupper($opt) }}</label>
                                                <input type="text" name="option_text[]" class="form-control option-text mb-2"
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

                                <!-- Jawaban Benar -->
                                <div class="col-md-4 mb-3">
                                    <div class="card shadow-sm border-0 h-100">
                                        <div class="card-body">
                                            <label class="form-label fw-semibold">Jawaban Benar</label>
                                            <select name="mc_answer" id="mc_answer" class="form-select">
                                                <option value="">-- Pilih Jawaban --</option>
                                                @foreach(['a','b','c','d','e'] as $opt)
                                                    <option value="{{ $opt }}">{{ strtoupper($opt) }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Isian Singkat -->
                    <div id="opsiIsianSingkat" style="display:none; margin-top:1rem;">
                        <hr>
                        <h5 class="fw-bold text-secondary mb-3">
                            <i class="bi bi-pencil-square me-2"></i> Jawaban Benar (Isian Singkat)
                        </h5>
                        <div id="jawabanContainer">
                            <input type="text" name="sa_answer[]" class="form-control sa-answer mb-2"
                                placeholder="Masukkan jawaban singkat">
                        </div>
                        <button type="button" id="tambahJawaban" class="btn btn-outline-secondary btn-sm mt-2">
                            <i class="bi bi-plus-circle"></i> Tambah Jawaban
                        </button>
                    </div>

                    <div class="text-end mt-4">
                        <button type="submit" id="submitBtn" class="btn btn-success px-4">
                            <i class="bi bi-save me-1"></i> Simpan Soal
                        </button>
                        <a href="{{ route('tampilanSoal') }}" class="btn btn-secondary px-4">
                            <i class="bi bi-arrow-left-circle me-1"></i> Kembali
                        </a>
                    </div>
                </form>
                {{-- FORM END --}}

            </div>
        </div>
    </div>

    {{-- SweetAlert2 --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Scripts -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const tipeSoal = document.getElementById('tipeSoal');
            const opsiPG = document.getElementById('opsiPilihanGanda');
            const opsiSA = document.getElementById('opsiIsianSingkat');
            const tambahJawaban = document.getElementById('tambahJawaban');
            const jawabanContainer = document.getElementById('jawabanContainer');
            const questionImageInput = document.getElementById('questionImageInput');
            const previewQuestionImage = document.getElementById('previewQuestionImage');
            const form = document.getElementById('soalForm');
            const submitBtn = document.getElementById('submitBtn');

            // toggle tampil area sesuai tipe soal
            tipeSoal.addEventListener('change', function () {
                opsiPG.style.display = this.value === 'MultipleChoice' ? 'block' : 'none';
                opsiSA.style.display = this.value === 'ShortAnswer' ? 'block' : 'none';
            });

            // tambah field jawaban singkat
            tambahJawaban.addEventListener('click', function () {
                const input = document.createElement('input');
                input.type = 'text';
                input.name = 'sa_answer[]';
                input.classList.add('form-control', 'sa-answer', 'mb-2');
                input.placeholder = 'Masukkan jawaban singkat';
                jawabanContainer.appendChild(input);
                input.focus();
            });

            // preview gambar soal
            questionImageInput?.addEventListener('change', function (e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function (event) {
                        previewQuestionImage.innerHTML = `<img src="${event.target.result}" alt="Preview Gambar Soal" class="img-fluid rounded shadow-sm" style="max-height: 200px;">`;
                    };
                    reader.readAsDataURL(file);
                } else {
                    previewQuestionImage.innerHTML = '';
                }
            });

            // tampilkan SweetAlert jika server mengirim flash 'success'
            @if(session('success'))
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: {!! json_encode(session('success')) !!},
                    confirmButtonColor: '#3b82f6',
                    allowOutsideClick: false
                }).then(() => {
                    window.location.href = '{{ route('tampilanSoal') }}';
                });
            @endif

            // VALIDASI SEBELUM SUBMIT
            form.addEventListener('submit', function (e) {
                // disable tombol submit sementara
                submitBtn.disabled = true;

                // helper untuk balikke tombol dan fokus
                function fail(msg, focusEl) {
                    e.preventDefault();
                    submitBtn.disabled = false;
                    Swal.fire({
                        icon: 'warning',
                        title: 'Form Belum Lengkap',
                        text: msg,
                        confirmButtonColor: '#f87171'
                    }).then(() => {
                        if (focusEl) {
                            focusEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
                            focusEl.focus();
                        }
                    });
                }

                const tipe = tipeSoal.value;
                const questionText = document.getElementById('question_text').value.trim();

                if (!tipe) {
                    return fail('Pilih tipe soal terlebih dahulu.', tipeSoal);
                }

                if (!questionText) {
                    return fail('Teks pertanyaan harus diisi.', document.getElementById('question_text'));
                }

                if (tipe === 'MultipleChoice') {
                    // semua option_text[] harus ada
                    const optionInputs = Array.from(document.querySelectorAll('.option-text'));
                    const labels = ['A', 'B', 'C', 'D', 'E'];

                    for (let i = 0; i < optionInputs.length; i++) {
                        if ((optionInputs[i].value || '').trim() === '') {
                            return fail(`Opsi ${labels[i]} belum diisi!`, optionInputs[i]);
                        }
                    }

                    const mcAnswer = document.getElementById('mc_answer').value;
                    if (!mcAnswer) {
                        return fail('Silakan pilih jawaban benar untuk soal pilihan ganda.', document.getElementById('mc_answer'));
                    }
                } else if (tipe === 'ShortAnswer') {
                    // setidaknya satu jawaban singkat tidak boleh kosong
                    const saInputs = Array.from(document.querySelectorAll('.sa-answer'));
                    const anyFilled = saInputs.some(i => (i.value || '').trim() !== '');
                    if (!anyFilled) {
                        // fokus ke pertama
                        return fail('Masukkan minimal satu jawaban untuk isian singkat.', saInputs[0] || document.getElementById('question_text'));
                    }
                }

                // semua ok -> biarkan submit berlangsung (tombol tetap dinonaktifkan sementara)
                submitBtn.innerHTML = 'Menyimpan...';
                // jangan panggil preventDefault(); form akan submit ke server
            });

        });
    </script>
@endsection
