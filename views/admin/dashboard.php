<?php
session_start();
// Cek apakah user sudah login dan rolenya admin
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] != 'admin') {
    header("Location: ../../views/login.php?error=Anda tidak memiliki akses");
    exit;
}
require_once '../../config/database.php';

// Ambil statistik sederhana
$total_guru = $koneksi->query("SELECT COUNT(*) AS total FROM guru")->fetch_assoc()['total'];
$total_siswa = $koneksi->query("SELECT COUNT(*) AS total FROM siswa")->fetch_assoc()['total'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Dashboard Admin - SD Lamaholot</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root {
            --ntt-primary: #e74c3c;
            --ntt-secondary: #f39c12;
            --ntt-accent: #2c3e50;
            --card-bg: #ffffff;
            --sidebar-bg: var(--ntt-accent);
            --text-light: #ffffff;
            --text-dark: #333333;
            --shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            --shadow-lg: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            overflow-x: hidden;
        }

        .sidebar {
            background: var(--sidebar-bg);
            color: var(--text-light);
            height: 100vh;
            position: fixed;
            top: 0;
            left: -280px;
            width: 280px;
            z-index: 1000;
            transition: left 0.3s ease;
            overflow-y: auto;
            padding-top: 3.5rem;
        }

        .sidebar.active {
            left: 0;
        }

        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.85);
            padding: 0.75rem 1.5rem;
            transition: all 0.2s;
            border-left: 3px solid transparent;
        }

        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            border-left: 3px solid var(--ntt-secondary);
        }

        .sidebar .nav-link i {
            margin-right: 10px;
            width: 24px;
            text-align: center;
        }

        .main-content {
            margin-left: 0;
            padding: 1rem;
            transition: margin-left 0.3s ease;
        }

        .main-content.expanded {
            margin-left: 280px;
        }

        .top-nav {
            background: white;
            box-shadow: var(--shadow);
            padding: 0.75rem 1rem;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .dashboard-header {
            margin-bottom: 1.5rem;
            padding: 1rem 0;
            border-bottom: 1px solid #e9ecef;
        }

        .welcome-card {
            background: linear-gradient(135deg, var(--ntt-primary) 0%, var(--ntt-secondary) 100%);
            color: white;
            border-radius: 1rem;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: var(--shadow-lg);
        }

        .stat-card {
            border-radius: 1rem;
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            margin-bottom: 1rem;
            border: none;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }

        .stat-card .card-body {
            padding: 1.5rem;
        }

        .stat-icon {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
        }

        .action-card {
            border-radius: 1rem;
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            margin-bottom: 1rem;
            border: none;
            text-decoration: none;
            color: inherit;
        }

        .action-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
            text-decoration: none;
            color: inherit;
        }

        .action-card .card-body {
            padding: 1.5rem;
        }

        .action-icon {
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }

        .hamburger {
            background: none;
            border: none;
            font-size: 1.5rem;
            color: var(--text-dark);
            cursor: pointer;
            padding: 0.75rem;
            border-radius: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 44px;
            height: 44px;
            transition: background-color 0.2s ease;
            margin-right: 0.5rem;
        }

        .hamburger:hover {
            background-color: rgba(0, 0, 0, 0.1);
        }

        .hamburger i {
            pointer-events: none; /* Prevent double click issues */
        }

        .welcome-text {
            font-size: 1.25rem;
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            line-height: 1;
        }

        .mobile-only {
            display: block;
        }

        .desktop-only {
            display: none;
        }

        @media (min-width: 768px) {
            .sidebar {
                left: 0;
            }

            .main-content {
                margin-left: 280px;
            }

            .mobile-only {
                display: none;
            }

            .desktop-only {
                display: block;
            }
        }

        .action-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 1rem;
        }
    </style>
