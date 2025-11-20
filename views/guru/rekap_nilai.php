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

// Ambil parameter dari URL
$selected_mapel_id = $_GET['mapel_id'] ?? null;
$selected_kelas = $_GET['kelas'] ?? null;
$selected_semester = $_GET['semester'] ?? null;
$selected_tahun = $_GET['tahun_ajaran'] ?? date('Y').'/'.(date('Y')+1);

$grades = [];
if ($selected_mapel_id && $selected_kelas && $selected_semester && $selected_tahun) {
    $stmt = $koneksi->prepare(
        "SELECT s.id as siswa_id, s.nis, s.nama, n.nilai_angka, n.predikat 
         FROM siswa s 
         JOIN nilai n ON s.id = n.siswa_id
         WHERE s.kelas = ? AND n.mapel_id = ? AND n.semester = ? AND n.tahun_ajaran = ?
         ORDER BY s.nama"
    );
    $stmt->bind_param("siss", $selected_kelas, $selected_mapel_id, $selected_semester, $selected_tahun);
    $stmt->execute();
    $grades = $stmt->get_result();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekap Nilai Siswa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-success">
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboard.php">Dashboard Guru</a>
        </div>
    </nav>

    <div class="container mt-4">
        <h3><i class="bi bi-journal-text"></i> Rekapitulasi Nilai Siswa</h3>
        
        <div class="card">
            <div class="card-header">Pilih Kriteria</div>
            <div class="card-body">
                <form method="GET" action="">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label for="tahun_ajaran" class="form-label">Tahun Ajaran</label>
                            <input type="text" class="form-control" name="tahun_ajaran" id="tahun_ajaran" value="<?php echo htmlspecialchars($selected_tahun); ?>">
                        </div>
                        <div class="col-md-3">
                            <label for="semester" class="form-label">Semester</label>
                            <select name="semester" id="semester" class="form-select" required>
                                <option value="1" <?php echo $selected_semester == '1' ? 'selected' : ''; ?>>Ganjil</option>
                                <option value="2" <?php echo $selected_semester == '2' ? 'selected' : ''; ?>>Genap</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="kelas" class="form-label">Kelas</label>
                            <select name="kelas" id="kelas" class="form-select" required>
                                <option value="">-- Pilih Kelas --</option>
                                <?php mysqli_data_seek($kelas_list, 0); while($k = $kelas_list->fetch_assoc()): ?>
                                    <option value="<?php echo htmlspecialchars($k['kelas']); ?>" <?php echo $selected_kelas == $k['kelas'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($k['kelas']); ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="mapel_id" class="form-label">Mata Pelajaran</label>
                            <select name="mapel_id" id="mapel_id" class="form-select" required>
                                <option value="">-- Pilih Mapel --</option>
                                <?php mysqli_data_seek($mapel_list, 0); while($m = $mapel_list->fetch_assoc()): ?>
                                    <option value="<?php echo $m['id']; ?>" <?php echo $selected_mapel_id == $m['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($m['nama_mapel']); ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-12">
                            <button type="submit" class="btn btn-primary">Tampilkan Rekap</button>
                            <a href="dashboard.php" class="btn btn-secondary">Kembali</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <?php if ($grades): ?>
        <div class="card mt-4">
            <div class="card-body">
                <table id="rekapTable" class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>NIS</th>
                            <th>Nama Siswa</th>
                            <th>Nilai</th>
                            <th>Predikat</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($grades->num_rows > 0): ?>
                            <?php while($g = $grades->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($g['nis']); ?></td>
                                <td><?php echo htmlspecialchars($g['nama']); ?></td>
                                <td><?php echo htmlspecialchars($g['nilai_angka']); ?></td>
                                <td><?php echo htmlspecialchars($g['predikat']); ?></td>
                                <td>
                                    <a href="preview_rapor.php?siswa_id=<?php echo $g['siswa_id']; ?>&semester=<?php echo $selected_semester; ?>&tahun_ajaran=<?php echo urlencode($selected_tahun); ?>" class="btn btn-sm btn-secondary" target="_blank"><i class="bi bi-eye"></i> Lihat Rapor</a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="5" class="text-center">Tidak ada data nilai untuk kriteria yang dipilih.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#rekapTable').DataTable();
        });
    </script>
</body>
</html>
