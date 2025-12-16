@extends('layouts.main')
@section('dataAktivitas', request()->is('guru/aktivitas/*/atur-soal') ? 'active' : '')

@section('content')
    <div class="container mt-4">

        <div class="d-flex align-items-center gap-2 mb-3">
            <h3 class="fw-bold mb-0 d-flex align-items-center gap-2 flex-wrap">
                Atur Soal untuk: {{ $aktivitas->title }}

                @if($aktivitas->addaptive === 'yes')
                    <span class="badge bg-success">
                        <i class="bi bi-cpu me-1"></i> Adaptif
                    </span>
                @else
                    <span class="badge bg-secondary">
                        <i class="bi bi-slash-circle me-1"></i> Non-Adaptif
                    </span>
                @endif
            </h3>

            <button type="button"
                class="btn btn-sm btn-outline-secondary rounded-circle d-flex align-items-center justify-content-center"
                style="width:32px;height:32px" data-bs-toggle="modal" data-bs-target="#modalInfoAturSoal"
                title="Informasi Pengaturan Soal">
                <i class="bi bi-info-lg"></i>
            </button>
        </div>


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

    {{-- MODAL: Daftar Soal (rapi + responsif) --}}
    <div class="modal fade" id="soalModal" tabindex="-1" aria-labelledby="soalModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Daftar Soal — {{ $aktivitas->title }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>

                <div class="modal-body">
                    <div class="card border-0 mb-0">
                        <div class="card-body p-3">

                            {{-- Kontrol atas (kiri: jumlah & tombol, kanan: instruksi singkat) --}}
                            <div class="d-flex flex-column flex-md-row gap-3 align-items-start mb-3">
                                <div>
                                    <h6 class="mb-2 text-muted">Pilih Jumlah Soal</h6>

                                    @php $savedJumlah = $aktivitas->jumlah_soal ?? null; @endphp

                                    <div class="btn-group btn-group-sm mb-2" role="group" aria-label="jumlah soal">
                                        @foreach ([5, 10, 15, 20, 25, 30] as $opt)
                                            <label class="btn btn-outline-primary {{ $savedJumlah == $opt ? 'active' : '' }}">
                                                <input type="radio" name="modalJumlahRadio" value="{{ $opt }}" class="me-1" {{ $savedJumlah == $opt ? 'checked' : '' }}>
                                                {{ $opt }}
                                            </label>
                                        @endforeach
                                    </div>


                                    <div class="d-flex flex-wrap gap-2">
                                        <button class="btn btn-primary btn-sm" id="btnAmbilModal">Ambil Soal
                                            Otomatis</button>

                                        <button class="btn btn-outline-primary btn-sm" id="btnSelectAllModal"
                                            title="Pilih semua soal pada daftar">
                                            <i class="bi bi-check2-all me-1"></i> Ambil Semua
                                        </button>

                                        <button class="btn btn-outline-secondary btn-sm"
                                            id="btnUnselectAllModal">Bersihkan</button>
                                    </div>
                                </div>


                            </div>

                            <hr class="my-2">

                            {{-- Header daftar soal --}}
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="mb-0 text-muted">Daftar Semua Soal</h6>
                                <div class="small text-muted">Total: <span id="modalTotalCount">{{ $questions->count() }}
                                        Soal</span></div>
                            </div>

                            {{-- tabel scrollable (table kecil + compact) --}}
                            <div style="max-height:540px; overflow:auto;">
                                <table class="table table-sm table-bordered mb-0 align-middle">
                                    <thead class="table-light text-center sticky-top" style="top:0; z-index:1;">
                                        <tr>
                                            <th style="width:84px">Aksi</th>
                                            <th style="width:56px">No</th>
                                            <th style="min-width:120px">Tipe</th>
                                            <th style="min-width:100px">Kesulitan</th>
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
                                                        onclick="modalToggleSelect({{ $q->id }})"
                                                        aria-label="{{ in_array($q->id, $selectedIds) ? 'Unselect' : 'Select' }}">
                                                        <i
                                                            class="bi {{ in_array($q->id, $selectedIds) ? 'bi-x-circle' : 'bi-plus-circle' }}"></i>
                                                    </button>
                                                </td>

                                                <td class="text-center">{{ $loop->iteration }}</td>
                                                <td>{{ $q->type }}</td>
                                                <td>{{ $q->difficulty }}</td>
                                                <td style="white-space:normal;">{{ Str::limit($qData->text ?? '-', 300) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <div class="me-auto text-muted small">Pilih soal lalu tekan <strong>Terapkan ke Aktivitas</strong></div>
                    <button class="btn btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
                    <button class="btn btn-primary" id="btnApplyToActivity">Terapkan ke Aktivitas</button>
                </div>
            </div>
        </div>
    </div>
    {{-- MODAL INFO ATUR SOAL --}}
    <div class="modal fade" id="modalInfoAturSoal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content rounded-4 shadow">

                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-info-circle me-2"></i>
                        Panduan Mengatur Soal
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <p class="text-muted">
                        Halaman <strong>Atur Soal</strong> digunakan untuk menentukan
                        soal-soal yang akan digunakan pada aktivitas
                        <strong>{{ $aktivitas->title }}</strong>.
                    </p>
                    <hr>

                    <!-- TIPE SOAL -->
                    <h6 class="fw-bold text-dark">
                        <i class="bi bi-ui-checks me-1"></i>
                        Jenis / Tipe Soal
                    </h6>

                    <ul>
                        <li>
                            <strong>Multiple Choice (Pilihan Ganda)</strong>
                            <ul>
                                <li>Soal dengan beberapa pilihan jawaban (A, B, C, D, E).</li>
                                <li>Siswa memilih <strong>satu jawaban yang paling benar</strong>.</li>
                                <li>Penilaian dilakukan otomatis berdasarkan jawaban yang ditentukan guru.</li>
                            </ul>

                            <div class="bg-light rounded p-3 mt-2 mb-3">
                                <div class="fw-semibold mb-1">Contoh Soal Pilihan Ganda</div>
                                <p class="mb-1">
                                    Fungsi utama aplikasi spreadsheet adalah...
                                </p>
                                <ul class="mb-0">
                                    <li>A. Mengedit video</li>
                                    <li>B. Mengolah data dalam bentuk tabel</li>
                                    <li>C. Menggambar ilustrasi</li>
                                    <li>D. Membuat animasi</li>
                                </ul>
                                <div class="text-muted small mt-1">
                                    Jawaban benar: B
                                </div>
                            </div>
                        </li>

                        <li>
                            <strong>Short Answer (Isian Singkat)</strong>
                            <ul>
                                <li>Soal berupa isian singkat tanpa pilihan jawaban.</li>
                                <li>Siswa menuliskan jawaban sendiri dalam bentuk teks.</li>
                                <li>Penilaian dilakukan berdasarkan <strong>kata kunci jawaban</strong>
                                    yang sudah ditentukan oleh guru.</li>
                                <li>Satu soal dapat memiliki beberapa kata kunci jawaban yang dianggap benar.</li>
                            </ul>

                            <div class="bg-light rounded p-3 mt-2">
                                <div class="fw-semibold mb-1">Contoh Soal Isian Singkat</div>
                                <p class="mb-1">
                                    Sebutkan fungsi untuk menghitung rata-rata pada spreadsheet.
                                </p>
                                <div class="text-muted small">
                                    Kata kunci jawaban:
                                    <ul class="mb-0">
                                        <li>AVERAGE</li>
                                        <li>Average</li>
                                        <li>Rata-rata</li>
                                    </ul>
                                </div>
                            </div>
                        </li>
                    </ul>


                    <!-- RINCIAN POIN -->
                    <div class="bg-light rounded p-3 mb-3">
                        <h6 class="fw-bold text-primary mb-2">
                            <i class="bi bi-gem me-2"></i>
                            Poin Berdasarkan Tingkat Kesulitan
                        </h6>

                        <ul class="list-group list-group-flush">
                            <li class="list-group-item px-0">
                                Soal mudah: <strong>10 poin</strong>
                            </li>
                            <li class="list-group-item px-0">
                                Soal sedang: <strong>20 poin</strong>
                            </li>
                            <li class="list-group-item px-0">
                                Soal sulit: <strong>30 poin</strong>
                            </li>
                        </ul>

                        <p class="text-muted small mt-2 mb-0">
                            Semakin tinggi tingkat kesulitan soal yang dipilih,
                            semakin besar potensi poin yang dapat diperoleh siswa.
                        </p>
                    </div>

                    <hr>
                    <!-- LANGKAH 1 -->
                    <h6 class="fw-bold text-primary">
                        <i class="bi bi-1-circle me-1"></i>
                        Langkah 1 – Menentukan Jumlah Soal
                    </h6>
                    <ul>
                        <li>Langkah pertama yang <strong>wajib dilakukan</strong> adalah memilih
                            <strong>jumlah soal</strong> (5, 10, 15, 20, dst).
                        </li>
                        <li>Jumlah soal ini menjadi acuan untuk:
                            <ul>
                                <li>Pengambilan soal otomatis</li>
                                <li>Validasi aktivitas adaptif</li>
                                <li>Penyimpanan konfigurasi soal</li>
                            </ul>
                        </li>
                    </ul>

                    <div class="bg-light rounded p-3 mb-3">
                        <div class="fw-semibold mb-2">Ketentuan jika Aktivitas Adaptif</div>
                        <p class="mb-2">
                            Jika aktivitas berstatus <strong>Adaptif</strong> dan guru memilih
                            jumlah soal <strong>n</strong>, maka sistem menetapkan kebutuhan
                            minimal soal sebagai berikut:
                        </p>
                        <ul class="mb-2">
                            <li>Soal <strong>sedang</strong> : <strong>n</strong></li>
                            <li>Soal <strong>mudah</strong> : <strong>n − 2</strong></li>
                            <li>Soal <strong>sulit</strong> : <strong>n − 2</strong></li>
                        </ul>
                        <p class="mb-0">
                            Sehingga total minimal soal yang harus tersedia adalah:
                            <br>
                            <strong>n + (n − 2) + (n − 2) = 3n − 4</strong>
                        </p>
                    </div>

                    <p class="text-muted small">
                        Contoh: jika memilih n = 10, maka dibutuhkan minimal
                        10 soal sedang, 8 soal mudah, dan 8 soal sulit (total 26 soal).
                    </p>

                    <hr>

                    <!-- LANGKAH 2 -->
                    <h6 class="fw-bold text-success">
                        <i class="bi bi-2-circle me-1"></i>
                        Langkah 2 – Memilih Soal
                    </h6>
                    <ul>
                        <li>Klik tombol <strong>Lihat Soal</strong> untuk membuka daftar soal.</li>
                        <li>Guru dapat memilih soal dengan cara:
                            <ul>
                                <li><strong>Manual</strong>: klik tombol tambah (+) pada soal</li>
                                <li><strong>Otomatis</strong>: klik tombol <strong>Ambil Soal Otomatis</strong></li>
                                <li><strong>Ambil Semua</strong>: memilih seluruh soal di daftar</li>
                            </ul>
                        </li>
                        <li>Perubahan di dalam modal belum tersimpan sampai diterapkan.</li>
                    </ul>

                    <hr>

                    <!-- LANGKAH 3 -->
                    <h6 class="fw-bold text-warning">
                        <i class="bi bi-3-circle me-1"></i>
                        Langkah 3 – Ambil Soal Otomatis
                    </h6>
                    <ul>
                        <li>Pastikan jumlah soal sudah dipilih.</li>
                        <li>Klik tombol <strong>Ambil Soal Otomatis</strong>.</li>
                        <li>Jika aktivitas <strong>Non-Adaptif</strong>:
                            <ul>
                                <li>Soal diambil secara acak tanpa aturan distribusi khusus.</li>
                            </ul>
                        </li>
                        <li>Jika aktivitas <strong>Adaptif</strong>:
                            <ul>
                                <li>Sistem akan menyesuaikan jumlah soal mudah, sedang, dan sulit.</li>
                                <li>Distribusi minimal harus terpenuhi sebelum bisa disimpan.</li>
                            </ul>
                        </li>
                    </ul>

                    <hr>

                    <!-- LANGKAH 4 -->
                    <h6 class="fw-bold text-info">
                        <i class="bi bi-4-circle me-1"></i>
                        Langkah 4 – Terapkan ke Aktivitas
                    </h6>
                    <ul>
                        <li>Klik tombol <strong>Terapkan ke Aktivitas</strong> di modal.</li>
                        <li>Soal yang dipilih akan muncul di bagian <strong>Soal Terpilih</strong>.</li>
                        <li>Guru masih dapat menghapus atau menyesuaikan soal.</li>
                    </ul>

                    <hr>

                    <!-- LANGKAH 5 -->
                    <h6 class="fw-bold text-secondary">
                        <i class="bi bi-5-circle me-1"></i>
                        Langkah 5 – Simpan Pilihan
                    </h6>
                    <ul>
                        <li>Klik tombol <strong>Simpan Pilihan</strong> untuk menyimpan ke database.</li>
                        <li>Pada aktivitas <strong>Adaptif</strong>, sistem akan memvalidasi:
                            <ul>
                                <li>Total jumlah soal</li>
                                <li>Distribusi soal mudah, sedang, dan sulit</li>
                            </ul>
                        </li>
                        <li>Jika validasi tidak terpenuhi, penyimpanan akan dibatalkan dan
                            sistem akan menampilkan penjelasan kekurangannya.</li>
                    </ul>
                    <hr>

                    <!-- SOAL TERPILIH -->
                    <h6 class="fw-bold text-primary">
                        <i class="bi bi-clipboard-check me-1"></i>
                        Soal Terpilih
                    </h6>

                    <ul>
                        <li>Menampilkan semua soal yang saat ini terhubung dengan aktivitas.</li>
                        <li>Soal ditampilkan dengan informasi:
                            <ul>
                                <li>Tingkat kesulitan</li>
                                <li>Tipe soal</li>
                                <li>Cuplikan pertanyaan</li>
                            </ul>
                        </li>
                        <li>
                            Klik tombol
                            <i class="bi bi-x-circle text-danger"></i>
                            untuk menghapus soal dari aktivitas.
                        </li>
                    </ul>

                    <hr>



                    <!-- HAPUS SEMUA -->
                    <h6 class="fw-bold text-danger">
                        <i class="bi bi-trash me-1"></i>
                        Hapus Semua Pilihan
                    </h6>
                    <ul>
                        <li>Menghapus seluruh soal terpilih dari aktivitas.</li>
                        <li>Akan muncul konfirmasi sebelum penghapusan.</li>
                    </ul>

                    <hr>

                    <!-- SIMPAN -->
                    <h6 class="fw-bold text-secondary">
                        <i class="bi bi-save me-1"></i>
                        Simpan Pilihan
                    </h6>
                    <ul>
                        <li>Menyimpan konfigurasi soal ke database.</li>
                        <li>Pada aktivitas <strong>Adaptif</strong>, sistem akan memvalidasi:
                            <ul>
                                <li>Total soal</li>
                                <li>Distribusi kesulitan</li>
                            </ul>
                        </li>
                        <li>Jika validasi gagal, penyimpanan akan dibatalkan.</li>
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

                // ✅ VALIDASI JUMLAH SOAL
                const jumlahSoalEl = document.querySelector('input[name="modalJumlahRadio"]:checked');
                if (!jumlahSoalEl) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Jumlah soal belum dipilih',
                        text: 'Silakan pilih jumlah soal terlebih dahulu.',
                        confirmButtonText: 'OK'
                    });
                    return; // ⛔ hentikan proses
                }

                const jumlahSoal = parseInt(jumlahSoalEl.value);

                // (opsional) validasi tambahan
                if (!modalSelected || modalSelected.length === 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Soal belum dipilih',
                        text: 'Silakan pilih soal terlebih dahulu.',
                    });
                    return;
                }

                fetch("{{ url('/guru/simpan-atur-soal/' . $aktivitas->id) }}", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": CSRF
                    },
                    body: JSON.stringify({
                        id_question: modalSelected,
                        jumlah_soal: jumlahSoal
                    })
                })
                    .then(r => r.json())
                    .then(res => {
                        if (res.success) {

                            let questionsMap = {};
                            document.querySelectorAll('#modalQuestionList tr').forEach(tr => {
                                const id = parseInt(tr.dataset.qid);
                                const tds = tr.querySelectorAll('td');
                                questionsMap[id] = {
                                    id,
                                    type: tds[2]?.innerText.trim() || '',
                                    difficulty: tds[3]?.innerText.trim() || '',
                                    text: tds[4]?.innerText.trim() || ''
                                };
                            });

                            renderSelectedArea(modalSelected, questionsMap);
                            window.lastPicked = modalSelected.slice();

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
        // ambil nilai dari database (null jika belum ada)
        let selectedN = @json($aktivitas->jumlah_soal ?? null);

        // baca jumlah radio yang terpilih
        function readCheckedN() {
            const modalRadio = document.querySelector('input[name="modalJumlahRadio"]:checked');
            if (modalRadio) return parseInt(modalRadio.value, 10);
            return selectedN || null;
        }

        // helper: reset semua label radio (hilangkan class active)
        function clearRadioActiveVisuals() {
            document.querySelectorAll('input[name="modalJumlahRadio"]').forEach(r => {
                const lbl = r.closest('label');
                if (lbl) lbl.classList.remove('active');
                // juga reset aria-pressed
                if (lbl) lbl.setAttribute('aria-pressed', 'false');
            });
        }

        // pasang handler change ke semua radio supaya perubahan langsung terlihat
        function attachRadioHandlers() {
            document.querySelectorAll('input[name="modalJumlahRadio"]').forEach(r => {
                r.addEventListener('change', function () {
                    // set selectedN
                    selectedN = parseInt(this.value, 10);

                    // visual: hilangkan active dari semua, beri active ke yang sekarang
                    clearRadioActiveVisuals();
                    const lbl = this.closest('label');
                    if (lbl) {
                        lbl.classList.add('active');
                        lbl.setAttribute('aria-pressed', 'true');
                    }
                });

                // juga support click pada label (beberapa tema/bs versi tidak toggle label.active otomatis)
                const lbl = r.closest('label');
                if (lbl) {
                    lbl.addEventListener('click', function () {
                        // klik label biasanya akan check radio, tapi kita juga set visual untuk respons cepat
                        // small delay supaya radio.checked sudah ter-update
                        setTimeout(() => {
                            if (r.checked) {
                                clearRadioActiveVisuals();
                                lbl.classList.add('active');
                                lbl.setAttribute('aria-pressed', 'true');
                                selectedN = parseInt(r.value, 10);
                            }
                        }, 1);
                    });
                }
            });
        }

        // jalankan saat halaman siap
        document.addEventListener("DOMContentLoaded", () => {
            // jika DB punya jumlah_soal, aktifkan radio secara visual dan checked
            if (selectedN) {
                const savedRadio = document.querySelector(
                    `input[name="modalJumlahRadio"][value="${selectedN}"]`
                );

                if (savedRadio) {
                    // set checked & visual active
                    savedRadio.checked = true;
                    clearRadioActiveVisuals();
                    const lbl = savedRadio.closest("label");
                    if (lbl) {
                        lbl.classList.add("active");
                        lbl.setAttribute('aria-pressed', 'true');
                    }
                }
            }

            // pasang handler setelah DOM siap
            attachRadioHandlers();

            // fallback: jika user klik label dengan keyboard focus, sync visuals
            document.addEventListener('keydown', (e) => {
                if (e.key === ' ' || e.key === 'Enter') {
                    // beri sedikit delay, radio state sudah berubah
                    setTimeout(() => {
                        const checked = document.querySelector('input[name="modalJumlahRadio"]:checked');
                        if (checked) {
                            clearRadioActiveVisuals();
                            const lbl = checked.closest('label');
                            if (lbl) {
                                lbl.classList.add('active');
                                lbl.setAttribute('aria-pressed', 'true');
                                selectedN = parseInt(checked.value, 10);
                            }
                        }
                    }, 1);
                }
            });
        });


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
        // ======= Ambil Semua handler =======
        const btnSelectAll = document.getElementById('btnSelectAllModal');
        if (btnSelectAll) {
            btnSelectAll.addEventListener('click', function () {
                // ambil semua id dari baris table modal
                const allIds = Array.from(document.querySelectorAll('#modalQuestionList tr'))
                    .map(tr => parseInt(tr.dataset.qid))
                    .filter(Boolean);

                if (!allIds.length) {
                    Swal.fire('Kosong', 'Tidak ada soal pada daftar untuk dipilih.', 'info');
                    return;
                }

                // set modalSelected ke seluruh id yang ada
                modalSelected = allIds.slice();

                // ubah semua tombol baris di modal jadi 'x' (btn-danger)
                document.querySelectorAll('#modalQuestionList tr').forEach(tr => {
                    const btn = tr.querySelector('button');
                    if (!btn) return;
                    btn.classList.remove('btn-success'); btn.classList.add('btn-danger');
                    btn.innerHTML = `<i class="bi bi-x-circle"></i>`;
                });

                // render preview di modal (kita buat questionsMap dari DOM agar cepat)
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

                Swal.fire({ icon: 'success', title: 'Semua soal dipilih' });
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

                // aturan adaptive (minimal)
                const reqEasy = Math.max(0, n - 2);
                const reqMedium = Math.max(0, n);
                const reqHard = Math.max(0, n - 2);
                const totalRequired = reqEasy + reqMedium + reqHard;

                const currentEasy = parseInt(document.getElementById('cnt-easy')?.innerText || '0', 10);
                const currentMedium = parseInt(document.getElementById('cnt-medium')?.innerText || '0', 10);
                const currentHard = parseInt(document.getElementById('cnt-hard')?.innerText || '0', 10);
                const currentTotal = (window.lastPicked || []).length;

                // hitung kekurangan tiap kategori (hanya jika kurang)
                const lackEasy = Math.max(0, reqEasy - currentEasy);
                const lackMedium = Math.max(0, reqMedium - currentMedium);
                const lackHard = Math.max(0, reqHard - currentHard);

                const lackTotal = Math.max(0, totalRequired - currentTotal);

                if (lackEasy > 0 || lackMedium > 0 || lackHard > 0 || lackTotal > 0) {
                    let parts = [];
                    if (lackTotal > 0) {
                        parts.push(`Total soal kurang: <b>${lackTotal}</b> (dibutuhkan minimal ${totalRequired})`);
                    }
                    if (lackEasy > 0) parts.push(`Mudah kurang: <b>${lackEasy}</b> (dibutuhkan minimal ${reqEasy})`);
                    if (lackMedium > 0) parts.push(`Sedang kurang: <b>${lackMedium}</b> (dibutuhkan minimal ${reqMedium})`);
                    if (lackHard > 0) parts.push(`Sulit kurang: <b>${lackHard}</b> (dibutuhkan minimal ${reqHard})`);

                    Swal.fire({
                        icon: 'warning',
                        title: 'Distribusi soal belum memenuhi minimum adaptive',
                        html: parts.join('<br>')
                    });
                    return;
                }
                // jika semua minimal terpenuhi -> lanjut simpan
            }

            // Lakukan simpan (adaptive atau non-adaptive)
            // Kirim juga 'jumlah' (n) supaya disimpan ke activities.jumlah_soal
            fetch("{{ url('/guru/simpan-atur-soal/' . $aktivitas->id) }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": CSRF
                },
                body: JSON.stringify({ id_question: window.lastPicked, jumlah: n })
            })
                .then(r => r.json())
                .then(res => {
                    if (res.success) {
                        Swal.fire({ icon: 'success', title: 'Berhasil Disimpan' });
                        // opsional: update UI kalau mau menampilkan jumlah tersimpan:
                        // document.getElementById('savedJumlah')?.innerText = n;
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