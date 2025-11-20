<?php
session_start();
require_once '../config/database.php';

// Keamanan: Pastikan hanya admin yang bisa mengakses file ini
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] != 'admin') {
    die("Akses ditolak. Anda bukan admin.");
}

$action = $_GET['action'] ?? '';

// ------------------------------------------------------------------
// ACTION: CREATE (TAMBAH SISWA)
// ------------------------------------------------------------------
if ($action == 'create' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ambil data dari form
    $nis = $_POST['nis'] ?? '';
    $nama = $_POST['nama'] ?? '';
    $kelas = $_POST['kelas'] ?? '';
    $jurusan = $_POST['jurusan'] ?? '';
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // Validasi dasar
    if (empty($nis) || empty($nama) || empty($username) || empty($password)) {
        header("Location: ../views/admin/manage_siswa.php?error=Semua field wajib diisi.");
        exit;
    }

    // Cek duplikasi NIS atau Username
    $stmt_check = $koneksi->prepare("SELECT id FROM siswa WHERE nis = ? UNION SELECT id FROM users WHERE username = ?");
    $stmt_check->bind_param("ss", $nis, $username);
    $stmt_check->execute();
    if ($stmt_check->get_result()->num_rows > 0) {
        header("Location: ../views/admin/manage_siswa.php?error=NIS atau Username sudah terdaftar.");
        exit;
    }
    $stmt_check->close();

    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Mulai transaksi
    $koneksi->begin_transaction();

    try {
        // 1. Insert ke tabel 'users'
        $stmt_user = $koneksi->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, 'siswa')");
        $stmt_user->bind_param("ss", $username, $hashed_password);
        $stmt_user->execute();
        $user_id = $koneksi->insert_id; // Dapatkan ID user yang baru dibuat
        $stmt_user->close();

        // 2. Insert ke tabel 'siswa'
        $stmt_siswa = $koneksi->prepare("INSERT INTO siswa (user_id, nis, nama, kelas, jurusan) VALUES (?, ?, ?, ?, ?)");
        $stmt_siswa->bind_param("issss", $user_id, $nis, $nama, $kelas, $jurusan);
        $stmt_siswa->execute();
        $stmt_siswa->close();

        // Jika semua berhasil, commit transaksi
        $koneksi->commit();
        header("Location: ../views/admin/manage_siswa.php?success=Data siswa berhasil ditambahkan.");

    } catch (Exception $e) {
        // Jika ada error, rollback transaksi
        $koneksi->rollback();
        header("Location: ../views/admin/manage_siswa.php?error=Gagal menambahkan data: " . $e->getMessage());
    }
    exit;
}

// ------------------------------------------------------------------
// ACTION: DELETE (HAPUS SISWA)
// ------------------------------------------------------------------
if ($action == 'delete' && isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // Untuk menghapus siswa, kita harus menghapus data di tabel 'users' juga.
    // Foreign key dengan ON DELETE CASCADE akan menangani ini secara otomatis.
    // Cukup hapus dari tabel 'users' dan data di 'siswa' akan ikut terhapus.
    
    // Cari user_id dari siswa_id
    $stmt_find = $koneksi->prepare("SELECT user_id FROM siswa WHERE id = ?");
    $stmt_find->bind_param("i", $id);
    $stmt_find->execute();
    $result = $stmt_find->get_result();
    
    if($result->num_rows > 0) {
        $user_id = $result->fetch_assoc()['user_id'];
        $stmt_find->close();

        // Hapus dari tabel 'users'
        $stmt_delete = $koneksi->prepare("DELETE FROM users WHERE id = ?");
        $stmt_delete->bind_param("i", $user_id);
        if ($stmt_delete->execute()) {
            header("Location: ../views/admin/manage_siswa.php?success=Data siswa berhasil dihapus.");
        } else {
            header("Location: ../views/admin/manage_siswa.php?error=Gagal menghapus data.");
        }
        $stmt_delete->close();
    } else {
        header("Location: ../views/admin/manage_siswa.php?error=Data siswa tidak ditemukan.");
    }
    exit;
}

// Jika tidak ada action yang cocok, redirect
header("Location: ../views/admin/dashboard.php");
exit;
?>
