<?php
/**
 * File: actions/logout.php
 * Deskripsi: File ini menangani proses logout pengguna dari sistem.
 * Fungsi ini membersihkan semua data session yang tersimpan dan mengarahkan
 * pengguna kembali ke halaman login dengan pesan konfirmasi logout berhasil.
 */

// Selalu mulai session di awal untuk mengakses data session.
session_start();

// Hapus semua variabel session.
$_SESSION = array();

// Hancurkan session untuk mengakhiri sesi pengguna.
session_destroy();

// Redirect ke halaman login dengan pesan sukses logout.
header("Location: ../views/login.php?message=Anda telah berhasil logout.");
exit;
?>
