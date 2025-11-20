<?php
session_start();
require_once '../config/database.php';

// Keamanan: Pastikan hanya admin yang bisa mengakses file ini
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] != 'admin') {
    die("Akses ditolak. Anda bukan admin.");
}

$action = $_GET['action'] ?? '';

// ------------------------------------------------------------------
// ACTION: CREATE (TAMBAH GURU)
// ------------------------------------------------------------------
if ($action == 'create' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ambil data dari form
    $nip = $_POST['nip'] ?? '';
    $nama = $_POST['nama'] ?? '';
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // Validasi dasar
    if (empty($nip) || empty($nama) || empty($username) || empty($password)) {
        header("Location: ../views/admin/manage_guru.php?error=Semua field wajib diisi.");
        exit;
    }

    // Cek duplikasi NIP atau Username
    $stmt_check = $koneksi->prepare("SELECT id FROM guru WHERE nip = ? UNION SELECT id FROM users WHERE username = ?");
    $stmt_check->bind_param("ss", $nip, $username);
    $stmt_check->execute();
    if ($stmt_check->get_result()->num_rows > 0) {
        header("Location: ../views/admin/manage_guru.php?error=NIP atau Username sudah terdaftar.");
        exit;
    }
    $stmt_check->close();

    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Mulai transaksi
    $koneksi->begin_transaction();

    try {
        // 1. Insert ke tabel 'users'
        $stmt_user = $koneksi->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, 'guru')");
        $stmt_user->bind_param("ss", $username, $hashed_password);
        $stmt_user->execute();
        $user_id = $koneksi->insert_id;
        $stmt_user->close();

        // 2. Insert ke tabel 'guru'
        $stmt_guru = $koneksi->prepare("INSERT INTO guru (user_id, nip, nama) VALUES (?, ?, ?)");
        $stmt_guru->bind_param("iss", $user_id, $nip, $nama);
        $stmt_guru->execute();
        $stmt_guru->close();

        // Jika semua berhasil, commit transaksi
        $koneksi->commit();
        header("Location: ../views/admin/manage_guru.php?success=Data guru berhasil ditambahkan.");

    } catch (Exception $e) {
        // Jika ada error, rollback transaksi
        $koneksi->rollback();
        header("Location: ../views/admin/manage_guru.php?error=Gagal menambahkan data: " . $e->getMessage());
    }
    exit;
}

// ------------------------------------------------------------------
// ACTION: DELETE (HAPUS GURU)
// ------------------------------------------------------------------
if ($action == 'delete' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    // Cari user_id dari guru_id
    $stmt_find = $koneksi->prepare("SELECT user_id FROM guru WHERE id = ?");
    $stmt_find->bind_param("i", $id);
    $stmt_find->execute();
    $result = $stmt_find->get_result();
    
    if($result->num_rows > 0) {
        $user_id = $result->fetch_assoc()['user_id'];
        $stmt_find->close();

        // Hapus dari tabel 'users'. ON DELETE CASCADE akan menghapus data guru terkait.
        $stmt_delete = $koneksi->prepare("DELETE FROM users WHERE id = ?");
        $stmt_delete->bind_param("i", $user_id);
        if ($stmt_delete->execute()) {
            header("Location: ../views/admin/manage_guru.php?success=Data guru berhasil dihapus.");
        } else {
            header("Location: ../views/admin/manage_guru.php?error=Gagal menghapus data.");
        }
        $stmt_delete->close();
    } else {
        header("Location: ../views/admin/manage_guru.php?error=Data guru tidak ditemukan.");
    }
    exit;
}

// Jika tidak ada action yang cocok, redirect
header("Location: ../views/admin/dashboard.php");
exit;
?>
