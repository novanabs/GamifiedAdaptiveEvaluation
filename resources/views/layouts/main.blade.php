<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Evolevel - @yield('title', 'Dashboard')</title>

    <!-- Font & Icons -->
    <link href="{{ asset('vendor-assets/fontawesome-free/css/all.min.css') }}" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,300,400,600,700,900" rel="stylesheet">

    <!-- SB Admin 2 CSS -->
    <link href="{{ asset('css/sb-admin-2.min.css') }}" rel="stylesheet">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <style>
        body {
            font-family: 'Nunito', sans-serif;
            background: #f8f9fa;
            overflow-x: hidden;

        }

        /* ===== SIDEBAR ===== */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: 250px;
            z-index: 1050;
            transition: transform .3s ease, width .3s ease;
        }

        /* ===== CONTENT ===== */
        #content-wrapper {
            margin-left: 220px;
            min-height: 100vh;
            transition: margin-left .3s ease;
        }

        .sidebar.toggled {
            width: 80px !important;
        }

        .sidebar.toggled .nav-item .nav-link span,
        .sidebar.toggled .sidebar-brand-text {
            display: none;
        }

        .sidebar.toggled .nav-item .nav-link {
            text-align: center;
        }

        .sidebar.toggled~#content-wrapper {
            margin-left: 80px;
        }

        /* ===== MOBILE SIDEBAR FIX ===== */
        @media (max-width: 991.98px) {
            .sidebar {
                width: 105px;
                transform: translateX(-100%);
            }

            body.sidebar-open .sidebar {
                transform: translateX(0);
            }

            #content-wrapper {
                margin-left: 0 !important;
            }
        }


        /* ===== OVERLAY ===== */
        #sidebarOverlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, .45);
            z-index: 1040;
            display: none;
        }

        body.sidebar-open #sidebarOverlay {
            display: block;
        }

        /* ===== SIDEBAR ACTIVE STATE ===== */
        .sidebar .nav-item .nav-link {
            border-radius: 0;
            margin: 0;
            transition: all .2s ease;
        }

        .sidebar .nav-item .nav-link.active {
            background-color: rgba(255, 255, 255, 0.15);
            color: #fff !important;
            position: relative;
        }

        /* garis indikator kiri */
        .sidebar .nav-item .nav-link.active::before {
            content: "";
            position: absolute;
            left: 0;
            top: 0;
            width: 4px;
            height: 100%;
            background-color: #ffffff;
        }

        .sidebar .nav-item {
            height: 56px;
            display: flex;
            align-items: center;
        }

        .sidebar .nav-link {
            height: 100%;
            display: flex;
            align-items: center;
            gap: 12px;
        }


        /* 3. Sidebar kecil = icon benar-benar center */
        .sidebar.toggled .nav-link {
            justify-content: center;
            padding: 0 !important;
        }

        /* 4. Samakan ukuran & lebar icon */
        .sidebar .nav-link i {
            font-size: 1.1rem;
            width: 24px;
            text-align: center;
        }

        .sidebar .nav-link.active {
            background-color: rgba(255, 255, 255, 0.12);
        }

        .sidebar .nav-link.active::before {
            left: 0;
            top: 0;
            height: 100%;
        }

        /* Kecilkan area brand */
        .sidebar .sidebar-brand {
            min-height: 56px;
            padding: 0.75rem 0;
        }

        .sidebar-divider {
            margin: 0.75rem 0 !important;
            opacity: 0.3;
        }

        /* Menu mulai rapi dari atas */
        .sidebar .navbar-nav {
            padding-top: 8px;
        }
    </style>

    @stack('head')
</head>

