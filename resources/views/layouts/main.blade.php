<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Evolevel - @yield('title', 'Dashboard')</title>

    {{-- Font dan CSS --}}
    <link href="{{ asset('vendor-assets/fontawesome-free/css/all.min.css') }}" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,300,400,600,700,900" rel="stylesheet">
    <link href="{{ asset('css/sb-admin-2.min.css') }}" rel="stylesheet">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- DataTables with Bootstrap 5 integration -->
    <link href="https://cdn.jsdelivr.net/npm/datatables.net-bs5@1.13.6/css/dataTables.bootstrap5.min.css"
        rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        /* ===== SIDEBAR ===== */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: 250px;
            overflow-y: auto;
            z-index: 1030;
            transition: all 0.3s ease;
        }

        /* ===== CONTENT ===== */
        #content-wrapper {
            margin-left: 220px;
            min-height: 100vh;
            transition: all 0.3s ease;
        }

        /* ===== TOPBAR ===== */
        .topbar {
            position: sticky;
            top: 0;
            z-index: 1020;
        }

        /* ===== DESKTOP COLLAPSE (KECIL, BUKAN HILANG) ===== */
        body.sidebar-collapsed .sidebar {
            width: 80px;
        }

        body.sidebar-collapsed #content-wrapper {
            margin-left: 80px;
        }

        /* sembunyikan teks menu */
        body.sidebar-collapsed .sidebar .nav-link span,
        body.sidebar-collapsed .sidebar .sidebar-brand-text {
            display: none;
        }

        body.sidebar-collapsed .sidebar .nav-link {
            text-align: center;
        }

        /* ===== MOBILE ===== */
        @media (max-width: 991.98px) {
            .sidebar {
                transform: translateX(-100%);
            }

            body.sidebar-open .sidebar {
                transform: translateX(0);
            }

            #content-wrapper {
                margin-left: 0;
            }
        }

        /* ===== EXTRA ===== */
        body {
            background-color: #f8f9fa;
        }

        .active {
            font-weight: bold !important;
            color: #fff !important;
            background-color: #07439f !important;
        }

        .sidebar .nav-item {
            width: 100%;
        }
    </style>

    @stack('head')
</head>

<body id="page-top">
    <div id="wrapper">
        <!-- Sidebar -->
        <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">
            <!-- Sidebar - Brand -->
            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="#">
                <div class="sidebar-brand-icon rotate-n-15">
                    <i class="fas fa-laugh-wink"></i>
                </div>
                <div class="sidebar-brand-text mx-3">Evolevel</div>
            </a>

            <hr class="sidebar-divider my-0">

            {{-- Jika role = student --}}
            @if (Auth::user()->role === 'student')
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

            {{-- Jika role = teacher --}}
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
                        <i class="fas fa-fw fa-book"></i>
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
                <button class="rounded-circle border-0" id="sidebarToggle" aria-label="Toggle sidebar"></button>
            </div>
        </ul>
        <!-- End Sidebar -->

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">

            <!-- Main Content -->
            <div id="content">

                <!-- Topbar -->
                <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
                    <!-- Sidebar Toggle (Topbar) untuk mobile -->
                    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle me-2"
                        aria-label="Toggle sidebar mobile">
                        <i class="fa fa-bars"></i>
                    </button>

                    <!-- Topbar Navbar -->
                    <ul class="navbar-nav ms-auto">
                        <!-- User Info -->
                        <li class="nav-item dropdown no-arrow">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                                data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span
                                    class="me-2 d-none d-lg-inline text-gray-600 small">{{ Auth::user()->name }}</span>
                                <i class="fas fa-user fa-lg"></i>
                            </a>

                            <div class="dropdown-menu dropdown-menu-end shadow animated--grow-in"
                                aria-labelledby="userDropdown">
                                <a class="dropdown-item" href="#"><i
                                        class="fas fa-user fa-sm fa-fw me-2 text-gray-400"></i> Profile</a>
                                <a class="dropdown-item" href="#"><i
                                        class="fas fa-cogs fa-sm fa-fw me-2 text-gray-400"></i> Settings</a>
                                <a class="dropdown-item" href="#"><i
                                        class="fas fa-list fa-sm fa-fw me-2 text-gray-400"></i> Activity Log</a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#logoutModal">
                                    <i class="fas fa-sign-out-alt fa-sm fa-fw me-2 text-gray-400"></i> Logout
                                </a>
                            </div>
                        </li>
                    </ul>
                </nav>
                <!-- End of Topbar -->

                <!-- Page Content -->
                <div class="container-fluid">
                    @yield('content')
                </div>

            </div>
            <!-- End of Main Content -->

            <!-- Footer -->
            <footer class="sticky-footer bg-white mt-auto py-3">
                <div class="container my-auto">
                    <div class="text-center text-gray-600">
                        &copy; Evolevel {{ date('Y') }}
                    </div>
                </div>
            </footer>

        </div>
        <!-- End of Content Wrapper -->

    </div>
    <!-- End of Page Wrapper -->

    <!-- Logout Modal -->
    <div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Ready to Leave?</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    Select "Logout" below if you are ready to end your current session.
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <a class="btn btn-primary" href="{{ url('/') }}">Logout</a>
                </div>
            </div>
        </div>
    </div>

    {{-- Script --}}
    <script src="{{ asset('vendor-assets/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('vendor-assets/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('vendor-assets/jquery-easing/jquery.easing.min.js') }}"></script>
    <script src="{{ asset('js/sb-admin-2.min.js') }}"></script>

    <!-- Bootstrap 5 bundle (CDN) - duplicate is fine if asset fallback exists, but if you prefer remove one -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        crossorigin="anonymous"></script>

    <script>
        // Toggle sidebar on desktop (collapse)
        document.getElementById('sidebarToggle')?.addEventListener('click', function (e) {
            e.preventDefault();
            document.body.classList.toggle('sidebar-collapsed');
        });

        // Toggle sidebar on mobile (slide in/out)
        document.getElementById('sidebarToggleTop')?.addEventListener('click', function (e) {
            e.preventDefault();
            document.body.classList.toggle('sidebar-open');
        });

        // Close sidebar-mobile when clicking outside (optional)
        document.addEventListener('click', function (e) {
            // if click is outside sidebar and not on toggle, close mobile sidebar
            const sidebar = document.querySelector('.sidebar');
            const isClickInsideSidebar = sidebar && sidebar.contains(e.target);
            const isToggle = e.target.closest('#sidebarToggleTop') || e.target.closest('#sidebarToggle');
            if (!isClickInsideSidebar && !isToggle && window.innerWidth < 992) {
                document.body.classList.remove('sidebar-open');
            }
        });
    </script>

    @stack('scripts')
</body>

</html>