<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Evolevel – Sistem Evaluasi Adaptif</title>

  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

  <style>
    :root {
      --primary: #4e73df;
      --primary-dark: #224abe;
      --primary-soft: rgba(78, 115, 223, .12);
    }

    * {
      box-sizing: border-box;
    }

    html,
    body {
      width: 100%;
      height: 100%;
      margin: 0;
      overflow: hidden;
      /* 🔥 FIX: TIDAK ADA SCROLL */
      font-family: 'Nunito', system-ui, -apple-system, BlinkMacSystemFont, sans-serif;
      background: linear-gradient(135deg, #f8f9fc, #eef2ff);
      color: #1f2937;
    }

    /* NAVBAR */
    .navbar {
      height: 72px;
      background: transparent;
    }

    .navbar-brand {
      font-weight: 800;
      font-size: 1.3rem;
      color: var(--primary) !important;
    }

    /* HERO */
    .hero {
      min-height: calc(100vh - 72px);
      /* 🔥 1 layar penuh */
      display: flex;
      align-items: center;
      justify-content: center;
      position: relative;
      text-align: center;
      overflow: hidden;
    }

    .hero::before {
      content: "";
      position: absolute;
      inset: 0;
      background:
        radial-gradient(circle at 80% 20%, rgba(78, 115, 223, .18), transparent 40%),
        radial-gradient(circle at 20% 70%, rgba(34, 74, 190, .15), transparent 45%);
      z-index: 0;
    }

    .hero-content {
      position: relative;
      z-index: 1;
      max-width: 900px;
      padding: 0 1rem;
    }

    .badge-platform {
      display: inline-flex;
      align-items: center;
      gap: .5rem;
      background: var(--primary-soft);
      color: var(--primary-dark);
      padding: .45rem .9rem;
      border-radius: 999px;
      font-weight: 700;
      font-size: .9rem;
      margin-bottom: 1.4rem;
    }

    .hero h1 {
      font-weight: 900;
      font-size: clamp(2.4rem, 5vw, 3.4rem);
      line-height: 1.15;
    }

    .hero h1 span {
      color: var(--primary);
    }

    .hero p {
      margin: 1.3rem auto 2.2rem;
      font-size: 1.05rem;
      color: #4b5563;
      max-width: 720px;
    }

    /* CTA BUTTON */
    .btn-cta {
      background: linear-gradient(135deg, var(--primary), var(--primary-dark));
      border: none;
      padding: .9rem 2.3rem;
      font-size: 1.05rem;
      font-weight: 800;
      border-radius: 999px;
      color: #fff;
      box-shadow: 0 12px 28px rgba(78, 115, 223, .35);
      transition: transform .25s cubic-bezier(.4, 0, .2, 1);
    }

    .btn-cta:hover {
      transform: scale(1.06);
      color: #fff;
    }

    /* FEATURES */
    .features {
      margin-top: 2.3rem;
      display: flex;
      flex-wrap: wrap;
      gap: 1.3rem;
      justify-content: center;
    }

    .feature-item {
      display: flex;
      align-items: center;
      gap: .55rem;
      color: #1e3a8a;
      font-weight: 700;
      font-size: .95rem;
    }

    /* FOOTER */
    footer {
      padding: .8rem 0;
      text-align: center;
      font-size: .85rem;
      color: #6b7280;
    }

    @media (max-width: 768px) {

      html,
      body {
        overflow-y: auto;
        /* mobile boleh scroll */
      }
    }
  </style>
</head>

<body>

  <!-- NAVBAR -->
  <nav class="navbar navbar-expand-lg">
    <div class="container">
      <a class="navbar-brand" href="#">
        <i class="bi bi-layers-fill me-1"></i> Evolevel
      </a>

      <div class="ms-auto">
        <a href="/login" class="fw-bold text-dark text-decoration-none">
          Masuk
        </a>
      </div>
    </div>
  </nav>

  <!-- HERO -->
  <section class="hero">
    <div class="hero-content">

      <div class="badge-platform mx-auto">
        <span class="bg-primary rounded-circle" style="width:8px;height:8px"></span>
        Platform Evaluasi Adaptif untuk Pengajar
      </div>

      <h1>
        Buat Evaluasi <span>Lebih Mudah</span><br>
        dan Lebih Efektif
      </h1>

      <p>
        Evolevel membantu guru dan pengajar membuat, mengelola,
        serta menganalisis evaluasi pembelajaran secara adaptif
        dengan cepat, rapi, dan terstruktur.
      </p>

      <a href="/login" class="btn btn-cta">
        Mulai Sekarang
        <i class="bi bi-arrow-right ms-1"></i>
      </a>

      <div class="features">
        <div class="feature-item">
          <i class="bi bi-check-circle-fill text-primary"></i>
          Evaluasi adaptif & berjenjang
        </div>
        <div class="feature-item">
          <i class="bi bi-check-circle-fill text-primary"></i>
          Analisis hasil otomatis
        </div>
        <div class="feature-item">
          <i class="bi bi-check-circle-fill text-primary"></i>
          Mudah digunakan guru & siswa
        </div>
      </div>

    </div>
  </section>

  <footer>
    &copy; 2025 Evolevel — Sistem Evaluasi Adaptif
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>