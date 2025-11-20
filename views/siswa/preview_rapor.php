<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] != 'siswa') {
    header("Location: ../../views/login.php?error=Anda tidak memiliki akses");
    exit;
}
require_once '../../config/database.php';

// Validasi GET parameter
$semester = $_GET['semester'] ?? null;
$tahun_ajaran = $_GET['tahun_ajaran'] ?? null;
$user_id = $_SESSION['user_id'];

if (!$semester || !$tahun_ajaran) {
    die("Parameter tidak lengkap untuk menampilkan preview rapor.");
}

// Ambil data siswa
$siswa_info_stmt = $koneksi->prepare("SELECT * FROM siswa WHERE user_id = ?");
$siswa_info_stmt->bind_param("i", $user_id);
$siswa_info_stmt->execute();
$siswa_info = $siswa_info_stmt->get_result()->fetch_assoc();
if (!$siswa_info) die('Data siswa tidak ditemukan.');
$siswa_id = $siswa_info['id'];

// Ambil data nilai
$nilai_stmt = $koneksi->prepare(
    "SELECT m.nama_mapel, m.kkm, n.nilai_angka, n.predikat
     FROM nilai n
     JOIN mapel m ON n.mapel_id = m.id
     WHERE n.siswa_id = ? AND n.semester = ? AND n.tahun_ajaran = ?
     ORDER BY m.nama_mapel ASC"
);
$nilai_stmt->bind_param("iis", $siswa_id, $semester, $tahun_ajaran);
$nilai_stmt->execute();
$nilai_result = $nilai_stmt->get_result();

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Preview Rapor - <?php echo htmlspecialchars($siswa_info['nama']); ?></title>
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

        .rapor-header {
            text-align: center;
            padding: 1.5rem;
            background: linear-gradient(135deg, var(--ntt-primary) 0%, var(--ntt-secondary) 100%);
            color: white;
            border-radius: 1rem 1rem 0 0;
            margin-bottom: 1.5rem;
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
            background: var(--ntt-primary);
            border: none;
        }

        .btn-primary:hover {
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

        .form-label {
            font-weight: 600;
            color: var(--ntt-accent);
            margin-bottom: 0.5rem;
        }

        .table-container {
            overflow-x: auto;
        }

        .table {
            margin-bottom: 0;
            border-collapse: separate;
            border-spacing: 0;
        }

        .table th {
            background-color: #f8f9fa;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .table th, .table td {
            vertical-align: middle;
            border: 1px solid #dee2e6;
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

            .rapor-header h4, .rapor-header h5 {
                margin: 0.25rem 0;
            }
        }

        .nilai-item {
            background: white;
            border-radius: 1rem;
            padding: 1rem;
            margin-bottom: 1rem;
            box-shadow: var(--shadow);
        }

        .nilai-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.5rem;
        }

        .mapel-name {
            font-weight: 600;
            font-size: 1.1rem;
        }

        .nilai-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin: 0.5rem 0;
        }

        .detail-item {
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

        .status-empty {
            text-align: center;
            padding: 2rem;
            color: #6c757d;
            font-style: italic;
        }

        .siswa-info {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .info-item {
            display: flex;
            flex-direction: column;
        }

        .info-label {
            font-size: 0.8rem;
            color: #6c757d;
            margin-bottom: 0.25rem;
        }

        .info-value {
            font-weight: 500;
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
                    <h2 class="mb-1">Preview Rapor</h2>
                    <p class="text-muted mb-0">Laporan Hasil Belajar Siswa</p>
                </div>
            </div>
        </div>

        <div class="rapor-header">
            <h4 class="mb-1">LAPORAN HASIL BELAJAR (RAPOR)</h4>
            <h5 class="mb-0">SEKOLAH DASAR LAMAHOLOT</h5>
        </div>

        <div class="d-flex flex-column flex-md-row justify-content-between mb-4 gap-2">
            <a href="dashboard.php" class="btn btn-back d-flex align-items-center justify-content-center">
                <i class="bi bi-arrow-left me-2"></i> Kembali
            </a>
            <a href="../../cetak_rapor.php?semester=<?php echo $semester; ?>&tahun_ajaran=<?php echo urlencode($tahun_ajaran); ?>" class="btn btn-primary d-flex align-items-center justify-content-center">
                <i class="bi bi-file-earmark-pdf me-2"></i> Cetak ke PDF
            </a>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="siswa-info">
                    <div class="info-item">
                        <span class="info-label">Nama Siswa</span>
                        <span class="info-value"><?php echo htmlspecialchars($siswa_info['nama']); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Kelas</span>
                        <span class="info-value"><?php echo htmlspecialchars($siswa_info['kelas']); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">NIS</span>
                        <span class="info-value"><?php echo htmlspecialchars($siswa_info['nis']); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Semester</span>
                        <span class="info-value"><?php echo htmlspecialchars($semester == 1 ? 'Ganjil' : 'Genap'); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Tahun Ajaran</span>
                        <span class="info-value"><?php echo htmlspecialchars($tahun_ajaran); ?></span>
                    </div>
                </div>

                <div class="desktop-table">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table-light text-center">
                                <tr>
                                    <th width="5%">No</th>
                                    <th width="40%">Mata Pelajaran</th>
                                    <th width="10%">KKM</th>
                                    <th width="15%">Nilai Angka</th>
                                    <th width="10%">Predikat</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php $no = 1; if ($nilai_result->num_rows > 0): ?>
                                <?php while($row = $nilai_result->fetch_assoc()): ?>
                                <tr>
                                    <td class="text-center"><?php echo $no++; ?></td>
                                    <td><?php echo htmlspecialchars($row['nama_mapel']); ?></td>
                                    <td class="text-center"><?php echo htmlspecialchars($row['kkm']); ?></td>
                                    <td class="text-center"><?php echo htmlspecialchars($row['nilai_angka']); ?></td>
                                    <td class="text-center"><?php echo htmlspecialchars($row['predikat']); ?></td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center fst-italic status-empty">Data nilai untuk periode ini belum tersedia.</td>
                                </tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="mobile-card">
                    <?php
                    // Reset result pointer to reuse for mobile view
                    $nilai_stmt = $koneksi->prepare(
                        "SELECT m.nama_mapel, m.kkm, n.nilai_angka, n.predikat
                         FROM nilai n
                         JOIN mapel m ON n.mapel_id = m.id
                         WHERE n.siswa_id = ? AND n.semester = ? AND n.tahun_ajaran = ?
                         ORDER BY m.nama_mapel ASC"
                    );
                    $nilai_stmt->bind_param("iis", $siswa_id, $semester, $tahun_ajaran);
                    $nilai_stmt->execute();
                    $nilai_result_mobile = $nilai_stmt->get_result();
                    ?>
                    <?php if ($nilai_result_mobile->num_rows > 0): ?>
                        <?php $no = 1; while($row = $nilai_result_mobile->fetch_assoc()): ?>
                        <div class="nilai-item">
                            <div class="nilai-header">
                                <div class="mapel-name"><?php echo $no++; ?>. <?php echo htmlspecialchars($row['nama_mapel']); ?></div>
                            </div>
                            <div class="nilai-details">
                                <div class="detail-item">
                                    <span class="detail-label">KKM</span>
                                    <span class="detail-value"><?php echo htmlspecialchars($row['kkm']); ?></span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Nilai Angka</span>
                                    <span class="detail-value"><?php echo htmlspecialchars($row['nilai_angka']); ?></span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Predikat</span>
                                    <span class="detail-value"><?php echo htmlspecialchars($row['predikat']); ?></span>
                                </div>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="nilai-item status-empty">
                            Data nilai untuk periode ini belum tersedia.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
