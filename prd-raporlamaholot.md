# PRD: Sistem Informasi Rapor Online (Web-Based)

## 1. Ringkasan Proyek

Membangun aplikasi web sederhana untuk manajemen nilai rapor di Sekolah Dasar Lamaholot. Fokus utama adalah kemudahan akses data nilai, keamanan role (hak akses), dan kemampuan generate laporan (PDF) yang rapi.

**Tech Stack:**
- **Backend:** PHP Native (Versi 7.4 atau 8.x).
- **Database:** MySQL (via XAMPP).
- **Frontend:** HTML5, CSS3 (Saran: Gunakan **Bootstrap 5** atau **Tailwind** via CDN agar UI otomatis bersih dan responsif tanpa coding CSS dari nol).
- **PDF Library:** FPDF atau DomPDF (untuk cetak rapor).

---

## 2. Struktur User & Hak Akses (Role)

Sistem menggunakan **Session Based Authentication**.

### A. Admin (Super User)
- **Dashboard:** Ringkasan jumlah Guru, Siswa, dan Kelas.
- **Manajemen User:**
  - CRUD Guru (NIP, Nama, Mapel yang diampu).
  - CRUD Siswa (NIS, Nama, Kelas, Jurusan).
  - Generate/Reset Password User.
- **Pengaturan:** Set Tahun Ajaran Aktif.
- **Laporan:** Export data master Siswa/Guru ke PDF/Excel.

### B. Guru (Inputter)
- **Dashboard:** Daftar kelas yang diajar.
- **Input Nilai:** Form grid untuk input nilai siswa per mata pelajaran & kelas.
  - _Fitur UX:_ Auto-save atau tombol simpan yang jelas.
- **Lihat Rekap:** Melihat daftar nilai yang sudah diinput.
- **Cetak:** Bisa print preview rapor sementara.

### C. Siswa / Wali Murid (Viewer)
- **Dashboard:** Profil siswa.
- **Lihat Nilai:** Tabel nilai per semester.
- **Download Rapor:** Tombol untuk mengunduh hasil rapor resmi (PDF).
- **Akun:** Ganti password pribadi.

---

## 3. Spesifikasi Fungsional & Alur UX

### 1. Login System
- Halaman login tunggal.
- Validasi role otomatis setelah login (Redirect: Admin ke `/admin`, Guru ke `/guru`, Siswa ke `/siswa`).
- _Security:_ Password harus di-hash (gunakan `password_hash()` default PHP).

### 2. Manajemen Data (Admin & Guru)
- **Validasi Input:** Tidak boleh ada field kosong. NIS/NIP harus unik.
- **Feedback System (Pengganti warna terminal):**
  - Sukses: Alert Hijau (Bootstrap `.alert-success`) → "Data berhasil disimpan".
  - Gagal: Alert Merah (Bootstrap `.alert-danger`) → "Gagal: NIS sudah terdaftar".
- **Tabel Data:** Gunakan tabel dengan fitur _Search_ dan _Pagination_ (bisa pakai library **DataTables** JS agar instan).

### 3. Fitur Export PDF
- Format nama file: `raport_{NIS}_{Semester}.pdf`.
- **Layout PDF:**
  - Kop Surat Sekolah (Logo & Alamat).
  - Identitas Siswa & Semester.
  - Tabel Nilai (Mapel, KKM, Nilai Angka, Nilai Huruf/Predikat).
  - Kolom Tanda Tangan (Wali Kelas & Orang Tua).

---

## 4. Desain Database (Skema MySQL)

Berikut adalah rancangan tabel minimal agar sistem berjalan efisien:

### Tabel `users` (Untuk login)
- `id`: Primary Key, auto-increment
- `username`: Username untuk login (bisa berupa NIP/NIS)
- `password`: Password hash untuk keamanan
- `role`: enum('admin','guru','siswa') - Peran pengguna dalam sistem

