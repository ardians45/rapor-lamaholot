<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] != 'admin') {
    header("Location: ../../views/login.php?error=Anda tidak memiliki akses");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Siswa - SD Lamaholot</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                    colors: {
                        primary: { 50: '#fff1f2', 100: '#ffe4e6', 500: '#f43f5e', 600: '#e11d48', 700: '#be123c' },
                        secondary: { 50: '#fffbeb', 100: '#fef3c7', 500: '#f59e0b', 600: '#d97706' }
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
            <p class="px-3 text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Menu Utama</p>
            <a href="dashboard.php" class="flex items-center px-3 py-2.5 text-slate-600 hover:bg-slate-50 hover:text-slate-900 rounded-xl group transition-colors">
                <i class="ph ph-squares-four text-xl mr-3 text-slate-400 group-hover:text-primary-600 transition-colors"></i>
                <span class="font-medium">Dashboard</span>
            </a>
            <a href="manage_guru.php" class="flex items-center px-3 py-2.5 text-slate-600 hover:bg-slate-50 hover:text-slate-900 rounded-xl group transition-colors">
                <i class="ph ph-chalkboard-teacher text-xl mr-3 text-slate-400 group-hover:text-primary-600 transition-colors"></i>
                <span class="font-medium">Kelola Guru</span>
            </a>
            <a href="manage_siswa.php" class="flex items-center px-3 py-2.5 bg-secondary-100 text-secondary-700 rounded-xl group transition-colors">
                <i class="ph-fill ph-student text-xl mr-3"></i>
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
                <a href="manage_siswa.php" class="mr-4 text-slate-400 hover:text-slate-600 lg:hidden">
                    <i class="ph ph-arrow-left text-2xl"></i>
                </a>
                <h1 class="text-xl font-bold text-slate-800">Tambah Siswa</h1>
            </div>
        </header>

        <!-- Content -->
        <div class="p-4 lg:p-8 max-w-3xl mx-auto w-full">
            
            <form action="../../actions/crud_siswa.php?action=create" method="POST" class="space-y-8">
                
                <!-- Card 1: Data Diri -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                    <div class="p-6 border-b border-slate-100 bg-slate-50/50 flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-secondary-100 flex items-center justify-center text-secondary-600">
                            <i class="ph-fill ph-user-circle text-xl"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-slate-800">Identitas Siswa</h3>
                            <p class="text-sm text-slate-500">Informasi dasar data siswa.</p>
                        </div>
                    </div>
                    <div class="p-6 space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label for="nis" class="text-sm font-semibold text-slate-700">NIS</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                        <i class="ph ph-identification-card text-lg"></i>
                                    </div>
                                    <input type="text" id="nis" name="nis" required 
                                        class="block w-full pl-10 pr-3 py-2.5 bg-white border border-slate-200 rounded-xl text-slate-900 placeholder-slate-400 focus:outline-none focus:border-secondary-500 focus:ring-1 focus:ring-secondary-500 transition-all"
                                        placeholder="Nomor Induk Siswa">
                                </div>
                            </div>
                            <div class="space-y-2">
                                <label for="nama" class="text-sm font-semibold text-slate-700">Nama Lengkap</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                        <i class="ph ph-user text-lg"></i>
                                    </div>
                                    <input type="text" id="nama" name="nama" required 
                                        class="block w-full pl-10 pr-3 py-2.5 bg-white border border-slate-200 rounded-xl text-slate-900 placeholder-slate-400 focus:outline-none focus:border-secondary-500 focus:ring-1 focus:ring-secondary-500 transition-all"
                                        placeholder="Nama lengkap siswa">
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label for="kelas" class="text-sm font-semibold text-slate-700">Kelas</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                        <i class="ph ph-door-open text-lg"></i>
                                    </div>
                                    <input type="text" id="kelas" name="kelas" placeholder="Contoh: 1A"
                                        class="block w-full pl-10 pr-3 py-2.5 bg-white border border-slate-200 rounded-xl text-slate-900 placeholder-slate-400 focus:outline-none focus:border-secondary-500 focus:ring-1 focus:ring-secondary-500 transition-all">
                                </div>
                            </div>
                            <div class="space-y-2">
                                <label for="jurusan" class="text-sm font-semibold text-slate-700">Jurusan</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                        <i class="ph ph-graduation-cap text-lg"></i>
                                    </div>
                                    <input type="text" id="jurusan" name="jurusan" value="Reguler"
                                        class="block w-full pl-10 pr-3 py-2.5 bg-white border border-slate-200 rounded-xl text-slate-900 placeholder-slate-400 focus:outline-none focus:border-secondary-500 focus:ring-1 focus:ring-secondary-500 transition-all">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card 2: Akun Login -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                    <div class="p-6 border-b border-slate-100 bg-slate-50/50 flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-amber-50 flex items-center justify-center text-amber-600">
                            <i class="ph-fill ph-lock-key text-xl"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-slate-800">Akun Akses</h3>
                            <p class="text-sm text-slate-500">Kredensial untuk login ke sistem.</p>
                        </div>
                    </div>
                    <div class="p-6 space-y-6">
                        <div class="space-y-2">
                            <label for="username" class="text-sm font-semibold text-slate-700">Username</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                    <i class="ph ph-at text-lg"></i>
                                </div>
                                <input type="text" id="username" name="username" required 
                                    class="block w-full pl-10 pr-3 py-2.5 bg-white border border-slate-200 rounded-xl text-slate-900 placeholder-slate-400 focus:outline-none focus:border-secondary-500 focus:ring-1 focus:ring-secondary-500 transition-all"
                                    placeholder="Username unik">
                            </div>
                            <p class="text-xs text-slate-500 flex items-center mt-1">
                                <i class="ph-fill ph-info mr-1"></i> Disarankan menggunakan NIS sebagai username.
                            </p>
                        </div>

                        <div class="space-y-2">
                            <label for="password" class="text-sm font-semibold text-slate-700">Password</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                    <i class="ph ph-password text-lg"></i>
                                </div>
                                <input type="password" id="password" name="password" required 
                                    class="block w-full pl-10 pr-3 py-2.5 bg-white border border-slate-200 rounded-xl text-slate-900 placeholder-slate-400 focus:outline-none focus:border-secondary-500 focus:ring-1 focus:ring-secondary-500 transition-all"
                                    placeholder="Password awal">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center justify-end gap-4 pt-4">
                    <a href="manage_siswa.php" class="px-6 py-2.5 rounded-xl text-slate-600 hover:bg-slate-100 font-medium transition-colors">
                        Batal
                    </a>
                    <button type="submit" class="px-8 py-2.5 bg-secondary-500 hover:bg-secondary-600 text-white font-bold rounded-xl shadow-lg shadow-secondary-500/30 transition-all transform hover:-translate-y-0.5 flex items-center">
                        <i class="ph-bold ph-check-circle mr-2"></i> Simpan Data Siswa
                    </button>
                </div>

            </form>
            
        </div>
    </main>
</body>
</html>
