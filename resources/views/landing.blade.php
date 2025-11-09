<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Beranda - Sistem Evaluasi Adaptif</title>

  {{-- Bootstrap CDN --}}
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />

  {{-- File CSS lokal (kalau ada) --}}
  <link rel="stylesheet" href="{{ asset('css/styles.css') }}" />
</head>
<body>
  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
      <a class="navbar-brand text-dark" href="{{ url('/') }}">Evaluasi Adaptif</a>
      <button
        class="navbar-toggler"
        type="button"
        data-bs-toggle="collapse"
        data-bs-target="#navbarNav"
        aria-controls="navbarNav"
        aria-expanded="false"
        aria-label="Toggle navigation"
      >
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto">
          <li class="nav-item"><a class="nav-link active text-dark" href="{{ url('/') }}">Tentang</a></li>
        </ul>
      </div>
    </div>
  </nav>

  <!-- Jumbotron -->
  <section class="py-5 bg-primary text-white ">
    <div class="container">
      <h1 class="display-4 fw-bold">Selamat Datang di Sistem Evaluasi Adaptif</h1>
      <p class="lead mt-3 text-dark">
        Sistem evaluasi interaktif dan adaptif untuk siswa dengan fitur gamifikasi dan kemudahan navigasi.
      </p>
      <a href="{{ url('/progress') }}" class="btn btn-light btn-lg mt-4">Mulai Evaluasi</a>
    </div>
  </section>

  <!-- Konten Utama -->
  <main class="container my-5">
    <div class="row">
      <div class="col-md-6 mb-4">
        <div class="card shadow-sm h-100">
          <div class="card-body">
            <h5 class="card-title">Evaluasi Berjenjang</h5>
            <p class="card-text">
              Modul evaluasi berbasis aturan yang menyesuaikan tingkat kesulitan soal sesuai kemampuan siswa.
            </p>
            <a href="{{ url('/evaluasi') }}" class="btn btn-primary">Mulai Soal</a>
          </div>
        </div>
      </div>

      <div class="col-md-6 mb-4">
        <div class="card shadow-sm h-100">
          <div class="card-body">
            <h5 class="card-title">Pantau Progress</h5>
            <p class="card-text">
              Lihat perkembangan dan keterkaitan evaluasi Anda sampai selesai.
            </p>
            <a href="{{ url('/progress') }}" class="btn btn-primary">Lihat Progress</a>
          </div>
        </div>
      </div>

      <div class="col-md-6 mb-4">
        <div class="card shadow-sm h-100">
          <div class="card-body">
            <h5 class="card-title">Halaman Guru</h5>
            <p class="card-text">
              Guru dapat melihat data nilai dan performa siswa secara mudah dan cepat.
            </p>
            <a href="{{ url('/guru') }}" class="btn btn-primary">Dashboard Guru</a>
          </div>
        </div>
      </div>

      <div class="col-md-6 mb-4">
        <div class="card shadow-sm h-100">
          <div class="card-body">
            <h5 class="card-title">Profil Siswa</h5>
            <p class="card-text">
              Lihat leaderboard dan capaian badge Anda sebagai motivasi belajar.
            </p>
            <a href="{{ url('/profil') }}" class="btn btn-primary">Lihat Profil</a>
          </div>
        </div>
      </div>
    </div>
  </main>

  <!-- Footer -->
  <footer class="bg-primary text-white text-center py-3">
    &copy; {{ date('Y') }} Sistem Evaluasi Adaptif. All rights reserved.
  </footer>

  {{-- Bootstrap Bundle JS --}}
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
