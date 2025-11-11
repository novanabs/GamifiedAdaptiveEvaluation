@extends('layouts.main')

@section('content')
<div class="container py-4">
    <div class="card shadow-lg border-0 rounded-4">
        <div class="card-body p-4">
            <h3 class="fw-bold text-primary mb-4">
                <i class="bi bi-pencil-square me-2"></i> Edit Soal
            </h3>

            <form action="{{ route('updateSoal', $data->id) }}" method="POST" enctype="multipart/form-data">
                @csrf

                <!-- 🔹 Tipe Soal -->
                <div class="mb-4">
                    <label class="form-label fw-semibold">Tipe Soal</label>
                    <select name="type" class="form-select" id="tipeSoal" disabled>
                        <option value="MultipleChoice" {{ $data->type == 'MultipleChoice' ? 'selected' : '' }}>Pilihan Ganda</option>
                        <option value="ShortAnswer" {{ $data->type == 'ShortAnswer' ? 'selected' : '' }}>Isian Singkat</option>
                    </select>
                    <small class="text-muted">Jenis soal tidak dapat diubah.</small>
                </div>

                @php 
                    $question = json_decode($data->question);
                    $mcOption = $data->MC_option ? json_decode($data->MC_option, true) : [];
                    $saAnswer = $data->SA_answer ? json_decode($data->SA_answer, true) : [];
                @endphp

                <!-- 🔹 Pertanyaan -->
                <div class="mb-4">
                    <label class="form-label fw-semibold">Teks Pertanyaan</label>
                    <textarea name="question_text" class="form-control" rows="3" required>{{ $question->text ?? '' }}</textarea>
                </div>

                <!-- 🔹 Upload / URL Gambar Soal -->
                <div class="mb-4">
                    <label class="form-label fw-semibold">Gambar Soal (opsional)</label>
                    <div class="input-group mb-2">
                        <input type="file" name="question_image" class="form-control" accept="image/*" id="questionImageInput">
                    </div>
                    <input type="text" name="question_url" class="form-control mb-2" value="{{ $question->URL ?? '' }}" placeholder="Atau masukkan URL gambar">
                    
                    @if(!empty($question->URL))
                        <div class="mt-3 text-center">
                            <img src="{{ $question->URL }}" alt="Gambar Soal" class="img-fluid rounded shadow-sm" style="max-height: 200px;">
                        </div>
                    @endif

                    <div id="previewQuestionImage" class="mt-2 text-center"></div>
                </div>

                <!-- 🔹 Untuk Pilihan Ganda -->
                @if($data->type == 'MultipleChoice')
                    <hr>
                    <h5 class="fw-bold text-secondary mb-3">
                        <i class="bi bi-list-check me-2"></i> Pilihan Jawaban
                    </h5>

                    @foreach($mcOption as $index => $item)
                        @php 
                            $label = array_keys($item)[0];
                            $teks = $item[$label]['teks'];
                            $url = $item[$label]['url'] ?? '';
                        @endphp
                        <div class="card mb-3 shadow-sm border-0">
                            <div class="card-body">
                                <label class="fw-semibold mb-2">Opsi {{ strtoupper($label) }}</label>
                                <input type="text" name="option_text[]" class="form-control mb-2" value="{{ $teks }}" placeholder="Teks opsi {{ strtoupper($label) }}">

                                <div class="row g-2">
                                    <div class="col-md-6">
                                        <input type="file" name="option_image[]" class="form-control" accept="image/*">
                                    </div>
                                    <div class="col-md-6">
                                        <input type="text" name="option_url[]" class="form-control" value="{{ $url }}" placeholder="Atau URL gambar (opsional)">
                                    </div>
                                </div>

                                @if(!empty($url))
                                    <div class="mt-2 text-center">
                                        <img src="{{ $url }}" alt="Opsi {{ strtoupper($label) }}" class="img-thumbnail shadow-sm" style="max-height: 120px;">
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach

                    <div class="mb-4">
                        <label class="form-label fw-semibold">Jawaban Benar</label>
                        <select name="mc_answer" class="form-select">
                            <option value="">-- Pilih Jawaban --</option>
                            @foreach(['a','b','c','d','e'] as $opt)
                                <option value="{{ $opt }}" {{ $data->MC_answer == $opt ? 'selected' : '' }}>
                                    {{ strtoupper($opt) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <!-- 🔹 Untuk Isian Singkat -->
                @if($data->type == 'ShortAnswer')
                    <hr>
                    <h5 class="fw-bold text-secondary mb-3">
                        <i class="bi bi-pencil-square me-2"></i> Jawaban Benar
                    </h5>
                    <div id="opsiIsianSingkat">
                        @foreach($saAnswer as $ans)
                            <input type="text" name="sa_answer[]" class="form-control mb-2" value="{{ $ans }}" placeholder="Masukkan jawaban singkat">
                        @endforeach
                        <button type="button" id="tambahJawaban" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-plus-circle"></i> Tambah Jawaban
                        </button>
                    </div>
                @endif

                <div class="text-end mt-4">
                    <button type="submit" class="btn btn-success px-4">
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

<!-- 🔹 Script -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    // Tambah jawaban singkat baru
    document.getElementById('tambahJawaban')?.addEventListener('click', function () {
        const input = document.createElement('input');
        input.type = 'text';
        input.name = 'sa_answer[]';
        input.classList.add('form-control', 'mb-2');
        input.placeholder = 'Masukkan jawaban singkat';
        this.parentElement.insertBefore(input, this);
    });

    // Preview gambar soal saat upload baru
    const questionImageInput = document.getElementById('questionImageInput');
    const previewQuestionImage = document.getElementById('previewQuestionImage');

    questionImageInput?.addEventListener('change', function (e) {
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
