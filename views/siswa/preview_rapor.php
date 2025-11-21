<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] != 'siswa') {
    header("Location: ../../views/login.php?error=Anda tidak memiliki akses");
    exit;
}
require_once '../../config/database.php';

// Validasi GET parameter
$semester = $_GET['semester'] ?? null;
$tahun_ajaran = $_GET['tahun_ajaran'] ?? null;
$user_id = $_SESSION['user_id'];

if (!$semester || !$tahun_ajaran) {
    die("Parameter tidak lengkap untuk menampilkan preview rapor.");
}

// Ambil data siswa
$siswa_info_stmt = $koneksi->prepare("SELECT * FROM siswa WHERE user_id = ?");
$siswa_info_stmt->bind_param("i", $user_id);
$siswa_info_stmt->execute();
$siswa_info = $siswa_info_stmt->get_result()->fetch_assoc();
if (!$siswa_info) die('Data siswa tidak ditemukan.');
$siswa_id = $siswa_info['id'];

// Ambil data nilai
$nilai_stmt = $koneksi->prepare(
    "SELECT m.nama_mapel, m.kkm, n.nilai_angka, n.predikat
     FROM nilai n
     JOIN mapel m ON n.mapel_id = m.id
     WHERE n.siswa_id = ? AND n.semester = ? AND n.tahun_ajaran = ?
     ORDER BY m.nama_mapel ASC"
);
$nilai_stmt->bind_param("iis", $siswa_id, $semester, $tahun_ajaran);
$nilai_stmt->execute();
$nilai_result = $nilai_stmt->get_result();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Preview Rapor - <?php echo htmlspecialchars($siswa_info['nama']); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Serif:wght@400;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { 
                        sans: ['Inter', 'sans-serif'],
                        serif: ['Noto Serif', 'serif']
                    },
                    colors: {
                        primary: { 50: '#eff6ff', 100: '#dbeafe', 500: '#3b82f6', 600: '#2563eb', 700: '#1d4ed8', 900: '#1e3a8a' }
                    }
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        .paper-sheet {
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        @media print {
            body { background-color: white; }
            .no-print { display: none; }
            .paper-sheet { box-shadow: none; border: none; }
        }
    </style>
</head>
<body class="bg-slate-100 min-h-screen py-8">

    <!-- Action Bar (Floating) -->
    <div class="fixed bottom-6 right-6 flex gap-4 no-print z-50">
        <a href="dashboard.php" class="px-6 py-3 bg-slate-800 text-white font-medium rounded-full shadow-lg hover:bg-slate-700 transition-all flex items-center">
            <i class="ph-bold ph-arrow-left mr-2"></i> Kembali
        </a>
        <a href="../../cetak_rapor.php?semester=<?php echo $semester; ?>&tahun_ajaran=<?php echo urlencode($tahun_ajaran); ?>" target="_blank" class="px-6 py-3 bg-primary-600 text-white font-bold rounded-full shadow-lg hover:bg-primary-700 transition-all flex items-center transform hover:-translate-y-1">
            <i class="ph-bold ph-printer mr-2"></i> Cetak PDF
        </a>
    </div>

    <!-- Paper Container -->
    <div class="max-w-[210mm] mx-auto bg-white paper-sheet p-12 min-h-[297mm] relative">
        
        <!-- Kop Surat -->
        <div class="text-center border-b-4 border-slate-800 pb-6 mb-8">
            <div class="flex justify-center mb-4">
                <i class="ph-fill ph-book-bookmark text-5xl text-slate-800"></i>
            </div>
            <h1 class="text-2xl font-bold text-slate-900 font-serif tracking-wide uppercase mb-1">Laporan Hasil Belajar (Rapor)</h1>
            <h2 class="text-xl font-bold text-slate-800 font-serif uppercase">Sekolah Dasar Lamaholot</h2>
            <p class="text-sm text-slate-600 mt-2 font-serif">Jl. Bojong Indah Raya No.48 2, RT.8/RW.8, Rw. Buaya, Kecamatan Cengkareng, Kota Jakarta Barat</p>
        </div>

        <!-- Identitas Siswa -->
        <div class="grid grid-cols-2 gap-x-12 gap-y-2 mb-8 text-sm font-serif">
            <div class="flex">
                <span class="w-32 font-bold">Nama Siswa</span>
                <span class="mr-2">:</span>
                <span class="uppercase border-b border-dotted border-slate-400 flex-1"><?php echo htmlspecialchars($siswa_info['nama']); ?></span>
            </div>
            <div class="flex">
                <span class="w-32 font-bold">Kelas</span>
                <span class="mr-2">:</span>
                <span class="uppercase border-b border-dotted border-slate-400 flex-1"><?php echo htmlspecialchars($siswa_info['kelas']); ?></span>
            </div>
            <div class="flex">
                <span class="w-32 font-bold">NIS</span>
                <span class="mr-2">:</span>
                <span class="uppercase border-b border-dotted border-slate-400 flex-1"><?php echo htmlspecialchars($siswa_info['nis']); ?></span>
            </div>
            <div class="flex">
                <span class="w-32 font-bold">Semester</span>
                <span class="mr-2">:</span>
                <span class="uppercase border-b border-dotted border-slate-400 flex-1"><?php echo htmlspecialchars($semester == 1 ? 'Ganjil' : 'Genap'); ?></span>
            </div>
            <div class="flex col-span-2">
                <span class="w-32 font-bold">Tahun Ajaran</span>
                <span class="mr-2">:</span>
                <span class="uppercase border-b border-dotted border-slate-400 flex-1"><?php echo htmlspecialchars($tahun_ajaran); ?></span>
            </div>
        </div>

        <!-- Tabel Nilai -->
        <div class="mb-12">
            <h3 class="text-center font-bold font-serif mb-4 text-lg border-b-2 border-slate-200 inline-block pb-1 px-4 mx-auto flex">CAPAIAN HASIL BELAJAR</h3>
            <table class="w-full border-collapse border border-slate-800 font-serif text-sm">
                <thead class="bg-slate-100">
                    <tr>
                        <th class="border border-slate-800 px-3 py-2 text-center w-12">No</th>
                        <th class="border border-slate-800 px-3 py-2 text-left">Mata Pelajaran</th>
                        <th class="border border-slate-800 px-3 py-2 text-center w-20">KKM</th>
                        <th class="border border-slate-800 px-3 py-2 text-center w-24">Nilai</th>
                        <th class="border border-slate-800 px-3 py-2 text-center w-24">Predikat</th>
                    </tr>
                </thead>
                <tbody>
                <?php $no = 1; if ($nilai_result->num_rows > 0): ?>
                    <?php while($row = $nilai_result->fetch_assoc()): ?>
                    <tr>
                        <td class="border border-slate-800 px-3 py-2 text-center"><?php echo $no++; ?></td>
                        <td class="border border-slate-800 px-3 py-2"><?php echo htmlspecialchars($row['nama_mapel']); ?></td>
                        <td class="border border-slate-800 px-3 py-2 text-center"><?php echo htmlspecialchars($row['kkm']); ?></td>
                        <td class="border border-slate-800 px-3 py-2 text-center font-bold"><?php echo htmlspecialchars($row['nilai_angka']); ?></td>
                        <td class="border border-slate-800 px-3 py-2 text-center"><?php echo htmlspecialchars($row['predikat']); ?></td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="border border-slate-800 px-3 py-8 text-center italic text-slate-500">
                            Data nilai untuk periode ini belum tersedia.
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Footer Tanda Tangan -->
        <div class="flex justify-between font-serif text-sm mt-16 px-8">
            <div class="text-center">
                <p class="mb-20">Mengetahui,<br>Orang Tua / Wali</p>
                <p class="font-bold border-t border-slate-800 pt-1 px-4 inline-block min-w-[150px]">( ........................... )</p>
            </div>
            <div class="text-center">
                <p class="mb-20">
                    Jakarta, <?php echo date('d F Y'); ?><br>
                    Wali Kelas
                </p>
                <p class="font-bold border-t border-slate-800 pt-1 px-4 inline-block min-w-[150px]">Budi Santoso, S.Pd.</p>
                <p class="text-xs mt-1">NIP. 19900101 202012 1 001</p>
            </div>
        </div>

    </div>

</body>
</html>
