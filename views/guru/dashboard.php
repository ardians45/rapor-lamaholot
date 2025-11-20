<?php
session_start();
// Cek apakah user sudah login dan rolenya guru
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] != 'guru') {
    header("Location: ../../views/login.php?error=Anda tidak memiliki akses");
    exit;
}
require_once '../../config/database.php';

// Mendapatkan informasi guru dari session
$user_id = $_SESSION['user_id'];
$guru_info = $koneksi->query("SELECT * FROM guru WHERE user_id = $user_id")->fetch_assoc();

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Dashboard Guru - SD Lamaholot</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root {
            --ntt-primary: #e74c3c;
            --ntt-secondary: #f39c12;
            --ntt-accent: #27ae60;
            --card-bg: #ffffff;
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
            padding-bottom: 1rem;
        }

        .sidebar {
            background: var(--ntt-accent);
            color: white;
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
            background: linear-gradient(135deg, var(--ntt-accent) 0%, var(--ntt-secondary) 100%);
            color: white;
            border-radius: 1rem;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: var(--shadow-lg);
        }

        .menu-card {
            border-radius: 1rem;
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            margin-bottom: 1rem;
            border: none;
            text-decoration: none;
            color: inherit;
        }

        .menu-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
            text-decoration: none;
            color: inherit;
        }

        .menu-card .card-body {
            padding: 1.5rem;
        }

        .menu-icon {
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }

        .hamburger {
            background: none;
            border: none;
            font-size: 1.5rem;
            color: var(--ntt-accent);
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

        .menu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 1rem;
        }

        .guru-info {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .guru-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--ntt-primary) 0%, var(--ntt-secondary) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
            font-weight: bold;
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
                <a class="nav-link" href="input_nilai.php">
                    <i class="bi bi-pencil-square"></i> Input Nilai
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="rekap_nilai.php">
                    <i class="bi bi-journal-text"></i> Rekap Nilai
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="preview_rapor.php?tahun_ajaran=<?php echo date('Y').'/'.(date('Y')+1); ?>&semester=<?php echo (date('n') > 6) ? 1 : 2; ?>">
                    <i class="bi bi-file-earmark-text"></i> Preview Rapor
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
                        <h2 class="mb-1">Dashboard Guru</h2>
                        <p class="text-muted mb-0">Sistem Manajemen Rapor Online</p>
                    </div>
                    <div class="d-none d-md-block">
                        <span class="badge bg-success">Role: <?php echo htmlspecialchars($_SESSION['role']); ?></span>
                    </div>
                </div>
            </div>

            <div class="guru-info">
                <div class="guru-avatar">
                    <?php echo strtoupper(substr($guru_info['nama'], 0, 1)); ?>
                </div>
                <div>
                    <h3 class="mb-0"><?php echo htmlspecialchars($guru_info['nama']); ?></h3>
                    <p class="text-muted mb-0">NIP: <?php echo htmlspecialchars($guru_info['nip']); ?></p>
                </div>
            </div>

            <div class="welcome-card">
                <h3 class="mb-3"><i class="bi bi-emoji-smile"></i> Selamat Datang!</h3>
                <p class="mb-0">Halo, <strong><?php echo htmlspecialchars($guru_info['nama']); ?></strong>! Anda sedang masuk sebagai guru di sistem rapor online SD Lamaholot. Gunakan menu di bawah untuk mengelola nilai siswa.</p>
            </div>

            <div class="mb-4">
                <h3 class="mb-3"><i class="bi bi-menu-button-wide"></i> Menu Utama</h3>
                <div class="menu-grid">
                    <a href="input_nilai.php" class="menu-card card text-decoration-none">
                        <div class="card-body text-center">
                            <div class="menu-icon text-primary">
                                <i class="bi bi-pencil-square"></i>
                            </div>
                            <h5 class="card-title">Input & Kelola Nilai</h5>
                            <p class="card-text text-muted">Input nilai siswa dan kelola data nilai</p>
                        </div>
                    </a>
                    <a href="rekap_nilai.php" class="menu-card card text-decoration-none">
                        <div class="card-body text-center">
                            <div class="menu-icon text-success">
                                <i class="bi bi-journal-text"></i>
                            </div>
                            <h5 class="card-title">Rekap Nilai</h5>
                            <p class="card-text text-muted">Lihat dan cetak rekapitulasi nilai siswa</p>
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

        // Add hover effect to menu cards
        const menuCards = document.querySelectorAll('.menu-card');
        menuCards.forEach(card => {
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