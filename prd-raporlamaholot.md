# PRD: Sistem Informasi Rapor Online (Web-Based)

## 1\. Ringkasan Proyek

Membangun aplikasi web sederhana untuk manajemen nilai rapor di Sekolah Dasar Lamaholot. Fokus utama adalah kemudahan akses data nilai, keamanan role (hak akses), dan kemampuan generate laporan (PDF) yang rapi.

**Tech Stack:**

- **Backend:** PHP Native (Versi 7.4 atau 8.x).
- **Database:** MySQL (via XAMPP).
- **Frontend:** HTML5, CSS3 (Saran: Gunakan **Bootstrap 5** atau **Tailwind** via CDN agar UI otomatis bersih dan responsif tanpa coding CSS dari nol).
- **PDF Library:** FPDF atau DomPDF (untuk cetak rapor).

---

## 2\. Struktur User & Hak Akses (Role)

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

## 3\. Spesifikasi Fungsional & Alur UX

### 1\. Login System

- Halaman login tunggal.
- Validasi role otomatis setelah login (Redirect: Admin ke `/admin`, Guru ke `/guru`, Siswa ke `/siswa`).
- _Security:_ Password harus di-hash (gunakan `password_hash()` default PHP).

### 2\. Manajemen Data (Admin & Guru)

- **Validasi Input:** Tidak boleh ada field kosong. NIS/NIP harus unik.
- **Feedback System (Pengganti warna terminal):**
  - Sukses: Alert Hijau (Bootstrap `.alert-success`) → "Data berhasil disimpan".
  - Gagal: Alert Merah (Bootstrap `.alert-danger`) → "Gagal: NIS sudah terdaftar".
- **Tabel Data:** Gunakan tabel dengan fitur _Search_ dan _Pagination_ (bisa pakai library **DataTables** JS agar instan).

### 3\. Fitur Export PDF

- Format nama file: `raport_{NIS}_{Semester}.pdf`.
- **Layout PDF:**
  - Kop Surat Sekolah (Logo & Alamat).
  - Identitas Siswa & Semester.
  - Tabel Nilai (Mapel, KKM, Nilai Angka, Nilai Huruf/Predikat).
  - Kolom Tanda Tangan (Wali Kelas & Orang Tua).

---

## 4\. Desain Database (Skema MySQL)

Berikut adalah rancangan tabel minimal agar sistem berjalan efisien:

1.  **users** (Untuk login)
    - `id`, `username` (bisa pakai NIS/NIP), `password`, `role` (admin/guru/siswa).
2.  **siswa**
    - `id`, `user_id`, `nis`, `nama`, `kelas`, `jurusan`.
3.  **guru**
    - `id`, `user_id`, `nip`, `nama`.
4.  **mapel**
    - `id`, `nama_mapel`, `kkm`.
5.  **nilai**
    - `id`, `siswa_id`, `mapel_id`, `guru_id`, `semester`, `nilai_angka`, `predikat`.

---

## 5\. Struktur Folder (Modular & Rapi)

Agar kodemu mudah dibaca dan di-maintenence (Clean Code):

```text
/rapor-online
│
├── /assets             # CSS (Bootstrap), JS, Images
├── /config
│   └── database.php    # Koneksi ke XAMPP MySQL
├── /library
│   └── fpdf.php        # Library PDF
│
├── /views              # Tampilan UI (Frontend)
│   ├── login.php
│   ├── /admin          # Folder khusus view Admin
│   ├── /guru           # Folder khusus view Guru
│   └── /siswa          # Folder khusus view Siswa
│
├── /actions            # Logika PHP (Backend Proses)
│   ├── auth.php        # Proses Login/Logout
│   ├── crud_siswa.php
│   ├── crud_guru.php
│   └── input_nilai.php
│
├── index.php           # Halaman utama (Redirect logic)
└── cetak_pdf.php       # Logic generate PDF
```

---

## 6\. Panduan Implementasi Cepat (Untuk Multitasking)

Mengingat kamu sering multitasking (Ojol & Freelance), gunakan strategi ini:

1.  **Fase 1 (Database & Koneksi):** Buat database di phpMyAdmin, lalu buat file `config/database.php`. Pastikan koneksi "Connected". dan buatkan source code mysqlnya pada file terpisah untuk saya copy ke dalam Localhost phpmyadmin
2.  **Fase 2 (Auth):** Buat login page simpel. Pastikan Admin, Guru, dan Siswa bisa masuk dan diarahkan ke halaman beda.
3.  **Fase 3 (CRUD Admin):** Fokus selesaikan fitur tambah Guru dan Siswa dulu. Ini inti datanya.
4.  **Fase 4 (Input Nilai):** Buat form guru.
5.  **Fase 5 (PDF & UI Polish):** Terakhir baru rapikan CSS dan pasang fitur cetak PDF.
