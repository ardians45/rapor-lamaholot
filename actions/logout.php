<?php
// File: actions/logout.php
// Deskripsi: Menghancurkan session dan mengarahkan pengguna ke halaman login.

// Selalu mulai session di awal.
session_start();

// Hapus semua variabel session.
$_SESSION = array();

// Hancurkan session.
session_destroy();

// Redirect ke halaman login dengan pesan sukses logout.
header("Location: ../views/login.php?message=Anda telah berhasil logout.");
exit;
?>