</head>
<body>
    <!-- Mobile Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="p-3">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="text-white mb-0">SD Lamaholot</h5>
                <button class="btn text-white d-md-none" id="close-sidebar">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
        </div>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link active" href="dashboard.php">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="manage_guru.php">
                    <i class="bi bi-person-video3"></i> Kelola Guru
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="manage_siswa.php">
                    <i class="bi bi-people-fill"></i> Kelola Siswa
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="../../actions/logout.php">
                    <i class="bi bi-box-arrow-right"></i> Logout
                </a>
            </li>
        </ul>
    </div>

    <div class="main-content" id="main-content">
        <!-- Top Navigation for Mobile -->
        <nav class="top-nav d-flex d-md-none">
            <button class="hamburger" id="hamburger">
                <i class="bi bi-list"></i>
            </button>
            <div class="ms-auto">
                <a href="../../actions/logout.php" class="btn btn-outline-danger btn-sm">
                    <i class="bi bi-box-arrow-right"></i>
                </a>
            </div>
        </nav>

        <div class="container-fluid">
            <div class="dashboard-header">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="mb-1">Dashboard Admin</h2>
                        <p class="text-muted mb-0">Manajemen Sistem Rapor Online</p>
                    </div>
                    <div class="d-none d-md-block">
                        <span class="badge bg-primary">Role: <?php echo htmlspecialchars($_SESSION['role']); ?></span>
                    </div>
                </div>
            </div>

            <div class="welcome-card">
                <h3 class="mb-3"><i class="bi bi-emoji-smile"></i> Selamat Datang!</h3>
                <p class="welcome-text mb-0">Halo, <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong>! Anda sedang masuk sebagai administrator sistem rapor online SD Lamaholot.</p>
            </div>

            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card bg-primary text-white stat-card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="stat-icon"><i class="bi bi-person-video3"></i></div>
                                    <p class="mb-1">Jumlah Guru</p>
                                    <div class="stat-number"><?php echo $total_guru; ?></div>
                                </div>
                                <div class="display-4 opacity-25"><i class="bi bi-person-video3"></i></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card bg-success text-white stat-card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="stat-icon"><i class="bi bi-people-fill"></i></div>
                                    <p class="mb-1">Jumlah Siswa</p>
                                    <div class="stat-number"><?php echo $total_siswa; ?></div>
                                </div>
                                <div class="display-4 opacity-25"><i class="bi bi-people-fill"></i></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mb-4">
                <h3 class="mb-3"><i class="bi bi-tools"></i> Manajemen Data Master</h3>
                <div class="action-grid">
                    <a href="manage_guru.php" class="action-card card text-decoration-none">
                        <div class="card-body">
                            <div class="action-icon text-primary">
                                <i class="bi bi-person-video3"></i>
                            </div>
                            <h5 class="card-title">Kelola Data Guru</h5>
                            <p class="card-text text-muted">Tambah, edit, dan hapus data guru</p>
                        </div>
                    </a>
                    <a href="manage_siswa.php" class="action-card card text-decoration-none">
                        <div class="card-body">
                            <div class="action-icon text-success">
                                <i class="bi bi-people-fill"></i>
                            </div>
                            <h5 class="card-title">Kelola Data Siswa</h5>
                            <p class="card-text text-muted">Tambah, edit, dan hapus data siswa</p>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Mobile sidebar functionality
        document.getElementById('hamburger').addEventListener('click', function() {
            document.getElementById('sidebar').classList.add('active');
            document.body.style.overflow = 'hidden';
        });

        document.getElementById('close-sidebar').addEventListener('click', function() {
            document.getElementById('sidebar').classList.remove('active');
            document.body.style.overflow = '';
        });

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(event) {
            const sidebar = document.getElementById('sidebar');
            const hamburger = document.getElementById('hamburger');

            if (window.innerWidth < 768 &&
                sidebar.classList.contains('active') &&
                !sidebar.contains(event.target) &&
                event.target !== hamburger) {
                sidebar.classList.remove('active');
                document.body.style.overflow = '';
            }
        });

        // Add hover effect to stat cards
        const statCards = document.querySelectorAll('.stat-card');
        statCards.forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-5px)';
            });

            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
            });
        });
    </script>
</body>
</html>