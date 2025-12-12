@extends('layouts.main')
@section('dataSoal', request()->is('soal/edit/*') ? 'active' : '')

@section('content')
    <div class="container py-4">
        <div class="card shadow-lg border-0 rounded-4">
            <div class="card-body p-4">

                <h3 class="fw-bold text-primary mb-4">
                    <i class="bi bi-pencil-square me-2"></i> Edit Soal
                </h3>

                <form id="editSoalForm" action="{{ route('updateSoal', $data->id) }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf

                    @php
                        $question = json_decode($data->question);
                        $mcOption = $data->MC_option ? json_decode($data->MC_option, true) : [];
                        $saAnswer = $data->SA_answer ? json_decode($data->SA_answer, true) : [];
                        $labels = ['a', 'b', 'c', 'd', 'e'];
                    @endphp

                    <!-- BARIS 1 -->
                    <div class="row mt-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Tipe Soal</label>
                            <select name="type" class="form-select" id="tipeSoal" disabled>
                                <option value="MultipleChoice" {{ $data->type == 'MultipleChoice' ? 'selected' : '' }}>Pilihan
                                    Ganda</option>
                                <option value="ShortAnswer" {{ $data->type == 'ShortAnswer' ? 'selected' : '' }}>Isian Singkat
                                </option>
                            </select>
                            <small class="text-muted">Jenis soal tidak dapat diubah.</small>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Tingkat Kesulitan</label>
                            <select name="difficulty" class="form-select" id="difficulty" required>
                                {{-- gunakan value sesuai enum pada migrasi: mudah, sedang, sulit --}}
                                <option value="mudah" {{ $data->difficulty == 'mudah' ? 'selected' : '' }}>Mudah</option>
                                <option value="sedang" {{ $data->difficulty == 'sedang' ? 'selected' : '' }}>Sedang</option>
                                <option value="sulit" {{ $data->difficulty == 'sulit' ? 'selected' : '' }}>Sulit</option>
                            </select>
                        </div>
                        {{-- contoh: letakkan tepat di bawah textarea question_text --}}
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Topik (opsional)</label>
                            <select name="id_topic" class="form-select">
                                <option value="">-- Pilih Topik --</option>
                                @foreach($topics as $t)
                                    <option value="{{ $t->id }}" {{ (isset($data->id_topic) && $data->id_topic == $t->id) ? 'selected' : '' }}>
                                        {{ $t->title }}
                                        {{-- optional: tampilkan nama subject / kelas jika Anda mau:
                                        ({{ optional($t->subject)->name ?? '-' }}) --}}
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text">Topik yang tampil hanya untuk mata pelajaran/kls yang Anda ampu.</div>
                        </div>

                    </div>

                    <!-- BARIS 2 -->
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Teks Pertanyaan</label>
                            <textarea name="question_text" id="question_text" class="form-control" rows="4"
                                required>{{ $question->text ?? '' }}</textarea>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Gambar Soal (opsional)</label>
                            <div class="input-group mb-2">
                                <input type="file" name="question_image" class="form-control" accept="image/*"
                                    id="questionImageInput">
                            </div>

                            <input type="text" name="question_url" id="question_url" class="form-control mb-2"
                                value="{{ $question->URL ?? '' }}" placeholder="Atau masukkan URL gambar">

                            @if(!empty($question->URL))
                                <div class="mt-2 text-center">
                                    <img src="{{ $question->URL }}" class="img-fluid rounded shadow-sm"
                                        style="max-height: 200px;">
                                </div>
                            @endif

                            <div id="previewQuestionImage" class="mt-2 text-center"></div>
                        </div>
                    </div>

                    {{-- MULTIPLE CHOICE --}}
                    @if($data->type == 'MultipleChoice')
                        <hr class="mt-4">
                        <h5 class="fw-bold text-secondary mb-3">
                            <i class="bi bi-list-check me-2"></i> Pilihan Jawaban
                        </h5>

                        <div class="row">
                            {{-- A, B, C --}}
                            @foreach($labels as $i => $label)
                                @if($i < 3)
                                    @php
                                        $teks = $mcOption[$i][$label]['teks'] ?? '';
                                        $url = $mcOption[$i][$label]['url'] ?? '';
                                    @endphp

                                    <div class="col-md-4 mb-3">
                                        <div class="card shadow-sm border-0 h-100">
                                            <div class="card-body">
                                                <label class="fw-semibold mb-2">Opsi {{ strtoupper($label) }}</label>
                                                <input type="text" name="option_text[]" class="form-control option-text mb-2"
                                                    value="{{ $teks }}" placeholder="Teks opsi {{ strtoupper($label) }}">
                                                <div class="row g-2">
                                                    <div class="col-6">
                                                        <input type="file" name="option_image[]" class="form-control" accept="image/*">
                                                    </div>
                                                    <div class="col-6">
                                                        <input type="text" name="option_url[]" class="form-control" value="{{ $url }}"
                                                            placeholder="URL gambar (opsional)">
                                                    </div>
                                                </div>

                                                @if(!empty($url))
                                                    <div class="mt-2 text-center">
                                                        <img src="{{ $url }}" class="img-thumbnail shadow-sm" style="max-height: 120px;">
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>

                        <div class="row">
                            {{-- D, E --}}
                            @foreach($labels as $i => $label)
                                @if($i >= 3)
                                    @php
                                        $teks = $mcOption[$i][$label]['teks'] ?? '';
                                        $url = $mcOption[$i][$label]['url'] ?? '';
                                    @endphp

                                    <div class="col-md-4 mb-3">
                                        <div class="card shadow-sm border-0 h-100">
                                            <div class="card-body">
                                                <label class="fw-semibold mb-2">Opsi {{ strtoupper($label) }}</label>
                                                <input type="text" name="option_text[]" class="form-control option-text mb-2"
                                                    value="{{ $teks }}" placeholder="Teks opsi {{ strtoupper($label) }}">
                                                <div class="row g-2">
                                                    <div class="col-6">
                                                        <input type="file" name="option_image[]" class="form-control" accept="image/*">
                                                    </div>
                                                    <div class="col-6">
                                                        <input type="text" name="option_url[]" class="form-control" value="{{ $url }}"
                                                            placeholder="URL gambar (opsional)">
                                                    </div>
                                                </div>

                                                @if(!empty($url))
                                                    <div class="mt-2 text-center">
                                                        <img src="{{ $url }}" class="img-thumbnail shadow-sm" style="max-height: 120px;">
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endforeach

                            {{-- Jawaban Benar --}}
                            <div class="col-md-4 mb-3">
                                <div class="card shadow-sm border-0 h-100">
                                    <div class="card-body">
                                        <label class="form-label fw-semibold">Jawaban Benar</label>
                                        <select name="mc_answer" id="mc_answer" class="form-select">
                                            <option value="">-- Pilih Jawaban --</option>
                                            @foreach(['a', 'b', 'c', 'd', 'e'] as $opt)
                                                <option value="{{ $opt }}" {{ $data->MC_answer == $opt ? 'selected' : '' }}>
                                                    {{ strtoupper($opt) }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- SHORT ANSWER --}}
                    @if($data->type == 'ShortAnswer')
                        <hr>
                        <h5 class="fw-bold text-secondary mb-3">
                            <i class="bi bi-pencil-square me-2"></i> Jawaban Benar
                        </h5>

                        <div id="opsiIsianSingkat">
                            @if(count($saAnswer))
                                @foreach($saAnswer as $ans)
                                    <input type="text" name="sa_answer[]" class="form-control sa-answer mb-2" value="{{ $ans }}"
                                        placeholder="Masukkan jawaban singkat">
                                @endforeach
                            @else
                                <input type="text" name="sa_answer[]" class="form-control sa-answer mb-2"
                                    placeholder="Masukkan jawaban singkat">
                            @endif

                            <button type="button" id="tambahJawaban" class="btn btn-outline-secondary btn-sm">
                                <i class="bi bi-plus-circle"></i> Tambah Jawaban
                            </button>
                        </div>
                    @endif

                    {{-- BUTTONS --}}
                    <div class="text-end mt-4">
                        <button type="submit" id="submitBtn" class="btn btn-success px-4">
                            <i class="bi bi-save me-1"></i> Simpan Perubahan
                        </button>
                        <a href="{{ route('tampilanSoal') }}" class="btn btn-secondary px-4">
                            <i class="bi bi-arrow-left-circle me-1"></i> Kembali
                        </a>
                    </div>

                </form>

            </div>
        </div>
    </div>

    {{-- SweetAlert2 --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        document.addEventListener('DOMContentLoaded', () => {

            // preview gambar upload
            const questionImageInput = document.getElementById('questionImageInput');
            const previewQuestionImage = document.getElementById('previewQuestionImage');

            questionImageInput?.addEventListener('change', function (e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function (event) {
                        previewQuestionImage.innerHTML = `<img src="${event.target.result}" class="img-fluid rounded shadow-sm" style="max-height: 200px;">`;
                    };
                    reader.readAsDataURL(file);
                } else {
                    previewQuestionImage.innerHTML = '';
                }
            });

            // tambah jawaban untuk short answer (jika ada)
            document.getElementById('tambahJawaban')?.addEventListener('click', function () {
                const input = document.createElement('input');
                input.type = 'text';
                input.name = 'sa_answer[]';
                input.classList.add('form-control', 'sa-answer', 'mb-2');
                input.placeholder = 'Masukkan jawaban singkat';
                this.parentElement.insertBefore(input, this);
                input.focus();
            });

            // Tampilkan SweetAlert jika ada session success (redirect setelah close)
            @if(session('success'))
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: {!! json_encode(session('success')) !!},
                    confirmButtonColor: '#3b82f6',
                    allowOutsideClick: false
                });
            @endif

        // VALIDASI CLIENT-SIDE sebelum submit (sama aturan seperti halaman tambah)
        const form = document.getElementById('editSoalForm');
            const submitBtn = document.getElementById('submitBtn');

            form?.addEventListener('submit', function (e) {
                submitBtn.disabled = true;

                function fail(msg, el) {
                    e.preventDefault();
                    submitBtn.disabled = false;
                    Swal.fire({
                        icon: 'warning',
                        title: 'Form Belum Lengkap',
                        text: msg,
                        confirmButtonColor: '#f87171'
                    }).then(() => {
                        if (el) {
                            el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                            el.focus();
                        }
                    });
                }

                const tipe = "{{ $data->type }}"; // tipe soal tetap dari server (field disabled)
                const questionText = (document.getElementById('question_text')?.value || '').trim();

                if (!questionText) {
                    return fail('Teks pertanyaan harus diisi.', document.getElementById('question_text'));
                }

                if (tipe === 'MultipleChoice') {
                    // cek semua option-text
                    const optionInputs = Array.from(document.querySelectorAll('.option-text'));
                    const labels = ['A', 'B', 'C', 'D', 'E'];

                    for (let i = 0; i < optionInputs.length; i++) {
                        if ((optionInputs[i].value || '').trim() === '') {
                            return fail(`Opsi ${labels[i]} belum diisi!`, optionInputs[i]);
                        }
                    }

                    const mcAnswer = (document.getElementById('mc_answer')?.value || '');
                    if (!mcAnswer) {
                        return fail('Silakan pilih jawaban benar untuk soal pilihan ganda.', document.getElementById('mc_answer'));
                    }
                } else if (tipe === 'ShortAnswer') {
                    const saInputs = Array.from(document.querySelectorAll('.sa-answer'));
                    const anyFilled = saInputs.some(i => (i.value || '').trim() !== '');
                    if (!anyFilled) {
                        return fail('Masukkan minimal satu jawaban untuk isian singkat.', saInputs[0] || document.getElementById('question_text'));
                    }
                }

                // semua ok -> biarkan form submit (tombol tetap dinonaktifkan agar tidak dobel)
                submitBtn.innerHTML = 'Menyimpan...';
            });

        });
    </script>

@endsection