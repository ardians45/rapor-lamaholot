<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] != 'admin') {
    header("Location: ../../views/login.php?error=Anda tidak memiliki akses");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Tambah Siswa - SD Lamaholot</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root {
            --ntt-primary: #e74c3c;
            --ntt-secondary: #f39c12;
            --ntt-accent: #2c3e50;
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

        .top-nav {
            background: white;
            box-shadow: var(--shadow);
            padding: 0.75rem 1rem;
            position: sticky;
            top: 0;
            z-index: 100;
            margin-bottom: 1.5rem;
        }

        .page-header {
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #e9ecef;
        }

        .card {
            border-radius: 1rem;
            border: none;
            box-shadow: var(--shadow);
            margin-bottom: 1.5rem;
            overflow: hidden;
        }

        .card-header {
            background: linear-gradient(135deg, var(--ntt-primary) 0%, var(--ntt-secondary) 100%);
            color: white;
            padding: 1rem 1.5rem;
            border: none;
        }

        .form-section {
            padding: 1.5rem;
            border-bottom: 1px solid #e9ecef;
        }

        .form-section:last-child {
            border-bottom: none;
        }

        .form-section h5 {
            margin-bottom: 1.5rem;
            color: var(--ntt-accent);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .form-label {
            font-weight: 600;
            color: var(--ntt-accent);
            margin-bottom: 0.5rem;
        }

        .form-control {
            border-radius: 0.75rem;
            padding: 0.75rem 1rem;
            border: 2px solid #e0e0e0;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--ntt-primary);
            box-shadow: 0 0 0 0.25rem rgba(231, 76, 60, 0.25);
        }

        .form-text {
            font-size: 0.85rem;
            color: #6c757d;
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

        .btn-submit {
            background: var(--ntt-primary);
            border: none;
        }

        .btn-submit:hover {
            background: #c0392b;
            transform: translateY(-2px);
        }

        .btn-back {
            background: var(--ntt-accent);
            border: none;
        }

        .btn-back:hover {
            background: #1a252f;
            transform: translateY(-2px);
        }

        .form-actions {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            padding: 1.5rem;
        }

        @media (min-width: 576px) {
            .form-actions {
                flex-direction: row;
                justify-content: flex-end;
            }
        }

        .section-icon {
            color: var(--ntt-secondary);
        }

        .input-group {
            display: flex;
            align-items: stretch;
        }

        .input-group-text {
            background: #f8f9fa;
            border: 2px solid #e0e0e0;
            border-right: none;
            border-radius: 0.75rem 0 0 0.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            min-width: 50px; /* Fixed width for icon container */
            text-align: center;
        }

        .input-group > .form-control {
            border-left: none;
            border-radius: 0 0.75rem 0.75rem 0;
            flex: 1; /* Take remaining space */
        }

    </style>
</head>
<body>
    <div class="top-nav d-flex d-md-none">
        <a href="manage_siswa.php" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div class="ms-auto">
            <a href="../../actions/logout.php" class="btn btn-outline-danger btn-sm">
                <i class="bi bi-box-arrow-right"></i>
            </a>
        </div>
    </div>

    <div class="container-fluid">
        <div class="page-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-1"><i class="bi bi-person-plus-fill me-2"></i> Tambah Siswa</h2>
                    <p class="text-muted mb-0">Formulir penambahan data siswa SD Lamaholot</p>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body p-0">
                <form action="../../actions/crud_siswa.php?action=create" method="POST">
                    <div class="form-section">
                        <h5><i class="bi bi-person-bounding-box section-icon"></i> Data Diri Siswa</h5>
                        <div class="mb-3">
                            <label for="nis" class="form-label">NIS (Nomor Induk Siswa)</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-card-text"></i></span>
                                <input type="text" class="form-control" id="nis" name="nis" required placeholder="Masukkan NIS siswa">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="nama" class="form-label">Nama Lengkap</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-person"></i></span>
                                <input type="text" class="form-control" id="nama" name="nama" required placeholder="Masukkan nama lengkap siswa">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="kelas" class="form-label">Kelas</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-door-open"></i></span>
                                <input type="text" class="form-control" id="kelas" name="kelas" placeholder="Masukkan kelas siswa">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="jurusan" class="form-label">Jurusan</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-journal-text"></i></span>
                                <input type="text" class="form-control" id="jurusan" name="jurusan" value="Reguler" placeholder="Masukkan jurusan siswa">
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <h5><i class="bi bi-shield-lock section-icon"></i> Akun Login Siswa</h5>
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-person-circle"></i></span>
                                <input type="text" class="form-control" id="username" name="username" required placeholder="Masukkan username unik">
                            </div>
                            <div class="form-text">Saran: gunakan NIS sebagai username agar unik.</div>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                <input type="password" class="form-control" id="password" name="password" required placeholder="Masukkan password">
                            </div>
                            <div class="form-text">Password default untuk pengguna baru.</div>
                        </div>
                    </div>

                    <div class="form-actions">
                        <a href="manage_siswa.php" class="btn btn-back">
                            <i class="bi bi-arrow-left"></i> Kembali
                        </a>
                        <button type="submit" class="btn btn-submit">
                            <i class="bi bi-save"></i> Simpan Data
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
