<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] != 'admin') {
    header("Location: ../../views/login.php?error=Anda tidak memiliki akses");
    exit;
}
require_once '../../config/database.php';

// Ambil semua data guru
$result = $koneksi->query("SELECT g.id, g.nip, g.nama, u.username FROM guru g JOIN users u ON g.user_id = u.id ORDER BY g.nama ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Guru - SD Lamaholot</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                    colors: {
                        primary: { 50: '#fff1f2', 100: '#ffe4e6', 500: '#f43f5e', 600: '#e11d48', 700: '#be123c' }
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

    <!-- Sidebar (Sama seperti Dashboard) -->
    <aside id="sidebar" class="fixed top-0 left-0 h-full w-64 bg-white border-r border-slate-200 z-50 transform -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out flex flex-col">
        <div class="h-16 flex items-center px-6 border-b border-slate-100">
            <div class="w-8 h-8 bg-gradient-to-br from-primary-500 to-primary-700 rounded-lg flex items-center justify-center shadow-md shadow-primary-500/30 mr-3">
                <i class="ph ph-book-bookmark text-white text-lg"></i>
            </div>
            <span class="text-lg font-bold bg-clip-text text-transparent bg-gradient-to-r from-slate-800 to-slate-600">SD Lamaholot</span>
        </div>
        <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-1">
            <p class="px-3 text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Menu Utama</p>
            <a href="dashboard.php" class="flex items-center px-3 py-2.5 text-slate-600 hover:bg-slate-50 hover:text-slate-900 rounded-xl group transition-colors">
                <i class="ph ph-squares-four text-xl mr-3 text-slate-400 group-hover:text-primary-600 transition-colors"></i>
                <span class="font-medium">Dashboard</span>
            </a>
            <a href="manage_guru.php" class="flex items-center px-3 py-2.5 bg-primary-50 text-primary-700 rounded-xl group transition-colors">
                <i class="ph-fill ph-chalkboard-teacher text-xl mr-3"></i>
                <span class="font-medium">Kelola Guru</span>
            </a>
            <a href="manage_siswa.php" class="flex items-center px-3 py-2.5 text-slate-600 hover:bg-slate-50 hover:text-slate-900 rounded-xl group transition-colors">
                <i class="ph ph-student text-xl mr-3 text-slate-400 group-hover:text-primary-600 transition-colors"></i>
                <span class="font-medium">Kelola Siswa</span>
            </a>
        </nav>
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
        <!-- Header -->
        <header class="h-16 bg-white/80 backdrop-blur-md border-b border-slate-200 sticky top-0 z-30 px-4 lg:px-8 flex items-center justify-between">
            <div class="flex items-center">
                <button id="menuBtn" class="p-2 -ml-2 mr-2 text-slate-500 hover:bg-slate-100 rounded-lg lg:hidden">
                    <i class="ph ph-list text-2xl"></i>
                </button>
                <h1 class="text-xl font-bold text-slate-800 hidden sm:block">Kelola Guru</h1>
            </div>
            <div class="flex items-center gap-4">
                <a href="add_guru.php" class="flex items-center gap-2 bg-primary-600 hover:bg-primary-700 text-white px-4 py-2 rounded-lg font-medium text-sm transition-all shadow-lg shadow-primary-500/30">
                    <i class="ph-bold ph-plus"></i> <span class="hidden sm:inline">Tambah Guru</span>
                </a>
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

            <!-- Table Card -->
            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
                <!-- Search & Filter Bar -->
                <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row justify-between gap-4">
                    <div class="relative w-full sm:w-72">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="ph ph-magnifying-glass text-slate-400"></i>
                        </div>
                        <input type="text" id="searchInput" placeholder="Cari nama atau NIP..." 
                            class="block w-full pl-10 pr-3 py-2 border border-slate-200 rounded-lg leading-5 bg-slate-50 placeholder-slate-400 focus:outline-none focus:bg-white focus:ring-1 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition-colors">
                    </div>
                    <div class="text-sm text-slate-500 self-center">
                        Total: <span class="font-bold text-slate-800"><?php echo $result->num_rows; ?></span> Guru
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Guru</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">NIP</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Username</th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-slate-200" id="tableBody">
                            <?php if ($result->num_rows > 0): ?>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                <tr class="hover:bg-slate-50 transition-colors group">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10 bg-primary-100 text-primary-600 rounded-full flex items-center justify-center font-bold">
                                                <?php echo strtoupper(substr($row['nama'], 0, 1)); ?>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-slate-900 search-name"><?php echo htmlspecialchars($row['nama']); ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-slate-500 search-nip"><?php echo htmlspecialchars($row['nip']); ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-slate-100 text-slate-600">
                                            @<?php echo htmlspecialchars($row['username']); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex items-center justify-end gap-2 opacity-100 sm:opacity-0 sm:group-hover:opacity-100 transition-opacity">
                                            <a href="edit_guru.php?id=<?php echo $row['id']; ?>" class="p-2 text-slate-400 hover:text-amber-500 hover:bg-amber-50 rounded-lg transition-all" title="Edit">
                                                <i class="ph ph-pencil-simple text-lg"></i>
                                            </a>
                                            <a href="../../actions/crud_guru.php?action=delete&id=<?php echo $row['id']; ?>" 
                                               onclick="return confirm('Apakah Anda yakin ingin menghapus data guru ini?');"
                                               class="p-2 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-all" title="Hapus">
                                                <i class="ph ph-trash text-lg"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="px-6 py-12 text-center text-slate-400">
                                        <i class="ph ph-folder-open text-4xl mb-2 block"></i>
                                        Belum ada data guru
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
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

        // Simple Client-side Search
        document.getElementById('searchInput').addEventListener('keyup', function() {
            const searchValue = this.value.toLowerCase();
            const rows = document.querySelectorAll('#tableBody tr');

            rows.forEach(row => {
                const name = row.querySelector('.search-name')?.textContent.toLowerCase() || '';
                const nip = row.querySelector('.search-nip')?.textContent.toLowerCase() || '';
                
                if (name.includes(searchValue) || nip.includes(searchValue)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>
