<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] != 'guru') {
    header("Location: ../../views/login.php?error=Anda tidak memiliki akses");
    exit;
}
require_once '../../config/database.php';

// Data untuk dropdown
$mapel_list = $koneksi->query("SELECT id, nama_mapel FROM mapel ORDER BY nama_mapel");
$kelas_list = $koneksi->query("SELECT DISTINCT kelas FROM siswa WHERE kelas IS NOT NULL AND kelas != '' ORDER BY kelas");

// Ambil parameter dari URL (jika ada)
$selected_mapel_id = $_GET['mapel_id'] ?? null;
$selected_kelas = $_GET['kelas'] ?? null;
$selected_semester = $_GET['semester'] ?? null;
$selected_tahun = $_GET['tahun_ajaran'] ?? date('Y').'/'.(date('Y')+1);

$students = [];
if ($selected_mapel_id && $selected_kelas && $selected_semester && $selected_tahun) {
    $stmt = $koneksi->prepare(
        "SELECT s.id, s.nama, n.nilai_angka, n.predikat
         FROM siswa s
         LEFT JOIN nilai n ON s.id = n.siswa_id AND n.mapel_id = ? AND n.semester = ? AND n.tahun_ajaran = ?
         WHERE s.kelas = ?
         ORDER BY s.nama"
    );
    $stmt->bind_param("isss", $selected_mapel_id, $selected_semester, $selected_tahun, $selected_kelas);
    $stmt->execute();
    $students = $stmt->get_result();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Input Nilai Siswa - SD Lamaholot</title>
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
            background: linear-gradient(135deg, var(--ntt-accent) 0%, var(--ntt-secondary) 100%);
            color: white;
            padding: 1rem 1.5rem;
            border: none;
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
            box-shadow: 0 0 0 0.25rem rgba(39, 174, 96, 0.25);
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
            background: #219653;
            transform: translateY(-2px);
        }

        .btn-success {
            background: var(--ntt-primary);
            border: none;
        }

        .btn-success:hover {
            background: #c0392b;
            transform: translateY(-2px);
        }

        .btn-back {
            background: var(--ntt-secondary);
            border: none;
        }

        .btn-back:hover {
            background: #e67e22;
            transform: translateY(-2px);
        }

        .form-actions {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            margin-top: 1.5rem;
        }

        @media (min-width: 576px) {
            .form-actions {
                flex-direction: row;
                justify-content: flex-end;
            }
        }

        .table-container {
            overflow-x: auto;
        }

        .table {
            margin-bottom: 0;
        }

        .table th {
            background-color: #f8f9fa;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .input-group {
            display: flex;
            align-items: center;
        }

        .input-group-text {
            background: #f8f9fa;
            border: 2px solid #e0e0e0;
            border-right: none;
            border-radius: 0.75rem 0 0 0.75rem;
        }

        .input-group .form-control {
            border-left: none;
            border-radius: 0 0.75rem 0.75rem 0;
        }

        .mobile-card {
            display: none;
        }

        @media (max-width: 767.98px) {
            .mobile-card {
                display: block;
                margin-bottom: 1rem;
            }

            .desktop-table {
                display: none;
            }

            .card-body {
                padding: 1rem;
            }

            .page-header h3 {
                font-size: 1.5rem;
            }

            .btn {
                width: 100%;
            }
        }

        .siswa-item {
            background: white;
            border-radius: 1rem;
            padding: 1rem;
            margin-bottom: 1rem;
            box-shadow: var(--shadow);
            border-left: 4px solid var(--ntt-accent);
        }

        .siswa-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.5rem;
        }

        .siswa-name {
            font-weight: 600;
            font-size: 1.1rem;
        }

        .siswa-details {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            margin: 0.5rem 0;
        }

        .detail-group {
            display: flex;
            flex-direction: column;
        }

        .detail-label {
            font-size: 0.8rem;
            color: #6c757d;
            margin-bottom: 0.25rem;
        }

        .detail-value {
            font-weight: 500;
        }

        .input-container {
            display: flex;
            gap: 0.5rem;
        }

        .input-container .form-control {
            flex: 1;
        }

        .alert {
            border-radius: 0.75rem;
            border: none;
        }

        .form-row {
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <div class="top-nav d-flex d-md-none">
        <a href="dashboard.php" class="btn btn-outline-secondary btn-sm">
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
                    <h2 class="mb-1"><i class="bi bi-pencil-square me-2"></i> Input & Kelola Nilai Siswa</h2>
                    <p class="text-muted mb-0">Formulir input nilai siswa SD Lamaholot</p>
                </div>
            </div>
        </div>

        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success" role="alert"><?php echo htmlspecialchars($_GET['success']); ?></div>
        <?php endif; ?>
        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger" role="alert"><?php echo htmlspecialchars($_GET['error']); ?></div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-filter me-2"></i> Pilih Kriteria</h5>
            </div>
            <div class="card-body">
                <form method="GET" action="">
                    <div class="row g-3">
                        <div class="col-12 col-md-3">
                            <div class="form-row">
                                <label for="tahun_ajaran" class="form-label">Tahun Ajaran</label>
                                <input type="text" class="form-control" name="tahun_ajaran" id="tahun_ajaran" value="<?php echo htmlspecialchars($selected_tahun); ?>" placeholder="Contoh: 2024/2025">
                            </div>
                        </div>
                        <div class="col-12 col-md-3">
                            <div class="form-row">
                                <label for="semester" class="form-label">Semester</label>
                                <select name="semester" id="semester" class="form-select" required>
                                    <option value="">-- Pilih --</option>
                                    <option value="1" <?php echo $selected_semester == '1' ? 'selected' : ''; ?>>Ganjil</option>
                                    <option value="2" <?php echo $selected_semester == '2' ? 'selected' : ''; ?>>Genap</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-12 col-md-3">
                            <div class="form-row">
                                <label for="kelas" class="form-label">Kelas</label>
                                <select name="kelas" id="kelas" class="form-select" required>
                                    <option value="">-- Pilih --</option>
                                    <?php while($k = $kelas_list->fetch_assoc()): ?>
                                        <option value="<?php echo htmlspecialchars($k['kelas']); ?>" <?php echo $selected_kelas == $k['kelas'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($k['kelas']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-12 col-md-3">
                            <div class="form-row">
                                <label for="mapel_id" class="form-label">Mata Pelajaran</label>
                                <select name="mapel_id" id="mapel_id" class="form-select" required>
                                    <option value="">-- Pilih --</option>
                                    <?php while($m = $mapel_list->fetch_assoc()): ?>
                                        <option value="<?php echo $m['id']; ?>" <?php echo $selected_mapel_id == $m['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($m['nama_mapel']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-search"></i> Tampilkan Siswa
                                </button>
                                <a href="dashboard.php" class="btn btn-back">
                                    <i class="bi bi-arrow-left"></i> Kembali
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <?php if ($students && $students->num_rows > 0): ?>
        <form action="../../actions/input_nilai.php" method="POST">
            <input type="hidden" name="tahun_ajaran" value="<?php echo htmlspecialchars($selected_tahun); ?>">
            <input type="hidden" name="semester" value="<?php echo htmlspecialchars($selected_semester); ?>">
            <input type="hidden" name="kelas" value="<?php echo htmlspecialchars($selected_kelas); ?>">
            <input type="hidden" name="mapel_id" value="<?php echo htmlspecialchars($selected_mapel_id); ?>">

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-people me-2"></i>
                        Daftar Siswa Kelas <?php echo htmlspecialchars($selected_kelas); ?>
                        <span class="badge bg-light text-dark ms-2"><?php echo $students->num_rows; ?> siswa</span>
                    </h5>
                </div>
                <div class="card-body">
                    <div class="desktop-table">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th><i class="bi bi-hash"></i> No</th>
                                        <th><i class="bi bi-person"></i> Nama Siswa</th>
                                        <th><i class="bi bi-123"></i> Nilai Angka</th>
                                        <th><i class="bi bi-card-text"></i> Predikat</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $i = 1; while($s = $students->fetch_assoc()): ?>
                                    <input type="hidden" name="siswa_id[]" value="<?php echo $s['id']; ?>">
                                    <tr>
                                        <td><?php echo $i++; ?></td>
                                        <td><?php echo htmlspecialchars($s['nama']); ?></td>
                                        <td>
                                            <input type="number" name="nilai_angka[]" class="form-control" min="0" max="100" value="<?php echo htmlspecialchars($s['nilai_angka']); ?>" placeholder="0-100">
                                        </td>
                                        <td>
                                            <input type="text" name="predikat[]" class="form-control" maxlength="2" value="<?php echo htmlspecialchars($s['predikat']); ?>" placeholder="A/B/C/D">
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="mobile-card">
                        <?php
                        // Reset result pointer to reuse for mobile view
                        $stmt = $koneksi->prepare(
                            "SELECT s.id, s.nama, n.nilai_angka, n.predikat
                             FROM siswa s
                             LEFT JOIN nilai n ON s.id = n.siswa_id AND n.mapel_id = ? AND n.semester = ? AND n.tahun_ajaran = ?
                             WHERE s.kelas = ?
                             ORDER BY s.nama"
                        );
                        $stmt->bind_param("isss", $selected_mapel_id, $selected_semester, $selected_tahun, $selected_kelas);
                        $stmt->execute();
                        $mobile_students = $stmt->get_result();
                        ?>
                        <?php $i = 1; while($s = $mobile_students->fetch_assoc()): ?>
                        <input type="hidden" name="siswa_id[<?php echo $i-1; ?>]" value="<?php echo $s['id']; ?>">
                        <div class="siswa-item">
                            <div class="siswa-header">
                                <div class="siswa-name">#<?php echo $i++; ?> <?php echo htmlspecialchars($s['nama']); ?></div>
                            </div>
                            <div class="siswa-details">
                                <div class="detail-group">
                                    <span class="detail-label">Nilai Angka</span>
                                    <input type="number" name="nilai_angka[<?php echo $i-2; ?>]" class="form-control" min="0" max="100" value="<?php echo htmlspecialchars($s['nilai_angka']); ?>" placeholder="0-100">
                                </div>
                                <div class="detail-group">
                                    <span class="detail-label">Predikat</span>
                                    <input type="text" name="predikat[<?php echo $i-2; ?>]" class="form-control" maxlength="2" value="<?php echo htmlspecialchars($s['predikat']); ?>" placeholder="A/B/C/D">
                                </div>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-save"></i> Simpan Semua Nilai
                        </button>
                    </div>
                </div>
            </div>
        </form>
        <?php elseif(isset($_GET['mapel_id'])): ?>
            <div class="alert alert-warning" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i>
                Tidak ada siswa yang ditemukan di kelas ini atau kriteria belum lengkap.
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
