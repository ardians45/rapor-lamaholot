<?php
// File: actions/auth.php
// Deskripsi: Logika untuk proses otentikasi (login).

// Mulai session PHP untuk menyimpan status login.
session_start();

// Include file koneksi database.
require_once '../config/database.php';

// Cek apakah request adalah POST.
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ambil data dari form dan sanitasi sederhana.
    $username = $koneksi->real_escape_string($_POST['username']);
    $password = $_POST['password'];

    // Query untuk mencari user berdasarkan username.
    $sql = "SELECT id, username, password, role FROM users WHERE username = ?";
    
    // Gunakan prepared statement untuk keamanan.
    if ($stmt = $koneksi->prepare($sql)) {
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();

            // Verifikasi password yang di-hash.
            if (password_verify($password, $user['password'])) {
                // Password benar. Simpan data user ke session.
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['logged_in'] = true;

                // Redirect berdasarkan role.
                switch ($user['role']) {
                    case 'admin':
                        header("Location: ../views/admin/dashboard.php");
                        exit;
                    case 'guru':
                        header("Location: ../views/guru/dashboard.php");
                        exit;
                    case 'siswa':
                        header("Location: ../views/siswa/dashboard.php");
                        exit;
                    default:
                        // Jika role tidak dikenal, redirect ke login.
                        header("Location: ../views/login.php?error=Role tidak valid");
                        exit;
                }
            } else {
                // Password salah.
                header("Location: ../views/login.php?error=Username atau password salah.");
                exit;
            }
        } else {
            // Username tidak ditemukan.
            header("Location: ../views/login.php?error=Username atau password salah.");
            exit;
        }

        $stmt->close();
    } else {
        // Gagal menyiapkan statement SQL.
        header("Location: ../views/login.php?error=Terjadi kesalahan sistem.");
        exit;
    }

    $koneksi->close();
} else {
    // Jika bukan metode POST, redirect ke halaman login.
    header("Location: ../views/login.php");
    exit;
}
?>
