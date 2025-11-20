<?php
/**
 * File: index.php
 * Deskripsi: Halaman utama yang berfungsi sebagai router untuk mengarahkan pengguna
 * ke halaman yang sesuai berdasarkan status login dan peran (role) mereka.
 * File ini memeriksa apakah pengguna sudah login, dan jika sudah,
 * akan mengarahkan mereka ke dashboard sesuai dengan peran mereka (admin, guru, siswa).
 */

// Mulai session untuk mengakses data session.
session_start();

// Cek apakah pengguna sudah login atau belum.
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    // Jika sudah login, redirect ke dashboard yang sesuai dengan rolenya.
    $role = $_SESSION['role'];
    switch ($role) {
        case 'admin':
            header("Location: views/admin/dashboard.php");
            exit;
        case 'guru':
            header("Location: views/guru/dashboard.php");
            exit;
        case 'siswa':
            header("Location: views/siswa/dashboard.php");
            exit;
        default:
            // Jika role tidak dikenal, hancurkan session dan redirect ke login.
            session_destroy();
            header("Location: views/login.php?error=Role tidak dikenal");
            exit;
    }
} else {
    // Jika belum login, redirect ke halaman login.
    header("Location: views/login.php");
    exit;
}
?>
