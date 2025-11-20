<?php
session_start();
require_once 'config/database.php';
require_once 'library/fpdf.php';

// ------------------------------------------------------------------
// Validasi Akses
// ------------------------------------------------------------------
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] != 'siswa') {
    die("Akses ditolak. Silakan login sebagai siswa.");
}

// ------------------------------------------------------------------
// Ambil Data
// ------------------------------------------------------------------
$user_id = $_SESSION['user_id'];
$semester = $_GET['semester'] ?? die('Semester tidak ditemukan.');
$tahun_ajaran = $_GET['tahun_ajaran'] ?? die('Tahun ajaran tidak ditemukan.');

// Ambil data siswa
$siswa_info_stmt = $koneksi->prepare("SELECT * FROM siswa WHERE user_id = ?");
$siswa_info_stmt->bind_param("i", $user_id);
$siswa_info_stmt->execute();
$siswa_info = $siswa_info_stmt->get_result()->fetch_assoc();
if (!$siswa_info) die('Data siswa tidak ditemukan.');

// Ambil data nilai
$nilai_stmt = $koneksi->prepare(
    "SELECT m.nama_mapel, m.kkm, n.nilai_angka, n.predikat
     FROM nilai n
     JOIN mapel m ON n.mapel_id = m.id
     WHERE n.siswa_id = ? AND n.semester = ? AND n.tahun_ajaran = ?
     ORDER BY m.nama_mapel ASC"
);
$nilai_stmt->bind_param("iis", $siswa_info['id'], $semester, $tahun_ajaran);
$nilai_stmt->execute();
$nilai_result = $nilai_stmt->get_result();

// ------------------------------------------------------------------
// Class PDF Kustom (untuk Header & Footer)
// ------------------------------------------------------------------
class PDF extends FPDF
{
    // Header Halaman
    function Header()
    {
        // Logo (jika ada, contoh: 'assets/logo.png')
        // $this->Image('assets/logo.png',10,6,30);
        $this->SetFont('Arial','B',15);
        $this->Cell(80); // Pindah ke kanan
        $this->Cell(30,10,'SEKOLAH DASAR LAMAHOLOT',0,1,'C'); // Changed to 0,1 to move to next line
        $this->SetFont('Arial','',10);
        $this->Cell(80);
        $this->Cell(30,5,'Jl. Bojong Indah Raya No.48 2, RT.8/RW.8, Rw. Buaya',0,1,'C'); // Changed to 0,1
        $this->Cell(80);
        $this->Cell(30,5,'Kecamatan Cengkareng, Kota Jakarta Barat, DKI Jakarta 11740',0,1,'C'); // Changed to 0,1
        $this->Cell(80);
        $this->Cell(30,10,'Telepon: (0383) 123456',0,0,'C');
        $this->Ln(8); // Increased spacing before line
        $this->Line(10, 40, 200, 40); // Adjusted Y position for line
        $this->Ln(7); // Increased spacing after line
    }

    // Footer Halaman
    function Footer()
    {
        $this->SetY(-15); // Posisi 1.5 cm dari bawah
        $this->SetFont('Helvetica','I',8);
        $this->Cell(0,10,'Halaman '.$this->PageNo().'/{nb}',0,0,'C');
    }
}

// ------------------------------------------------------------------
// Mulai Membuat PDF
// ------------------------------------------------------------------
$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetFont('Helvetica','B',14);
$pdf->Cell(0,10,'LAPORAN HASIL BELAJAR (RAPOR)',0,1,'C');
$pdf->Ln(5);

// Informasi Siswa
$pdf->SetFont('Helvetica','B',10);
$pdf->Cell(30, 6, 'Nama Siswa', 0, 0);
$pdf->Cell(5, 6, ':', 0, 0);
$pdf->SetFont('Helvetica','',10);
$pdf->Cell(80, 6, $siswa_info['nama'], 0, 0);

