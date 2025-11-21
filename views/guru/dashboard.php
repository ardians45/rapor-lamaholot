<?php
session_start();
// Cek apakah user sudah login dan rolenya guru
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] != 'guru') {
    header("Location: ../../views/login.php?error=Anda tidak memiliki akses");
    exit;
}
require_once '../../config/database.php';

// Mendapatkan informasi guru dari session
$user_id = $_SESSION['user_id'];
$guru_info = $koneksi->query("SELECT * FROM guru WHERE user_id = $user_id")->fetch_assoc();

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Guru - SD Lamaholot</title>
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
            <p class="px-3 text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Menu Guru</p>
            
            <a href="dashboard.php" class="flex items-center px-3 py-2.5 bg-primary-50 text-primary-700 rounded-xl group transition-colors">
                <i class="ph-fill ph-squares-four text-xl mr-3"></i>
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
                <h1 class="text-xl font-bold text-slate-800 hidden sm:block">Dashboard Guru</h1>
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
                        <?php echo strtoupper(substr($guru_info['nama'], 0, 1)); ?>
                    </div>
                    <div class="text-center md:text-left">
                        <h2 class="text-3xl font-bold mb-2">Halo, <?php echo htmlspecialchars($guru_info['nama']); ?>! 👋</h2>
                        <p class="text-primary-100 max-w-xl">Selamat datang di panel guru. Kelola nilai siswa, rekapitulasi, dan laporan hasil belajar dengan mudah di sini.</p>
                    </div>
                </div>
            </div>

            <!-- Menu Grid -->
            <div>
                <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center">
                    <i class="ph-fill ph-grid-four text-primary-500 mr-2"></i> Menu Akses Cepat
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Input Nilai -->
                    <a href="input_nilai.php" class="group bg-white p-6 rounded-2xl border border-slate-200 shadow-sm hover:shadow-xl hover:border-primary-500 transition-all duration-300 relative overflow-hidden">
                        <div class="absolute top-0 right-0 p-4 opacity-5 group-hover:scale-110 transition-transform duration-500">
                            <i class="ph-fill ph-pencil-simple text-8xl text-primary-600"></i>
                        </div>
                        <div class="relative z-10">
                            <div class="w-14 h-14 bg-primary-50 text-primary-600 rounded-xl flex items-center justify-center mb-4 group-hover:bg-primary-600 group-hover:text-white transition-colors">
                                <i class="ph-fill ph-pencil-simple text-2xl"></i>
                            </div>
                            <h4 class="text-xl font-bold text-slate-800 mb-2 group-hover:text-primary-700">Input Nilai</h4>
                            <p class="text-slate-500 text-sm">Masukkan nilai harian, UTS, dan UAS siswa secara terstruktur.</p>
                        </div>
                    </a>

                    <!-- Rekap Nilai -->
                    <a href="rekap_nilai.php" class="group bg-white p-6 rounded-2xl border border-slate-200 shadow-sm hover:shadow-xl hover:border-primary-500 transition-all duration-300 relative overflow-hidden">
                        <div class="absolute top-0 right-0 p-4 opacity-5 group-hover:scale-110 transition-transform duration-500">
                            <i class="ph-fill ph-notebook text-8xl text-blue-600"></i>
                        </div>
                        <div class="relative z-10">
                            <div class="w-14 h-14 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center mb-4 group-hover:bg-blue-600 group-hover:text-white transition-colors">
                                <i class="ph-fill ph-notebook text-2xl"></i>
                            </div>
                            <h4 class="text-xl font-bold text-slate-800 mb-2 group-hover:text-blue-700">Rekap Nilai</h4>
                            <p class="text-slate-500 text-sm">Lihat rekapitulasi nilai seluruh siswa dalam satu tampilan tabel.</p>
                        </div>
                    </a>

                    <!-- Preview Rapor -->
                    <a href="preview_rapor.php?tahun_ajaran=<?php echo date('Y').'/'.(date('Y')+1); ?>&semester=<?php echo (date('n') > 6) ? 1 : 2; ?>" class="group bg-white p-6 rounded-2xl border border-slate-200 shadow-sm hover:shadow-xl hover:border-primary-500 transition-all duration-300 relative overflow-hidden">
                        <div class="absolute top-0 right-0 p-4 opacity-5 group-hover:scale-110 transition-transform duration-500">
                            <i class="ph-fill ph-file-text text-8xl text-purple-600"></i>
                        </div>
                        <div class="relative z-10">
                            <div class="w-14 h-14 bg-purple-50 text-purple-600 rounded-xl flex items-center justify-center mb-4 group-hover:bg-purple-600 group-hover:text-white transition-colors">
                                <i class="ph-fill ph-file-text text-2xl"></i>
                            </div>
                            <h4 class="text-xl font-bold text-slate-800 mb-2 group-hover:text-purple-700">Preview Rapor</h4>
                            <p class="text-slate-500 text-sm">Pratinjau hasil belajar siswa sebelum dicetak final.</p>
                        </div>
                    </a>
                </div>
            </div>

        </div>
    </main>

    <script>
        // Mobile Sidebar Toggle
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
    </script>
</body>
</html>
