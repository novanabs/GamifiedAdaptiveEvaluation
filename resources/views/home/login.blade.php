<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Login · EvoLevel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        html,
        body {
            height: 100%;
        }

        .left-panel {
            background-image: url('https://images.unsplash.com/photo-1545239351-1141bd82e8a6?q=80&w=1400&auto=format&fit=crop&sat=-20');
            background-size: cover;
            background-position: center;
            color: #fff;
            position: relative;
            min-height: 100%;
        }

        .left-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(180deg, rgba(10, 25, 47, 0.65), rgba(3, 9, 23, 0.65));
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        .left-content {
            max-width: 520px;
        }

        .brand {
            font-weight: 700;
            letter-spacing: .4px;
        }

        .card-login {
            max-width: 420px;
            width: 100%;
        }

        @media (max-width: 767.98px) {
            .left-panel {
                display: none;
            }
        }
    </style>
</head>

<body>
    <div class="container-fluid vh-100">
        <div class="row g-0 h-100">

            <!-- Kiri -->
            <div class="col-md-6 left-panel d-none d-md-block">
                <div class="left-overlay">
                    <div class="left-content text-white">
                        <h1 class="brand display-6">EvoLevel</h1>
                        <p class="lead mt-3">
                            EvoLevel membantu pendidik dan peserta didik mencapai kompetensi tinggi dengan modul
                            interaktif dan penilaian adaptif.
                        </p>
                        <ul class="mt-4">
                            <li>Modul interaktif</li>
                            <li>Penilaian berbasis kompetensi</li>
                            <li>Pelacakan perkembangan real-time</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Kanan -->
            <div class="col-md-6 d-flex align-items-center justify-content-center">
                <div class="p-4 w-100">
                    <div class="card shadow-sm card-login mx-auto">
                        <div class="card-body p-4">
                            <h4 class="card-title mb-1">Masuk ke EvoLevel</h4>
                            <p class="text-muted mb-4 small">Masukkan akun Anda untuk melanjutkan</p>

                            <form id="loginForm" action="{{ route('login.process') }}" method="POST" novalidate>
                                @csrf
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" name="email" class="form-control" id="email"
                                        placeholder="nama@contoh.com" required>
                                    <div class="invalid-feedback">Masukkan alamat email yang valid.</div>
                                </div>

                                <div class="mb-3">
                                    <label for="password" class="form-label">Kata Sandi</label>
                                    <div class="input-group">
                                        <input type="password" name="password" class="form-control" id="password"
                                            placeholder="Kata sandi" required>
                                        <button class="btn btn-outline-secondary" type="button" id="togglePwd">
                                            <span id="eyeText">Tampilkan</span>
                                        </button>
                                    </div>
                                    <div class="invalid-feedback">Kata sandi tidak boleh kosong.</div>
                                </div>

                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="remember" name="remember">
                                        <label class="form-check-label small" for="remember">Ingat saya</label>
                                    </div>
                                    <a href="#" class="small">Lupa kata sandi?</a>
                                </div>

                                <div class="d-grid mb-3">
                                    <!-- tombol sekarang langsung submit -->
                                    <button id="pilih" type="submit" class="btn btn-primary">Masuk</button>
                                </div>
                            </form>

                        </div>
                        <div class="card-footer text-center small text-muted">
                            Belum punya akun? <a href="{{ url('/register') }}">Daftar sekarang</a> atau
                            <a href="{{ url('/') }}">ke beranda</a>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // === Tampilkan/Sembunyikan Password ===
        document.getElementById('togglePwd').addEventListener('click', function () {
            const pwd = document.getElementById('password');
            const eyeText = document.getElementById('eyeText');
            if (pwd.type === 'password') {
                pwd.type = 'text';
                eyeText.textContent = 'Sembunyikan';
            } else {
                pwd.type = 'password';
                eyeText.textContent = 'Tampilkan';
            }
        });

        // === Validasi Form saat submit (HTML5 validation) ===
        (function () {
            const form = document.getElementById('loginForm');

            form.addEventListener('submit', function (event) {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                    form.classList.add('was-validated');
                }
                // jika valid, form akan submit ke server seperti biasa
            }, false);
        })();
    </script>
</body>

</html>
