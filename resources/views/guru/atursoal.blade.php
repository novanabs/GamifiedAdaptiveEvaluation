@extends('layouts.main')
@section('dataAktivitas', request()->is('guru/aktivitas/*/atur-soal') ? 'active' : '')

@section('content')
    <div class="container mt-4">

        <h3 class="fw-bold mb-3">Atur Soal untuk: {{ $aktivitas->title }}</h3>

        <a href="{{ url('/dataaktivitas') }}" class="btn btn-secondary mb-3">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>

        <div class="row">

            {{-- KIRI – Soal Terpilih --}}
            <div class="col-md-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white fw-semibold">
                        Soal Terpilih
                    </div>

                    <div class="card-body" id="selectedArea" style="height:450px; overflow-y:auto;">
                        @foreach($selectedQuestions as $s)
                            @php $sData = json_decode($s->question); @endphp
                            <div class="p-2 border rounded mb-2 bg-light d-flex justify-content-between"
                                id="selectedItem-{{ $s->id }}">

                                <div>
                                    <small class="text-muted">{{ $s->difficulty }} — {{ $s->type }}</small>
                                    <div>{{ $sData->text ?? '-' }}</div>
                                </div>

                                <button class="btn btn-sm btn-danger" onclick="hapusDariTerpilih({{ $s->id }})">
                                    <i class="bi bi-x-circle"></i>
                                </button>
                            </div>
                        @endforeach
                    </div>

                    <div class="card-footer">

                        <div class="mb-2">
                            <div>Mudah: <span id="cnt-easy">0</span></div>
                            <div>Sedang: <span id="cnt-medium">0</span></div>
                            <div>Sulit: <span id="cnt-hard">0</span></div>
                        </div>



                        <label class="fw-semibold">Pilih Jumlah Soal</label>
                        <div class="d-flex gap-2 flex-wrap mb-2">
                            @foreach ([5, 10, 15, 20, 25, 30] as $opt)
                                <label class="btn btn-outline-primary">
                                    <input type="radio" name="jumlahRadio" value="{{ $opt }}" class="me-1">
                                    {{ $opt }}
                                </label>
                            @endforeach
                        </div>
                        <input type="hidden" id="jumlah">




                        <button class="btn btn-primary w-100 mb-2" onclick="ambilSoal()">
                            Ambil Soal Otomatis
                        </button>

                        <button class="btn btn-success w-100" onclick="simpanPilihan()">
                            Simpan Pilihan
                        </button>
                    </div>

                </div>
            </div>

            {{-- KANAN – Daftar Semua Soal --}}
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-header fw-semibold bg-light">
                        Daftar Soal
                    </div>

                    <div class="card-body" style="height:550px; overflow-y:auto;">
                        <table class="table table-bordered">
                            <thead class="table-secondary text-center">
                                <tr>
                                    <th>Aksi</th>
                                    <th>No</th>
                                    <th>Tipe</th>
                                    <th>Kesulitan</th>
                                    <th>Pertanyaan</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach ($questions as $q)
                                                        @php $qData = json_decode($q->question); @endphp
                                                        <tr id="rowRight-{{ $q->id }}">
                                                            <td class="text-center">
                                                                <button
                                                                    class="btn btn-sm {{ in_array($q->id, $selectedIds) ? 'btn-danger' : 'btn-success' }}"
                                                                    onclick="{{ in_array($q->id, $selectedIds)
                                    ? 'hapusDariTerpilih(' . $q->id . ')'
                                    : 'tambahKeTerpilih(' . $q->id . ')' }}">
                                                                    <i
                                                                        class="bi {{ in_array($q->id, $selectedIds) ? 'bi-x-circle' : 'bi-plus-circle' }}"></i>
                                                                </button>
                                                            </td>
                                                            <td class="text-center">{{ $loop->iteration }}</td>
                                                            <td>{{ $q->type }}</td>
                                                            <td>{{ $q->difficulty }}</td>
                                                            <td>{{ $qData->text ?? '-' }}</td>
                                                        </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>

        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


    <script>
        const ADDAPTIVE = "{{ $aktivitas->addaptive }}";

        window.lastPicked = @json($selectedIds);



        function updateCounter() {
            let easy = 0, medium = 0, hard = 0;

            document.querySelectorAll("#selectedArea .p-2").forEach(item => {
                let diff = item.querySelector("small").innerText.split("—")[0].trim().toLowerCase();

                if (diff.includes("easy") || diff.includes("mudah")) easy++;
                if (diff.includes("medium") || diff.includes("sedang")) medium++;
                if (diff.includes("hard") || diff.includes("sulit")) hard++;
            });

            // tampilkan ke counter
            document.getElementById("cnt-easy").innerText = easy;
            document.getElementById("cnt-medium").innerText = medium;
            document.getElementById("cnt-hard").innerText = hard;

            // 🔥 Isi input jumlah otomatis
            document.getElementById("jumlah").value = easy + medium + hard;
        }



        document.addEventListener("DOMContentLoaded", updateCounter);

        /* =============================
           ➕ TAMBAH SOAL MANUAL
        ============================= */
        function tambahKeTerpilih(id) {

            fetch("{{ url('/guru/tambah-soal-manual/' . $aktivitas->id) }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({ id_question: id })
            })
                .then(r => r.json())
                .then(res => {
                    if (res.success) {

                        // Pastikan tidak duplicate
                        if (!window.lastPicked.includes(id)) {
                            window.lastPicked.push(id);
                        }

                        // Update tombol di kanan
                        let row = document.querySelector("#rowRight-" + id);
                        let btn = row.querySelector("button");
                        btn.classList.remove("btn-success");
                        btn.classList.add("btn-danger");
                        btn.innerHTML = `<i class="bi bi-x-circle"></i>`;
                        btn.setAttribute("onclick", `hapusDariTerpilih(${id})`);

                        // Ambil isi soal dan tambahkan ke kiri
                        fetch("/get-question/" + id)
                            .then(r => r.json())
                            .then(q => {

                                // Jika sudah ada, jangan duplikat
                                if (!document.querySelector("#selectedItem-" + id)) {
                                    document.querySelector("#selectedArea").innerHTML += `
                                                                                                                            <div class="p-2 border rounded mb-2 bg-light d-flex justify-content-between"
                                                                                                                                 id="selectedItem-${q.id}">
                                                                                                                                <div>
                                                                                                                                    <small class="text-muted">${q.difficulty} — ${q.type}</small>
                                                                                                                                    <div>${q.text}</div>

                                                                                                                                </div>
                                                                                                                                <button class="btn btn-sm btn-danger" onclick="hapusDariTerpilih(${q.id})">
                                                                                                                                    <i class="bi bi-x-circle"></i>
                                                                                                                                </button>
                                                                                                                            </div>
                                                                                                                        `;
                                }
                                updateCounter();
                            });
                    }
                });
        }



        /* =============================
           ❌ HAPUS DARI TERPILIH
        ============================= */
        function hapusDariTerpilih(id) {

            fetch("{{ url('/guru/hapus-soal-manual/' . $aktivitas->id) }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({ id_question: id })
            })
                .then(r => r.json())
                .then(res => {
                    if (res.success) {
                        window.lastPicked = window.lastPicked.filter(x => x !== id);

                        let item = document.querySelector("#selectedItem-" + id);
                        if (item) item.remove();

                        let row = document.querySelector("#rowRight-" + id);
                        let btn = row.querySelector("button");

                        btn.classList.remove("btn-danger");
                        btn.classList.add("btn-success");
                        btn.innerHTML = `<i class="bi bi-plus-circle"></i>`;
                        btn.setAttribute("onclick", `tambahKeTerpilih(${id})`);
                    }
                    updateCounter();
                });
        }

        /* =============================
           🎯 AMBIL SOAL OTOMATIS (AJAX)
        ============================= */
        function ambilSoal() {

            let jumlah;

            // ADDAPTIVE MODE → pakai radio preset
            if (ADDAPTIVE === "yes") {
                let selected = document.querySelector('input[name="jumlahRadio"]:checked');
                if (!selected) return alert("Pilih jumlah soal terlebih dahulu.");

                jumlah = parseInt(selected.value);
                document.getElementById("jumlah").value = jumlah;

                // hitung pembagian (easy - medium - hard)
                const n = jumlah;

                var easy = n - 2;
                var hard = n - 2;
                var medium = n + 1;

                // kirim ke backend
                var payload = {
                    jumlah: jumlah,
                    adaptive: true,
                    easy: easy,
                    medium: medium,
                    hard: hard
                };
            }

            // NORMAL MODE → bebas jumlah
            else {
                // NORMAL MODE → sama seperti adaptive, pakai radio
                let selected = document.querySelector('input[name="jumlahRadio"]:checked');
                if (!selected) return alert("Pilih jumlah soal terlebih dahulu.");

                jumlah = parseInt(selected.value);
                document.getElementById("jumlah").value = jumlah;

                var payload = {
                    jumlah: jumlah,
                    adaptive: false
                };
            }


            fetch("{{ url('/guru/ambil-soal/' . $aktivitas->id) }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify(payload)
            })
                .then(r => r.json())
                .then(res => {

                    window.lastPicked = res.data.map(q => q.id);

                    const area = document.getElementById("selectedArea");
                    area.innerHTML = "";

                    res.data.forEach(x => {
                        area.innerHTML += `
                                            <div class="p-2 border rounded mb-2 bg-light d-flex justify-content-between"
                                                id="selectedItem-${x.id}">
                                                <div>
                                                    <small class="text-muted">${x.difficulty} — ${x.type}</small>
                                                    <div>${x.text}</div>
                                                </div>

                                                <button class="btn btn-sm btn-danger"
                                                    onclick="hapusDariTerpilih(${x.id})">
                                                    <i class="bi bi-x-circle"></i>
                                                </button>
                                            </div>
                                        `;
                    });

                    // update tombol kanan
                    document.querySelectorAll("tbody tr").forEach(tr => {
                        let id = parseInt(tr.id.replace("rowRight-", ""));
                        let btn = tr.querySelector("button");

                        if (window.lastPicked.includes(id)) {
                            btn.className = "btn btn-sm btn-danger";
                            btn.innerHTML = `<i class="bi bi-x-circle"></i>`;
                            btn.setAttribute("onclick", `hapusDariTerpilih(${id})`);
                        } else {
                            btn.className = "btn btn-sm btn-success";
                            btn.innerHTML = `<i class="bi bi-plus-circle"></i>`;
                            btn.setAttribute("onclick", `tambahKeTerpilih(${id})`);
                        }
                    });

                    updateCounter();
                });
        }


        function validateManualSelection() {
            const jumlahDipilih = window.lastPicked.length;

            let selectedRadio = document.querySelector('input[name="jumlahRadio"]:checked');
            if (!selectedRadio) {
                Swal.fire("Pilih jumlah terlebih dahulu!", "", "warning");
                return false;
            }

            const n = parseInt(selectedRadio.value);

            let easy = parseInt(document.getElementById("cnt-easy").innerText);
            let medium = parseInt(document.getElementById("cnt-medium").innerText);
            let hard = parseInt(document.getElementById("cnt-hard").innerText);

            if (ADDAPTIVE === "yes") {

                const reqEasy = Math.max(0, n - 2);
                const reqHard = Math.max(0, n - 2);
                const reqMedium = Math.max(0, n + 1);

                const totalRequired = reqEasy + reqMedium + reqHard;

                // ❌ Perbaikan utama → cek total required, bukan n
                if (jumlahDipilih !== totalRequired) {
                    Swal.fire({
                        icon: "warning",
                        title: "Total Soal Tidak Sesuai",
                        html: `
                            Total soal yang dipilih: <b>${jumlahDipilih}</b><br>
                            Total soal yang seharusnya: <b>${totalRequired}</b><br><br>
                            (Mudah: ${reqEasy}, Sedang: ${reqMedium}, Sulit: ${reqHard})
                        `,
                    });
                    return false;
                }

                // cek distribusi
                let pesan = [];
                if (easy !== reqEasy) pesan.push(`Mudah: ${easy} / ${reqEasy}`);
                if (medium !== reqMedium) pesan.push(`Sedang: ${medium} / ${reqMedium}`);
                if (hard !== reqHard) pesan.push(`Sulit: ${hard} / ${reqHard}`);

                if (pesan.length > 0) {
                    Swal.fire({
                        icon: "error",
                        title: "Pembagian Tidak Sesuai",
                        html: pesan.join("<br>")
                    });
                    return false;
                }

                return true;
            }

            // MODE NORMAL
            else {
                if (jumlahDipilih !== n) {
                    Swal.fire({
                        icon: "warning",
                        title: "Jumlah Soal Tidak Sesuai",
                        html: `Dipilih <b>${jumlahDipilih}</b> soal, tetapi harus <b>${n}</b>.`,
                    });
                    return false;
                }
                return true;
            }
        }


        /* =============================
           💾 SIMPAN KE DATABASE
        ============================= */
        function simpanPilihan() {

            if (!validateManualSelection()) return;

            fetch("{{ url('/guru/simpan-atur-soal/' . $aktivitas->id) }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({ id_question: window.lastPicked })
            })
                .then(r => r.json())
                .then(res => {
                    Swal.fire({
                        icon: res.success ? "success" : "error",
                        title: res.success ? "Berhasil Disimpan" : "Gagal Menyimpan",
                    });
                });
        }


    </script>
@endsection