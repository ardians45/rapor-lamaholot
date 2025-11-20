-- ===================================================================
-- PRD: Sistem Informasi Rapor Online (Web-Based)
-- Fase 1: Database & Koneksi
-- File: database.sql
-- Deskripsi: Skema database MySQL untuk aplikasi rapor online.
-- Keterangan: File ini berisi struktur tabel dan data awal untuk aplikasi
-- ===================================================================

-- Membuat database jika belum ada
-- Database ini menggunakan karakter UTF-8 (utf8mb4) untuk mendukung karakter internasional
CREATE DATABASE IF NOT EXISTS `rapor_lamaholot` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `rapor_lamaholot`;

-- ===================================================================
-- TABEL `users`
-- ===================================================================
-- Struktur dari tabel `users`
-- Tabel ini menyimpan data login untuk semua peran (admin, guru, siswa).
-- Password disimpan dalam format hash menggunakan password_hash() PHP.
--
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,      -- ID unik untuk setiap pengguna
  `username` varchar(50) NOT NULL,           -- Username untuk login (bisa berupa NIP/NIS)
  `password` varchar(255) NOT NULL,          -- Password hash untuk keamanan
  `role` enum('admin','guru','siswa') NOT NULL, -- Peran pengguna dalam sistem
  PRIMARY KEY (`id`),                        -- Indeks utama
  UNIQUE KEY `username` (`username`)         -- Username harus unik
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Data awal untuk tabel `users`
-- Password untuk semua user di bawah ini adalah 'password123' (telah di-hash)
--
INSERT INTO `users` (`id`, `username`, `password`, `role`) VALUES
(1, 'admin', '$2y$10$E.t8H4KMM/BME16i3mSg8uWuK2hJm2Qk5L5D2.dY.6e9a7tG4pG.q', 'admin'),
(2, 'guru01', '$2y$10$E.t8H4KMM/BME16i3mSg8uWuK2hJm2Qk5L5D2.dY.6e9a7tG4pG.q', 'guru'),
(3, 'siswa01', '$2y$10$E.t8H4KMM/BME16i3mSg8uWuK2hJm2Qk5L5D2.dY.6e9a7tG4pG.q', 'siswa');

