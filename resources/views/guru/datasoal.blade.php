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

        .w-30 {
            width: 30% !important;
        }

        .w-40 {
            width: 40% !important;
        }

        .topic-label {
            display: inline-block;
            max-width: 420px;
            white-space: normal;
            word-break: break-word;
        }

        @media (max-width:768px) {
            .topic-label {
                max-width: 200px;
            }
        }
    </style>

    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="d-flex align-items-center gap-2">
                <h3 class="fw-bold mb-0 text-black">
                    Daftar Soal
                </h3>

                <button type="button" class="btn btn-sm btn-outline-secondary rounded-circle" style="width:32px;height:32px"
                    data-bs-toggle="modal" data-bs-target="#modalInfoSoal" title="Informasi Daftar Soal">
                    <i class="bi bi-info-lg"></i>
                </button>
            </div>

            <div>
                <a href="{{ route('tambahSoal') }}" class="btn btn-primary me-2 shadow-sm">
                    <i class="bi bi-plus-circle"></i> Tambah Soal Manual
                </a>
                <a href="{{ route('generateSoal') }}" class="btn btn-success shadow-sm">
                    <i class="bi bi-lightbulb"></i> Buat soal lebih cepat
                </a>
            </div>
        </div>

        {{-- panel filter --}}
        <div class="mb-3">
            <label class="fw-bold">Filter Topik:</label>
            <select id="filterTopik" class="form-select w-auto d-inline-block ms-2">
                <option value="">Semua Topik</option>
                @foreach($topics as $t)
                    <option value="{{ $t->id }}">{{ $t->title }}</option>
                @endforeach
            </select>

            <button id="resetFilterBtn" class="btn btn-outline-secondary btn-sm ms-2">Reset</button>

            <span id="totalSoal" class="ms-3 fw-semibold">Total: {{ $data->count() ?? count($data) }} soal</span>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body">
                <table id="soalTable" class="table table-striped table-hover align-middle">
                    <thead class="table-primary">
                        <tr>
                            <th class="w-5">No</th>
                            <th class="w-10">Tipe</th>
                            <th class="w-40">Pertanyaan</th>
                            <th class="w-20">Topik</th>
                            <th class="w-10">Kesulitan</th>
                            <th class="w-10">Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($data as $index => $item)
                            @php
                                $topicObj = $topics->firstWhere('id', $item->id_topic);
                                $topicTitle = $topicObj ? $topicObj->title : '-';
                            @endphp
                            <tr data-question-id="{{ $item->id }}" data-topic-title="{{ $topicTitle }}"
                                data-id_topic="{{ $item->id_topic ?? '' }}">
                                <td class="fw-bold"></td>
                                <td>{{ $item->type }}</td>
                                <td class="text-start">
                                    {!! nl2br(e($item->question->text ?? ($item->question['text'] ?? '-'))) !!}
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="topic-label" title="{{ $topicTitle }}">{{ $topicTitle }}</span>
                                        <button class="btn btn-sm btn-outline-primary btn-edit-topic" type="button"
                                            data-id="{{ $item->id }}" data-topic-id="{{ $item->id_topic ?? '' }}">
                                            <i class="bi bi-pencil"></i> Edit
                                        </button>
                                    </div>
                                </td>
                                <td>
                                    <span
                                        class="badge
                                                                                                                                @if($item->difficulty == 'mudah') bg-success
                                                                                                                                @elseif($item->difficulty == 'sedang') bg-warning text-dark
                                                                                                                                @else bg-danger @endif">
                                        {{ ucfirst($item->difficulty) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex gap-2">
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

                                        <form action="{{ route('hapusSoal', $item->id) }}" method="POST"
                                            class="d-inline form-delete-soal">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="btn btn-outline-danger btn-sm btn-delete-soal">
                                                <i class="bi bi-trash-fill"></i>
                                            </button>
                                        </form>

                                    </div>
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

        {{-- MODAL EDIT TOPIK --}}
        <div class="modal fade" id="modalEditTopic" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form id="formEditTopic">@csrf
                        <div class="modal-header">
                            <h5 class="modal-title">Edit Topik Soal</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" id="modalQuestionId" name="question_id" value="">
                            <div class="mb-3">
                                <label class="form-label">Pilih Topik</label>
                                <select id="modalTopicSelect" class="form-select">
                                    <option value="">-- Pilih Topik --</option>
                                    @foreach($topics as $t)
                                        <option value="{{ $t->id }}" data-id_subject="{{ $t->id_subject }}">{{ $t->title }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="form-text">Pilih topik yang sudah ada untuk mengaitkan soal.</div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Atau buat topik baru (harus pilih Mata Pelajaran)</label>
                                <select id="modalSubjectSelect" class="form-select mb-2">
                                    <option value="">-- Pilih Mata Pelajaran --</option>
                                    @foreach($subjects as $s)
                                        <option value="{{ $s->id }}" data-id_class="{{ $s->id_class }}">{{ $s->name }}</option>
                                    @endforeach
                                </select>
                                <input id="modalNewTopic" type="text" class="form-control"
                                    placeholder="Judul topik baru (opsional)">
                                <div class="form-text">Jika mengisi judul baru, pastikan memilih Mata Pelajaran di atas.
                                </div>
                            </div>

                            <div id="modalEditAlert" class="alert d-none"></div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary" id="modalSaveBtn">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        {{-- MODAL INFO DAFTAR SOAL --}}
        {{-- MODAL INFO DAFTAR SOAL --}}
        <div class="modal fade" id="modalInfoSoal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content shadow rounded-4">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">
                            <i class="bi bi-info-circle me-2"></i>Informasi Daftar Soal
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">

                        <p>
                            Halaman <strong>Daftar Soal</strong> digunakan untuk mengelola seluruh soal yang dimiliki guru,
                            baik soal pilihan ganda maupun isian singkat, yang terhubung dengan topik dan mata pelajaran.
                        </p>

                        <hr>

                        <h6 class="fw-bold text-primary">
                            <i class="bi bi-funnel me-1"></i> Filter Topik
                        </h6>
                        <ul>
                            <li>Digunakan untuk menampilkan soal berdasarkan topik tertentu.</li>
                            <li>Pilih <em>Semua Topik</em> untuk menampilkan seluruh soal.</li>
                            <li>Tombol <strong>Reset</strong> akan mengembalikan tampilan ke kondisi awal.</li>
                        </ul>

                        <hr>

                        <h6 class="fw-bold text-success">
                            <i class="bi bi-plus-circle me-1"></i> Tambah Soal Manual
                        </h6>
                        <ul>
                            <li>Digunakan untuk membuat soal secara manual.</li>
                            <li>Guru dapat menentukan:
                                <ul>
                                    <li>Tipe soal (Pilihan Ganda / Isian Singkat)</li>
                                    <li>Pertanyaan</li>
                                    <li>Jawaban benar</li>
                                    <li>Tingkat kesulitan</li>
                                    <li>Topik & mata pelajaran</li>
                                </ul>
                            </li>
                        </ul>

                        <hr>

                        <h6 class="fw-bold text-success">
                            <i class="bi bi-lightbulb me-1"></i> Buat Soal Otomatis
                        </h6>
                        <ul>
                            <li>Digunakan untuk menghasilkan soal secara otomatis.</li>
                            <li>Soal dibuat berdasarkan:
                                <ul>
                                    <li>Topik yang dipilih</li>
                                    <li>Mata pelajaran</li>
                                    <li>Jumlah soal yang diinginkan</li>
                                </ul>
                            </li>
                            <li>Cocok untuk mempercepat pembuatan bank soal.</li>
                        </ul>

                        <hr>

                        <h6 class="fw-bold text-warning">
                            <i class="bi bi-gear me-1"></i> Aksi Soal
                        </h6>
                        <ul>
                            <li>
                                <i class="bi bi-eye text-primary"></i>
                                <strong>Lihat</strong> – Melihat detail soal.
                            </li>
                            <li>
                                <i class="bi bi-pencil-square text-warning"></i>
                                <strong>Edit</strong> – Mengubah isi soal.
                            </li>
                            <li>
                                <i class="bi bi-trash text-danger"></i>
                                <strong>Hapus</strong> – Menghapus soal secara permanen.
                            </li>
                        </ul>

                        <hr>

                        <h6 class="fw-bold text-secondary">
                            <i class="bi bi-bar-chart me-1"></i> Kesulitan Soal
                        </h6>
                        <ul>
                            <li><span class="badge bg-success">Mudah</span> – Untuk pemahaman dasar.</li>
                            <li><span class="badge bg-warning text-dark">Sedang</span> – Untuk pemahaman menengah.</li>
                            <li><span class="badge bg-danger">Sulit</span> – Untuk pemahaman tingkat lanjut.</li>
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



    </div>

@endsection

@push('head')
    <!-- DataTables CSS (hanya di-push ke head sekali) -->
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
@endpush

@push('scripts')
    <!-- Jangan muat jQuery di sini — layout sudah memuat jQuery -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            if (typeof $.fn.DataTable !== 'function') {
                console.error('DataTables tidak terdeteksi — cek urutan skrip dan pastikan jQuery + DataTables dimuat sekali di layout.');
                return;
            }

            // Inisialisasi DataTable (kolom No di-render dari meta)
            var dt = $('#soalTable').DataTable({
                responsive: true,
                autoWidth: false,
                pageLength: 10,
                order: [[1, 'asc']],
                autoWidth: false,
                columnDefs: [
                    {
                        targets: 0,
                        searchable: false,
                        orderable: false,
                        className: 'text-center fw-bold',
                        render: function (data, type, row, meta) {
                            return meta.row + meta.settings._iDisplayStart + 1;
                        }
                    },
                    {
                        targets: 5,
                        orderable: false,
                        searchable: false
                    }
                ]
            });

            // fungsi bantu untuk meng-update elemen total berdasarkan baris yg terlihat
            function updateTotalLabel() {
                // dt.rows({ search: 'applied' }) menghitung rows yang lolos filter/pencarian DataTables
                var visibleCount = dt.rows({ search: 'applied' }).count();
                var totalEl = document.getElementById('totalSoal');
                if (totalEl) {
                    totalEl.textContent = 'Total: ' + visibleCount + ' soal';
                }
            }

            // panggil sekali untuk set awal (jika server already rendered total, ini sinkronisasi)
            updateTotalLabel();

            // custom filter by topic id (ext.search)
            var currentTopicFilter = '';
            $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
                if (settings.nTable.id !== 'soalTable') return true;
                if (!currentTopicFilter) return true;
                var rowNode = dt.row(dataIndex).node();
                var rowTopic = rowNode ? (rowNode.getAttribute('data-id_topic') || '') : '';
                return String(rowTopic) === String(currentTopicFilter);
            });

            // saat select berubah -> set filter dan redraw
            $('#filterTopik').on('change', function () {
                currentTopicFilter = $(this).val() || '';
                dt.draw();
            });

            // tombol reset filter: kosongkan select dan redraw
            $('#resetFilterBtn').on('click', function () {
                $('#filterTopik').val('');      // set ke option kosong
                currentTopicFilter = '';
                dt.search('');                  // bersihkan search global
                dt.order([[1, 'asc']]);         // optional: reset ordering ke default kolom tipe
                dt.page.len(10);                // optional: reset pageLength bila pernah diubah
                dt.draw();
                // fokus UI kecil
                $('#filterTopik').focus();
            });

            // update numbering & total saat table di-redraw (draw event)
            dt.on('draw.dt', function () {
                // numbering sudah di-handle oleh render kolom, tapi tetap aman
                dt.column(0, { search: 'applied', order: 'applied' }).nodes().each(function (cell, i) {
                    cell.innerHTML = i + 1;
                });

                // update total setiap kali draw (filter berubah / paging / search)
                updateTotalLabel();
            });
            // View soal modal
            $(document).on('click', '.view-soal', function () {
                var btn = this;
                var decode = v => v ? JSON.parse(atob(v)) : null;
                var q = decode(btn.dataset.q);
                var opt = decode(btn.dataset.opt);
                var sa = decode(btn.dataset.sa);
                var type = btn.dataset.type;
                var mcAns = btn.dataset.mcanswer;

                $('#soalText').text(q?.text ?? "-");
                $('#soalImage').html(q?.URL ? `<img src="${q.URL}" class="img-fluid rounded" style="max-height:250px">` : "");
                var pilihan = $('#soalPilihan').empty();

                if (type === "MultipleChoice" && opt) {
                    opt.forEach(o => {
                        var label = Object.keys(o)[0];
                        var d = o[label];
                        pilihan.append(`
                                                                    <div class="border p-2 mb-2 rounded">
                                                                        <strong>${label.toUpperCase()}.</strong> ${d.teks}
                                                                        ${d.url ? `<br><img src="${d.url}" class="img-thumbnail mt-2" style="max-height:100px">` : ""}
                                                                    </div>
                                                                `);
                    });
                } else {
                    pilihan.html("<em>Tidak ada pilihan jawaban.</em>");
                }

                $('#soalJawaban').text(type === "MultipleChoice" ? mcAns : (sa?.join(", ") ?? "-"));
            });

            // Edit topic modal handling
            var modalEl = document.getElementById('modalEditTopic');
            var bsModal = (typeof bootstrap !== 'undefined' && modalEl) ? new bootstrap.Modal(modalEl) : null;

            $(document).on('click', '.btn-edit-topic', function () {
                var qid = $(this).data('id');
                var topicId = $(this).data('topic-id') || '';

                $('#modalQuestionId').val(qid);
                $('#modalTopicSelect').val(topicId);
                $('#modalNewTopic').val('');
                $('#modalSubjectSelect').val('');
                $('#modalEditAlert').addClass('d-none').removeClass('alert-success alert-danger').text('');

                if (bsModal) bsModal.show();
            });

            $('#formEditTopic').on('submit', function (e) {
                e.preventDefault();
                var qid = $('#modalQuestionId').val();
                var chosenTopic = $('#modalTopicSelect').val();
                var newTitle = $('#modalNewTopic').val().trim();
                var subjectForNew = $('#modalSubjectSelect').val();

                if (!chosenTopic && !newTitle) {
                    $('#modalEditAlert').removeClass('d-none alert-success').addClass('alert-danger').text('Pilih topik atau isi judul topik baru.');
                    return;
                }
                if (newTitle && !subjectForNew) {
                    $('#modalEditAlert').removeClass('d-none alert-success').addClass('alert-danger').text('Pilih Mata Pelajaran jika akan membuat topik baru.');
                    return;
                }

                var payload = chosenTopic ? { id_topic: chosenTopic } : { topic_title: newTitle, id_subject: subjectForNew };

                $('#modalSaveBtn').prop('disabled', true).text('Menyimpan...');

                fetch(`/edit-topik-soal/${qid}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(payload)
                })
                    .then(res => res.json())
                    .then(res => {
                        if (res.success) {
                            var $tr = $(`tr[data-question-id='${qid}']`);
                            if ($tr.length) {
                                if (res.id_topic) $tr.attr('data-id_topic', res.id_topic);
                                if (res.title) {
                                    $tr.attr('data-topic-title', res.title);
                                    $tr.find('.topic-label').text(res.title).attr('title', res.title);
                                } else if (newTitle) {
                                    $tr.attr('data-topic-title', newTitle);
                                    $tr.find('.topic-label').text(newTitle).attr('title', newTitle);
                                }
                                if (res.id_topic && res.title && $('#filterTopik option[value="' + res.id_topic + '"]').length === 0) {
                                    $('#filterTopik').append(`<option value="${res.id_topic}">${res.title}</option>`);
                                }
                                dt.row($tr).invalidate().draw(false);
                            }

                            $('#modalEditAlert').removeClass('d-none alert-danger').addClass('alert-success').text('Topik berhasil diperbarui.');
                            setTimeout(() => {
                                if (bsModal) bsModal.hide();
                                $('#modalEditAlert').addClass('d-none').removeClass('alert-success').text('');
                            }, 700);
                        } else {
                            $('#modalEditAlert').removeClass('d-none alert-success').addClass('alert-danger').text(res.message || 'Gagal menyimpan topik.');
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        $('#modalEditAlert').removeClass('d-none alert-success').addClass('alert-danger').text('Kesalahan jaringan.');
                    })
                    .finally(() => {
                        $('#modalSaveBtn').prop('disabled', false).text('Simpan');
                    });
            });

        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {

            document.querySelectorAll('.btn-delete-soal').forEach(btn => {
                btn.addEventListener('click', function (e) {
                    e.preventDefault();

                    const form = this.closest('form');
                    const row = this.closest('tr');

                    // ambil teks soal (aman, ringkas)
                    let soalText = row?.querySelector('td:nth-child(3)')?.innerText ?? 'soal ini';
                    soalText = soalText.length > 120 ? soalText.substring(0, 120) + '…' : soalText;

                    Swal.fire({
                        title: 'Hapus Soal?',
                        html: `
                        <div class="text-start">
                            <p class="mb-2">
                                Anda akan menghapus:
                            </p>
                            <blockquote class="small border-start ps-2 text-muted">
                                ${soalText}
                            </blockquote>
                            <small class="text-danger">
                                ⚠️ Soal yang dihapus tidak dapat dikembalikan.
                            </small>
                        </div>
                    `,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#dc3545',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Ya, hapus',
                        cancelButtonText: 'Batal',
                        reverseButtons: true
                    }).then((result) => {
                        if (result.isConfirmed) {

                            Swal.fire({
                                title: 'Menghapus...',
                                text: 'Mohon tunggu',
                                allowOutsideClick: false,
                                didOpen: () => Swal.showLoading()
                            });

                            form.submit();
                        }
                    });
                });
            });

        });
    </script>

@endpush