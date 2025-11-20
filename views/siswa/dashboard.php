<?php
session_start();
// Cek apakah user sudah login dan rolenya siswa
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] != 'siswa') {
    header("Location: ../../views/login.php?error=Anda tidak memiliki akses");
    exit;
}
require_once '../../config/database.php';

// Mendapatkan informasi siswa dari session
$user_id = $_SESSION['user_id'];
$siswa_info = $koneksi->query("SELECT * FROM siswa WHERE user_id = $user_id")->fetch_assoc();
$current_year = date('Y').'/'.(date('Y')+1);

// Ambil daftar tahun ajaran yang ada nilainya untuk siswa ini, dan gabungkan dengan tahun ajaran saat ini
$query = "(SELECT DISTINCT tahun_ajaran FROM nilai WHERE siswa_id = {$siswa_info['id']})
          UNION
          (SELECT '{$current_year}')
          ORDER BY tahun_ajaran DESC";
$tahun_ajaran_list = $koneksi->query($query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Dashboard Siswa - SD Lamaholot</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root {
            --ntt-primary: #e74c3c;
            --ntt-secondary: #f39c12;
            --ntt-accent: #3498db;
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

        .tab-card {
            border-radius: 1rem;
            border: none;
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        .nav-tabs {
            border: none;
            padding: 0.5rem;
            background: white;
            border-radius: 0.75rem;
            margin-bottom: 1.5rem;
        }

        .nav-tabs .nav-link {
            border: none;
            border-radius: 0.5rem;
            padding: 0.75rem 1.5rem;
            margin: 0.25rem;
            transition: all 0.3s ease;
        }

        .nav-tabs .nav-link.active {
            background: var(--ntt-accent);
            color: white;
        }

        .form-label {
            font-weight: 600;
            color: var(--ntt-accent);
            margin-bottom: 0.5rem;
        }

        .form-control, .form-select {
            border-radius: 0.75rem;
            padding: 0.75rem 1rem;
            border: 2px solid #e0e0e0;
            transition: all 0.3s ease;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--ntt-accent);
            box-shadow: 0 0 0 0.25rem rgba(52, 152, 219, 0.25);
        }

        .btn {
            border-radius: 0.75rem;
            padding: 0.75rem 1.5rem;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .btn-primary {
            background: var(--ntt-accent);
            border: none;
        }

        .btn-primary:hover {
            background: #2980b9;
            transform: translateY(-2px);
        }

        .hamburger {
            background: none;
            border: none;
            font-size: 1.5rem;
            color: var(--ntt-accent);
            cursor: pointer;
            padding: 0.25rem 0.5rem;
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

        .profile-card {
            border-radius: 1rem;
            border: none;
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        .profile-item {
            padding: 1rem;
            border-bottom: 1px solid #e9ecef;
        }

        .profile-item:last-child {
            border-bottom: none;
        }

        .profile-label {
            font-weight: 600;
            color: var(--ntt-accent);
        }

        .profile-value {
            font-weight: 500;
        }

        .siswa-info {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .siswa-avatar {
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
                <a class="nav-link" href="preview_rapor.php">
                    <i class="bi bi-file-earmark-text"></i> Lihat Rapor
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
                        <h2 class="mb-1">Dashboard Siswa</h2>
                        <p class="text-muted mb-0">Sistem Manajemen Rapor Online</p>
                    </div>
                    <div class="d-none d-md-block">
                        <span class="badge bg-info">Role: <?php echo htmlspecialchars($_SESSION['role']); ?></span>
                    </div>
                </div>
            </div>

            <div class="siswa-info">
                <div class="siswa-avatar">
                    <?php echo strtoupper(substr($siswa_info['nama'], 0, 1)); ?>
                </div>
                <div>
                    <h3 class="mb-0"><?php echo htmlspecialchars(explode(' ', $siswa_info['nama'])[0]); ?></h3>
                    <p class="text-muted mb-0">Kelas: <?php echo htmlspecialchars($siswa_info['kelas']); ?></p>
                </div>
            </div>

            <div class="welcome-card">
                <h3 class="mb-3"><i class="bi bi-emoji-smile"></i> Selamat Datang!</h3>
                <p class="mb-0">Halo, <strong><?php echo htmlspecialchars(explode(' ', $siswa_info['nama'])[0]); ?></strong>! Ini adalah halaman dashboard pribadi Anda. Anda dapat melihat profil dan rapor Anda di sini.</p>
            </div>

            <div class="card tab-card">
                <ul class="nav nav-tabs" id="myTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="rapor-tab" data-bs-toggle="tab" data-bs-target="#rapor" type="button" role="tab" aria-controls="rapor" aria-selected="true">
                            <i class="bi bi-file-earmark-text me-2"></i> Lihat Rapor
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="profil-tab" data-bs-toggle="tab" data-bs-target="#profil" type="button" role="tab" aria-controls="profil" aria-selected="false">
                            <i class="bi bi-person me-2"></i> Profil Anda
                        </button>
                    </li>
                </ul>
                <div class="tab-content p-4" id="myTabContent">
                    <div class="tab-pane fade show active" id="rapor" role="tabpanel" aria-labelledby="rapor-tab">
                        <h4 class="mb-3"><i class="bi bi-file-earmark-text me-2"></i> Lihat & Cetak Rapor</h4>
                        <p class="text-muted mb-4">Silakan pilih tahun ajaran dan semester untuk melihat rapor Anda.</p>
                        <form action="preview_rapor.php" method="GET" class="row g-3">
                            <div class="col-12 col-md-5">
                                <label for="tahun_ajaran" class="form-label">Pilih Tahun Ajaran</label>
                                <select name="tahun_ajaran" id="tahun_ajaran" class="form-select" required>
                                    <option value="">-- Pilih Tahun --</option>
                                    <?php while($th = $tahun_ajaran_list->fetch_assoc()): ?>
                                        <option value="<?php echo htmlspecialchars($th['tahun_ajaran']); ?>"><?php echo htmlspecialchars($th['tahun_ajaran']); ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="col-12 col-md-5">
                                <label for="semester" class="form-label">Pilih Semester</label>
                                <select name="semester" id="semester" class="form-select" required>
                                    <option value="1">Ganjil</option>
                                    <option value="2">Genap</option>
                                </select>
                            </div>
                            <div class="col-12 col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="bi bi-eye"></i> Lihat
                                </button>
                            </div>
                        </form>
                    </div>
                    <div class="tab-pane fade" id="profil" role="tabpanel" aria-labelledby="profil-tab">
                        <h4 class="mb-3"><i class="bi bi-person me-2"></i> Profil Siswa</h4>
                        <div class="card profile-card">
                            <div class="profile-item">
                                <div class="row">
                                    <div class="col-4">
                                        <span class="profile-label">NIS:</span>
                                    </div>
                                    <div class="col-8">
                                        <span class="profile-value"><?php echo htmlspecialchars($siswa_info['nis']); ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="profile-item">
                                <div class="row">
                                    <div class="col-4">
                                        <span class="profile-label">Nama Lengkap:</span>
                                    </div>
                                    <div class="col-8">
                                        <span class="profile-value"><?php echo htmlspecialchars($siswa_info['nama']); ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="profile-item">
                                <div class="row">
                                    <div class="col-4">
                                        <span class="profile-label">Kelas:</span>
                                    </div>
                                    <div class="col-8">
                                        <span class="profile-value"><?php echo htmlspecialchars($siswa_info['kelas']); ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="profile-item">
                                <div class="row">
                                    <div class="col-4">
                                        <span class="profile-label">Jurusan:</span>
                                    </div>
                                    <div class="col-8">
                                        <span class="profile-value"><?php echo htmlspecialchars($siswa_info['jurusan']); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
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
    </script>
</body>
</html>