-- ===================================================================
-- TABEL `guru`
-- ===================================================================
--
-- Struktur dari tabel `guru`
-- Tabel ini menyimpan informasi detail tentang guru
--
CREATE TABLE `guru` (
  `id` int(11) NOT NULL AUTO_INCREMENT,      -- ID unik untuk setiap guru
  `user_id` int(11) NOT NULL,                -- Foreign key ke tabel users
  `nip` varchar(20) NOT NULL,                -- Nomor Induk Pegawai
  `nama` varchar(100) NOT NULL,              -- Nama lengkap guru
  PRIMARY KEY (`id`),                        -- Indeks utama
  UNIQUE KEY `nip` (`nip`),                  -- NIP harus unik
  KEY `user_id` (`user_id`)                  -- Indeks untuk foreign key
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Data awal untuk tabel `guru`
--
INSERT INTO `guru` (`id`, `user_id`, `nip`, `nama`) VALUES
(1, 2, '199001012020121001', 'Budi Santoso, S.Pd.');

-- ===================================================================
-- TABEL `siswa`
-- ===================================================================
--
-- Struktur dari tabel `siswa`
-- Tabel ini menyimpan informasi detail tentang siswa
--
CREATE TABLE `siswa` (
  `id` int(11) NOT NULL AUTO_INCREMENT,      -- ID unik untuk setiap siswa
  `user_id` int(11) NOT NULL,                -- Foreign key ke tabel users
  `nis` varchar(20) NOT NULL,                -- Nomor Induk Siswa
  `nama` varchar(100) NOT NULL,              -- Nama lengkap siswa
  `kelas` varchar(10) DEFAULT NULL,          -- Kelas siswa (misal: 6A, 5B)
  `jurusan` varchar(50) DEFAULT NULL,        -- Jurusan (untuk jenjang yang relevan)
  PRIMARY KEY (`id`),                        -- Indeks utama
  UNIQUE KEY `nis` (`nis`),                  -- NIS harus unik
  KEY `user_id` (`user_id`)                  -- Indeks untuk foreign key
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Data awal untuk tabel `siswa`
--
INSERT INTO `siswa` (`id`, `user_id`, `nis`, `nama`, `kelas`, `jurusan`) VALUES
(1, 3, '1001', 'Ani Suryani', '6A', 'Reguler');

-- ===================================================================
-- TABEL `mapel` (Mata Pelajaran)
-- ===================================================================
--
-- Struktur dari tabel `mapel` (Mata Pelajaran)
-- Tabel ini menyimpan daftar mata pelajaran yang diajarkan
--
CREATE TABLE `mapel` (
  `id` int(11) NOT NULL AUTO_INCREMENT,      -- ID unik untuk setiap mata pelajaran
  `nama_mapel` varchar(100) NOT NULL,        -- Nama mata pelajaran
  `kkm` int(3) NOT NULL,                     -- Kriteria Ketuntasan Minimal (KKM)
  PRIMARY KEY (`id`)                         -- Indeks utama
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Data awal untuk tabel `mapel`
--
INSERT INTO `mapel` (`id`, `nama_mapel`, `kkm`) VALUES
(1, 'Pendidikan Agama', 75),
(2, 'Bahasa Indonesia', 70),
(3, 'Matematika', 65),
(4, 'Ilmu Pengetahuan Alam', 68);

-- ===================================================================
-- TABEL `nilai`
-- ===================================================================
--
-- Struktur dari tabel `nilai`
-- Tabel ini menyimpan nilai siswa untuk setiap mata pelajaran
--
CREATE TABLE `nilai` (
  `id` int(11) NOT NULL AUTO_INCREMENT,      -- ID unik untuk setiap entri nilai
  `siswa_id` int(11) NOT NULL,               -- Foreign key ke tabel siswa
  `mapel_id` int(11) NOT NULL,               -- Foreign key ke tabel mapel
  `guru_id` int(11) NOT NULL,                -- Foreign key ke tabel guru (yang menginput)
  `semester` int(2) NOT NULL,                -- Semester (1 atau 2)
  `tahun_ajaran` varchar(10) NOT NULL,       -- Tahun ajaran (misal: 2023/2024)
  `nilai_angka` int(3) NOT NULL,             -- Nilai dalam angka (0-100)
  `predikat` varchar(2) DEFAULT NULL,        -- Predikat (A, B, C, D)
  PRIMARY KEY (`id`),                        -- Indeks utama
  KEY `siswa_id` (`siswa_id`),               -- Indeks untuk foreign key
  KEY `mapel_id` (`mapel_id`),               -- Indeks untuk foreign key
  KEY `guru_id` (`guru_id`)                  -- Indeks untuk foreign key
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ===================================================================
-- RELASI TABEL (Foreign Key Constraints)
-- ===================================================================
-- Constraints untuk tabel `guru`
-- Ketika user dihapus, data guru terkait juga akan dihapus (CASCADE)
ALTER TABLE `guru`
  ADD CONSTRAINT `guru_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- Constraints untuk tabel `siswa`
-- Ketika user dihapus, data siswa terkait juga akan dihapus (CASCADE)
ALTER TABLE `siswa`
  ADD CONSTRAINT `siswa_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- Constraints untuk tabel `nilai`
-- Mengatur relasi antara nilai, siswa, mapel, dan guru
-- Ketika entitas terkait dihapus, nilai juga akan dihapus (CASCADE)
ALTER TABLE `nilai`
  ADD CONSTRAINT `nilai_ibfk_1` FOREIGN KEY (`siswa_id`) REFERENCES `siswa` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `nilai_ibfk_2` FOREIGN KEY (`mapel_id`) REFERENCES `mapel` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `nilai_ibfk_3` FOREIGN KEY (`guru_id`) REFERENCES `guru` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- Selesai
COMMIT;
