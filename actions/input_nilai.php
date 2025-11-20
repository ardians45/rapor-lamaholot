<?php
session_start();
require_once '../config/database.php';

// Keamanan: Pastikan hanya guru yang bisa mengakses
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] != 'guru') {
    die("Akses ditolak.");
}

// Cek jika ini adalah request POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../views/guru/dashboard.php');
    exit;
}

// Ambil ID guru dari session
$guru_info = $koneksi->query("SELECT id FROM guru WHERE user_id = {$_SESSION['user_id']}")->fetch_assoc();
$guru_id = $guru_info['id'];

// Ambil data yang di-POST
$mapel_id = $_POST['mapel_id'];
$semester = $_POST['semester'];
$tahun_ajaran = $_POST['tahun_ajaran'];
$kelas = $_POST['kelas'];

// Ambil array nilai
$siswa_ids = $_POST['siswa_id'];
$nilai_angkas = $_POST['nilai_angka'];
$predikats = $_POST['predikat'];

// URL untuk redirect kembali
$redirect_url = "../views/guru/input_nilai.php?mapel_id=$mapel_id&kelas=$kelas&semester=$semester&tahun_ajaran=$tahun_ajaran";

// Mulai Transaksi
$koneksi->begin_transaction();

try {
    // Siapkan statement untuk check, update, dan insert
    $stmt_check = $koneksi->prepare("SELECT id FROM nilai WHERE siswa_id = ? AND mapel_id = ? AND semester = ? AND tahun_ajaran = ?");
    $stmt_update = $koneksi->prepare("UPDATE nilai SET nilai_angka = ?, predikat = ? WHERE id = ?");
    $stmt_insert = $koneksi->prepare("INSERT INTO nilai (siswa_id, mapel_id, guru_id, semester, tahun_ajaran, nilai_angka, predikat) VALUES (?, ?, ?, ?, ?, ?, ?)");

    // Loop sebanyak data siswa yang dikirim
    for ($i = 0; $i < count($siswa_ids); $i++) {
        $siswa_id = $siswa_ids[$i];
        // Hanya proses jika ada nilai yang diinput
        $nilai_angka = !empty($nilai_angkas[$i]) ? $nilai_angkas[$i] : null;
        $predikat = !empty($predikats[$i]) ? $predikats[$i] : null;

        // Cek apakah data nilai sudah ada
        $stmt_check->bind_param("iiss", $siswa_id, $mapel_id, $semester, $tahun_ajaran);
        $stmt_check->execute();
        $result = $stmt_check->get_result();
        $existing_nilai = $result->fetch_assoc();

        if ($existing_nilai) {
            // Jika sudah ada, UPDATE
            // Hanya update jika ada nilai baru yang diinput
            if ($nilai_angka !== null) {
                $stmt_update->bind_param("ssi", $nilai_angka, $predikat, $existing_nilai['id']);
                $stmt_update->execute();
            }
        } else {
            // Jika belum ada, INSERT
            // Hanya insert jika ada nilai yang diinput
            if ($nilai_angka !== null) {
                $stmt_insert->bind_param("iiiisss", $siswa_id, $mapel_id, $guru_id, $semester, $tahun_ajaran, $nilai_angka, $predikat);
                $stmt_insert->execute();
            }
        }
    }

    // Commit transaksi
    $koneksi->commit();
    header("Location: " . $redirect_url . "&success=Nilai berhasil disimpan.");

} catch (Exception $e) {
    // Rollback jika terjadi error
    $koneksi->rollback();
    header("Location: " . $redirect_url . "&error=Gagal menyimpan nilai: " . $e->getMessage());
} finally {
    // Tutup statement
    $stmt_check->close();
    $stmt_update->close();
    $stmt_insert->close();
    $koneksi->close();
}
exit;
?>
