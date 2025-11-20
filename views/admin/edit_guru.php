<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] != 'admin') {
    header("Location: ../../views/login.php?error=Anda tidak memiliki akses");
    exit;
}
require_once '../../config/database.php';

// Ambil ID guru dari parameter
$guru_id = $_GET['id'] ?? null;

if (!$guru_id) {
    header("Location: manage_guru.php?error=ID guru tidak ditemukan.");
    exit;
}

// Ambil data guru berdasarkan ID
$guru_stmt = $koneksi->prepare(
    "SELECT g.id, g.nip, g.nama, u.username, u.role
     FROM guru g
     JOIN users u ON g.user_id = u.id
     WHERE g.id = ?"
);
$guru_stmt->bind_param("i", $guru_id);
$guru_stmt->execute();
$guru = $guru_stmt->get_result()->fetch_assoc();

if (!$guru) {
    header("Location: manage_guru.php?error=Data guru tidak ditemukan.");
    exit;
}

// Proses update jika form disubmit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nip = $_POST['nip'] ?? '';
    $nama = $_POST['nama'] ?? '';
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // Validasi input
    if (empty($nip) || empty($nama) || empty($username)) {
        $error = "Semua field wajib diisi.";
    } else {
        // Cek apakah NIP atau username sudah digunakan oleh guru lain
        $check_stmt = $koneksi->prepare("
            SELECT g.id FROM guru g
            JOIN users u ON g.user_id = u.id
            WHERE (g.nip = ? OR u.username = ?) AND g.id != ?
        ");
        $check_stmt->bind_param("ssi", $nip, $username, $guru_id);
        $check_stmt->execute();
        if ($check_stmt->get_result()->num_rows > 0) {
            $error = "NIP atau Username sudah terdaftar oleh guru lain.";
        } else {
            // Update data guru
            $koneksi->begin_transaction();

            try {
                // Update tabel users
                if (!empty($password)) {
                    // Update dengan password baru
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $user_stmt = $koneksi->prepare("UPDATE users SET username = ?, password = ? WHERE id = ?");
                    $user_stmt->bind_param("ssi", $username, $hashed_password, $guru['user_id']);
                } else {
                    // Update tanpa password
                    $user_stmt = $koneksi->prepare("UPDATE users SET username = ? WHERE id = ?");
                    $user_stmt->bind_param("si", $username, $guru['user_id']);
                }
                $user_stmt->execute();

                // Update tabel guru
                $guru_update_stmt = $koneksi->prepare("UPDATE guru SET nip = ?, nama = ? WHERE id = ?");
                $guru_update_stmt->bind_param("ssi", $nip, $nama, $guru_id);
                $guru_update_stmt->execute();

                $koneksi->commit();
                header("Location: manage_guru.php?success=Data guru berhasil diperbarui.");
                exit;

            } catch (Exception $e) {
                $koneksi->rollback();
                $error = "Gagal memperbarui data guru: " . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Edit Guru - <?php echo htmlspecialchars($guru['nama']); ?></title>
    <!-- CSS -->
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
            color: var(--ntt-primary);
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


        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 1rem;
            border-radius: 0.75rem;
            border: 1px solid #f5c6cb;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <div class="top-nav d-flex d-md-none">
        <a href="manage_guru.php" class="btn btn-outline-secondary btn-sm">
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
                    <h2 class="mb-1"><i class="bi bi-pencil-square me-2"></i> Edit Data Guru</h2>
                    <p class="text-muted mb-0">Formulir edit data guru SD Lamaholot</p>
                </div>
            </div>
        </div>

        <?php if (isset($error)): ?>
            <div class="error-message" role="alert">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-body p-0">
                <form method="POST" action="">
                    <div class="form-section">
                        <h5><i class="bi bi-person section-icon"></i> Data Diri Guru</h5>
                        <div class="mb-3">
                            <label for="nip" class="form-label">NIP <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-card-text"></i></span>
                                <input type="text" class="form-control" id="nip" name="nip"
                                       value="<?php echo htmlspecialchars($guru['nip']); ?>" required placeholder="Masukkan NIP guru">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="nama" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-person"></i></span>
                                <input type="text" class="form-control" id="nama" name="nama"
                                       value="<?php echo htmlspecialchars($guru['nama']); ?>" required placeholder="Masukkan nama lengkap guru">
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <h5><i class="bi bi-shield-lock section-icon"></i> Akun Login Guru</h5>
                        <div class="mb-3">
                            <label for="username" class="form-label">Username <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-person-circle"></i></span>
                                <input type="text" class="form-control" id="username" name="username"
                                       value="<?php echo htmlspecialchars($guru['username']); ?>" required placeholder="Masukkan username unik">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password (kosongkan jika tidak ingin mengganti)</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                <input type="password" class="form-control" id="password" name="password"
                                       placeholder="Ketik password baru jika ingin mengganti">
                            </div>
                        </div>
                    </div>

                    <div class="form-actions">
                        <a href="manage_guru.php" class="btn btn-back">
                            <i class="bi bi-arrow-left"></i> Kembali
                        </a>
                        <button type="submit" class="btn btn-submit">
                            <i class="bi bi-save"></i> Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>