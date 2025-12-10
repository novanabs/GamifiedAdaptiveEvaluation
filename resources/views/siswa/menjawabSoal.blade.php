<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $judul }} - Kuis</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        body {
            background-color: #f8f9fa;
        }

        .question-panel {
            background: #fff;
            border-radius: 1rem;
            padding: 2rem;
            box-shadow: 0 0 10px rgba(0, 0, 0, .1);
        }

        #timer {
            font-size: 1.4rem;
            font-weight: bold;
            color: #dc3545;
        }

        @keyframes firePulse {
            0% {
                transform: scale(1);
                text-shadow: 0 0 10px orange;
            }

            50% {
                transform: scale(1.2);
                text-shadow: 0 0 25px red;
            }

            100% {
                transform: scale(1);
                text-shadow: 0 0 10px orange;
            }
        }

        #onFire.active {
            animation: firePulse 1s infinite;
        }

        /* Container utama */
        #soal-test {
            background: #ffffff;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.08);
            animation: fadeIn 0.4s ease;
        }

        /* Meta soal (kelas, mapel, topik + timer) */
        .soal-meta {
            padding: 15px 20px;
            border-radius: 10px;
            background: #f8f9fa;
            margin-bottom: 20px;
            border: 1px solid #e3e6f0;
        }

        .soal-meta strong {
            color: #4e73df;
        }

        /* Timer */
        #timer {
            font-size: 1.4rem;
            font-weight: 700;
            background: #CF0F0F;
            color: white;
            padding: 6px 14px;
            border-radius: 8px;
            min-width: 90px;
            text-align: center;
            box-shadow: 0 2px 8px rgba(78, 115, 223, 0.3);
        }

        /* Panel Soal */
        .question-panel {
            background: #fff;
            border: 1px solid #e3e6f0;
            border-radius: 12px;
            padding: 20px;
        }

        /* Teks Soal */
        #questionText {
            font-size: 1.15rem;
            line-height: 1.6;
        }

        /* Opsi jawaban */
        .option-item {
            padding: 12px 16px;
            border-radius: 10px;
            border: 2px solid #dce1eb;
            margin-bottom: 10px;
            cursor: pointer;
            transition: all 0.25s ease;
            background: #fdfdfd;
            font-size: 1rem;
        }

        .option-item:hover {
            border-color: #4e73df;
            background: #f4f7ff;
        }

        .option-item.selected {
            border-color: #1cc88a !important;
            background: #e8fff3 !important;
            box-shadow: 0 0 0 3px rgba(28, 200, 138, 0.25);
        }

        /* Tombol Next */
        .btn-next {
            padding: 10px 22px;
            font-size: 1rem;
            border-radius: 10px;
            font-weight: 600;
            background: linear-gradient(135deg, #1cc88a, #18b077);
            border: none;
        }

        .btn-next:hover {
            background: linear-gradient(135deg, #18b077, #159a66);
        }

        /* Animation */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(8px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>

</head>

<body class="p-4">

    <div class="container">

        <h3 class="text-center mb-4">
            {{ $judul }} <small class="text-muted">({{ ucfirst($topik) }})</small>
        </h3>

        <!-- INFORMASI AWAL -->
        <div id="info-test" class="text-center">
            <div class="card mx-auto shadow-sm border-0" style="max-width: 550px;">
                <div class="card-body p-4">

                    <h5 class="fw-bold mb-3">Keterangan Aktivitas</h5>


                    <div class="mb-2">
                        <span class="fw-semibold">Durasi:</span>
                        <span id="infoDurasi" class="text-primary fw-bold">
                            {{ isset($durasi) ? $durasi . ' Menit' : '—' }}
                        </span>
                    </div>


                    <p class="text-muted small mb-4">
                        Waktu akan mulai dihitung setelah Anda menekan tombol <b>Mulai</b>.
                    </p>

                    <div class="d-flex justify-content-center gap-2">
                        <button class="btn btn-primary px-4" onclick="mulai()">Mulai</button>
                        <a href="{{ route('dashboard.siswa') }}" class="btn btn-outline-secondary px-4">Kembali</a>
                    </div>

                </div>
            </div>
        </div>


        <!-- AREA SOAL -->
        <div id="soal-test" hidden>

            <!-- Header Soal -->
            <div class="soal-meta d-flex justify-content-between align-items-start">
                <div>
                    <div><strong>Kelas:</strong> {{ $kelas }}</div>
                    <div><strong>Mata Pelajaran:</strong> {{ $mapel }}</div>
                    <div><strong>Topik:</strong> {{ $topik }}</div>
                </div>

                <div id="timer">
                    {{ str_pad($durasi, 2, '0', STR_PAD_LEFT) }}:00
                </div>
            </div>

            <!-- Panel Soal -->
            <div class="question-panel">
                <div id="questionText" class="mb-3 fw-semibold"></div>

                <!-- Tempat opsi -->
                <div id="optionsContainer" class="mb-4"></div>

                <!-- Tombol -->
                <div class="d-flex justify-content-end">
                    <button id="nextBtn" class="btn btn-success btn-next" onclick="checkAnswer()">
                        Selanjutnya
                    </button>
                </div>
            </div>

        </div>


    </div>
    <!-- COMBO METER -->
    <div id="comboMeter" style="position:fixed; top:20px; left:20px; 
            font-size:2rem; font-weight:bold; 
            color:#ff9800; text-shadow:2px 2px 8px rgba(0,0,0,.4);
            display:none; z-index:9999;">
    </div>

    <!-- ON FIRE EFFECT -->
    <div id="onFire" style="position:fixed; bottom:20px; right:20px;
            font-size:2.5rem; font-weight:bold; 
            color:#ff3b3b; text-shadow:0 0 15px orange;
            display:none; z-index:9999;">
        🔥 ON FIRE!
    </div>



    <script>
        let currentIndex = 0;
        let totalQuestions = 0;
        let answers = [];
        let currentQuestionID = null;
        let timeLeft = 30 * 60;
        let timerInterval;

        function startTimer() {
            timerInterval = setInterval(() => {
                timeLeft--;

                let m = Math.floor(timeLeft / 60);
                let s = timeLeft % 60;

                document.getElementById("timer").innerText =
                    `${m.toString().padStart(2, '0')}:${s.toString().padStart(2, '0')}`;

                if (timeLeft <= 0) {
                    clearInterval(timerInterval);
                    Swal.fire("Waktu Habis!", "Tes otomatis diselesaikan.", "info");
                    showResult();
                }
            }, 1000);
        }

        function mulai() {
            fetch(`/activity/{{ $id_activity }}/start`)
                .then(r => r.json())
                .then(data => {
                    totalQuestions = data.totalQuestions;
                    answers = Array(totalQuestions).fill(null);

                    document.getElementById("info-test").hidden = true;
                    document.getElementById("soal-test").hidden = false;

                    // set timer berdasarkan durasi yang dikirim server (menit). fallback: 30 menit
                    const durasiMenit = (data.durasi_pengerjaan && Number.isInteger(data.durasi_pengerjaan))
                        ? data.durasi_pengerjaan
                        : 30;
                    timeLeft = durasiMenit * 60;

                    loadQuestion();
                    startTimer();
                })
                .catch(err => {
                    console.error(err);
                    Swal.fire("Error", "Gagal memulai aktivitas. Coba lagi.", "error");
                });
        }


        function loadQuestion() {

            fetch(`/activity/{{ $id_activity }}/question?index=${currentIndex}`)
                .then(r => r.json())
                .then(q => {

                    currentQuestionID = q.question_id;

                    // Tentukan warna badge difficulty
                    let diff = q.difficulty ?? "tidak ada";

                    let badgeClass = "bg-secondary"; // default

                    if (diff.toLowerCase() === "mudah") badgeClass = "bg-success";
                    if (diff.toLowerCase() === "sedang") badgeClass = "bg-warning text-dark";
                    if (diff.toLowerCase() === "sulit") badgeClass = "bg-danger";

                    // Tampilkan soal + difficulty
                    document.getElementById('questionText').innerHTML = `
                        <div class="mb-2">
                            <b>Soal ${currentIndex + 1}:</b> ${q.question.text}
                        </div>
                    `;

                    let html = "";

                    if (q.type === "MultipleChoice") {

                        q.options.forEach(o => {
                            let key = Object.keys(o)[0];
                            let val = o[key].teks;

                            html += `
            <div class="form-check">
                <input type="radio" name="answer" value="${key}" class="form-check-input"
                    ${answers[currentIndex] === key ? "checked" : ""}>
                <label class="form-check-label">${key.toUpperCase()}. ${val}</label>
            </div>
        `;
                        });

                    } else if (q.type === "ShortAnswer") {

                        html = `
        <input type="text" name="answer" class="form-control"
            placeholder="Ketik jawaban..."
            value="${answers[currentIndex] ?? ''}">
    `;
                    }

                    document.getElementById("optionsContainer").innerHTML = html;


                    // 🔥 UBAH TOMBOL JADI “SELESAI” JIKA INI SOAL TERAKHIR
                    if (currentIndex === totalQuestions - 1) {
                        document.getElementById("nextBtn").innerText = "Selesai";
                        document.getElementById("nextBtn").classList.replace("btn-success", "btn-primary");
                    } else {
                        document.getElementById("nextBtn").innerText = "Selanjutnya";
                        document.getElementById("nextBtn").classList.replace("btn-danger", "btn-success");
                    }
                });
        }

        function checkAnswer() {

            let selectedRadio = document.querySelector('input[name="answer"]:checked');
            let textAnswer = document.querySelector('input[name="answer"]:not([type=radio])');

            let finalAnswer = null;

            // Jika Multiple Choice
            if (selectedRadio) {
                finalAnswer = selectedRadio.value;
            }

            // Jika Short Answer
            else if (textAnswer) {
                finalAnswer = textAnswer.value.trim();
                if (finalAnswer === "") {
                    return Swal.fire("Oops", "Isi jawaban dulu!", "warning");
                }
            }

            // Jika tidak memilih apa pun
            else {
                return Swal.fire("Oops", "Pilih atau isi jawaban dulu!", "warning");
            }

            // Simpan jawaban
            answers[currentIndex] = finalAnswer;

            fetch(`/activity/{{ $id_activity }}/submit`, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({
                    question_id: currentQuestionID,
                    user_answer: finalAnswer
                })
            })
                .then(r => r.json())
                .then(res => {
                    // Tampilkan combo & fire berdasarkan streak backend
                    updateComboUI(res.correct ? res.streak_correct : 0);
                });

            if (currentIndex < totalQuestions - 1) {
                currentIndex++;
                loadQuestion();
            } else {
                showResult();
            }
        }

        // 🔥 COMBO & ON-FIRE EFFECT
        function updateComboUI(streak) {

            const combo = document.getElementById("comboMeter");
            const fire = document.getElementById("onFire");

            if (streak >= 2) {
                combo.style.display = "block";
                combo.innerText = `COMBO x${streak}`;
            } else {
                combo.style.display = "none";
            }

            if (streak >= 3) {
                fire.style.display = "block";
                fire.classList.add("active");
            } else {
                fire.style.display = "none";
                fire.classList.remove("active");
            }
        }

        function showResult() {
            clearInterval(timerInterval);

            fetch(`/activity/{{ $id_activity }}/finish`, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                }
            })
                .then(r => r.json())
                .then(res => {
                    // Jika server balikkan result_db, pakai itu; kalau tidak, fallback ke res
                    const db = res.result_db ?? null;
                    const sec = res.duration_seconds ?? (db ? db.waktu_mengerjakan : 0);
                    const m = Math.floor(sec / 60);
                    const s = sec % 60;

                    // Ambil nilai tampil: prioritas dari DB
                    const totalBenar = db ? db.total_benar : (res.total_correct ?? 0);
                    const jumlahSoal = res.jumlah_soal ?? (db ? (db.total_benar + 0) : 0); // kalau perlu ubah metode ambil
                    const base = db ? db.result : null;
                    const bonus = db ? db.bonus_poin : null;
                    const real = db ? db.real_poin : null;
                    const statusText = db ? db.result_status : (res.status_benar ? 'Pass' : 'Remedial');

                    // Bangun HTML ringkasan hasil
                    const html = `
            <div style="text-align:left">
                <p><strong>Waktu mengerjakan:</strong> ${m} m ${s} s</p>
                <p><strong>Jumlah benar:</strong> ${totalBenar} / ${res.jumlah_soal}</p>
                <p><strong>Nilai dasar:</strong> ${base !== null ? base : '-'}</p>
                <p><strong>Bonus poin:</strong> ${bonus !== null ? bonus : '-'}</p>
                <p><strong>Nilai akhir:</strong> ${real !== null ? real : '-'}</p>
                <p><strong>Status:</strong> ${statusText}</p>
            </div>
        `;

                    // Tampilkan modal Swal dengan detail hasil
                    Swal.fire({
                        title: "Selesai!",
                        html: html,
                        icon: "success",
                        showCancelButton: true,
                        confirmButtonText: "Kembali ke Aktivitas",
                        reverseButtons: true,
                    }).then(result => {
                        if (result.isConfirmed) {
                            location.href = "{{ route('siswa.aktivitas') }}";
                        }
                    });

                })
                .catch(err => {
                    console.error(err);
                    Swal.fire("Error", "Gagal menyelesaikan tes. Coba lagi.", "error")
                        .then(() => location.href = "{{ route('siswa.aktivitas') }}");
                });
        }

    </script>

</body>

</html>