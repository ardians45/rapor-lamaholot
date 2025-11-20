<?php
// File: config/database.php
// Deskripsi: Konfigurasi dan koneksi ke database MySQL.

// --- Pengaturan Database ---
// Sesuaikan dengan konfigurasi XAMPP Anda jika berbeda.
$db_host = 'localhost';       // Host database (biasanya 'localhost')
$db_user = 'root';            // Username database
$db_pass = '';                // Password database (kosongkan jika tidak ada)
$db_name = 'rapor_lamaholot'; // Nama database

// --- Proses Koneksi ---
// Membuat koneksi menggunakan MySQLi
$koneksi = new mysqli($db_host, $db_user, $db_pass, $db_name);

// --- Cek Koneksi ---
// Jika koneksi gagal, hentikan skrip dan tampilkan pesan error.
if ($koneksi->connect_error) {
    // Jangan tampilkan error detail di produksi, tapi untuk development ini sangat membantu.
    die("Koneksi ke database gagal: " . $koneksi->connect_error);
}

// --- Set Charset (Opsional tapi direkomendasikan) ---
// Memastikan data yang dikirim dan diterima menggunakan encoding yang benar.
$koneksi->set_charset("utf8mb4");

// --- Pesan Sukses (Untuk Debugging) ---
// Baris ini bisa dihapus jika sudah yakin koneksi berjalan baik.
// echo "Koneksi ke database berhasil.";

// Variabel $koneksi siap digunakan di file lain yang meng-include file ini.
?>