### Tabel `siswa`
- `id`: Primary Key, auto-increment
- `user_id`: Foreign Key ke tabel users
- `nis`: Nomor Induk Siswa (unik)
- `nama`: Nama lengkap siswa
- `kelas`: Kelas siswa (misal: 6A, 5B)
- `jurusan`: Jurusan (untuk jenjang yang relevan)

### Tabel `guru`
- `id`: Primary Key, auto-increment
- `user_id`: Foreign Key ke tabel users
- `nip`: Nomor Induk Pegawai (unik)
- `nama`: Nama lengkap guru

### Tabel `mapel` (Mata Pelajaran)
- `id`: Primary Key, auto-increment
- `nama_mapel`: Nama mata pelajaran
- `kkm`: Kriteria Ketuntasan Minimal (KKM)

### Tabel `nilai`
- `id`: Primary Key, auto-increment
- `siswa_id`: Foreign Key ke tabel siswa
- `mapel_id`: Foreign Key ke tabel mapel
- `guru_id`: Foreign Key ke tabel guru (yang menginput)
- `semester`: Semester (1 atau 2)
- `tahun_ajaran`: Tahun ajaran (misal: 2023/2024)
- `nilai_angka`: Nilai dalam angka (0-100)
- `predikat`: Predikat (A, B, C, D)

**Catatan:** Struktur database ini telah diimplementasikan dalam file `database.sql`. Tabel-tabel ini memiliki relasi foreign key sesuai dengan kebutuhan sistem, dengan constraint ON DELETE CASCADE untuk menjaga konsistensi data.

---

## 5. Struktur Folder (Modular & Rapi)

Agar kodemu mudah dibaca dan di-maintenence (Clean Code):

```
/rapor_lamaholot
│
├── /actions            # Logika PHP (Backend Proses)
│   ├── auth.php        # Proses Login/Logout
│   ├── crud_siswa.php  # Proses CRUD siswa
│   ├── crud_guru.php   # Proses CRUD guru
│   ├── input_nilai.php # Proses input nilai oleh guru
│   └── logout.php      # Proses logout
│
├── /assets             # CSS (Bootstrap), JS, Images
├── /config
│   └── database.php    # Koneksi ke XAMPP MySQL
├── /FPDF-master        # Library FPDF
├── /library            # Tempat file-file pendukung lainnya
│
├── /views              # Tampilan UI (Frontend)
│   ├── login.php       # Halaman login untuk semua peran
│   ├── /admin          # Folder khusus view Admin
│   ├── /guru           # Folder khusus view Guru
│   └── /siswa          # Folder khusus view Siswa
│
├── cetak_rapor.php     # Logic generate PDF untuk siswa
├── cetak_rapor_by_guru.php # Logic generate PDF oleh guru
├── database.sql        # Skema database MySQL
├── fpdf.zip            # File ZIP library FPDF
├── index.php           # Halaman utama (Redirect logic)
├── reset_admin_password.php # Reset password admin (aman untuk localhost)
└── prd-raporlamaholot.md # File PRD ini
```

---

## 6. Panduan Implementasi & Instalasi

