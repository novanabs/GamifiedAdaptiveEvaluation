@extends('layouts.main')

@section('content')
    <div class="container py-3">
        <h3 class="fw-bold mb-3">Daftar Soal</h3>
        <a href="{{ route('tambahSoal') }}" class="btn btn-primary mb-3">+ Tambah Soal</a>

        <table class="table table-bordered align-middle">
            <thead class="table-light">
                <tr>
                    <th style="width: 60px;">No</th>
                    <th style="width: 140px;">Tipe</th>
                    <th>Pertanyaan</th>
                    <th style="width: 220px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data as $index => $item)
                    @php $q = $item->question; @endphp
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ $item->type }}</td>
                        <td>
                            {{ $q->text ?? '-' }}
                        </td>
                        <td>
                            <button class="btn btn-info btn-sm view-soal" data-bs-toggle="modal"
                                data-bs-target="#modalLihatSoal" data-question='@json($item->question)'
                                data-mcoption='@json($item->MC_option)' data-mcanswer='{{ $item->MC_answer }}'
                                data-saanswer='@json($item->SA_answer)' data-type='{{ $item->type }}'>
                                <i class="bi bi-eye"></i> Lihat
                            </button>


                            <a href="{{ route('editSoal', $item->id) }}" class="btn btn-warning btn-sm">
                                <i class="bi bi-pencil-square"></i> Edit
                            </a>

                            <form action="{{ route('hapusSoal', $item->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus?')">
                                    <i class="bi bi-trash"></i> Hapus
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Modal Lihat Soal -->
        <div class="modal fade" id="modalLihatSoal" tabindex="-1" aria-labelledby="modalLihatSoalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content rounded-3 shadow-lg">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" id="modalLihatSoalLabel">Detail Soal</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <h5 id="soalText" class="fw-bold mb-3"></h5>
                        <div id="soalImage" class="mb-3 text-center"></div>
                        <hr>
                        <div id="soalPilihan"></div>
                        <hr>
                        <div>
                            <strong>Jawaban Benar:</strong>
                            <p id="soalJawaban" class="text-success fw-semibold"></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const soalText = document.getElementById('soalText');
            const soalImage = document.getElementById('soalImage');
            const soalPilihan = document.getElementById('soalPilihan');
            const soalJawaban = document.getElementById('soalJawaban');

            document.querySelectorAll('.view-soal').forEach(button => {
                button.addEventListener('click', function () {
                    const q = JSON.parse(this.getAttribute('data-question'));
                    const type = this.getAttribute('data-type');
                    const mcOption = this.getAttribute('data-mcoption') ? JSON.parse(this.getAttribute('data-mcoption')) : null;
                    const mcAnswer = this.getAttribute('data-mcanswer');
                    const saAnswer = this.getAttribute('data-saanswer') ? JSON.parse(this.getAttribute('data-saanswer')) : null;

                    // ✅ Tampilkan teks soal
                    soalText.textContent = q.text ?? 'Tidak ada teks soal';

                    // ✅ Tampilkan gambar soal (jika ada)
                    soalImage.innerHTML = q.URL
                        ? `<img src="${q.URL}" alt="Gambar Soal" class="img-fluid rounded shadow-sm" style="max-height: 250px;">`
                        : '';

                    // ✅ Reset dan isi pilihan
                    soalPilihan.innerHTML = '';
                    if (type === 'MultipleChoice' && mcOption) {
                        mcOption.forEach(opt => {
                            const label = Object.keys(opt)[0];
                            const detail = opt[label];
                            const div = document.createElement('div');
                            div.classList.add('mb-2', 'p-2', 'border', 'rounded');
                            div.innerHTML = `
                                <strong>${label.toUpperCase()}.</strong> ${detail.teks}<br>
                                ${detail.url ? `<img src="${detail.url}" alt="Opsi ${label}" class="img-thumbnail mt-2" style="max-height:100px;">` : ''}
                            `;
                            soalPilihan.appendChild(div);
                        });
                    } else if (type === 'ShortAnswer' && saAnswer) {
                        soalPilihan.innerHTML = `<em>Soal jawaban singkat — tidak memiliki pilihan.</em>`;
                    }

                    // ✅ Tampilkan jawaban benar
                    if (type === 'MultipleChoice') {
                        soalJawaban.textContent = mcAnswer ? mcAnswer.toUpperCase() : 'Belum ada jawaban';
                    } else if (type === 'ShortAnswer') {
                        soalJawaban.textContent = saAnswer ? saAnswer.join(', ') : 'Belum ada jawaban';
                    } else {
                        soalJawaban.textContent = '-';
                    }
                });
            });
        });
    </script>
@endsection