// Struktur data soal untuk setiap level/tugas
const soalData = {
    tugas1: [
      {
        id: 't1q1',
        pertanyaan: 'Apa ibu kota Indonesia?',
        pilihan: ['Jakarta', 'Bandung', 'Surabaya', 'Medan'],
        jawabanBenar: 0,
        tingkatKesulitan: 1,
      },
      {
        id: 't1q2',
        pertanyaan: 'Berapa hasil dari 7 x 6?',
        pilihan: ['42', '36', '40', '48'],
        jawabanBenar: 0,
        tingkatKesulitan: 1,
      },
    ],
    remedialT1q1: [
      {
        id: 'rT1q1',
        pertanyaan: 'Ibu kota Indonesia terletak di pulau?',
        pilihan: ['Sumatera', 'Jawa', 'Kalimantan', 'Bali'],
        jawabanBenar: 1,
        tingkatKesulitan: 0,
      },
    ],
    tugas2: [
      {
        id: 't2q1',
        pertanyaan: 'Siapa penulis novel "Laskar Pelangi"?',
        pilihan: ['Andrea Hirata', 'Pramoedya Ananta Toer', 'Tere Liye', 'Dewi Lestari'],
        jawabanBenar: 0,
        tingkatKesulitan: 2,
      },
      {
        id: 't2q2',
        pertanyaan: 'Apa rumus kimia air?',
        pilihan: ['O2', 'H2O', 'CO2', 'NaCl'],
        jawabanBenar: 1,
        tingkatKesulitan: 1,
      },
    ],
    remedialT2q1: [
      {
        id: 'rT2q1',
        pertanyaan: 'Air terdiri dari unsur?',
        pilihan: ['Hidrogen dan Oksigen', 'Nitrogen dan Oksigen', 'Karbon dan Hidrogen', 'Nitrogen dan Karbon'],
        jawabanBenar: 0,
        tingkatKesulitan: 0,
      },
    ],
    kuis: [
      {
        id: 'kq1',
        pertanyaan: 'Apa simbol ilmiah untuk emas?',
        pilihan: ['Au', 'Ag', 'Fe', 'Pb'],
        jawabanBenar: 0,
        tingkatKesulitan: 3,
      },
    ],
    uts: [
      {
        id: 'utsq1',
        pertanyaan: 'Apa yang dimaksud fotosintesis?',
        pilihan: [
          'Proses tumbuhan menghasilkan makanan dengan sinar matahari',
          'Proses pembusukan bahan organik',
          'Pergerakan air dari akar ke daun',
          'Pembentukan batuan dari magma',
        ],
        jawabanBenar: 0,
        tingkatKesulitan: 3,
      },
    ],
    uas: [
      {
        id: 'uasq1',
        pertanyaan: 'Jelaskan hukum Newton kedua!',
        pilihan: [
          'Gaya sama dengan massa dikali percepatan',
          'Setiap aksi ada reaksi',
          'Benda diam cenderung diam',
          'Energi tidak bisa hilang',
        ],
        jawabanBenar: 0,
        tingkatKesulitan: 4,
      },
    ],
  };
  
  // State aplikasi
  let currentSection = 'tugas1';
  let currentQuestionIndex = 0;
  let score = 0;
  let badges = [];
  let totalQuestionsAnswered = 0;
  
  const maxBadges = 3;
  
  // Elemen DOM
  const questionEl = document.getElementById('question-text');
  const choicesEl = document.getElementById('choices-list');
  const nextBtn = document.getElementById('next-btn');
  const progressEl = document.getElementById('progress');
  const scoreEl = document.getElementById('score');
  const badgeEl = document.getElementById('badge');
  const feedbackEl = document.getElementById('feedback');
  
  // Fungsi menampilkan soal berdasarkan currentSection dan currentQuestionIndex
  function tampilkanSoal() {
    feedbackEl.textContent = '';
    let soalSekarang = soalData[currentSection][currentQuestionIndex];
    if (!soalSekarang) {
      // Jika soal habis di section saat ini, lanjut ke section berikutnya
      lanjutkanKeSectionBerikutnya();
      return;
    }
    questionEl.textContent = soalSekarang.pertanyaan;
  
    // Kosongkan daftar pilihan
    choicesEl.innerHTML = '';
  
    // Buat pilihan jawaban
    soalSekarang.pilihan.forEach((pilihan, index) => {
      const li = document.createElement('li');
      li.classList.add('choice');
      li.tabIndex = 0;
      li.setAttribute('role', 'button');
      li.textContent = pilihan;
      li.onclick = () => cekJawaban(index);
      li.onkeypress = e => {
        if (e.key === 'Enter' || e.key === ' ') {
          cekJawaban(index);
        }
      };
      choicesEl.appendChild(li);
    });
  
    updateProgress();
    updateScore();
    updateBadge();
    nextBtn.disabled = true;
  }
  
  // Fungsi untuk mengecek jawaban siswa
  function cekJawaban(pilihanUser) {
    let soalSekarang = soalData[currentSection][currentQuestionIndex];
    let benar = soalSekarang.jawabanBenar === pilihanUser;
  
    // Hilangkan event click untuk pilihan lain
    Array.from(choicesEl.children).forEach((li, idx) => {
      li.onclick = null;
      li.onkeypress = null;
      if (idx === soalSekarang.jawabanBenar) {
        li.classList.add('correct');
      }
      if (idx === pilihanUser && pilihanUser !== soalSekarang.jawabanBenar) {
        li.classList.add('wrong');
      }
    });
  
    if (benar) {
      feedbackEl.textContent = 'Jawaban benar! 🎉';
      score += soalSekarang.tingkatKesulitan * 10;
      totalQuestionsAnswered++;
      checkBadgeUnlock();
      nextBtn.disabled = false;
    } else {
      feedbackEl.textContent = 'Jawaban salah. Akan tampil soal remedial.';
      tampilkanSoalRemedial();
    }
    updateScore();
  }
  
  // Fungsi menampilkan soal remedial jika siswa kesulitan
  function tampilkanSoalRemedial() {
    // Definisikan nama remedial dari currentSection dan currentQuestionIndex
    let remedialSection = `remedial${capitalizeFirstLetter(currentSection)}q${currentQuestionIndex + 1}`;
    if (!soalData[remedialSection]) {
      // Jika tidak ada soal remedial, bisa tetap lanjut
      feedbackEl.textContent += ' Namun soal remedial tidak tersedia, mohon coba ulang.';
      nextBtn.disabled = false; // tetap aktifkan tombol next utk lanjutkan
      return;
    }
  
    currentSection = remedialSection;
    currentQuestionIndex = 0;
    tampilkanSoal();
  }
  
  // Fungsi lanjut ke section berikutnya sesuai urutan evaluasi
  function lanjutkanKeSectionBerikutnya() {
    switch (currentSection) {
      case 'tugas1':
        currentSection = 'tugas2';
        break;
      case 'tugas2':
        currentSection = 'kuis';
        break;
      case 'kuis':
        currentSection = 'uts';
        break;
      case 'uts':
        currentSection = 'uas';
        break;
      case 'uas':
        feedbackEl.textContent = 'Evaluasi selesai! Terima kasih sudah mengerjakan.';
        questionEl.textContent = '';
        choicesEl.innerHTML = '';
        nextBtn.disabled = true;
        return;
      default:
        // Jika sedang di remedial, kembali ke section utama berikutnya
        if (currentSection.startsWith('remedial')) {
          if (currentSection.includes('T1')) {
            currentSection = 'tugas2';
          } else if (currentSection.includes('T2')) {
            currentSection = 'kuis';
          } else {
            currentSection = 'tugas1';
          }
        } else {
          currentSection = 'tugas1';
        }
    }
    currentQuestionIndex = 0;
    tampilkanSoal();
  }
  
  // Fungsi untuk tombol next
  function soalSelanjutnya() {
    currentQuestionIndex++;
    tampilkanSoal();
  }
  
  // Fungsi update progress bar atau teks progress
  function updateProgress() {
    let totalSoal = soalData[currentSection].length;
    progressEl.textContent = `Soal ${currentQuestionIndex + 1} dari ${totalSoal} (${capitalizeFirstLetter(currentSection)})`;
  }
  
  // Fungsi update skor ke DOM
  function updateScore() {
    scoreEl.textContent = `Skor: ${score}`;
  }
  
  // Membuat badge reward sederhana berdasarkan skor dan progress
  function checkBadgeUnlock() {
    const milestones = [50, 100, 150];
    milestones.forEach((milestone, index) => {
      if (score >= milestone && !badges.includes(index)) {
        badges.push(index);
        alert(`Selamat! Anda mendapatkan badge ke-${index + 1}`);
        updateBadge();
      }
    });
  }
  
  // Update tampilan badge
  function updateBadge() {
    if (badges.length === 0) {
      badgeEl.textContent = 'Belum ada badge.';
    } else {
      badgeEl.textContent = `Badge terkumpul: ${badges.length}`;
    }
  }
  
  // Fungsi pembantu untuk kapitalisasi string
  function capitalizeFirstLetter(string) {
    return string.charAt(0).toUpperCase() + string.slice(1);
  }
  
  // Event listener tombol next
  nextBtn.addEventListener('click', soalSelanjutnya);
  
  // Mulai evaluasi pertama kali
  window.onload = () => {
    tampilkanSoal();
    updateBadge();
  };
  