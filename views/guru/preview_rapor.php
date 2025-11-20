<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] != 'guru') {
    header("Location: ../../views/login.php?error=Anda tidak memiliki akses");
    exit;
}
require_once '../../config/database.php';

// Validasi GET parameter
$siswa_id = $_GET['siswa_id'] ?? null;
$semester = $_GET['semester'] ?? null;
$tahun_ajaran = $_GET['tahun_ajaran'] ?? null;

if (!$siswa_id || !$semester || !$tahun_ajaran) {
    die("Parameter tidak lengkap untuk menampilkan preview rapor.");
}

// Ambil data siswa
$siswa_info_stmt = $koneksi->prepare("SELECT * FROM siswa WHERE id = ?");
$siswa_info_stmt->bind_param("i", $siswa_id);
$siswa_info_stmt->execute();
$siswa_info = $siswa_info_stmt->get_result()->fetch_assoc();
if (!$siswa_info) die('Data siswa tidak ditemukan.');

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
    <title>Preview Rapor - <?php echo htmlspecialchars($siswa_info['nama']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        .rapor-container { max-width: 800px; margin: auto; }
        .rapor-header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 10px; margin-bottom: 20px; }
    </style>
</head>
<body>

    <div class="container rapor-container mt-4">
        <div class="rapor-header">
            <h4>LAPORAN HASIL BELAJAR (RAPOR)</h4>
            <h5>SEKOLAH DASAR LAMAHOLOT</h5>
        </div>

        <div class="d-flex justify-content-end mb-3">
            <a href="../../cetak_rapor_by_guru.php?siswa_id=<?php echo $siswa_id; ?>&semester=<?php echo $semester; ?>&tahun_ajaran=<?php echo urlencode($tahun_ajaran); ?>" class="btn btn-primary" target="_blank"><i class="bi bi-file-earmark-pdf"></i> Cetak ke PDF</a>
        </div>
        
        <table class="table table-sm table-borderless mb-4">
             <tr>
                <td width="20%"><strong>Nama Siswa</strong></td><td width="2%">:</td><td><?php echo htmlspecialchars($siswa_info['nama']); ?></td>
                <td width="20%"><strong>Kelas</strong></td><td width="2%">:</td><td><?php echo htmlspecialchars($siswa_info['kelas']); ?></td>
            </tr>
            <tr>
                <td><strong>NIS</strong></td><td>:</td><td><?php echo htmlspecialchars($siswa_info['nis']); ?></td>
                <td><strong>Semester</strong></td><td>:</td><td><?php echo htmlspecialchars($semester == 1 ? 'Ganjil' : 'Genap'); ?></td>
            </tr>
            <tr>
                <td><strong>Tahun Ajaran</strong></td><td>:</td><td colspan="4"><?php echo htmlspecialchars($tahun_ajaran); ?></td>
            </tr>
        </table>
        
        <table class="table table-bordered">
            <thead class="table-light text-center">
                <tr>
                    <th>No</th>
                    <th>Mata Pelajaran</th>
                    <th>KKM</th>
                    <th>Nilai Angka</th>
                    <th>Predikat</th>
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
                <tr><td colspan="5" class="text-center fst-italic">Data nilai untuk periode ini belum tersedia.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>

        <div class="text-center mt-4">
            <a href="rekap_nilai.php" class="btn btn-secondary">Kembali ke Halaman Rekap</a>
        </div>
    </div>
</body>
</html>
