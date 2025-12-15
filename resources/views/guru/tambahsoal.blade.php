@extends('layouts.main')
@section('dataSoal', request()->is('soal/tambah') ? 'active' : '')

@section('content')
    <div class="container py-4">
        <div class="card shadow-lg border-0 rounded-4">
            <div class="card-body p-4">
                <div class="d-flex align-items-center gap-2 mb-4">
                    <div class="d-flex align-items-start justify-content-between mb-4 flex-wrap">
                        <div>
                            <div class="d-flex align-items-start justify-content-between mb-4 flex-wrap gap-2">
                                <h3 class="fw-bold text-primary mb-1 d-flex align-items-center gap-2">
                                    <i class="bi bi-plus-circle"></i>
                                    Tambah Soal
                                </h3>
                                <button type="button"
                                    class="btn btn-sm btn-outline-secondary rounded-circle d-flex align-items-center justify-content-center"
                                    style="width:32px;height:32px" data-bs-toggle="modal"
                                    data-bs-target="#modalInfoTambahSoal" title="Informasi Tambah Soal">
                                    <i class="bi bi-info-lg"></i>
                                </button>
                            </div>


                            {{-- Info kelas --}}
                            @if($kelasGuru->count())
                                <div class="d-flex align-items-center flex-wrap gap-2 mt-1">
                                    @foreach($kelasGuru as $k)

                                        <span class="text-muted"> Nama Kelas : {{ $k->name }}</span>

                                    @endforeach
                                </div>
                            @else
                                <div class="text-danger small mt-1">
                                    Anda belum tergabung pada kelas manapun.
                                </div>
                            @endif
                        </div>

                    </div>
                </div>


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
                                @foreach(['a', 'b', 'c', 'd', 'e'] as $i => $opt)
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
    {{-- MODAL INFO TAMBAH SOAL --}}
    <div class="modal fade" id="modalInfoTambahSoal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content rounded-4 shadow">

                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-info-circle me-2"></i>
                        Panduan Menambah Soal
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <p>
                        Halaman <strong>Tambah Soal</strong> digunakan untuk membuat soal baru
                        yang akan disimpan ke bank soal dan dapat digunakan dalam berbagai aktivitas.
                    </p>

                    <hr>

                    <h6 class="fw-bold text-primary">
                        <i class="bi bi-ui-checks me-1"></i>
                        Tipe & Kesulitan Soal
                    </h6>
                    <ul>
                        <li><strong>Tipe Soal</strong> menentukan bentuk soal:
                            <ul>
                                <li><b>Pilihan Ganda</b>: memiliki opsi A–E dan satu jawaban benar</li>
                                <li><b>Isian Singkat</b>: memiliki satu atau lebih jawaban benar</li>
                            </ul>
                        </li>
                        <li><strong>Tingkat Kesulitan</strong> digunakan untuk pengelompokan dan sistem adaptive.</li>
                    </ul>

                    <hr>

                    <h6 class="fw-bold text-secondary">
                        <i class="bi bi-tags me-1"></i>
                        Topik Soal
                    </h6>
                    <ul>
                        <li>Topik bersifat <b>opsional</b>.</li>
                        <li>Topik yang muncul hanya berasal dari mata pelajaran dan kelas yang Anda ajar.</li>
                        <li>Topik memudahkan pengelompokan soal dan pemilihan otomatis.</li>
                    </ul>

                    <hr>

                    <h6 class="fw-bold text-success">
                        <i class="bi bi-question-circle me-1"></i>
                        Teks & Gambar Pertanyaan
                    </h6>
                    <ul>
                        <li>Teks pertanyaan wajib diisi.</li>
                        <li>Gambar soal bersifat opsional dan dapat diisi dengan:
                            <ul>
                                <li>Upload file gambar</li>
                                <li>Atau menggunakan URL gambar</li>
                            </ul>
                        </li>
                    </ul>

                    <hr>

                    <h6 class="fw-bold text-warning">
                        <i class="bi bi-list-check me-1"></i>
                        Pilihan Jawaban (Pilihan Ganda)
                    </h6>
                    <ul>
                        <li>Semua opsi A–E harus diisi.</li>
                        <li>Setiap opsi dapat memiliki:
                            <ul>
                                <li>Teks jawaban</li>
                                <li>Gambar atau URL gambar (opsional)</li>
                            </ul>
                        </li>
                        <li>Jawaban benar wajib dipilih.</li>
                    </ul>

                    <hr>

                    <h6 class="fw-bold text-info">
                        <i class="bi bi-pencil-square me-1"></i>
                        Jawaban Isian Singkat
                    </h6>
                    <ul>
                        <li>Minimal satu jawaban harus diisi.</li>
                        <li>Gunakan tombol <b>Tambah Jawaban</b> untuk menambahkan variasi jawaban benar.</li>
                        <li>Jawaban digunakan untuk mencocokkan input siswa.</li>
                    </ul>

                    <hr>

                    <h6 class="fw-bold text-danger">
                        <i class="bi bi-shield-check me-1"></i>
                        Validasi Form
                    </h6>
                    <ul>
                        <li>Sistem akan memeriksa kelengkapan data sebelum soal disimpan.</li>
                        <li>Jika ada data yang belum valid, proses penyimpanan akan dibatalkan.</li>
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