<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] != 'guru') {
    header("Location: ../../views/login.php?error=Anda tidak memiliki akses");
    exit;
}
require_once '../../config/database.php';

// Initial Params
$siswa_id = $_GET['siswa_id'] ?? null;
$semester = $_GET['semester'] ?? (date('n') > 6 ? 1 : 2);
$tahun_ajaran = $_GET['tahun_ajaran'] ?? date('Y').'/'.(date('Y')+1);
$kelas = $_GET['kelas'] ?? null;

// Determine Mode
$is_preview = ($siswa_id !== null);

// --- LOGIC FOR SELECTION MODE ---
$kelas_list = null;
$students = null;

if (!$is_preview) {
    // Fetch Classes for Dropdown
    $kelas_list = $koneksi->query("SELECT DISTINCT kelas FROM siswa WHERE kelas IS NOT NULL AND kelas != '' ORDER BY kelas");

    // Fetch Students if Class is selected
    if ($kelas) {
        $stmt = $koneksi->prepare("SELECT id, nis, nama FROM siswa WHERE kelas = ? ORDER BY nama ASC");
        $stmt->bind_param("s", $kelas);
        $stmt->execute();
        $students = $stmt->get_result();
    }
}

// --- LOGIC FOR PREVIEW MODE ---
$siswa_info = null;
$nilai_result = null;

if ($is_preview) {
    // Fetch Siswa Info
    $stmt = $koneksi->prepare("SELECT * FROM siswa WHERE id = ?");
    $stmt->bind_param("i", $siswa_id);
    $stmt->execute();
    $siswa_info = $stmt->get_result()->fetch_assoc();
    
    if (!$siswa_info) {
        // Fallback if student not found
        header("Location: preview_rapor.php?error=Siswa tidak ditemukan");
        exit;
    }
    
    // Fetch Nilai
    $stmt = $koneksi->prepare(
        "SELECT m.nama_mapel, m.kkm, n.nilai_angka, n.predikat
         FROM nilai n
         JOIN mapel m ON n.mapel_id = m.id
         WHERE n.siswa_id = ? AND n.semester = ? AND n.tahun_ajaran = ?
         ORDER BY m.nama_mapel ASC"
    );
    $stmt->bind_param("iis", $siswa_id, $semester, $tahun_ajaran);
    $stmt->execute();
    $nilai_result = $stmt->get_result();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Preview Rapor - SD Lamaholot</title>
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
                        primary: { 50: '#ecfdf5', 100: '#d1fae5', 500: '#10b981', 600: '#059669', 700: '#047857', 900: '#064e3b' }
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
            .paper-sheet { box-shadow: none; border: none; margin: 0; width: 100%; max-width: 100%; }
        }
    </style>
</head>
<body class="<?php echo $is_preview ? 'bg-slate-100 min-h-screen py-8' : 'bg-slate-50 text-slate-800'; ?>">