$pdf->SetFont('Helvetica','B',10);
$pdf->Cell(30, 6, 'Kelas', 0, 0);
$pdf->Cell(5, 6, ':', 0, 0);
$pdf->SetFont('Helvetica','',10);
$pdf->Cell(0, 6, $siswa_info['kelas'], 0, 1);

$pdf->SetFont('Helvetica','B',10);
$pdf->Cell(30, 6, 'NIS', 0, 0);
$pdf->Cell(5, 6, ':', 0, 0);
$pdf->SetFont('Helvetica','',10);
$pdf->Cell(80, 6, $siswa_info['nis'], 0, 0);

$pdf->SetFont('Helvetica','B',10);
$pdf->Cell(30, 6, 'Semester', 0, 0);
$pdf->Cell(5, 6, ':', 0, 0);
$pdf->SetFont('Helvetica','',10);
$pdf->Cell(0, 6, $semester == 1 ? 'Ganjil' : 'Genap', 0, 1);

$pdf->SetFont('Helvetica','B',10);
$pdf->Cell(30, 6, 'Tahun Ajaran', 0, 0);
$pdf->Cell(5, 6, ':', 0, 0);
$pdf->SetFont('Helvetica','',10);
$pdf->Cell(80, 6, $tahun_ajaran, 0, 1);

$pdf->Ln(10); // Spasi sebelum tabel

// Tabel Nilai
$pdf->SetFont('Helvetica','B',10);
$pdf->SetFillColor(230,230,230); // Warna abu-abu untuk header tabel
$pdf->Cell(10, 8, 'No', 1, 0, 'C', true);
$pdf->Cell(80, 8, 'Mata Pelajaran', 1, 0, 'C', true);
$pdf->Cell(20, 8, 'KKM', 1, 0, 'C', true);
$pdf->Cell(30, 8, 'Nilai Angka', 1, 0, 'C', true);
$pdf->Cell(30, 8, 'Predikat', 1, 1, 'C', true);

$pdf->SetFont('Helvetica','',10);
$no = 1;
if ($nilai_result->num_rows > 0) {
    while($row = $nilai_result->fetch_assoc()) {
        $pdf->Cell(10, 7, $no++, 1, 0, 'C');
        $pdf->Cell(80, 7, $row['nama_mapel'], 1, 0, 'L');
        $pdf->Cell(20, 7, $row['kkm'], 1, 0, 'C');
        $pdf->Cell(30, 7, $row['nilai_angka'], 1, 0, 'C');
        $pdf->Cell(30, 7, $row['predikat'], 1, 1, 'C');
    }
} else {
    $pdf->Cell(170, 10, 'Data nilai untuk periode ini belum tersedia.', 1, 1, 'C');
}
$pdf->Ln(15);

// Tanda Tangan
$pdf->SetFont('Arial','',10);
$pdf->Cell(120); // Pindah ke kanan
$pdf->Cell(0, 5, 'Jakarta Barat, ' . date('d F Y'), 0, 1, 'L');
$pdf->Cell(120);
$pdf->Cell(0, 5, 'Orang Tua / Wali Murid', 0, 1, 'L');
$pdf->Ln(20); // Spasi untuk tanda tangan
$pdf->Cell(120);
$pdf->Cell(0, 5, '(___________________)', 0, 1, 'L');

$pdf->Ln(-30); // Kembali ke atas sedikit untuk tanda tangan wali kelas

$pdf->Cell(10);
$pdf->Cell(0, 5, 'Wali Kelas', 0, 1, 'L');
$pdf->Ln(20);
$pdf->Cell(10);
$guru_wali_nama = "Budi Santoso, S.Pd."; // Contoh, bisa diambil dari database
$pdf->Cell(0, 5, $guru_wali_nama, 0, 1, 'L');
$pdf->Cell(10);
$pdf->Cell(0, 5, 'NIP: 199001012020121001', 0, 1, 'L');


// ------------------------------------------------------------------
// Output PDF
// ------------------------------------------------------------------
$filename = 'raport_' . $siswa_info['nis'] . '_' . $semester . '.pdf';
$pdf->Output('D', $filename); // 'D' untuk download langsung

$koneksi->close();
?>
