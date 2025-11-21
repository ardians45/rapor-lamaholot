<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] != 'guru') {
    header("Location: ../../views/login.php?error=Anda tidak memiliki akses");
    exit;
}
require_once '../../config/database.php';

// Data untuk dropdown
$mapel_list = $koneksi->query("SELECT id, nama_mapel FROM mapel ORDER BY nama_mapel");
$kelas_list = $koneksi->query("SELECT DISTINCT kelas FROM siswa WHERE kelas IS NOT NULL AND kelas != '' ORDER BY kelas");

// Ambil parameter dari URL (jika ada)
$selected_mapel_id = $_GET['mapel_id'] ?? null;
$selected_kelas = $_GET['kelas'] ?? null;
$selected_semester = $_GET['semester'] ?? null;
$selected_tahun = $_GET['tahun_ajaran'] ?? date('Y').'/'.(date('Y')+1);

$students = [];
if ($selected_mapel_id && $selected_kelas && $selected_semester && $selected_tahun) {
    $stmt = $koneksi->prepare(
        "SELECT s.id, s.nama, n.nilai_angka, n.predikat
         FROM siswa s
         LEFT JOIN nilai n ON s.id = n.siswa_id AND n.mapel_id = ? AND n.semester = ? AND n.tahun_ajaran = ?
         WHERE s.kelas = ?
         ORDER BY s.nama"
    );
    $stmt->bind_param("isss", $selected_mapel_id, $selected_semester, $selected_tahun, $selected_kelas);
    $stmt->execute();
    $students = $stmt->get_result();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Input Nilai Siswa - SD Lamaholot</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                    colors: {
                        primary: { 50: '#ecfdf5', 100: '#d1fae5', 500: '#10b981', 600: '#059669', 700: '#047857', 900: '#064e3b' } // Emerald
                    }
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 text-slate-800">

    <!-- Sidebar -->
    <aside id="sidebar" class="fixed top-0 left-0 h-full w-64 bg-white border-r border-slate-200 z-50 hidden lg:flex flex-col">
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
            
            <a href="input_nilai.php" class="flex items-center px-3 py-2.5 bg-primary-50 text-primary-700 rounded-xl group transition-colors">
                <i class="ph-fill ph-pencil-simple text-xl mr-3"></i>
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
                <a href="dashboard.php" class="mr-4 text-slate-400 hover:text-slate-600 lg:hidden">
                    <i class="ph ph-arrow-left text-2xl"></i>
                </a>
                <h1 class="text-xl font-bold text-slate-800">Input Nilai Siswa</h1>
            </div>
        </header>

        <!-- Content -->
        <div class="p-4 lg:p-8 max-w-7xl mx-auto w-full">

            <?php if (isset($_GET['success'])): ?>
            <div class="bg-green-50 border border-green-100 text-green-600 rounded-xl p-4 mb-6 flex items-center gap-3 animate-fade-in-down">
                <i class="ph-fill ph-check-circle text-xl"></i>
                <span><?php echo htmlspecialchars($_GET['success']); ?></span>
            </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['error'])): ?>
            <div class="bg-red-50 border border-red-100 text-red-600 rounded-xl p-4 mb-6 flex items-center gap-3 animate-fade-in-down">
                <i class="ph-fill ph-warning-circle text-xl"></i>
                <span><?php echo htmlspecialchars($_GET['error']); ?></span>
            </div>
            <?php endif; ?>

            <!-- Filter Card -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden mb-8">
                <div class="p-6 border-b border-slate-100 bg-slate-50/50 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-primary-100 flex items-center justify-center text-primary-600">
                        <i class="ph-fill ph-faders text-xl"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-800">Filter Data</h3>
                        <p class="text-sm text-slate-500">Pilih kelas dan mata pelajaran untuk mulai mengisi nilai.</p>
                    </div>
                </div>
                <div class="p-6">
                    <form method="GET" action="" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        <div class="space-y-2">
                            <label for="tahun_ajaran" class="text-sm font-semibold text-slate-700">Tahun Ajaran</label>
                            <div class="relative">
                                <input type="text" id="tahun_ajaran" name="tahun_ajaran" value="<?php echo htmlspecialchars($selected_tahun); ?>" 
                                    class="block w-full pl-4 pr-3 py-2.5 bg-white border border-slate-200 rounded-xl text-slate-900 placeholder-slate-400 focus:outline-none focus:border-primary-500 focus:ring-1 focus:ring-primary-500 transition-all">
                            </div>
                        </div>
                        <div class="space-y-2">
                            <label for="semester" class="text-sm font-semibold text-slate-700">Semester</label>
                            <div class="relative">
                                <select name="semester" id="semester" required 
                                    class="block w-full pl-4 pr-10 py-2.5 bg-white border border-slate-200 rounded-xl text-slate-900 focus:outline-none focus:border-primary-500 focus:ring-1 focus:ring-primary-500 appearance-none">
                                    <option value="">-- Pilih --</option>
                                    <option value="1" <?php echo $selected_semester == '1' ? 'selected' : ''; ?>>Ganjil</option>
                                    <option value="2" <?php echo $selected_semester == '2' ? 'selected' : ''; ?>>Genap</option>
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
                                    <?php while($k = $kelas_list->fetch_assoc()): ?>
                                        <option value="<?php echo htmlspecialchars($k['kelas']); ?>" <?php echo $selected_kelas == $k['kelas'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($k['kelas']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none text-slate-500">
                                    <i class="ph-bold ph-caret-down"></i>
                                </div>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <label for="mapel_id" class="text-sm font-semibold text-slate-700">Mata Pelajaran</label>
                            <div class="relative">
                                <select name="mapel_id" id="mapel_id" required 
                                    class="block w-full pl-4 pr-10 py-2.5 bg-white border border-slate-200 rounded-xl text-slate-900 focus:outline-none focus:border-primary-500 focus:ring-1 focus:ring-primary-500 appearance-none">
                                    <option value="">-- Pilih --</option>
                                    <?php while($m = $mapel_list->fetch_assoc()): ?>
                                        <option value="<?php echo $m['id']; ?>" <?php echo $selected_mapel_id == $m['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($m['nama_mapel']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none text-slate-500">
                                    <i class="ph-bold ph-caret-down"></i>
                                </div>
                            </div>
                        </div>
                        
                        <div class="md:col-span-2 lg:col-span-4 flex justify-end mt-2">
                            <button type="submit" class="px-6 py-2.5 bg-primary-600 hover:bg-primary-700 text-white font-bold rounded-xl shadow-lg shadow-primary-500/30 transition-all transform hover:-translate-y-0.5 flex items-center">
                                <i class="ph-bold ph-magnifying-glass mr-2"></i> Tampilkan Data Siswa
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <?php if ($students && $students->num_rows > 0): ?>
            <form action="../../actions/input_nilai.php" method="POST">
                <input type="hidden" name="tahun_ajaran" value="<?php echo htmlspecialchars($selected_tahun); ?>">
                <input type="hidden" name="semester" value="<?php echo htmlspecialchars($selected_semester); ?>">
                <input type="hidden" name="kelas" value="<?php echo htmlspecialchars($selected_kelas); ?>">
                <input type="hidden" name="mapel_id" value="<?php echo htmlspecialchars($selected_mapel_id); ?>">

                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                    <div class="p-6 border-b border-slate-100 flex flex-col sm:flex-row justify-between items-center gap-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-blue-50 flex items-center justify-center text-blue-600">
                                <i class="ph-fill ph-student text-xl"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-slate-800">Daftar Siswa Kelas <?php echo htmlspecialchars($selected_kelas); ?></h3>
                                <p class="text-sm text-slate-500">Total: <?php echo $students->num_rows; ?> Siswa</p>
                            </div>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider w-16">No</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Nama Siswa</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider w-40">Nilai Angka</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider w-32">Predikat</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-slate-200">
                                <?php $i = 1; while($s = $students->fetch_assoc()): ?>
                                <input type="hidden" name="siswa_id[]" value="<?php echo $s['id']; ?>">
                                <tr class="hover:bg-slate-50 transition-colors group">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">
                                        <?php echo $i++; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-slate-900"><?php echo htmlspecialchars($s['nama']); ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <input type="number" name="nilai_angka[]" min="0" max="100" value="<?php echo htmlspecialchars($s['nilai_angka']); ?>" placeholder="0-100"
                                            class="block w-full px-3 py-2 bg-white border border-slate-200 rounded-lg text-slate-900 focus:outline-none focus:border-primary-500 focus:ring-1 focus:ring-primary-500 text-center">
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <input type="text" name="predikat[]" maxlength="2" value="<?php echo htmlspecialchars($s['predikat']); ?>" placeholder="A/B/C"
                                            class="block w-full px-3 py-2 bg-white border border-slate-200 rounded-lg text-slate-900 focus:outline-none focus:border-primary-500 focus:ring-1 focus:ring-primary-500 text-center uppercase">
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="p-6 bg-slate-50 border-t border-slate-100 flex justify-end">
                         <button type="submit" class="px-8 py-3 bg-primary-600 hover:bg-primary-700 text-white font-bold rounded-xl shadow-lg shadow-primary-500/30 transition-all transform hover:-translate-y-0.5 flex items-center">
                            <i class="ph-bold ph-floppy-disk mr-2"></i> Simpan Semua Nilai
                        </button>
                    </div>
                </div>
            </form>
            <?php elseif(isset($_GET['mapel_id'])): ?>
                <div class="bg-amber-50 border border-amber-100 text-amber-700 rounded-xl p-8 text-center flex flex-col items-center animate-fade-in-down">
                    <div class="w-16 h-16 bg-amber-100 rounded-full flex items-center justify-center mb-4 text-amber-600">
                        <i class="ph-fill ph-warning-circle text-3xl"></i>
                    </div>
                    <h3 class="text-lg font-bold mb-2">Data Tidak Ditemukan</h3>
                    <p>Tidak ada siswa yang ditemukan di kelas ini atau kriteria pencarian belum lengkap.</p>
                </div>
            <?php endif; ?>

        </div>
    </main>
</body>
</html>