<body id="page-top">

    <div id="sidebarOverlay"></div>

    <div id="wrapper">

        <!-- SIDEBAR -->
        <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion">

            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="#">
                <div class="sidebar-brand-icon rotate-n-15">
                    <i class="fas fa-laugh-wink"></i>
                </div>
                <div class="sidebar-brand-text mx-3">Evolevel</div>
            </a>

            <hr class="sidebar-divider my-0">

            @if(Auth::user()->role === 'student')
                <li class="nav-item">
                    <a class="nav-link @yield('dashboard')" href="{{ url('/dashboardsiswa') }}">
                        <i class="fas fa-fw fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link @yield('aktivitas')" href="{{ url('/aktivitassiswa') }}">
                        <i class="fas fa-fw fa-clipboard"></i>
                        <span>Aktivitas</span>
                    </a>
                </li>
            @endif

            @if (Auth::user()->role === 'teacher')
                <li class="nav-item">
                    <a class="nav-link @yield('dashboardGuru')" href="{{ url('/dashboardguru') }}">
                        <i class="fas fa-fw fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link @yield('dataSiswa')" href="{{ url('/datasiswa') }}">
                        <i class="fas fa-fw fa-user-graduate"></i>
                        <span>Data Siswa</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link @yield('dataNilai')" href="{{ route('data.nilai') }}">
                        <i class="fas fa-fw fa-chart-bar"></i>
                        <span>Data Nilai</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link @yield('dataKelas')" href="{{ url('/datakelas') }}">
                        <i class="fas fa-fw fa-school"></i>
                        <span>Data Kelas</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link @yield('dataSubject')" href="{{ url('/datamatapelajaran') }}">
                        <i class="fas fa-fw fa-book"></i>
                        <span>Data Mata Pelajaran</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link @yield('dataTopic')" href="{{  url('/datatopik') }}">
                        <i class="fas fa-fw fa-tags"></i>
                        <span>Data Topik</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link @yield('dataSoal')" href="{{ url('/datasoal') }}">
                        <i class="fas fa-fw fa-question-circle"></i>
                        <span>Data Soal</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link @yield('dataAktivitas')" href="{{  url('/dataaktivitas') }}">
                        <i class="fas fa-fw fa-tasks"></i>
                        <span>Data Evaluasi</span>
                    </a>
                </li>
            @endif

            <hr class="sidebar-divider d-none d-md-block">

            <div class="text-center d-none d-md-inline">
                <button id="sidebarToggle" class="rounded-circle border-0"></button>
            </div>
        </ul>
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">

                <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 shadow">
                    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle me-2">
                        <i class="fa fa-bars"></i>
                    </button>

                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item dropdown no-arrow">
                            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                                <span class="me-2 d-none d-lg-inline text-gray-600 small">
                                    {{ Auth::user()->name }}
                                </span>
                                <i class="fas fa-user fa-lg"></i>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end shadow">
                                <a class="dropdown-item" href="#">Profile</a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" data-bs-toggle="modal" data-bs-target="#logoutModal">
                                    Logout
                                </a>
                            </div>
                        </li>
                    </ul>
                </nav>

                <div class="container-fluid">
                    @yield('content')
                </div>
            </div>
            <!-- modal logout -->
            <div class="modal fade" id="logoutModal" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Logout</h5>
                            <button class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            Yakin ingin logout?
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button class="btn btn-danger">Logout</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <footer class="sticky-footer bg-white py-3 mt-auto">
                <div class="container text-center">
                    &copy; Evolevel {{ date('Y') }}
                </div>
            </footer>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const sidebar = document.querySelector('.sidebar');
            const toggleDesktop = document.getElementById('sidebarToggle');
            const toggleMobile = document.getElementById('sidebarToggleTop');
            const overlay = document.getElementById('sidebarOverlay');

            // DESKTOP TOGGLE
            toggleDesktop?.addEventListener('click', function (e) {
                e.preventDefault();
                sidebar.classList.toggle('toggled');
            });

            // MOBILE TOGGLE
            toggleMobile?.addEventListener('click', function (e) {
                e.preventDefault();
                document.body.classList.toggle('sidebar-open');
            });

            // OVERLAY CLICK (mobile)
            overlay?.addEventListener('click', function () {
                document.body.classList.remove('sidebar-open');
            });

            // RESET SAAT RESIZE
            window.addEventListener('resize', function () {
                if (window.innerWidth >= 992) {
                    document.body.classList.remove('sidebar-open');
                }
            });
        });
    </script>

    <!-- jQuery (WAJIB SEBELUM DATATABLES) -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    @stack('scripts')
</body>

</html>