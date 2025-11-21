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

// Ambil parameter dari URL
$selected_mapel_id = $_GET['mapel_id'] ?? null;
$selected_kelas = $_GET['kelas'] ?? null;
$selected_semester = $_GET['semester'] ?? null;
$selected_tahun = $_GET['tahun_ajaran'] ?? date('Y').'/'.(date('Y')+1);

$grades = [];
if ($selected_mapel_id && $selected_kelas && $selected_semester && $selected_tahun) {
    $stmt = $koneksi->prepare(
        "SELECT s.id as siswa_id, s.nis, s.nama, n.nilai_angka, n.predikat 
         FROM siswa s 
         JOIN nilai n ON s.id = n.siswa_id
         WHERE s.kelas = ? AND n.mapel_id = ? AND n.semester = ? AND n.tahun_ajaran = ?
         ORDER BY s.nama"
    );
    $stmt->bind_param("siss", $selected_kelas, $selected_mapel_id, $selected_semester, $selected_tahun);
    $stmt->execute();
    $grades = $stmt->get_result();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekap Nilai - SD Lamaholot</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                    colors: {
                        primary: { 50: '#ecfdf5', 100: '#d1fae5', 500: '#10b981', 600: '#059669', 700: '#047857', 900: '#064e3b' }
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
            
            <a href="input_nilai.php" class="flex items-center px-3 py-2.5 text-slate-600 hover:bg-slate-50 hover:text-slate-900 rounded-xl group transition-colors">
                <i class="ph ph-pencil-simple text-xl mr-3 text-slate-400 group-hover:text-primary-600 transition-colors"></i>
                <span class="font-medium">Input Nilai</span>
            </a>
            
            <a href="rekap_nilai.php" class="flex items-center px-3 py-2.5 bg-primary-50 text-primary-700 rounded-xl group transition-colors">
                <i class="ph-fill ph-notebook text-xl mr-3"></i>
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
                <h1 class="text-xl font-bold text-slate-800">Rekap Nilai Siswa</h1>
            </div>
        </header>

        <!-- Content -->
        <div class="p-4 lg:p-8 max-w-7xl mx-auto w-full">
            
            <!-- Filter Card -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden mb-8">
                <div class="p-6 border-b border-slate-100 bg-slate-50/50 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-primary-100 flex items-center justify-center text-primary-600">
                        <i class="ph-fill ph-faders text-xl"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-800">Filter Rekap</h3>
                        <p class="text-sm text-slate-500">Pilih kriteria untuk melihat rekapitulasi nilai.</p>
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
                                    <?php mysqli_data_seek($kelas_list, 0); while($k = $kelas_list->fetch_assoc()): ?>
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
                                    <?php mysqli_data_seek($mapel_list, 0); while($m = $mapel_list->fetch_assoc()): ?>
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
                                <i class="ph-bold ph-list-magnifying-glass mr-2"></i> Tampilkan Rekap
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <?php if ($grades): ?>
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="p-6 border-b border-slate-100 flex flex-col sm:flex-row justify-between items-center gap-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-blue-50 flex items-center justify-center text-blue-600">
                            <i class="ph-fill ph-table text-xl"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-slate-800">Hasil Rekapitulasi</h3>
                            <p class="text-sm text-slate-500">Total: <?php echo $grades->num_rows; ?> Data Nilai</p>
                        </div>
                    </div>
                    <!-- Search Bar -->
                    <div class="relative w-full sm:w-64">
                        <input type="text" id="searchInput" placeholder="Cari nama siswa..." 
                            class="block w-full pl-10 pr-3 py-2 border border-slate-200 rounded-lg leading-5 bg-slate-50 placeholder-slate-400 focus:outline-none focus:bg-white focus:ring-1 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition-colors">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="ph ph-magnifying-glass text-slate-400"></i>
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">NIS</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Nama Siswa</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider text-center">Nilai Angka</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider text-center">Predikat</th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-slate-200" id="tableBody">
                            <?php if ($grades->num_rows > 0): ?>
                                <?php while($g = $grades->fetch_assoc()): ?>
                                <tr class="hover:bg-slate-50 transition-colors group">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500 font-mono">
                                        <?php echo htmlspecialchars($g['nis']); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-slate-900 search-name"><?php echo htmlspecialchars($g['nama']); ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-blue-50 text-blue-700">
                                            <?php echo htmlspecialchars($g['nilai_angka']); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full 
                                            <?php 
                                                $p = strtoupper($g['predikat']);
                                                if($p == 'A') echo 'bg-green-100 text-green-800';
                                                elseif($p == 'B') echo 'bg-blue-100 text-blue-800';
                                                elseif($p == 'C') echo 'bg-yellow-100 text-yellow-800';
                                                else echo 'bg-red-100 text-red-800';
                                            ?>">
                                            <?php echo htmlspecialchars($g['predikat']); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <a href="preview_rapor.php?siswa_id=<?php echo $g['siswa_id']; ?>&semester=<?php echo $selected_semester; ?>&tahun_ajaran=<?php echo urlencode($selected_tahun); ?>" target="_blank"
                                            class="text-primary-600 hover:text-primary-900 font-medium hover:underline flex items-center justify-end gap-1">
                                            <i class="ph-bold ph-eye"></i> Lihat Rapor
                                        </a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center text-slate-400">
                                        <i class="ph ph-folder-open text-4xl mb-2 block"></i>
                                        Belum ada data nilai untuk kriteria yang dipilih.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>

        </div>
    </main>

    <script>
        // Simple Client-side Search
        document.getElementById('searchInput')?.addEventListener('keyup', function() {
            const searchValue = this.value.toLowerCase();
            const rows = document.querySelectorAll('#tableBody tr');

            rows.forEach(row => {
                const name = row.querySelector('.search-name')?.textContent.toLowerCase() || '';
                
                if (name.includes(searchValue)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>