### 1. Instalasi
1. **Copy Project:** Salin folder project ke direktori `htdocs` XAMPP Anda (misal: `C:\xampp\htdocs\rapor_lamaholot`).
2. **Start Services:** Jalankan Apache dan MySQL pada XAMPP Control Panel.
3. **Import Database:**
   - Buka phpMyAdmin di browser (biasanya http://localhost/phpmyadmin).
   - Buat database baru dengan nama `rapor_lamaholot`.
   - Pilih database tersebut dan klik tab "Import".
   - Pilih file `database.sql` dari folder project untuk diimpor.
4. **Konfigurasi Database:**
   - Periksa file `config/database.php` untuk memastikan konfigurasi database Anda (default: root, tanpa password).
5. **Akses Aplikasi:**
   - Buka browser dan akses http://localhost/rapor_lamaholot

### 2. Akun Default
- **Admin:** username: `admin`, password: `password123`
- **Guru:** username: `guru01`, password: `password123`
- **Siswa:** username: `siswa01`, password: `password123`

### 3. Reset Password Admin
- Jika Anda lupa password admin, akses file `reset_admin_password.php` dari localhost.
- Ini akan mereset password admin ke `admin123`.
- **Catatan:** Hapus file ini setelah digunakan untuk alasan keamanan.

---

## 7. Fitur-Fitur Utama Aplikasi

### 1. Sistem Otentikasi
- Sistem login berbasis session dengan otentikasi password yang di-hash.
- Otomatis redirect berdasarkan peran pengguna setelah login.

### 2. Manajemen Pengguna (Admin)
- CRUD data guru (tambah, edit, hapus dengan validasi unik NIP dan username).
- CRUD data siswa (tambah, edit, hapus dengan validasi unik NIS dan username).
- Transaksi database untuk menjaga konsistensi data.

### 3. Input Nilai (Guru)
- Form input nilai dalam bentuk grid untuk efisiensi.
- Sistem update nilai jika sudah ada data sebelumnya.
- Validasi untuk mencegah input kosong atau data duplikat.

### 4. Laporan & Cetak
- Generate PDF rapor untuk siswa oleh guru atau siswa itu sendiri.
- Layout PDF yang profesional dengan kop sekolah, informasi siswa, nilai, dan kolom tanda tangan.

### 5. Keamanan
- Validasi role untuk setiap akses halaman.
- Password di-hash menggunakan fungsi `password_hash()`.
- Pembatasan akses localhost untuk file sensitive (seperti reset password admin).

---

## 8. Teknologi & Library yang Digunakan

### 1. PHP Native
- Session management untuk otentikasi.
- Prepared statements untuk mencegah SQL injection.
- Fungsi password_hash dan password_verify untuk keamanan password.

### 2. MySQLi
- Database connection dan query handling.
- Transaksi database untuk operasi CRUD yang kompleks.

### 3. FPDF Library
- Generate dokumen PDF untuk rapor.
- Custom header dan footer untuk tampilan profesional.

### 4. HTML/CSS
- Tampilan sederhana namun fungsional.
- Bootstrap (melalui CDN) untuk tampilan responsif.

---

## 9. Panduan Penggunaan untuk Pihak Sekolah

### 1. Untuk Administrator
- Login sebagai admin untuk mengelola data guru dan siswa.
- Gunakan menu manajemen untuk menambah atau menghapus pengguna.
- Gunakan fitur reset password jika diperlukan.

### 2. Untuk Guru
- Login sebagai guru untuk mengakses fitur input nilai.
- Pilih mata pelajaran dan kelas yang ingin diinput nilainya.
- Masukkan nilai siswa satu per satu dan simpan perubahan.

### 3. Untuk Siswa/Orang Tua
- Login sebagai siswa untuk melihat nilai dan download rapor.
- Gunakan fitur download untuk mendapatkan rapor dalam format PDF.
- Ganti password jika ingin meningkatkan keamanan akun.

---

## 10. Catatan Penting

### 1. Keamanan Produksi
- Ganti password default pada semua akun.
- Pastikan file `reset_admin_password.php` dihapus dari server produksi.
- Gunakan password yang kuat dan unik untuk semua pengguna.
- Pertimbangkan untuk menggunakan HTTPS untuk koneksi yang lebih aman.

### 2. Backup Database
- Lakukan backup database secara berkala untuk mencegah kehilangan data.
- Gunakan fitur export pada phpMyAdmin untuk membuat backup.

### 3. Pemeliharaan
- Monitor log akses untuk mendeteksi aktivitas tidak normal.
- Perbarui PHP dan MySQL ke versi terbaru untuk patch keamanan.
- Lakukan update rutin terhadap library yang digunakan.

### 4. Pengembangan Lanjutan
- Fitur notifikasi nilai untuk orang tua murid.
- Laporan statistik dan grafik kinerja siswa.
- Penggantian FPDF dengan DomPDF untuk fitur yang lebih lengkap.
- Integrasi dengan sistem pembayaran SPP atau sistem lainnya.
- Mobile responsiveness yang lebih baik.
