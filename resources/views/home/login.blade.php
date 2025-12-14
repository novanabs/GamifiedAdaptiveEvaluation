<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Login · Evolevel</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        :root {
            --primary: #4e73df;
            --primary-dark: #224abe;
            --primary-soft: rgba(78, 115, 223, .12);
        }

        html,
        body {
            height: 100%;
            margin: 0;
            font-family: 'Nunito', system-ui, -apple-system, BlinkMacSystemFont, sans-serif;
            background: #f8f9fc;
        }

        /* RESET BOOTSTRAP GAP */
        .container-fluid,
        .row {
            margin: 0 !important;
            padding: 0 !important;
        }

        .left-panel {
            min-height: 100vh;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 3rem;
            position: relative;
            overflow: hidden;

            /* SOFT AIRY GRADIENT – SAMA RASA DENGAN HERO */
            background:
                radial-gradient(circle at 50% 35%, #7f98ff 0%, transparent 55%),
                linear-gradient(135deg,
                    #5f7ff2 0%,
                    #4e73df 45%,
                    #3f63d6 100%);
        }

        /* CAHAYA HALUS (TIDAK NORAK) */
        .left-panel::before {
            content: "";
            position: absolute;
            inset: 0;
            background:
                radial-gradient(circle at 60% 30%, rgba(255, 255, 255, .25), transparent 55%),
                radial-gradient(circle at 30% 75%, rgba(255, 255, 255, .15), transparent 60%);
            z-index: 0;
        }

        .left-content {
            position: relative;
            z-index: 1;
            max-width: 460px;
        }


        .brand {
            font-weight: 900;
            font-size: 2.2rem;
            margin-bottom: 1rem;
        }

        .left-content ul {
            padding-left: 1.2rem;
            margin-top: 1.5rem;
        }

        .left-content li {
            margin-bottom: .5rem;
        }

        /* RIGHT PANEL */
        .right-panel {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        .card-login {
            width: 100%;
            max-width: 420px;
            border-radius: 1rem;
            border: none;
            box-shadow: 0 18px 40px rgba(78, 115, 223, .18);
        }

        .card-login .card-body {
            padding: 2rem;
        }

        .card-login h4 {
            font-weight: 800;
        }

        /* BUTTON */
        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            border: none;
            font-weight: 700;
            padding: .7rem 1rem;
            box-shadow: 0 10px 22px rgba(78, 115, 223, .35);
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, var(--primary-dark), var(--primary));
        }

        .input-group .btn {
            border-radius: 0 .375rem .375rem 0;
        }

        /* LINKS */
        a {
            color: var(--primary);
            font-weight: 600;
            text-decoration: none;
        }

        a:hover {
            color: var(--primary-dark);
            text-decoration: underline;
        }

        /* MOBILE */
        @media (max-width: 767.98px) {
            .left-panel {
                display: none;
            }
        }
    </style>
</head>

<body>
    <div class="container-fluid">
        <div class="row g-0">

            <!-- LEFT -->
            <div class="col-md-6 left-panel d-none d-md-flex">
                <div class="left-content">
                    <div class="brand">Evolevel</div>
                    <p class="lead">
                        Platform evaluasi adaptif untuk membantu guru dan siswa
                        mencapai hasil belajar yang lebih efektif.
                    </p>

                    <ul>
                        <li>Evaluasi adaptif & berjenjang</li>
                        <li>Analisis hasil otomatis</li>
                        <li>Pemantauan progres real-time</li>
                    </ul>
                </div>
            </div>

            <!-- RIGHT -->
            <div class="col-md-6 right-panel">
                <div class="card card-login">
                    <div class="card-body">
                        <h4 class="mb-1">Masuk ke Evolevel</h4>
                        <p class="text-muted mb-4 small">
                            Gunakan akun Anda untuk melanjutkan
                        </p>

                        <form id="loginForm" action="{{ route('login.process') }}" method="POST" novalidate>
                            @csrf

                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" placeholder="nama@contoh.com"
                                    required>
                                <div class="invalid-feedback">
                                    Masukkan alamat email yang valid.
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Kata Sandi</label>
                                <div class="input-group">
                                    <input type="password" name="password" class="form-control" id="password"
                                        placeholder="Kata sandi" required>
                                    <button class="btn btn-outline-secondary" type="button" id="togglePwd">
                                        <span id="eyeText">Tampilkan</span>
                                    </button>
                                </div>
                                <div class="invalid-feedback">
                                    Kata sandi tidak boleh kosong.
                                </div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="remember">
                                    <label class="form-check-label small">Ingat saya</label>
                                </div>
                                <a href="#" class="small">Lupa kata sandi?</a>
                            </div>

                            <div class="d-grid mb-3">
                                <button type="submit" class="btn btn-primary">
                                    Masuk
                                </button>
                            </div>
                        </form>
                    </div>

                    <div class="card-footer text-center small text-muted bg-transparent border-0">
                        Belum punya akun?
                        <a href="{{ url('/register') }}">Daftar sekarang</a><br>
                        <a href="{{ url('/') }}">← Kembali ke beranda</a>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle password
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

        // Validation
        (function () {
            const form = document.getElementById('loginForm');
            form.addEventListener('submit', function (event) {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                    form.classList.add('was-validated');
                }
            }, false);
        })();
    </script>
</body>

</html>