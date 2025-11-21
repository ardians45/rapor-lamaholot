<?php
session_start();
// Cek apakah user sudah login dan rolenya admin
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] != 'admin') {
    header("Location: ../../views/login.php?error=Anda tidak memiliki akses");
    exit;
}
require_once '../../config/database.php';

// Ambil statistik sederhana
$total_guru = $koneksi->query("SELECT COUNT(*) AS total FROM guru")->fetch_assoc()['total'];
$total_siswa = $koneksi->query("SELECT COUNT(*) AS total FROM siswa")->fetch_assoc()['total'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - SD Lamaholot</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Google Fonts: Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Phosphor Icons -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    colors: {
                        primary: {
                            50: '#fff1f2',
                            100: '#ffe4e6',
                            500: '#f43f5e', // Rose 500
                            600: '#e11d48', // Rose 600
                            700: '#be123c', // Rose 700
                            900: '#881337',
                        },
                        secondary: '#f59e0b', // Amber
                    }
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        /* Custom Scrollbar */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    </style>
</head>
<body class="bg-slate-50 text-slate-800">

    <!-- Mobile Sidebar Overlay -->
    <div id="sidebarOverlay" class="fixed inset-0 bg-black/50 z-40 hidden transition-opacity opacity-0 lg:hidden"></div>

    <!-- Sidebar -->
    <aside id="sidebar" class="fixed top-0 left-0 h-full w-64 bg-white border-r border-slate-200 z-50 transform -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out flex flex-col">
        <!-- Logo -->
        <div class="h-16 flex items-center px-6 border-b border-slate-100">
            <div class="w-8 h-8 bg-gradient-to-br from-primary-500 to-primary-700 rounded-lg flex items-center justify-center shadow-md shadow-primary-500/30 mr-3">
                <i class="ph ph-book-bookmark text-white text-lg"></i>
            </div>
            <span class="text-lg font-bold bg-clip-text text-transparent bg-gradient-to-r from-slate-800 to-slate-600">SD Lamaholot</span>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-1">
            <p class="px-3 text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Menu Utama</p>
            
            <a href="dashboard.php" class="flex items-center px-3 py-2.5 bg-primary-50 text-primary-700 rounded-xl group transition-colors">
                <i class="ph-fill ph-squares-four text-xl mr-3"></i>
                <span class="font-medium">Dashboard</span>
            </a>
            
            <a href="manage_guru.php" class="flex items-center px-3 py-2.5 text-slate-600 hover:bg-slate-50 hover:text-slate-900 rounded-xl group transition-colors">
                <i class="ph ph-chalkboard-teacher text-xl mr-3 text-slate-400 group-hover:text-primary-600 transition-colors"></i>
                <span class="font-medium">Kelola Guru</span>
            </a>
            
            <a href="manage_siswa.php" class="flex items-center px-3 py-2.5 text-slate-600 hover:bg-slate-50 hover:text-slate-900 rounded-xl group transition-colors">
                <i class="ph ph-student text-xl mr-3 text-slate-400 group-hover:text-primary-600 transition-colors"></i>
                <span class="font-medium">Kelola Siswa</span>
            </a>
        </nav>

        <!-- User Profile Bottom -->
        <div class="p-4 border-t border-slate-100">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 rounded-full bg-primary-100 flex items-center justify-center text-primary-700 font-bold">
                    <?php echo strtoupper(substr($_SESSION['username'], 0, 2)); ?>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-slate-900 truncate"><?php echo htmlspecialchars($_SESSION['username']); ?></p>
                    <p class="text-xs text-slate-500 truncate">Administrator</p>
                </div>
            </div>
            <a href="../../actions/logout.php" class="flex items-center justify-center w-full py-2 px-4 text-sm font-medium text-red-600 bg-red-50 hover:bg-red-100 rounded-lg transition-colors">
                <i class="ph ph-sign-out mr-2"></i> Logout
            </a>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="lg:ml-64 min-h-screen flex flex-col">
        <!-- Top Header -->
        <header class="h-16 bg-white/80 backdrop-blur-md border-b border-slate-200 sticky top-0 z-30 px-4 lg:px-8 flex items-center justify-between">
            <div class="flex items-center">
                <button id="menuBtn" class="p-2 -ml-2 mr-2 text-slate-500 hover:bg-slate-100 rounded-lg lg:hidden">
                    <i class="ph ph-list text-2xl"></i>
                </button>
                <h1 class="text-xl font-bold text-slate-800 hidden sm:block">Overview</h1>
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
                
                <div class="relative z-10">
                    <h2 class="text-3xl font-bold mb-2">Selamat Datang, Admin! 👋</h2>
                    <p class="text-primary-100 max-w-xl">Kelola data guru, siswa, dan sistem rapor online SD Lamaholot dengan mudah dan cepat melalui dashboard ini.</p>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Card Guru -->
                <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm hover:shadow-md transition-shadow group relative overflow-hidden">
                    <div class="absolute top-0 right-0 p-4 opacity-5 group-hover:scale-110 transition-transform duration-500">
                        <i class="ph-fill ph-chalkboard-teacher text-9xl text-primary-600"></i>
                    </div>
                    <div class="relative z-10">
                        <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center mb-4 group-hover:bg-blue-600 group-hover:text-white transition-colors">
                            <i class="ph-fill ph-chalkboard-teacher text-2xl"></i>
                        </div>
                        <p class="text-slate-500 font-medium mb-1">Total Guru</p>
                        <h3 class="text-4xl font-bold text-slate-800"><?php echo $total_guru; ?></h3>
                        <div class="mt-4 flex items-center text-sm text-blue-600 font-medium cursor-pointer hover:underline">
                            <a href="manage_guru.php">Lihat Detail <i class="ph-bold ph-arrow-right inline-block ml-1"></i></a>
                        </div>
                    </div>
                </div>

                <!-- Card Siswa -->
                <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm hover:shadow-md transition-shadow group relative overflow-hidden">
                    <div class="absolute top-0 right-0 p-4 opacity-5 group-hover:scale-110 transition-transform duration-500">
                        <i class="ph-fill ph-student text-9xl text-emerald-600"></i>
                    </div>
                    <div class="relative z-10">
                        <div class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-xl flex items-center justify-center mb-4 group-hover:bg-emerald-600 group-hover:text-white transition-colors">
                            <i class="ph-fill ph-student text-2xl"></i>
                        </div>
                        <p class="text-slate-500 font-medium mb-1">Total Siswa</p>
                        <h3 class="text-4xl font-bold text-slate-800"><?php echo $total_siswa; ?></h3>
                        <div class="mt-4 flex items-center text-sm text-emerald-600 font-medium cursor-pointer hover:underline">
                            <a href="manage_siswa.php">Lihat Detail <i class="ph-bold ph-arrow-right inline-block ml-1"></i></a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div>
                <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center">
                    <i class="ph-fill ph-lightning text-yellow-500 mr-2"></i> Akses Cepat
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <a href="add_guru.php" class="flex items-center p-4 bg-white border border-slate-200 rounded-xl hover:border-primary-500 hover:shadow-lg hover:shadow-primary-500/10 transition-all group">
                        <div class="w-10 h-10 bg-slate-50 rounded-full flex items-center justify-center mr-4 group-hover:bg-primary-50 group-hover:text-primary-600 transition-colors">
                            <i class="ph-bold ph-plus"></i>
                        </div>
                        <span class="font-medium text-slate-700 group-hover:text-primary-700">Tambah Guru Baru</span>
                    </a>
                    <a href="add_siswa.php" class="flex items-center p-4 bg-white border border-slate-200 rounded-xl hover:border-primary-500 hover:shadow-lg hover:shadow-primary-500/10 transition-all group">
                        <div class="w-10 h-10 bg-slate-50 rounded-full flex items-center justify-center mr-4 group-hover:bg-primary-50 group-hover:text-primary-600 transition-colors">
                            <i class="ph-bold ph-plus"></i>
                        </div>
                        <span class="font-medium text-slate-700 group-hover:text-primary-700">Tambah Siswa Baru</span>
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
