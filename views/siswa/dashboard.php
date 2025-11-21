<?php
session_start();
// Cek apakah user sudah login dan rolenya siswa
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] != 'siswa') {
    header("Location: ../../views/login.php?error=Anda tidak memiliki akses");
    exit;
}
require_once '../../config/database.php';

// Mendapatkan informasi siswa dari session
$user_id = $_SESSION['user_id'];
$siswa_info = $koneksi->query("SELECT * FROM siswa WHERE user_id = $user_id")->fetch_assoc();
$current_year = date('Y').'/'.(date('Y')+1);

// Ambil daftar tahun ajaran yang ada nilainya untuk siswa ini
$query = "(SELECT DISTINCT tahun_ajaran FROM nilai WHERE siswa_id = {$siswa_info['id']})
          UNION
          (SELECT '{$current_year}')
          ORDER BY tahun_ajaran DESC";
$tahun_ajaran_list = $koneksi->query($query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Siswa - SD Lamaholot</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                    colors: {
                        primary: { 50: '#eff6ff', 100: '#dbeafe', 500: '#3b82f6', 600: '#2563eb', 700: '#1d4ed8', 900: '#1e3a8a' } // Blue
                    }
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 3px; }
    </style>
</head>
<body class="bg-slate-50 text-slate-800">

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
            <p class="px-3 text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Menu Siswa</p>
            
            <a href="dashboard.php" class="flex items-center px-3 py-2.5 bg-primary-50 text-primary-700 rounded-xl group transition-colors">
                <i class="ph-fill ph-squares-four text-xl mr-3"></i>
                <span class="font-medium">Dashboard</span>
            </a>
            
            <a href="../../actions/logout.php" class="flex items-center px-3 py-2.5 text-slate-600 hover:bg-slate-50 hover:text-slate-900 rounded-xl group transition-colors mt-auto">
                <i class="ph ph-sign-out text-xl mr-3 text-slate-400 group-hover:text-primary-600 transition-colors"></i>
                <span class="font-medium">Logout</span>
            </a>
        </nav>

        <div class="p-4 border-t border-slate-100">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-primary-100 flex items-center justify-center text-primary-700 font-bold">
                    <?php echo strtoupper(substr($siswa_info['nama'], 0, 2)); ?>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-slate-900 truncate"><?php echo htmlspecialchars(explode(' ', $siswa_info['nama'])[0]); ?></p>
                    <p class="text-xs text-slate-500 truncate">Siswa</p>
                </div>
            </div>
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
                <h1 class="text-xl font-bold text-slate-800 hidden sm:block">Dashboard Siswa</h1>
            </div>
            
            <div class="flex items-center gap-4">
                <div class="hidden md:flex items-center text-sm text-slate-500 bg-slate-100 px-3 py-1.5 rounded-full">
                    <i class="ph-fill ph-calendar-blank mr-2 text-slate-400"></i>
                    <?php echo date('d F Y'); ?>
                </div>
            </div>
        </header>

        <!-- Content Body -->
        <div class="p-4 lg:p-8 max-w-7xl mx-auto w-full space-y-8">
            
            <!-- Welcome Banner -->
            <div class="relative overflow-hidden bg-gradient-to-r from-primary-600 to-primary-800 rounded-2xl p-8 text-white shadow-xl shadow-primary-900/10">
                <div class="absolute top-0 right-0 -mt-10 -mr-10 w-40 h-40 bg-white opacity-10 rounded-full blur-2xl"></div>
                <div class="absolute bottom-0 left-0 -mb-10 -ml-10 w-40 h-40 bg-black opacity-10 rounded-full blur-2xl"></div>
                
                <div class="relative z-10 flex flex-col md:flex-row items-center gap-6">
                    <div class="w-16 h-16 bg-white/20 backdrop-blur-sm rounded-full flex items-center justify-center text-2xl font-bold border-2 border-white/30">
                        <?php echo strtoupper(substr($siswa_info['nama'], 0, 1)); ?>
                    </div>
                    <div class="text-center md:text-left">
                        <h2 class="text-2xl md:text-3xl font-bold mb-2">Halo, <?php echo htmlspecialchars($siswa_info['nama']); ?>! 👋</h2>
                        <p class="text-primary-100">Selamat datang di dashboard siswa. Lihat hasil belajarmu di sini.</p>
                    </div>
                </div>
            </div>

            <!-- Main Tabs & Content -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <!-- Left Column: Report Form (2/3 width on large) -->
                <div class="lg:col-span-2 space-y-6">
                    
                    <!-- Tab Navigation (Visual Only as sections are stacked or toggled) -->
                    <div class="bg-slate-200 p-1 rounded-xl inline-flex">
                        <button onclick="switchTab('rapor')" id="tab-rapor" class="px-6 py-2 rounded-lg text-sm font-medium bg-white text-slate-900 shadow-sm transition-all">
                            <i class="ph-fill ph-file-text mr-2"></i> Lihat Rapor
                        </button>
                        <button onclick="switchTab('profil')" id="tab-profil" class="px-6 py-2 rounded-lg text-sm font-medium text-slate-500 hover:text-slate-700 transition-all">
                            <i class="ph-fill ph-user mr-2"></i> Profil Saya
                        </button>
                    </div>

                    <!-- Content: Rapor -->
                    <div id="content-rapor" class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 md:p-8 animate-fade-in">
                        <div class="flex items-center mb-6">
                            <div class="w-10 h-10 bg-primary-50 text-primary-600 rounded-lg flex items-center justify-center mr-4">
                                <i class="ph-fill ph-file-text text-xl"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-slate-800">Cetak Rapor & Nilai</h3>
                                <p class="text-sm text-slate-500">Pilih periode akademik untuk melihat hasil studi.</p>
                            </div>
                        </div>

                        <form action="preview_rapor.php" method="GET" class="space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="space-y-2">
                                    <label for="tahun_ajaran" class="text-sm font-semibold text-slate-700">Tahun Ajaran</label>
                                    <div class="relative">
                                        <select name="tahun_ajaran" id="tahun_ajaran" required class="block w-full pl-4 pr-10 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 focus:outline-none focus:border-primary-500 focus:ring-1 focus:ring-primary-500 appearance-none">
                                            <option value="">-- Pilih Tahun --</option>
                                            <?php while($th = $tahun_ajaran_list->fetch_assoc()): ?>
                                                <option value="<?php echo htmlspecialchars($th['tahun_ajaran']); ?>"><?php echo htmlspecialchars($th['tahun_ajaran']); ?></option>
                                            <?php endwhile; ?>
                                        </select>
                                        <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none text-slate-500">
                                            <i class="ph-bold ph-caret-down"></i>
                                        </div>
                                    </div>
                                </div>

                                <div class="space-y-2">
                                    <label for="semester" class="text-sm font-semibold text-slate-700">Semester</label>
                                    <div class="relative">
                                        <select name="semester" id="semester" required class="block w-full pl-4 pr-10 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 focus:outline-none focus:border-primary-500 focus:ring-1 focus:ring-primary-500 appearance-none">
                                            <option value="1">Ganjil</option>
                                            <option value="2">Genap</option>
                                        </select>
                                        <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none text-slate-500">
                                            <i class="ph-bold ph-caret-down"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="pt-4">
                                <button type="submit" class="w-full md:w-auto px-8 py-3 bg-primary-600 hover:bg-primary-700 text-white font-bold rounded-xl shadow-lg shadow-primary-500/30 transition-all transform hover:-translate-y-0.5 flex items-center justify-center">
                                    <i class="ph-bold ph-eye mr-2"></i>
                                    Lihat Rapor
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Content: Profil (Hidden by default) -->
                    <div id="content-profil" class="hidden bg-white rounded-2xl border border-slate-200 shadow-sm p-6 md:p-8 animate-fade-in">
                        <div class="flex items-center mb-6">
                            <div class="w-10 h-10 bg-indigo-50 text-indigo-600 rounded-lg flex items-center justify-center mr-4">
                                <i class="ph-fill ph-user-circle text-xl"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-slate-800">Data Pribadi</h3>
                                <p class="text-sm text-slate-500">Informasi data diri siswa terdaftar.</p>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <div class="flex flex-col sm:flex-row justify-between p-4 bg-slate-50 rounded-xl border border-slate-100">
                                <span class="text-sm text-slate-500 mb-1 sm:mb-0">Nama Lengkap</span>
                                <span class="font-semibold text-slate-900"><?php echo htmlspecialchars($siswa_info['nama']); ?></span>
                            </div>
                            <div class="flex flex-col sm:flex-row justify-between p-4 bg-slate-50 rounded-xl border border-slate-100">
                                <span class="text-sm text-slate-500 mb-1 sm:mb-0">Nomor Induk Siswa (NIS)</span>
                                <span class="font-semibold text-slate-900"><?php echo htmlspecialchars($siswa_info['nis']); ?></span>
                            </div>
                            <div class="flex flex-col sm:flex-row justify-between p-4 bg-slate-50 rounded-xl border border-slate-100">
                                <span class="text-sm text-slate-500 mb-1 sm:mb-0">Kelas Saat Ini</span>
                                <span class="font-semibold text-slate-900"><?php echo htmlspecialchars($siswa_info['kelas']); ?></span>
                            </div>
                            <div class="flex flex-col sm:flex-row justify-between p-4 bg-slate-50 rounded-xl border border-slate-100">
                                <span class="text-sm text-slate-500 mb-1 sm:mb-0">Jurusan</span>
                                <span class="font-semibold text-slate-900"><?php echo htmlspecialchars($siswa_info['jurusan']); ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Quick Info -->
                <div class="space-y-6">
                    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                        <h4 class="font-bold text-slate-800 mb-4">Informasi Sekolah</h4>
                        <div class="space-y-4 text-sm">
                            <div class="flex items-start gap-3">
                                <i class="ph-fill ph-map-pin text-primary-500 text-lg mt-0.5"></i>
                                <span class="text-slate-600">Jl. Bojong Indah Raya No.48 2, Cengkareng, Jakarta Barat</span>
                            </div>
                            <div class="flex items-start gap-3">
                                <i class="ph-fill ph-phone text-primary-500 text-lg mt-0.5"></i>
                                <span class="text-slate-600">(0383) 123456</span>
                            </div>
                            <div class="flex items-start gap-3">
                                <i class="ph-fill ph-envelope text-primary-500 text-lg mt-0.5"></i>
                                <span class="text-slate-600">info@sdlamaholot.sch.id</span>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl p-6 text-white shadow-lg">
                        <i class="ph-fill ph-quotes text-4xl opacity-30 mb-2"></i>
                        <p class="font-medium italic mb-4">"Pendidikan adalah senjata paling ampuh yang bisa kamu gunakan untuk mengubah dunia."</p>
                        <p class="text-sm opacity-80 text-right">- Nelson Mandela</p>
                    </div>
                </div>

            </div>
        </div>
    </main>

    <script>
        // Sidebar Toggle
        const menuBtn = document.getElementById('menuBtn');
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');
        let isSidebarOpen = false;

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

        // Tab Switcher
        function switchTab(tabName) {
            const tabs = ['rapor', 'profil'];
            
            tabs.forEach(t => {
                const content = document.getElementById(`content-${t}`);
                const btn = document.getElementById(`tab-${t}`);
                
                if (t === tabName) {
                    content.classList.remove('hidden');
                    btn.classList.remove('text-slate-500');
                    btn.classList.add('bg-white', 'text-slate-900', 'shadow-sm');
                } else {
                    content.classList.add('hidden');
                    btn.classList.remove('bg-white', 'text-slate-900', 'shadow-sm');
                    btn.classList.add('text-slate-500');
                }
            });
        }
    </script>
</body>
</html>
