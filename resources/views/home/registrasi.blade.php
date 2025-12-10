<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Registrasi - EvoLevel</title>

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <style>
        :root {
            --bg: #f3f6fb;
            --card: #ffffff;
            --accent: #3b82f6;
            --muted: #6b7280;
            --danger: #e11d48;
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial;
        }

        html,
        body {
            height: 100%
        }

        body {
            margin: 0;
            background: linear-gradient(180deg, var(--bg), #e9f0ff);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }

        .card {
            width: 100%;
            max-width: 420px;
            background: var(--card);
            border-radius: 12px;
            box-shadow: 0 8px 30px rgba(17, 24, 39, 0.08);
            padding: 28px;
        }

        h1 {
            margin: 0 0 8px;
            font-size: 20px
        }

        p.lead {
            margin: 0 0 18px;
            color: var(--muted);
            font-size: 13px
        }

        label {
            display: block;
            font-size: 13px;
            margin-bottom: 6px;
            color: #111827
        }

        .input,
        select {
            width: 100%;
            box-sizing: border-box;
            padding: 10px 12px;
            border: 1px solid #e6e9ef;
            border-radius: 8px;
            font-size: 14px;
            outline: none;
            transition: box-shadow .12s, border-color .12s;
            background: #fff;
        }

        .input:focus,
        select:focus {
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.08);
            border-color: var(--accent)
        }

        .row {
            display: flex;
            gap: 10px;
            align-items: center
        }

        .muted {
            color: var(--muted);
            font-size: 13px;
            margin-top: 6px
        }

        .actions {
            margin-top: 18px;
            display: flex;
            gap: 10px;
            align-items: center
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 10px 14px;
            border-radius: 8px;
            border: 0;
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
        }

        .btn-primary {
            background: var(--accent);
            color: white
        }

        .btn-ghost {
            background: transparent;
            border: 1px solid #e6e9ef;
            color: #111827
        }

        .small {
            font-size: 13px;
            color: var(--muted)
        }

        .error {
            color: var(--danger);
            font-size: 13px;
            margin-top: 8px
        }

        .field {
            margin-bottom: 12px
        }

        .inline {
            display: flex;
            align-items: center;
            gap: 8px
        }

        .toggle-pass {
            background: transparent;
            border: 0;
            color: var(--muted);
            cursor: pointer;
            font-size: 13px
        }

        .hidden {
            display: none
        }

        .footer {
            margin-top: 14px;
            font-size: 13px;
            color: var(--muted);
            text-align: center
        }
    </style>
</head>

<body>
    <main class="card" role="main" aria-labelledby="title">
        <h1 id="title">Daftar EvoLevel</h1>
        <p class="lead">Buat akun baru untuk murid atau guru</p>

        <form id="regForm" novalidate>
            <!-- tambahkan ini sebelum input email -->
            <div class="field">
                <label for="name">Nama</label>
                <input id="name" name="name" type="text" class="input" placeholder="Nama lengkap" required />
            </div>

            <div class="field">
                <label for="email">Email</label>
                <input id="email" name="email" type="email" class="input" placeholder="nama@contoh.com" required />
            </div>

            <div class="field">
                <label for="password">Sandi</label>
                <div class="row">
                    <input id="password" name="password" type="password" class="input" placeholder="Minimal 6 karakter"
                        minlength="6" required />
                    <button type="button" class="toggle-pass" id="togglePass"
                        aria-label="Tampilkan sandi">Tampil</button>
                </div>
            </div>

            <div class="field">
                <label>Daftar sebagai</label>
                <div class="row">
                    <label class="inline"><input type="radio" name="role" value="murid" checked /> Murid</label>
                    <label class="inline"><input type="radio" name="role" value="guru" /> Guru</label>
                </div>
                <div class="muted">Pilih peran Anda. Jika murid, Anda boleh memasukkan kode kelas (opsional).</div>
            </div>

            <div class="field" id="kelasField">
                <label for="kodeKelas">Kode Kelas (opsional)</label>
                <input id="kodeKelas" name="kodeKelas" type="text" class="input"
                    placeholder="Misal: EVO-1234 — kosongkan jika tidak ada" />
                <div class="muted">Kosongkan jika Anda tidak punya kode kelas.</div>
            </div>

            <!-- identity fields -->
            <div class="field">
                <label for="type_id_other">Jenis ID (opsional)</label>
                <select id="type_id_other" name="type_id_other" class="input">
                    <option value="">— Pilih jenis ID —</option>
                    <option value="NISN">NISN</option>
                    <option value="NIM">NIM</option>
                    <option value="NIP">NIP</option>
                    <option value="NIDN">NIDN</option>
                    <option value="NUPTK">NUPTK</option>
                </select>
            </div>

            <div class="field">
                <label for="id_other">Nomor ID (opsional)</label>
                <input id="id_other" name="id_other" type="text" class="input"
                    placeholder="Masukkan nomor ID jika ada" />
            </div>

            <div id="errMsg" class="error hidden" role="alert"></div>

            <div class="actions">
                <button type="submit" class="btn btn-primary">Daftar</button>
                <button type="button" class="btn btn-ghost" id="toLogin">Masuk</button>
            </div>

            <div class="footer">Sudah punya akun? <a href="{{ url('/login') }}">Masuk di sini</a></div>
        </form>
    </main>

    <script>
        // Element refs
        const form = document.getElementById('regForm');
        const roleInputs = Array.from(document.querySelectorAll('input[name="role"]'));
        const kelasField = document.getElementById('kelasField');
        const kodeKelas = document.getElementById('kodeKelas');
        const errMsg = document.getElementById('errMsg');
        const togglePass = document.getElementById('togglePass');
        const password = document.getElementById('password');
        const toLogin = document.getElementById('toLogin');
        const typeIdSelect = document.getElementById('type_id_other');
        const idOtherInput = document.getElementById('id_other');

        function getCsrfToken() {
            const m = document.querySelector('meta[name="csrf-token"]');
            if (m) return m.getAttribute('content');
            const match = document.cookie.match(/XSRF-TOKEN=([^;]+)/);
            return match ? decodeURIComponent(match[1]) : '';
        }

        // Show/hide Kode Kelas (kode tetap opsional)
        function updateRoleVisibility() {
            const role = document.querySelector('input[name="role"]:checked').value;
            if (role === 'murid') {
                kelasField.classList.remove('hidden');
                kodeKelas.removeAttribute('required');
            } else {
                kelasField.classList.add('hidden');
                kodeKelas.value = '';
                kodeKelas.removeAttribute('required');
            }
            errMsg.classList.add('hidden');
        }

        roleInputs.forEach(r => r.addEventListener('change', updateRoleVisibility));
        updateRoleVisibility();

        // Toggle password text
        togglePass.addEventListener('click', () => {
            const isHidden = password.type === 'password';
            password.type = isHidden ? 'text' : 'password';
            togglePass.textContent = isHidden ? 'Sembunyi' : 'Tampil';
            togglePass.setAttribute('aria-pressed', String(!isHidden));
        });

        // Submit handler: send JSON to /register
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            errMsg.classList.add('hidden');
            errMsg.textContent = '';

            const email = (document.getElementById('email').value || '').trim();
            const pass = (password.value || '');
            const role = document.querySelector('input[name="role"]:checked').value || 'murid';
            const kode = (kodeKelas.value || '').trim();
            const type_id_other = (typeIdSelect.value || '') || null;
            const id_other = (idOtherInput.value || '').trim() || null;

            // Basic client-side validation
            if (!email || !pass) {
                errMsg.textContent = 'Email dan sandi harus diisi.';
                errMsg.classList.remove('hidden');
                return;
            }
            if (pass.length < 6) {
                errMsg.textContent = 'Sandi minimal 6 karakter.';
                errMsg.classList.remove('hidden');
                return;
            }

            const payload = {
                name: document.getElementById('name').value.trim(),
                email,
                password: pass,
                role,
                kodeKelas: kode === '' ? null : kode,
                type_id_other,
                id_other
            };


            try {
                const res = await fetch("{{ route('register.submit') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': getCsrfToken(),
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify(payload)
                });

                const text = await res.text();
                const contentType = res.headers.get('content-type') || '';

                if (!res.ok) {
                    try {
                        const json = JSON.parse(text);
                        errMsg.textContent = json.message || text;
                    } catch (err) {
                        errMsg.textContent = text || 'Terjadi kesalahan saat registrasi.';
                    }
                    errMsg.classList.remove('hidden');
                    return;
                }

                if (contentType.includes('application/json')) {
                    const json = JSON.parse(text);
                    alert(json.message || 'Registrasi berhasil.');
                    window.location.href = '/login';
                    return;
                }

                // fallback: redirect to login
                window.location.href = '/login';
            } catch (err) {
                console.error(err);
                errMsg.textContent = 'Gagal menghubungi server.';
                errMsg.classList.remove('hidden');
            }
        });

        toLogin.addEventListener('click', () => {
            window.location.href = '{{ url("/login") }}';
        });
    </script>
</body>

</html>