<?php if (!$is_preview): ?>
    <!-- ================= SELECTION MODE ================= -->
    
    <!-- Mobile Sidebar Overlay -->
    <div id="sidebarOverlay" class="fixed inset-0 bg-black/50 z-40 hidden transition-opacity opacity-0 lg:hidden"></div>

    <!-- Sidebar -->
    <aside id="sidebar" class="fixed top-0 left-0 h-full w-64 bg-white border-r border-slate-200 z-50 transform -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out flex flex-col">
        <div class="h-16 flex items-center px-6 border-b border-slate-100">
            <div class="w-8 h-8 bg-gradient-to-br from-primary-500 to-primary-700 rounded-lg flex items-center justify-center shadow-md shadow-primary-500/30 mr-3">
                <i class="ph ph-book-bookmark text-white text-lg"></i>
            </div>
            <span class="text-lg font-bold bg-clip-text text-transparent bg-gradient-to-r from-slate-800 to-slate-600">SD Lamaholot</span>
        </div>

        <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-1">
            <p class="px-3 text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Menu Guru</p>
            <a href="dashboard.php" class="flex items-center px-3 py-2.5 text-slate-600 hover:bg-slate-50 hover:text-slate-900 rounded-xl group transition-colors">
                <i class="ph ph-squares-four text-xl mr-3 text-slate-400 group-hover:text-primary-600 transition-colors"></i>
                <span class="font-medium">Dashboard</span>
            </a>
            <a href="input_nilai.php" class="flex items-center px-3 py-2.5 text-slate-600 hover:bg-slate-50 hover:text-slate-900 rounded-xl group transition-colors">
                <i class="ph ph-pencil-simple text-xl mr-3 text-slate-400 group-hover:text-primary-600 transition-colors"></i>
                <span class="font-medium">Input Nilai</span>
            </a>
            <a href="rekap_nilai.php" class="flex items-center px-3 py-2.5 text-slate-600 hover:bg-slate-50 hover:text-slate-900 rounded-xl group transition-colors">
                <i class="ph ph-notebook text-xl mr-3 text-slate-400 group-hover:text-primary-600 transition-colors"></i>
                <span class="font-medium">Rekap Nilai</span>
            </a>
           
        </nav>

        <div class="p-4 border-t border-slate-100">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 rounded-full bg-primary-100 flex items-center justify-center text-primary-700 font-bold">
                    <?php echo strtoupper(substr($_SESSION['username'], 0, 2)); ?>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-slate-900 truncate"><?php echo htmlspecialchars($_SESSION['username']); ?></p>
                    <p class="text-xs text-slate-500 truncate">Guru Pengajar</p>
                </div>
            </div>
            <a href="../../actions/logout.php" class="flex items-center justify-center w-full py-2 px-4 text-sm font-medium text-red-600 bg-red-50 hover:bg-red-100 rounded-lg transition-colors">
                <i class="ph ph-sign-out mr-2"></i> Logout
            </a>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="lg:ml-64 min-h-screen flex flex-col">
        <!-- Header -->
        <header class="h-16 bg-white/80 backdrop-blur-md border-b border-slate-200 sticky top-0 z-30 px-4 lg:px-8 flex items-center justify-between">
            <div class="flex items-center">
                <button id="menuBtn" class="p-2 -ml-2 mr-2 text-slate-500 hover:bg-slate-100 rounded-lg lg:hidden">
                    <i class="ph ph-list text-2xl"></i>
                </button>
                <h1 class="text-xl font-bold text-slate-800 hidden sm:block">Preview Rapor</h1>
            </div>
        </header>

        <!-- Content -->
        <div class="p-4 lg:p-8 max-w-7xl mx-auto w-full">
            
            <!-- Filter Card -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden mb-8">
                <div class="p-6 border-b border-slate-100 bg-slate-50/50 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-primary-100 flex items-center justify-center text-primary-600">
                        <i class="ph-fill ph-funnel text-xl"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-800">Pilih Kelas</h3>
                        <p class="text-sm text-slate-500">Tentukan kriteria untuk menampilkan daftar siswa.</p>
                    </div>
                </div>
                <div class="p-6">
                    <form method="GET" action="" class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-6">
                        <div class="space-y-2">
                            <label for="tahun_ajaran" class="text-sm font-semibold text-slate-700">Tahun Ajaran</label>
                            <div class="relative">
                                <input type="text" id="tahun_ajaran" name="tahun_ajaran" value="<?php echo htmlspecialchars($tahun_ajaran); ?>" 
                                    class="block w-full pl-4 pr-3 py-2.5 bg-white border border-slate-200 rounded-xl text-slate-900 placeholder-slate-400 focus:outline-none focus:border-primary-500 focus:ring-1 focus:ring-primary-500 transition-all">
                            </div>
                        </div>
                        <div class="space-y-2">
                            <label for="semester" class="text-sm font-semibold text-slate-700">Semester</label>
                            <div class="relative">
                                <select name="semester" id="semester" required 
                                    class="block w-full pl-4 pr-10 py-2.5 bg-white border border-slate-200 rounded-xl text-slate-900 focus:outline-none focus:border-primary-500 focus:ring-1 focus:ring-primary-500 appearance-none">
                                    <option value="1" <?php echo $semester == '1' ? 'selected' : ''; ?>>Ganjil</option>
                                    <option value="2" <?php echo $semester == '2' ? 'selected' : ''; ?>>Genap</option>
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none text-slate-500">
                                    <i class="ph-bold ph-caret-down"></i>
                                </div>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <label for="kelas" class="text-sm font-semibold text-slate-700">Kelas</label>
                            <div class="relative">
                                <select name="kelas" id="kelas" required 
                                    class="block w-full pl-4 pr-10 py-2.5 bg-white border border-slate-200 rounded-xl text-slate-900 focus:outline-none focus:border-primary-500 focus:ring-1 focus:ring-primary-500 appearance-none">
                                    <option value="">-- Pilih --</option>
                                    <?php if($kelas_list): ?>
                                        <?php mysqli_data_seek($kelas_list, 0); while($k = $kelas_list->fetch_assoc()): ?>
                                            <option value="<?php echo htmlspecialchars($k['kelas']); ?>" <?php echo $kelas == $k['kelas'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($k['kelas']); ?>
                                            </option>
                                        <?php endwhile; ?>
                                    <?php endif; ?>
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none text-slate-500">
                                    <i class="ph-bold ph-caret-down"></i>
                                </div>
                            </div>
                        </div>
                        
                        <div class="flex items-end">
                            <button type="submit" class="w-full px-6 py-2.5 bg-primary-600 hover:bg-primary-700 text-white font-bold rounded-xl shadow-lg shadow-primary-500/30 transition-all transform hover:-translate-y-0.5 flex items-center justify-center">
                                <i class="ph-bold ph-magnifying-glass mr-2"></i> Tampilkan
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <?php if ($students && $students->num_rows > 0): ?>
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="p-6 border-b border-slate-100 flex flex-col sm:flex-row justify-between items-center gap-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-blue-50 flex items-center justify-center text-blue-600">
                            <i class="ph-fill ph-student text-xl"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-slate-800">Daftar Siswa Kelas <?php echo htmlspecialchars($kelas); ?></h3>
                            <p class="text-sm text-slate-500">Total: <?php echo $students->num_rows; ?> Siswa</p>
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider w-16">No</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">NIS</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Nama Siswa</th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-slate-200">
                            <?php $i = 1; while($s = $students->fetch_assoc()): ?>
                            <tr class="hover:bg-slate-50 transition-colors group">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500"><?php echo $i++; ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500 font-mono"><?php echo htmlspecialchars($s['nis']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-slate-900"><?php echo htmlspecialchars($s['nama']); ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <a href="preview_rapor.php?siswa_id=<?php echo $s['id']; ?>&semester=<?php echo $semester; ?>&tahun_ajaran=<?php echo urlencode($tahun_ajaran); ?>&kelas=<?php echo urlencode($kelas); ?>" 
                                        class="inline-flex items-center px-4 py-2 bg-primary-50 text-primary-700 hover:bg-primary-100 rounded-lg transition-colors font-semibold text-xs uppercase tracking-wide">
                                        <i class="ph-bold ph-eye mr-2"></i> Lihat Rapor
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php elseif($kelas): ?>
                <div class="bg-amber-50 border border-amber-100 text-amber-700 rounded-xl p-8 text-center flex flex-col items-center animate-fade-in-down">
                    <div class="w-16 h-16 bg-amber-100 rounded-full flex items-center justify-center mb-4 text-amber-600">
                        <i class="ph-fill ph-warning-circle text-3xl"></i>
                    </div>
                    <h3 class="text-lg font-bold mb-2">Data Tidak Ditemukan</h3>
                    <p>Tidak ada siswa yang terdaftar di kelas ini.</p>
                </div>
            <?php endif; ?>

        </div>
    </main>

    <script>
        // Sidebar Toggle
        const menuBtn = document.getElementById('menuBtn');
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');
        let isSidebarOpen = false;

        if(menuBtn) {
            function toggleSidebar() {
                isSidebarOpen = !isSidebarOpen;
                if (isSidebarOpen) {
                    sidebar.classList.remove('-translate-x-full');
                    overlay.classList.remove('hidden', 'opacity-0');
                    document.body.style.overflow = 'hidden';
                } else {
                    sidebar.classList.add('-translate-x-full');
                    overlay.classList.add('opacity-0');
                    setTimeout(() => {
                        overlay.classList.add('hidden');
                    }, 300);
                    document.body.style.overflow = '';
                }
            }

            menuBtn.addEventListener('click', toggleSidebar);
            overlay.addEventListener('click', toggleSidebar);
        }
    </script>

<?php else: ?>
    <!-- ================= PREVIEW MODE ================= -->
    
    <!-- Action Bar (Floating) -->
    <div class="fixed bottom-6 right-6 flex gap-4 no-print z-50">
        <a href="preview_rapor.php?tahun_ajaran=<?php echo urlencode($tahun_ajaran); ?>&semester=<?php echo $semester; ?><?php echo $kelas ? '&kelas='.urlencode($kelas) : ''; ?>" 
           class="px-6 py-3 bg-slate-800 text-white font-medium rounded-full shadow-lg hover:bg-slate-700 transition-all flex items-center">
            <i class="ph-bold ph-arrow-left mr-2"></i> Kembali
        </a>
        <a href="../../cetak_rapor_by_guru.php?siswa_id=<?php echo $siswa_id; ?>&semester=<?php echo $semester; ?>&tahun_ajaran=<?php echo urlencode($tahun_ajaran); ?>" 
           target="_blank" 
           class="px-6 py-3 bg-primary-600 text-white font-bold rounded-full shadow-lg hover:bg-primary-700 transition-all flex items-center transform hover:-translate-y-1">
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
<?php endif; ?>

</body>
</html>
