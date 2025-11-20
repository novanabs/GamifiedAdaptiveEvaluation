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
    </style>

</head>

<body class="p-4">

    <div class="container">

        <h3 class="text-center mb-4">
            {{ $judul }} <small class="text-muted">({{ ucfirst($topik) }})</small>
        </h3>

        <!-- INFORMASI AWAL -->
        <div id="info-test" class="text-center">
            <div class="card mx-auto p-4 shadow-sm" style="max-width:600px">
                <p class="fw-bold">Keterangan Aktivitas</p>
                <p><b>Jumlah Soal:</b> <span id="jumlahSoal">-</span></p>
                <p><b>Durasi:</b> 30 Menit</p>
                <p class="text-muted">Waktu mulai setelah menekan tombol <b>Mulai</b></p>

                <button class="btn btn-primary me-2" onclick="mulai()">Mulai</button>
                <a href="{{ route('dashboard.siswa') }}" class="btn btn-outline-secondary">Kembali</a>
            </div>
        </div>

        <!-- AREA SOAL -->
        <div id="soal-test" hidden>

            <div class="question-panel">

                <!-- TIMER -->
                <div class="text-end mb-3">
                    ⏰ <span id="timer">30:00</span>
                </div>

                <div id="questionText" class="mb-3 fw-semibold"></div>
                <div id="optionsContainer" class="mb-4"></div>

                <div class="d-flex justify-content-end">
                    <button id="nextBtn" class="btn btn-success" onclick="checkAnswer()">Selanjutnya</button>
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

                    document.getElementById("jumlahSoal").innerText = totalQuestions;
                    document.getElementById("info-test").hidden = true;
                    document.getElementById("soal-test").hidden = false;

                    loadQuestion();
                    startTimer();
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

                        <span class="badge ${badgeClass}">
                            Difficulty: ${diff}
                        </span>
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
                        document.getElementById("nextBtn").classList.replace("btn-success", "btn-danger");
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
            }).then(() => {
                Swal.fire("Selesai!", "Jawaban kamu telah disimpan.", "success")
                    .then(() => location.href = "{{ route('siswa.aktivitas') }}");
            });
        }

    </script>

</body>

</html>