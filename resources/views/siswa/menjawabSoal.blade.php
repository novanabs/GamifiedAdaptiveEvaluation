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

        .cbt-container {
            display: flex;
            flex-direction: row;
            gap: 1rem;
            height: 90vh;
        }

        .question-panel,
        .side-panel {
            background-color: #fff;
            border-radius: 1rem;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            padding: 2rem;
        }

        .question-panel {
            flex: 3;
            overflow-y: auto;
        }

        .side-panel {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .nav-question {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(45px, 1fr));
            gap: 8px;
            margin-top: 1rem;
        }

        .nav-question button {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            border: none;
            color: #fff;
            background-color: #6c757d;
            font-weight: bold;
        }

        .nav-question button.active {
            background-color: #0d6efd;
        }

        .nav-question button.done {
            background-color: #198754;
        }

        .question-img {
            max-width: 100%;
            max-height: 200px;
            margin: 10px auto;
            display: block;
            border-radius: 0.5rem;
        }

        .progress {
            height: 18px;
        }

        #timer {
            font-weight: bold;
            color: #dc3545;
            text-align: center;
            font-size: 1.2rem;
        }
    </style>
</head>

<body class="p-4">
    <div class="container-fluid">
        <h3 class="text-center mb-4">{{ $judul }} <small class="text-muted">({{ ucfirst($topik) }})</small></h3>

        @php
            $soals = json_decode($konten, true);
            $jumlahSoal = is_array($soals) ? count($soals) : 0;
        @endphp

        {{-- 🧠 Info Awal --}}
        <div id="info-test" class="text-center">
            <div class="card mx-auto p-4 shadow-sm" style="max-width: 600px;">
                <p class="fw-bold">Keterangan Aktivitas</p>
                <p><b>Jumlah Soal:</b> {{ $jumlahSoal }}</p>
                <p><b>Durasi:</b> 30 Menit</p>
                <p class="text-muted">Waktu akan dimulai saat Anda menekan tombol <b>Mulai</b></p>
                <div>
                    <button class="btn btn-primary me-2" onclick="mulai()">Mulai</button>
                    <a href="{{ route('dashboard.siswa') }}" class="btn btn-outline-secondary">Kembali</a>
                </div>
            </div>
        </div>

        {{-- 🧩 CBT --}}
        <div id="soal-test" class="cbt-container" hidden>
            <div class="question-panel">
                <div class="progress mb-3">
                    <div id="status_bar" class="progress-bar" role="progressbar" style="width:0%">0%</div>
                </div>

                <div id="question-area">
                    <div id="questionText" class="mb-3 fw-semibold"></div>
                    <div id="optionsContainer" class="mb-3"></div>
                </div>

                <div class="d-flex justify-content-between mt-4">
                    <button class="btn btn-secondary" id="prevBtn" onclick="prevQuestion()">Sebelumnya</button>
                    <button class="btn btn-success" id="nextBtn" onclick="checkAnswer()">Selanjutnya</button>
                </div>
            </div>

            <div class="side-panel">
                <div>
                    <h5 class="text-center mb-2">⏰ Waktu</h5>
                    <div id="timer">30:00</div>
                    <hr>
                    <h6 class="text-center">Nomor Soal</h6>
                    <div id="nav-questions" class="nav-question"></div>
                </div>
                <div class="text-center mt-3">
                    <button class="btn btn-danger mt-3 w-100" id="finishBtn" onclick="showResult()"
                        hidden>Selesai</button>
                </div>
            </div>
        </div>

        {{-- 🧾 Form penyimpanan hasil --}}
        <form id="saveResultForm" action="{{ route('activity.saveResult') }}" method="POST" hidden>
            @csrf
            <input type="hidden" name="id_activity" value="{{ $id_activity ?? '' }}">
            <input type="hidden" name="id_user" value="{{ Auth::id() }}">
            <input type="hidden" name="result" id="resultField">
            <input type="hidden" name="result_status" id="statusField">
        </form>
    </div>

    <script>
        let currentQuestion = 0;
        let correctCount = 0;
        let questions = @json($soals);
        const totalQuestions = {{ $jumlahSoal }};
        let timerInterval;
        let timeLeft = 30 * 60;
        let answered = Array(totalQuestions).fill(false);
        let userAnswers = Array(totalQuestions).fill(null);

        function mulai() {
            document.getElementById('info-test').hidden = true;
            document.getElementById('soal-test').hidden = false;
            generateNavButtons();
            loadQuestion();
            startTimer();

            // 🔥 Tombol Selesai langsung muncul dari awal
            document.getElementById("finishBtn").hidden = false;
        }

        function startTimer() {
            const timerElement = document.getElementById('timer');
            timerInterval = setInterval(() => {
                let m = Math.floor(timeLeft / 60);
                let s = timeLeft % 60;
                timerElement.textContent = `${m}:${s < 10 ? "0" + s : s}`;
                timeLeft--;
                if (timeLeft < 0) {
                    clearInterval(timerInterval);
                    Swal.fire({
                        icon: 'warning',
                        title: '⏰ Waktu Habis!',
                        text: 'Waktu pengerjaan telah selesai.',
                        confirmButtonText: 'Lihat Hasil'
                    }).then(showResult);
                }
            }, 1000);
        }

        function generateNavButtons() {
            const nav = document.getElementById('nav-questions');
            nav.innerHTML = "";
            for (let i = 0; i < totalQuestions; i++) {
                const btn = document.createElement("button");
                btn.textContent = i + 1;
                btn.id = "nav-" + i;
                btn.onclick = () => goToQuestion(i);
                nav.appendChild(btn);
            }
            updateNavHighlight();
        }

        function updateNavHighlight() {
            for (let i = 0; i < totalQuestions; i++) {
                const btn = document.getElementById("nav-" + i);
                btn.classList.remove("active");
                if (answered[i]) btn.classList.add("done");
            }
            document.getElementById("nav-" + currentQuestion).classList.add("active");
        }

        function loadQuestion() {
            let q = questions[currentQuestion];
            if (typeof q.MC_option === "string") try { q.MC_option = JSON.parse(q.MC_option); } catch { }
            if (typeof q.question === "string") try { q.question = JSON.parse(q.question); } catch { }

            let questionHTML = `<b>Soal ${currentQuestion + 1}:</b> ${q.question?.text || q.question}`;
            if (q.question?.URL) questionHTML += `<img src="${q.question.URL}" class="question-img">`;
            document.getElementById('questionText').innerHTML = questionHTML;

            let container = document.getElementById('optionsContainer');
            container.innerHTML = "";
            if (q.type === "MultipleChoice" && q.MC_option) {
                q.MC_option.forEach(optObj => {
                    let key = Object.keys(optObj)[0];
                    let opt = optObj[key];
                    let teks = opt.teks;
                    let url = opt.url;
                    let imgHTML = url ? `<br><img src="${url}" class="question-img">` : "";
                    let checked = (userAnswers[currentQuestion] === key) ? "checked" : "";
                    container.innerHTML += `
                <div class="form-check mb-2">
                    <input type="radio" name="answer" value="${key}" class="form-check-input" id="opt${key}" ${checked}>
                    <label for="opt${key}" class="form-check-label">
                        <b>${key.toUpperCase()}.</b> ${teks || ""} ${imgHTML}
                    </label>
                </div>`;
                });
            } else {
                let prevAns = userAnswers[currentQuestion] || "";
                container.innerHTML = `<textarea class="form-control" id="shortAnswer">${prevAns}</textarea>`;
            }

            updateButtons();
            let progress = Math.round((answered.filter(a => a).length / totalQuestions) * 100);
            document.getElementById('status_bar').style.width = progress + "%";
            document.getElementById('status_bar').textContent = progress + "%";
            updateNavHighlight();
        }

        function checkAnswer() {
            let q = questions[currentQuestion];
            let correct = false;
            let ansValue = "";

            if (q.type === "MultipleChoice") {
                let selected = document.querySelector('input[name="answer"]:checked');
                if (!selected) return Swal.fire('Pilih jawaban dulu!', '', 'info');
                ansValue = selected.value;
                if (selected.value.toLowerCase() === q.MC_answer.toLowerCase()) correct = true;
            } else {
                let ans = document.getElementById('shortAnswer').value.trim();
                if (!ans) return Swal.fire('Isi jawaban dulu!', '', 'info');
                ansValue = ans;
                let possible = Array.isArray(q.SA_answer) ? q.SA_answer.map(a => a.toLowerCase()) : [q.SA_answer.toLowerCase()];
                if (possible.includes(ans.toLowerCase())) correct = true;
            }

            userAnswers[currentQuestion] = ansValue;
            answered[currentQuestion] = true;
            if (correct) correctCount++;

            if (currentQuestion < totalQuestions - 1) {
                currentQuestion++;
                loadQuestion();
            }
        }

        function prevQuestion() {
            if (currentQuestion > 0) {
                currentQuestion--;
                loadQuestion();
            }
        }

        function goToQuestion(i) {
            currentQuestion = i;
            loadQuestion();
        }

        function updateButtons() {
            document.getElementById('prevBtn').hidden = (currentQuestion === 0);
            document.getElementById('nextBtn').hidden = (currentQuestion === totalQuestions - 1);
        }

        // 🔥 Modifikasi tombol Selesai → dengan konfirmasi sebelum simpan
        function showResult() {
            Swal.fire({
                icon: 'question',
                title: 'Apakah Anda yakin ingin menyelesaikan kuis ini?',
                text: 'Pastikan semua jawaban sudah diisi dengan benar.',
                showCancelButton: true,
                confirmButtonText: 'Ya, Selesai!',
                cancelButtonText: 'Belum'
            }).then(result => {
                if (result.isConfirmed) {
                    clearInterval(timerInterval);
                    let score = Math.round((correctCount / totalQuestions) * 100);
                    let resultStatus = score < 70 ? "Remedial" : "Pass";

                    Swal.fire({
                        icon: 'success',
                        title: '🎉 Selesai!',
                        html: `<p>Nilai akhir Anda: <b>${score}</b><br>Status: <b>${resultStatus}</b></p>`,
                        confirmButtonText: 'Simpan & Kembali'
                    }).then(() => {
                        document.getElementById('resultField').value = score;
                        document.getElementById('statusField').value = resultStatus;
                        document.getElementById('saveResultForm').submit();
                    });
                }
            });
        }
    </script>

</body>

</html>