@extends('layouts.main')
@section('dataAktivitas', request()->is('guru/aktivitas/*/atur-soal') ? 'active' : '')

@section('content')
    <div class="container mt-4">

        <h3 class="fw-bold mb-3">Atur Soal untuk: {{ $aktivitas->title }}</h3>

        <a href="{{ url('/dataaktivitas') }}" class="btn btn-secondary mb-3">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>

        {{-- PETUNJUK --}}
        <div class="card shadow-sm mb-3">
            <div class="card-body">
                <div class="small text-muted">Petunjuk</div>
                <p class="mb-0">
                    Soal terpilih akan ditampilkan di bawah. Gunakan tombol <strong>Lihat Soal</strong> untuk memilih soal
                    dari daftar (manual atau otomatis). Setelah memilih di modal, tekan <strong>Terapkan ke
                        Aktivitas</strong>
                    untuk memindahkan hasil ke halaman ini. Gunakan tombol <strong>Simpan Pilihan</strong> untuk menyimpan
                    ke database.
                </p>
            </div>
        </div>

        {{-- SOAL TERPILIH --}}
        <div class="card shadow-sm mb-3">
            <div class="card-header bg-primary text-white fw-semibold">Soal Terpilih</div>

            <div class="card-body" id="selectedArea" style="min-height:240px; max-height:420px; overflow-y:auto;">
                @if($selectedQuestions->isEmpty())
                    <div id="noSelectedPlaceholder" class="text-center text-muted py-4">
                        <i class="bi bi-clipboard-x" style="font-size:2rem"></i>
                        <div class="mt-2">Belum ada soal.</div>
                    </div>
                @else
                    @foreach($selectedQuestions as $s)
                        @php $sData = json_decode($s->question); @endphp
                        <div class="p-2 border rounded mb-2 bg-light d-flex justify-content-between align-items-start"
                            id="selectedItem-{{ $s->id }}">
                            <div>
                                <small class="text-muted">{{ $s->difficulty }} — {{ $s->type }}</small>
                                <div class="mt-1">{{ Str::limit($sData->text ?? '-', 240) }}</div>
                            </div>

                            {{-- tombol hapus tetap ikonnya sama (bi-x-circle) --}}
                            <button class="btn btn-sm btn-danger" onclick="hapusDariTerpilih({{ $s->id }})">
                                <i class="bi bi-x-circle"></i>
                            </button>
                        </div>
                    @endforeach
                @endif
            </div>

            <div class="card-footer d-flex gap-2">
                <button class="btn btn-outline-primary flex-fill" data-bs-toggle="modal" data-bs-target="#soalModal">
                    <i class="bi bi-list-ul me-1"></i> Lihat Soal
                </button>

                <button class="btn btn-danger" onclick="clearAll()">
                    <i class="bi bi-trash me-1"></i> Hapus Semua Pilihan
                </button>

                <button class="btn btn-success" onclick="simpanPilihan()">
                    <i class="bi bi-save me-1"></i> Simpan Pilihan
                </button>
            </div>
        </div>

        {{-- INFORMASI COUNT (opsional) --}}
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body py-3">

                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center text-center gap-3">

                    {{-- Total Soal --}}
                    <div class="px-3">
                        <div class="small text-muted">Total Soal Terpilih</div>
                        <div class="fw-bold text-primary fs-4" id="currentTotal">
                            {{ $selectedQuestions->count() }}
                        </div>
                    </div>

                    <div class="vr d-none d-md-block" style="height:40px; opacity:.2;"></div>

                    {{-- Mudah --}}
                    <div class="px-3">
                        <div class="small text-muted">Mudah</div>
                        <div class="fw-bold text-success fs-5">
                            <span id="cnt-easy">0</span>
                        </div>
                    </div>

                    {{-- Sedang --}}
                    <div class="px-3">
                        <div class="small text-muted">Sedang</div>
                        <div class="fw-bold text-warning fs-5">
                            <span id="cnt-medium">0</span>
                        </div>
                    </div>

                    {{-- Sulit --}}
                    <div class="px-3">
                        <div class="small text-muted">Sulit</div>
                        <div class="fw-bold text-danger fs-5">
                            <span id="cnt-hard">0</span>
                        </div>
                    </div>

                </div>

            </div>
        </div>

    </div>

    {{-- MODAL: Daftar Soal (satu kolom scrollable; dengan tombol reset) --}}
    <div class="modal fade" id="soalModal" tabindex="-1" aria-labelledby="soalModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Daftar Soal — {{ $aktivitas->title }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>

                <div class="modal-body">
                    <div class="card p-2 mb-3">

                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div class="small text-muted">Daftar Semua Soal</div>
                            {{-- HAPUS "Pilih Semua" --}}
                            {{-- HAPUS tombol lama Bersihkan --}}
                        </div>

                        {{-- tabel scrollable --}}
                        <div style="max-height:560px; overflow-y:auto;">
                            <table class="table table-sm table-bordered mb-0">
                                <thead class="table-light text-center">
                                    <tr>
                                        <th style="width:80px">Aksi</th>
                                        <th style="width:60px">No</th>
                                        <th>Tipe</th>
                                        <th>Kesulitan</th>
                                        <th>Pertanyaan</th>
                                    </tr>
                                </thead>

                                <tbody id="modalQuestionList">
                                    @foreach ($questions as $q)
                                        @php $qData = json_decode($q->question); @endphp
                                        <tr data-qid="{{ $q->id }}" id="modalRow-{{ $q->id }}">
                                            <td class="text-center">
                                                <button
                                                    class="btn btn-sm {{ in_array($q->id, $selectedIds) ? 'btn-danger' : 'btn-success' }}"
                                                    onclick="modalToggleSelect({{ $q->id }})">
                                                    <i
                                                        class="bi {{ in_array($q->id, $selectedIds) ? 'bi-x-circle' : 'bi-plus-circle' }}"></i>
                                                </button>
                                            </td>
                                            <td class="text-center">{{ $loop->iteration }}</td>
                                            <td>{{ $q->type }}</td>
                                            <td>{{ $q->difficulty }}</td>
                                            <td>{{ Str::limit($qData->text ?? '-', 300) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>

                            </table>
                        </div>

                        {{-- pilihan jumlah & tombol ambil/reset --}}
                        <div class="mt-3">

                            <div class="small text-muted mb-1">Pilih Jumlah Soal</div>

                            <div class="d-flex flex-wrap gap-2 mb-3">
                                @foreach ([5, 10, 15, 20, 25, 30] as $opt)
                                    <label class="btn btn-outline-primary d-flex align-items-center">
                                        <input type="radio" name="modalJumlahRadio" value="{{ $opt }}" class="me-1">
                                        {{ $opt }}
                                    </label>
                                @endforeach
                            </div>

                            <div class="d-flex gap-2">
                                <button class="btn btn-primary" id="btnAmbilModal">Ambil Soal Otomatis</button>

                                {{-- Tombol RESET baru --}}
                                <button class="btn btn-sm btn-outline-secondary" id="btnUnselectAllModal">Bersihkan</button>

                                <div class="ms-auto text-muted align-self-center">Gunakan Ambil Otomatis setelah memilih
                                    jumlah.</div>
                            </div>

                        </div>

                    </div>
                </div>

                <div class="modal-footer">
                    <div class="me-auto text-muted small">
                        Pilih soal lalu tekan <strong>Terapkan ke Aktivitas</strong>
                    </div>
                    <button class="btn btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
                    <button class="btn btn-primary" id="btnApplyToActivity">Terapkan ke Aktivitas</button>
                </div>

            </div>
        </div>
    </div>



    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // GLOBALS
        const ACTIVITAS_ID = {{ $aktivitas->id }};
        const CSRF = "{{ csrf_token() }}";

        // modalSelected: array id soal yang dipilih di modal (temp)
        let modalSelected = @json($selectedIds); // mulai dari server selection
        // window.lastPicked tetap menyimpan selection yang sudah disimpan/applied di halaman
        window.lastPicked = @json($selectedIds);

        // helper escape
        function escapeHtml(s) {
            if (!s) return '';
            return s.replaceAll('&', '&amp;').replaceAll('<', '&lt;').replaceAll('>', '&gt;');
        }

        // --- helper: fetch single question detail (caches results) ---
        const _questionCache = {}; // id -> question object
        async function fetchQuestionById(id) {
            if (_questionCache[id]) return _questionCache[id];

            try {
                const res = await fetch(`/get-question/${id}`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                if (!res.ok) throw new Error('Network');
                const j = await res.json();
                // expected shape: { id, text, type, difficulty, ... }
                _questionCache[id] = j;
                return j;
            } catch (e) {
                console.error('fetchQuestionById error', id, e);
                return null;
            }
        }

        // --- render selected area on page (soal terpilih)
        // ids: array of ids
        // questionsMap (optional): { id: { id, text, type, difficulty } }
        async function renderSelectedArea(ids, questionsMap = null) {
            const area = document.getElementById('selectedArea');
            if (!area) return;

            if (!ids || ids.length === 0) {
                area.innerHTML = `<div id="noSelectedPlaceholder" class="text-center text-muted py-4">
                                                    <i class="bi bi-clipboard-x" style="font-size:2rem"></i>
                                                    <div class="mt-2">Belum ada soal.</div>
                                              </div>`;
                document.getElementById('currentTotal') && (document.getElementById('currentTotal').innerText = 0);
                updateCounter();
                return;
            }

            // build initial HTML with placeholders (so we show something immediately)
            let html = '';
            ids.forEach(id => {
                const q = questionsMap && questionsMap[id] ? questionsMap[id] : null;
                const smallText = q ? (q.difficulty + ' — ' + q.type) : '';
                const bodyText = q ? escapeHtml(q.text) : `Memuat soal #${id}...`;
                html += `<div class="p-2 border rounded mb-2 bg-light d-flex justify-content-between align-items-start" id="selectedItem-${id}">
                                        <div>
                                            <small class="text-muted">${smallText}</small>
                                            <div class="mt-1" id="selectedText-${id}">${bodyText}</div>
                                        </div>
                                        <button class="btn btn-sm btn-danger" onclick="hapusDariTerpilih(${id})">
                                            <i class="bi bi-x-circle"></i>
                                        </button>
                                    </div>`;
            });
            area.innerHTML = html;
            document.getElementById('currentTotal') && (document.getElementById('currentTotal').innerText = ids.length);
            updateCounter();

            // For any id lacking text, fetch it and replace placeholder
            const toFetch = ids.filter(id => {
                const q = questionsMap && questionsMap[id] ? questionsMap[id] : null;
                return !(q && q.text);
            });

            if (toFetch.length === 0) return;

            // fetch in parallel
            await Promise.all(toFetch.map(async id => {
                const q = await fetchQuestionById(id);
                if (q && q.text) {
                    // update cache and DOM
                    const el = document.getElementById(`selectedText-${id}`);
                    if (el) el.innerHTML = escapeHtml(q.text);
                    // also update small (difficulty/type) if missing
                    const smallEl = document.querySelector(`#selectedItem-${id} small.text-muted`);
                    if (smallEl && q.difficulty && q.type) smallEl.innerText = `${q.difficulty} — ${q.type}`;
                } else {
                    // failed to fetch — show fallback
                    const el = document.getElementById(`selectedText-${id}`);
                    if (el) el.innerHTML = `Soal #${id}`;
                }
            }));

            // after fetching, update counter again (difficulty counts)
            updateCounter();
        }

        // --- render preview inside modal (uses modalSelected array)
        // gracefully handles absence of preview container
        async function renderModalSelected(questionsMap = null) {
            const wrap = document.getElementById('modalSelectedArea');
            // if no preview area in DOM (you removed it), skip rendering but still ensure functions using it won't break
            if (!wrap) return;

            if (!modalSelected || modalSelected.length === 0) {
                wrap.innerHTML = `<div class="text-center text-muted py-4" id="modalNoSelected">Belum ada pilihan di modal.</div>`;
                return;
            }

            // initial render with placeholders
            let html = '';
            modalSelected.forEach(id => {
                const q = questionsMap && questionsMap[id] ? questionsMap[id] : null;
                const smallText = q ? (q.difficulty + ' — ' + q.type) : '';
                const bodyText = q ? escapeHtml(q.text) : `Memuat soal #${id}...`;
                html += `<div class="p-2 border rounded mb-2 bg-white" id="modalSelectedItem-${id}">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <small class="text-muted">${smallText}</small>
                                                <div class="mt-1" id="modalSelectedText-${id}">${bodyText}</div>
                                            </div>
                                            <button class="btn btn-sm btn-outline-danger" onclick="modalToggleSelect(${id})">
                                                <i class="bi bi-x-circle"></i>
                                            </button>
                                        </div>
                                    </div>`;
            });
            wrap.innerHTML = html;

            // figure out which ids need fetching
            const toFetch = modalSelected.filter(id => {
                const q = questionsMap && questionsMap[id] ? questionsMap[id] : null;
                return !(q && q.text);
            });

            if (toFetch.length === 0) return;

            // fetch details
            await Promise.all(toFetch.map(async id => {
                const q = await fetchQuestionById(id);
                if (q && q.text) {
                    const el = document.getElementById(`modalSelectedText-${id}`);
                    if (el) el.innerHTML = escapeHtml(q.text);
                    const smallEl = document.querySelector(`#modalSelectedItem-${id} small.text-muted`);
                    if (smallEl && q.difficulty && q.type) smallEl.innerText = `${q.difficulty} — ${q.type}`;
                } else {
                    const el = document.getElementById(`modalSelectedText-${id}`);
                    if (el) el.innerHTML = `Soal #${id}`;
                }
            }));
        }

        // count distribusi di selectedArea
        function updateCounter() {
            let easy = 0, medium = 0, hard = 0;
            document.querySelectorAll("#selectedArea .p-2").forEach(item => {
                const small = item.querySelector("small");
                if (!small) return;
                let diff = small.innerText.split("—")[0].trim().toLowerCase();
                if (diff.includes("easy") || diff.includes("mudah")) easy++;
                else if (diff.includes("medium") || diff.includes("sedang")) medium++;
                else if (diff.includes("hard") || diff.includes("sulit")) hard++;
            });

            document.getElementById("cnt-easy") && (document.getElementById("cnt-easy").innerText = easy);
            document.getElementById("cnt-medium") && (document.getElementById("cnt-medium").innerText = medium);
            document.getElementById("cnt-hard") && (document.getElementById("cnt-hard").innerText = hard);
        }

        // ketika modal dibuka: sinkronkan tombol aksi di modal dengan modalSelected
        const soalModalEl = document.getElementById('soalModal');
        if (soalModalEl) {
            soalModalEl.addEventListener('show.bs.modal', function () {
                // build questionsMap from table rows
                let questionsMap = {};
                document.querySelectorAll('#modalQuestionList tr').forEach(tr => {
                    const qid = parseInt(tr.dataset.qid);
                    const tds = tr.querySelectorAll('td');
                    // struktur kolom: Aksi(0), No(1), Tipe(2), Kesulitan(3), Pertanyaan(4)
                    const tipe = tds[2] ? tds[2].innerText.trim() : '';
                    const diff = tds[3] ? tds[3].innerText.trim() : '';
                    const txt = tds[4] ? tds[4].innerText.trim() : '';
                    questionsMap[qid] = { id: qid, type: tipe, difficulty: diff, text: txt };
                });

                // sync modal buttons icons (jika id ada di modalSelected => x / btn-danger)
                document.querySelectorAll('#modalQuestionList tr').forEach(tr => {
                    const id = parseInt(tr.dataset.qid);
                    const btn = tr.querySelector('button');
                    if (!btn) return;
                    if (modalSelected.includes(id)) {
                        btn.classList.remove('btn-success');
                        btn.classList.add('btn-danger');
                        btn.innerHTML = `<i class="bi bi-x-circle"></i>`;
                    } else {
                        btn.classList.remove('btn-danger');
                        btn.classList.add('btn-success');
                        btn.innerHTML = `<i class="bi bi-plus-circle"></i>`;
                    }
                });

                // render preview only if preview container exists
                renderModalSelected(questionsMap);
            });
        }

        // toggle select di modal (klik tombol + / x)
        function modalToggleSelect(id) {
            id = parseInt(id);
            const row = document.getElementById('modalRow-' + id);
            if (!row) return;
            const btn = row.querySelector('button');

            if (modalSelected.includes(id)) {
                modalSelected = modalSelected.filter(x => x !== id);
                // ubah tombol ke plus
                if (btn) {
                    btn.classList.remove('btn-danger');
                    btn.classList.add('btn-success');
                    btn.innerHTML = `<i class="bi bi-plus-circle"></i>`;
                }
            } else {
                modalSelected.push(id);
                if (btn) {
                    btn.classList.remove('btn-success');
                    btn.classList.add('btn-danger');
                    btn.innerHTML = `<i class="bi bi-x-circle"></i>`;
                }
            }

            // rebuild modal preview using available question texts in table (if preview exists)
            let questionsMap = {};
            document.querySelectorAll('#modalQuestionList tr').forEach(tr => {
                const qid = parseInt(tr.dataset.qid);
                const tds = tr.querySelectorAll('td');
                const tipe = tds[2] ? tds[2].innerText.trim() : '';
                const diff = tds[3] ? tds[3].innerText.trim() : '';
                const txt = tds[4] ? tds[4].innerText.trim() : '';
                questionsMap[qid] = { id: qid, type: tipe, difficulty: diff, text: txt };
            });
            renderModalSelected(questionsMap);
        }

        // =======================
        // SAFELY-ATTACH BUTTON HANDLERS (null-safe)
        // =======================

        // helper: update modal row buttons based on modalSelected
        function syncModalRowButtons() {
            document.querySelectorAll('#modalQuestionList tr').forEach(tr => {
                const id = parseInt(tr.dataset.qid);
                const btn = tr.querySelector('button');
                if (!btn) return;
                if (modalSelected.includes(id)) {
                    btn.classList.remove('btn-success'); btn.classList.add('btn-danger');
                    btn.innerHTML = `<i class="bi bi-x-circle"></i>`;
                } else {
                    btn.classList.remove('btn-danger'); btn.classList.add('btn-success');
                    btn.innerHTML = `<i class="bi bi-plus-circle"></i>`;
                }
            });
        }

        // BERSIHKAN semua pilihan di modal (Unselect All)
        const btnUnselect = document.getElementById('btnUnselectAllModal');
        if (btnUnselect) {
            btnUnselect.addEventListener('click', function () {
                modalSelected = [];
                // ubah semua tombol baris menjadi plus
                document.querySelectorAll('#modalQuestionList tr').forEach(tr => {
                    const btn = tr.querySelector('button');
                    if (!btn) return;
                    btn.classList.remove('btn-danger'); btn.classList.add('btn-success');
                    btn.innerHTML = `<i class="bi bi-plus-circle"></i>`;
                });
                renderModalSelected();
            });
        }

        // RESET modal ke selection awal dari server (jika ada tombol)
        const btnReset = document.getElementById('btnResetModal');
        if (btnReset) {
            btnReset.addEventListener('click', function () {
                modalSelected = @json($selectedIds);
                syncModalRowButtons();
                renderModalSelected();
            });
        }

        

        // Terapkan pilihan di modal -> ke halaman + simpan ke DB (safety: attach only if exists)
        const btnApply = document.getElementById('btnApplyToActivity');
        if (btnApply) {
            btnApply.addEventListener('click', function () {
                fetch("{{ url('/guru/simpan-atur-soal/' . $aktivitas->id) }}", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": CSRF
                    },
                    body: JSON.stringify({ id_question: modalSelected })
                })
                    .then(r => r.json())
                    .then(res => {
                        if (res.success) {
                            // gunakan DOM modal table utk membangun questionsMap bila server tidak mengembalikan detail
                            let questionsMap = {};
                            document.querySelectorAll('#modalQuestionList tr').forEach(tr => {
                                const id = parseInt(tr.dataset.qid);
                                const tds = tr.querySelectorAll('td');
                                const tipe = tds[2] ? tds[2].innerText.trim() : '';
                                const diff = tds[3] ? tds[3].innerText.trim() : '';
                                const txt = tds[4] ? tds[4].innerText.trim() : '';
                                questionsMap[id] = { id, type: tipe, difficulty: diff, text: txt };
                            });

                            // update page selected area with modalSelected
                            renderSelectedArea(modalSelected, questionsMap);

                            // sync global lastPicked
                            window.lastPicked = modalSelected.slice();

                            // close modal (if open)
                            const modal = document.getElementById('soalModal');
                            if (modal) {
                                const inst = bootstrap.Modal.getInstance(modal);
                                if (inst) inst.hide();
                            }

                            Swal.fire('Berhasil', res.message || 'Soal diterapkan ke aktivitas.', 'success');
                        } else {
                            Swal.fire('Gagal', res.message || 'Tidak dapat menyimpan pilihan.', 'error');
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        Swal.fire('Error', 'Terjadi kesalahan saat menyimpan pilihan.', 'error');
                    });
            });
        }

        // HAPUS dari halaman (fungsi lama, tetap memanggil server)
        function hapusDariTerpilih(id) {
            fetch("{{ url('/guru/hapus-soal-manual/' . $aktivitas->id) }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": CSRF
                },
                body: JSON.stringify({ id_question: id })
            })
                .then(r => r.json())
                .then(res => {
                    if (res.success) {
                        // remove from window.lastPicked & modalSelected
                        window.lastPicked = window.lastPicked.filter(x => x !== id);
                        modalSelected = modalSelected.filter(x => x !== id);
                        const el = document.getElementById('selectedItem-' + id);
                        if (el) el.remove();

                        // jika ada baris di modal, ubah tombol menjadi plus
                        const rowBtn = document.querySelector(`#modalRow-${id} button`);
                        if (rowBtn) {
                            rowBtn.classList.remove('btn-danger'); rowBtn.classList.add('btn-success');
                            rowBtn.innerHTML = `<i class="bi bi-plus-circle"></i>`;
                        }

                        if ((window.lastPicked || []).length === 0) {
                            renderSelectedArea([]);
                        }

                        document.getElementById('currentTotal') && (document.getElementById('currentTotal').innerText = (window.lastPicked || []).length);
                        updateCounter();
                    } else {
                        Swal.fire('Gagal', res.message || 'Tidak dapat menghapus soal.', 'error');
                    }
                })
                .catch(err => {
                    console.error(err);
                    Swal.fire('Error', 'Terjadi kesalahan jaringan.', 'error');
                });
        }

        // CLEAR ALL (endpoint lama)
        function clearAll() {
            Swal.fire({
                title: "Hapus Semua?",
                text: "Semua soal terpilih akan dihapus.",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#d33",
                cancelButtonColor: "#3085d6",
                confirmButtonText: "Ya, hapus!"
            }).then((result) => {
                if (!result.isConfirmed) return;

                fetch("{{ url('/guru/clear-all/' . $aktivitas->id) }}", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": CSRF
                    }
                })
                    .then(r => r.json())
                    .then(res => {
                        if (res.success) {
                            modalSelected = [];
                            window.lastPicked = [];
                            renderSelectedArea([]);
                            // reset modal buttons to plus
                            document.querySelectorAll('#modalQuestionList tr').forEach(tr => {
                                const btn = tr.querySelector('button');
                                if (!btn) return;
                                btn.classList.remove('btn-danger'); btn.classList.add('btn-success');
                                btn.innerHTML = `<i class="bi bi-plus-circle"></i>`;
                            });
                            document.getElementById('currentTotal') && (document.getElementById('currentTotal').innerText = 0);
                            updateCounter();
                            Swal.fire("Berhasil!", "Semua soal terpilih telah dihapus.", "success");
                        } else {
                            Swal.fire('Gagal', res.message || 'Tidak dapat menghapus semua.', 'error');
                        }
                    });
            });
        }
        // SIMPAN pilihan (pakai window.lastPicked)
        // ------------- CACHE JUMLAH SOAL (selectedN) & RADIO HELPERS -------------
        let selectedN = null;

        function readCheckedN() {
            const modalRadio = document.querySelector('input[name="modalJumlahRadio"]:checked');
            if (modalRadio) return parseInt(modalRadio.value, 10);
            const pageRadio = document.querySelector('input[name="jumlahRadio"]:checked');
            if (pageRadio) return parseInt(pageRadio.value, 10);
            return null;
        }

        function attachRadioHandlers() {
            document.querySelectorAll('input[name="modalJumlahRadio"]').forEach(r => {
                r.addEventListener('change', function () { selectedN = parseInt(this.value, 10); });
            });
            document.querySelectorAll('input[name="jumlahRadio"]').forEach(r => {
                r.addEventListener('change', function () { selectedN = parseInt(this.value, 10); });
            });
        }

        // ------------- AMBIL SOAL OTOMATIS (AMEND) -------------
        const btnAmbil = document.getElementById('btnAmbilModal');
        if (btnAmbil) {
            btnAmbil.addEventListener('click', function () {
                const selectedRadio = document.querySelector('input[name="modalJumlahRadio"]:checked');
                if (!selectedRadio) {
                    Swal.fire('Pilih jumlah soal terlebih dahulu', '', 'warning');
                    return;
                }
                const jumlah = parseInt(selectedRadio.value, 10);

                // cache jumlah supaya validasi nanti tahu n walau radio tidak ter-check
                selectedN = jumlah;

                let payload = { jumlah: jumlah, adaptive: {{ $aktivitas->addaptive === 'yes' ? 'true' : 'false' }} };

                if ({{ $aktivitas->addaptive === 'yes' ? 'true' : 'false' }}) {
                    payload.easy = Math.max(0, jumlah - 2);
                    payload.medium = Math.max(0, jumlah);
                    payload.hard = Math.max(0, jumlah - 2);
                }

                fetch("{{ url('/guru/ambil-soal/' . $aktivitas->id) }}", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": CSRF
                    },
                    body: JSON.stringify(payload)
                })
                    .then(r => r.json())
                    .then(res => {
                        if (!res || !res.data) {
                            Swal.fire('Gagal mengambil soal', '', 'error');
                            return;
                        }

                        modalSelected = res.data.map(q => q.id);
                        syncModalRowButtons();

                        // render preview jika ada container preview
                        let questionsMap = {};
                        res.data.forEach(q => {
                            questionsMap[q.id] = { id: q.id, type: q.type || '', difficulty: q.difficulty || '', text: q.text || '' };
                        });
                        renderModalSelected(questionsMap);

                        Swal.fire('Sukses', 'Soal otomatis telah dipilih di modal.', 'success');
                    })
                    .catch(err => {
                        console.error(err);
                        Swal.fire('Error', 'Terjadi kesalahan saat mengambil soal.', 'error');
                    });
            });
        }

        // ------------- SIMPAN PILIHAN (AMEND) -------------
        function simpanPilihan() {
            const isAdaptive = {{ $aktivitas->addaptive === 'yes' ? 'true' : 'false' }};

            // Determine n: checked radio -> cached selectedN -> lastPicked length
            let n = readCheckedN();
            if (!n && selectedN) n = selectedN;
            if (!n) n = (window.lastPicked || []).length || null;

            if (isAdaptive) {
                if (!n || n <= 0) {
                    Swal.fire('Perlu memilih jumlah soal', 'Silakan pilih jumlah soal (radio) atau gunakan Ambil Soal Otomatis dulu.', 'warning');
                    return;
                }

                const reqEasy = Math.max(0, n - 2);
                const reqMedium = Math.max(0, n);
                const reqHard = Math.max(0, n - 2);
                const totalRequired = reqEasy + reqMedium + reqHard;

                const currentEasy = parseInt(document.getElementById('cnt-easy')?.innerText || '0', 10);
                const currentMedium = parseInt(document.getElementById('cnt-medium')?.innerText || '0', 10);
                const currentHard = parseInt(document.getElementById('cnt-hard')?.innerText || '0', 10);
                const currentTotal = (window.lastPicked || []).length;

                if (currentTotal !== totalRequired) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Total soal tidak sesuai',
                        html: `
                            Total soal saat ini: <b>${currentTotal}</b><br>
                            Total soal yang dibutuhkan untuk jumlah soal sebanyak ${n} soal adaptive adalah <b>${totalRequired} soal</b><br>
                            (Mudah: ${currentEasy} / ${reqEasy})<br>
                            (Sedang: ${currentMedium} / ${reqMedium})<br>
                            (Sulit: ${currentHard} / ${reqHard})
                        `
                    });
                    return;
                }

                const errors = [];
                if (currentEasy !== reqEasy) errors.push(`Mudah: ${currentEasy} / ${reqEasy}`);
                if (currentMedium !== reqMedium) errors.push(`Sedang: ${currentMedium} / ${reqMedium}`);
                if (currentHard !== reqHard) errors.push(`Sulit: ${currentHard} / ${reqHard}`);

                if (errors.length > 0) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Distribusi soal tidak sesuai',
                        html: errors.join('<br>')
                    });
                    return;
                }
            }

            // Lakukan simpan
            fetch("{{ url('/guru/simpan-atur-soal/' . $aktivitas->id) }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": CSRF
                },
                body: JSON.stringify({ id_question: window.lastPicked })
            })
                .then(r => r.json())
                .then(res => {
                    if (res.success) {
                        Swal.fire({ icon: 'success', title: 'Berhasil Disimpan' });
                    } else {
                        Swal.fire({ icon: 'error', title: 'Gagal Menyimpan', text: res.message || '' });
                    }
                })
                .catch(err => {
                    console.error(err);
                    Swal.fire('Error', 'Terjadi kesalahan saat menyimpan.', 'error');
                });
        }



        // Inisialisasi render awal
        document.addEventListener('DOMContentLoaded', function () {
            renderSelectedArea(window.lastPicked);
            attachRadioHandlers();

            // juga set initial state tombol di modal agar sesuai dengan selectedIds
            document.querySelectorAll('#modalQuestionList tr').forEach(tr => {
                const id = parseInt(tr.dataset.qid);
                const btn = tr.querySelector('button');
                if (!btn) return;
                if (window.lastPicked.includes(id)) {
                    btn.classList.remove('btn-success'); btn.classList.add('btn-danger');
                    btn.innerHTML = `<i class="bi bi-x-circle"></i>`;
                } else {
                    btn.classList.remove('btn-danger'); btn.classList.add('btn-success');
                    btn.innerHTML = `<i class="bi bi-plus-circle"></i>`;
                }
            });

            updateCounter();
        });
    </script>

@endsection