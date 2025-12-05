@extends('layouts.main')
@section('dataSoal', request()->is('datasoal') ? 'active' : '')

@section('content')

    <style>
        .w-5 {
            width: 5% !important;
        }

        .w-10 {
            width: 10% !important;
        }

        .w-15 {
            width: 15% !important;
        }

        .w-20 {
            width: 20% !important;
        }

        .w-45 {
            width: 45% !important;
        }
    </style>

    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-bold mb-0 text-primary">
                <i class="bi bi-journal-check me-2"></i>Daftar Soal
            </h3>

            <div>
                <a href="{{ route('tambahSoal') }}" class="btn btn-primary me-2 shadow-sm">
                    <i class="bi bi-plus-circle"></i> Tambah Soal Manual
                </a>
                <a href="{{ route('generateSoal') }}" class="btn btn-success shadow-sm">
                    <i class="bi bi-lightbulb"></i> Buat Soal Otomatis
                </a>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="mb-3">
                    <label class="fw-bold">Filter Topik:</label>
                    <select id="filterTopik" class="form-select w-auto d-inline-block ms-2">
                        <option value="">Semua Topik</option>

                        @foreach($topics as $t)
                            <option value="{{ $t->title }}">{{ $t->title }}</option>
                        @endforeach
                    </select>
                </div>


                {{-- TABEL SOAL --}}
                <table id="soalTable" class="table table-striped table-hover align-middle">
                    <thead class="table-primary">
                        <tr>
                            <th class="w-5">No</th>
                            <th class="w-10">Tipe</th>
                            <th class="w-45">Pertanyaan</th>
                            <th class="w-20">Topik</th>
                            <th class="w-10">Kesulitan</th>
                            <th class="w-10">Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($data as $index => $item)
                            <tr>
                                <td class="fw-bold">{{ $index + 1 }}</td>

                                <td>{{ $item->type }}</td>

                                <td class="text-start">{{ $item->question->text ?? '-' }}</td>

                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <select class="form-select form-select-sm topic-select" data-id="{{ $item->id }}">
                                            <option value="">-- Pilih Topik --</option>
                                            @foreach($topics as $t)
                                                <option value="{{ $t->id }}" {{ $item->id_topic == $t->id ? 'selected' : '' }}>
                                                    {{ $t->title }}
                                                </option>
                                            @endforeach
                                        </select>

                                        <button class="btn btn-sm btn-success save-topic" data-id="{{ $item->id }}">
                                            <i class="bi bi-save"></i>
                                        </button>
                                    </div>
                                </td>

                                <td>
                                    <span class="badge 
                                                            @if($item->difficulty == 'mudah') bg-success
                                                            @elseif($item->difficulty == 'sedang') bg-warning text-dark
                                                            @else bg-danger @endif">
                                        {{ ucfirst($item->difficulty) }}
                                    </span>
                                </td>

                                <td>
                                    <button class="btn btn-outline-primary btn-sm view-soal" data-bs-toggle="modal"
                                        data-bs-target="#modalLihatSoal"
                                        data-q="{{ base64_encode(json_encode($item->question)) }}"
                                        data-opt="{{ base64_encode(json_encode($item->MC_option)) }}"
                                        data-mcanswer="{{ $item->MC_answer }}"
                                        data-sa="{{ base64_encode(json_encode($item->SA_answer)) }}"
                                        data-type="{{ $item->type }}">
                                        <i class="bi bi-eye-fill"></i>
                                    </button>

                                    <a href="{{ route('editSoal', $item->id) }}" class="btn btn-outline-warning btn-sm">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>

                                    <form action="{{ route('hapusSoal', $item->id) }}" method="POST" class="d-inline">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-outline-danger btn-sm"
                                            onclick="return confirm('Yakin ingin menghapus soal ini?')">
                                            <i class="bi bi-trash-fill"></i>
                                        </button>
                                    </form>
                                </td>

                            </tr>
                        @endforeach
                    </tbody>

                </table>
            </div>
        </div>

        {{-- MODAL DETAIL --}}
        <div class="modal fade" id="modalLihatSoal" tabindex="-1">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content rounded-4 shadow">
                    <div class="modal-header bg-primary text-white rounded-top">
                        <h5 class="modal-title"><i class="bi bi-card-text me-2"></i>Detail Soal</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <h5 id="soalText" class="fw-bold mb-3 text-dark"></h5>
                        <div id="soalImage" class="mb-3 text-center"></div>
                        <hr>
                        <div id="soalPilihan" class="mb-3"></div>
                        <hr>
                        <strong>Jawaban Benar:</strong>
                        <p id="soalJawaban" class="text-success fw-semibold"></p>
                    </div>
                </div>
            </div>
        </div>

    </div>


    {{-- SCRIPTS --}}
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/2.1.8/js/dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/2.1.8/js/dataTables.bootstrap5.min.js"></script>

    <script>
        document.addEventListener("DOMContentLoaded", () => {

            let table = new DataTable('#soalTable', {
                responsive: true,
                autoWidth: false,
                pageLength: 10,
                columnDefs: [
                    {
                        targets: 3,   // kolom dropdown topik
                        render: function (data, type, row, meta) {
                            if (type === 'filter') {
                                // Ambil teks dari option yang selected
                                let html = document.createElement("div");
                                html.innerHTML = data;
                                let selected = html.querySelector("option[selected]");
                                return selected ? selected.textContent : "";
                            }
                            return data; // tampilan normal
                        }
                    }
                ]
            });

            // FIX NOMOR URUT DATA TABLES
            table.on('draw', () => {
                table.column(0, { search: 'applied', order: 'applied' })
                    .nodes()
                    .each((cell, i) => { cell.innerHTML = i + 1; });
            });
            // === FILTER TOPIK ===
            document.getElementById("filterTopik").addEventListener("change", function () {
                table.column(3).search(this.value).draw();
            });


            // === EVENT DELEGATION UNTUK MODAL ===
            document.addEventListener('click', function (e) {
                if (!e.target.closest(".view-soal")) return;

                let btn = e.target.closest(".view-soal");

                const decode = (v) => v ? JSON.parse(atob(v)) : null;

                let q = decode(btn.dataset.q);
                let opt = decode(btn.dataset.opt);
                let sa = decode(btn.dataset.sa);
                let type = btn.dataset.type;
                let mcAns = btn.dataset.mcanswer;

                document.getElementById("soalText").textContent = q?.text ?? "-";
                document.getElementById("soalImage").innerHTML =
                    q?.URL ? `<img src="${q.URL}" class="img-fluid rounded" style="max-height:250px">` : "";

                let pilihan = document.getElementById("soalPilihan");
                pilihan.innerHTML = "";

                if (type === "MultipleChoice" && opt) {
                    opt.forEach(o => {
                        let label = Object.keys(o)[0];
                        let d = o[label];
                        pilihan.innerHTML += `
                                <div class="border p-2 mb-2 rounded">
                                    <strong>${label.toUpperCase()}.</strong> ${d.teks}
                                    ${d.url ? `<br><img src="${d.url}" class="img-thumbnail mt-2" style="max-height:100px">` : ""}
                                </div>
                            `;
                    });
                } else {
                    pilihan.innerHTML = "<em>Tidak ada pilihan jawaban.</em>";
                }

                document.getElementById("soalJawaban").textContent =
                    type === "MultipleChoice" ? mcAns : (sa?.join(", ") ?? "-");
            });

            // === SIMPAN TOPIK ===
            document.addEventListener("click", function (e) {
                if (!e.target.closest(".save-topic")) return;

                let btn = e.target.closest(".save-topic");
                let id = btn.dataset.id;
                let select = document.querySelector(`.topic-select[data-id='${id}']`);
                let topic = select.value;

                if (!topic) return alert("Silahkan pilih topik.");

                fetch(`/edit-topik-soal/${id}`, {
                    method: "POST",
                    headers: {
                        "X-CSRF-TOKEN": "{{ csrf_token() }}",
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify({ id_topic: topic })
                })
                    .then(r => r.json())
                    .then(r => {
                        if (r.success) {
                            btn.innerHTML = '<i class="bi bi-check2-circle"></i>';
                            btn.classList.replace("btn-success", "btn-primary");
                            setTimeout(() => {
                                btn.classList.replace("btn-primary", "btn-success");
                                btn.innerHTML = '<i class="bi bi-save"></i>';
                            }, 1000);
                        }
                    });

            });

        });
    </script>

@endsection