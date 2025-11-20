-- PRD: Sistem Informasi Rapor Online (Web-Based)
-- Fase 1: Database & Koneksi
-- File: database.sql
-- Deskripsi: Skema database MySQL untuk aplikasi rapor online.

-- Membuat database jika belum ada
CREATE DATABASE IF NOT EXISTS `rapor_lamaholot` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `rapor_lamaholot`;

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
-- Tabel ini menyimpan data login untuk semua peran.
--
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','guru','siswa') NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data untuk tabel `users`
-- password untuk semua user di bawah ini adalah 'password123'
--
INSERT INTO `users` (`id`, `username`, `password`, `role`) VALUES
(1, 'admin', '$2y$10$E.t8H4KMM/BME16i3mSg8uWuK2hJm2Qk5L5D2.dY.6e9a7tG4pG.q', 'admin'),
(2, 'guru01', '$2y$10$E.t8H4KMM/BME16i3mSg8uWuK2hJm2Qk5L5D2.dY.6e9a7tG4pG.q', 'guru'),
(3, 'siswa01', '$2y$10$E.t8H4KMM/BME16i3mSg8uWuK2hJm2Qk5L5D2.dY.6e9a7tG4pG.q', 'siswa');

-- --------------------------------------------------------

--
-- Struktur dari tabel `guru`
--
CREATE TABLE `guru` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `nip` varchar(20) NOT NULL,
  `nama` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nip` (`nip`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data untuk tabel `guru`
--
INSERT INTO `guru` (`id`, `user_id`, `nip`, `nama`) VALUES
(1, 2, '199001012020121001', 'Budi Santoso, S.Pd.');

-- --------------------------------------------------------

--
-- Struktur dari tabel `siswa`
--
CREATE TABLE `siswa` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `nis` varchar(20) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `kelas` varchar(10) DEFAULT NULL,
  `jurusan` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nis` (`nis`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data untuk tabel `siswa`
--
INSERT INTO `siswa` (`id`, `user_id`, `nis`, `nama`, `kelas`, `jurusan`) VALUES
(1, 3, '1001', 'Ani Suryani', '6A', 'Reguler');

-- --------------------------------------------------------

--
-- Struktur dari tabel `mapel` (Mata Pelajaran)
--
CREATE TABLE `mapel` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama_mapel` varchar(100) NOT NULL,
  `kkm` int(3) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data untuk tabel `mapel`
--
INSERT INTO `mapel` (`id`, `nama_mapel`, `kkm`) VALUES
(1, 'Pendidikan Agama', 75),
(2, 'Bahasa Indonesia', 70),
(3, 'Matematika', 65),
(4, 'Ilmu Pengetahuan Alam', 68);

-- --------------------------------------------------------

--
-- Struktur dari tabel `nilai`
--
CREATE TABLE `nilai` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `siswa_id` int(11) NOT NULL,
  `mapel_id` int(11) NOT NULL,
  `guru_id` int(11) NOT NULL,
  `semester` int(2) NOT NULL,
  `tahun_ajaran` varchar(10) NOT NULL,
  `nilai_angka` int(3) NOT NULL,
  `predikat` varchar(2) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `siswa_id` (`siswa_id`),
  KEY `mapel_id` (`mapel_id`),
  KEY `guru_id` (`guru_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `guru`
--
ALTER TABLE `guru`
  ADD CONSTRAINT `guru_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `siswa`
--
ALTER TABLE `siswa`
  ADD CONSTRAINT `siswa_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `nilai`
--
ALTER TABLE `nilai`
  ADD CONSTRAINT `nilai_ibfk_1` FOREIGN KEY (`siswa_id`) REFERENCES `siswa` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `nilai_ibfk_2` FOREIGN KEY (`mapel_id`) REFERENCES `mapel` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `nilai_ibfk_3` FOREIGN KEY (`guru_id`) REFERENCES `guru` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;
