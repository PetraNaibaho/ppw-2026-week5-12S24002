<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'SIPUS-Del' }} — Institut Teknologi Del</title>
    <!-- Bootstrap 5 CSS & Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        :root {
            --del-purple: #4C1D95;
            --del-purple-dark: #3B0764;
            --del-purple-light: #6D28D9;
            --del-purple-subtle: #EDE9FE;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background-color: #f8fafc;
            color: #1e293b;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .navbar-del {
            background-color: var(--del-purple) !important;
            box-shadow: 0 4px 6px -1px rgba(76, 29, 149, 0.1), 0 2px 4px -2px rgba(76, 29, 149, 0.1);
        }

        .navbar-del .navbar-brand {
            font-weight: 700;
            letter-spacing: -0.5px;
            color: #ffffff !important;
        }

        .navbar-del .nav-link {
            color: rgba(255, 255, 255, 0.85) !important;
            font-weight: 500;
            transition: color 0.15s ease-in-out;
        }

        .navbar-del .nav-link:hover,
        .navbar-del .nav-link.active {
            color: #ffffff !important;
            font-weight: 600;
        }

        .btn-del {
            background-color: var(--del-purple);
            color: #ffffff;
            border: 1px solid var(--del-purple);
            font-weight: 500;
            transition: all 0.2s ease-in-out;
        }

        .btn-del:hover {
            background-color: var(--del-purple-dark);
            border-color: var(--del-purple-dark);
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(76, 29, 149, 0.25);
        }

        .btn-outline-del {
            color: var(--del-purple);
            border-color: var(--del-purple);
            font-weight: 500;
        }

        .btn-outline-del:hover {
            background-color: var(--del-purple);
            color: #ffffff;
        }

        .badge-del {
            background-color: var(--del-purple-subtle);
            color: var(--del-purple);
            font-weight: 600;
            border: 1px solid rgba(109, 40, 217, 0.2);
        }

        .card-custom {
            border: 1px solid #e2e8f0;
            border-radius: 0.75rem;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05);
            background-color: #ffffff;
        }

        .card-header-del {
            background-color: #ffffff;
            border-bottom: 2px solid var(--del-purple-subtle);
            font-weight: 600;
            color: var(--del-purple);
        }

        .table-custom thead th {
            background-color: #f1f5f9;
            color: #475569;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
            border-bottom: 1px solid #cbd5e1;
        }

        footer {
            margin-top: auto;
            background-color: #ffffff;
            border-top: 1px solid #e2e8f0;
        }
    </style>
</head>
<body>
    <!-- Top Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark navbar-del shadow-sm">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2" href="{{ route('buku.index') }}">
                <i class="bi bi-journal-bookmark-fill fs-4 text-warning"></i>
                <span>SIPUS-Del</span>
                <span class="badge bg-light text-dark text-wrap fs-8 px-2 py-1 ms-1 d-none d-sm-inline" style="font-size: 0.65rem; font-weight: 600;">
                    Institut Teknologi Del
                </span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto ms-lg-3">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('buku.*') ? 'active' : '' }}" href="{{ route('buku.index') }}">
                            <i class="bi bi-book me-1"></i> Katalog Buku
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('kategori.*') ? 'active' : '' }}" href="{{ route('kategori.index') }}">
                            <i class="bi bi-tags me-1"></i> Kategori
                        </a>
                    </li>
                </ul>
                <div class="d-flex align-items-center gap-2">
                    <a href="{{ route('buku.create') }}" class="btn btn-warning btn-sm fw-semibold shadow-sm text-dark">
                        <i class="bi bi-plus-circle-fill me-1"></i> Tambah Buku
                    </a>
                    <a href="{{ route('kategori.create') }}" class="btn btn-outline-light btn-sm fw-semibold">
                        <i class="bi bi-folder-plus me-1"></i> Tambah Kategori
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content Container -->
    <main class="container py-4">
        <!-- Flash Session Success Notification -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 d-flex align-items-center" role="alert">
                <i class="bi bi-check-circle-fill fs-5 me-2 text-success"></i>
                <div>{{ session('success') }}</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Flash Session Error Notification -->
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 d-flex align-items-center" role="alert">
                <i class="bi bi-exclamation-triangle-fill fs-5 me-2 text-danger"></i>
                <div>{{ session('error') }}</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Slot Content from Views -->
        {{ $slot }}
    </main>

    <!-- Footer -->
    <footer class="py-3 text-center text-muted small">
        <div class="container">
            <p class="mb-1 fw-medium text-dark">
                &copy; {{ date('Y') }} <strong>SIPUS-Del</strong> — Sistem Informasi Perpustakaan Kampus Del
            </p>
            <p class="mb-0 text-secondary" style="font-size: 0.8rem;">
                Praktikum Pemrograman & Pengujian Web (12S3101) &bull; Institut Teknologi Del, Sitoluama, Laguboti, Toba
            </p>
        </div>
    </footer>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
