<!DOCTYPE html>
<html lang="id" class="h-full bg-white">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Rapor Online SD Lamaholot</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Google Fonts: Inter & Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Phosphor Icons -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        display: ['Plus Jakarta Sans', 'sans-serif'],
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
                        brand: {
                            dark: '#0f172a',
                            light: '#f8fafc',
                        }
                    },
                    animation: {
                        'fade-in': 'fadeIn 0.6s ease-out',
                        'slide-up': 'slideUp 0.8s ease-out',
                        'float': 'float 6s ease-in-out infinite',
                    },
                    keyframes: {
                        fadeIn: {
                            '0%': { opacity: '0' },
                            '100%': { opacity: '1' },
                        },
                        slideUp: {
                            '0%': { opacity: '0', transform: 'translateY(20px)' },
                            '100%': { opacity: '1', transform: 'translateY(0)' },
                        },
                        float: {
                            '0%, 100%': { transform: 'translateY(0)' },
                            '50%': { transform: 'translateY(-20px)' },
                        }
                    }
                }
            }
        }
    </script>
    <style>
        /* Hide scrollbar globally */
        body { overflow: hidden; }
        
        /* Custom scrollbar for inner containers if needed */
        .custom-scroll::-webkit-scrollbar { width: 4px; }
        .custom-scroll::-webkit-scrollbar-track { background: transparent; }
        .custom-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 2px; }
        
        .input-group:focus-within label {
            color: #e11d48;
        }
        .input-group:focus-within i {
            color: #e11d48;
        }
        
        /* Abstract Mesh Gradient Background */
        .mesh-bg {
            background-color: #ff9a9e;
            background-image: 
                radial-gradient(at 40% 20%, hsla(28,100%,74%,1) 0px, transparent 50%),
                radial-gradient(at 80% 0%, hsla(189,100%,56%,1) 0px, transparent 50%),
                radial-gradient(at 0% 50%, hsla(340,100%,76%,1) 0px, transparent 50%),
                radial-gradient(at 80% 50%, hsla(340,100%,77%,1) 0px, transparent 50%),
                radial-gradient(at 0% 100%, hsla(22,100%,77%,1) 0px, transparent 50%),
                radial-gradient(at 80% 100%, hsla(242,100%,70%,1) 0px, transparent 50%),
                radial-gradient(at 0% 0%, hsla(343,100%,76%,1) 0px, transparent 50%);
        }
    </style>
</head>
<body class="h-screen w-screen overflow-hidden font-sans text-slate-900 antialiased selection:bg-primary-500 selection:text-white">

    <div class="h-full w-full flex">
        
        <!-- LEFT SIDE: FORM AREA -->
        <!-- Added overflow-y-auto to allow internal scrolling on very small screens, but hidden on normal desktop -->
        <div class="flex-1 flex flex-col justify-center items-center px-4 sm:px-6 lg:flex-none lg:px-20 xl:px-24 bg-white z-10 relative w-full lg:w-[45%] xl:w-[40%] h-full custom-scroll overflow-y-auto">
            
            <div class="w-full max-w-sm lg:w-96 animate-fade-in py-8">
                <!-- Logo -->
                <div class="flex items-center gap-3 mb-8">
                    <div class="w-10 h-10 bg-gradient-to-br from-primary-600 to-orange-500 rounded-xl flex items-center justify-center shadow-lg shadow-primary-500/30">
                        <i class="ph-fill ph-graduation-cap text-white text-xl"></i>
                    </div>
                    <span class="font-display font-bold text-xl tracking-tight text-slate-900">Rapor Lamaholot</span>
                </div>

                <div>
                    <h2 class="text-2xl lg:text-3xl font-display font-bold text-slate-900 tracking-tight">Selamat Datang 👋</h2>
                    <p class="mt-2 text-sm text-slate-500 leading-relaxed">
                        Silakan masuk untuk mengakses sistem manajemen penilaian.
                    </p>
                </div>

                <div class="mt-8">
                    <!-- Error Alert -->
                    <?php if (isset($_GET['error'])): ?>
                    <div class="mb-5 bg-red-50 border border-red-100 rounded-2xl p-3 flex items-start gap-3 animate-slide-up">
                        <i class="ph-fill ph-warning-circle text-red-500 text-lg shrink-0 mt-0.5"></i>
                        <div class="text-sm text-red-600 font-medium">
                            <?php echo htmlspecialchars($_GET['error']); ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <form action="../actions/auth.php" method="POST" class="space-y-5">
                        
                        <!-- Username Input -->
                        <div class="space-y-1.5 input-group group">
                            <label for="username" class="block text-xs font-bold uppercase tracking-wide text-slate-500 transition-colors duration-300">
                                Username / NIP / NISN
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                    <i class="ph ph-user text-slate-400 text-lg transition-colors duration-300"></i>
                                </div>
                                <input type="text" id="username" name="username" required 
                                    class="block w-full pl-11 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 placeholder-slate-400 focus:bg-white focus:outline-none focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 transition-all duration-300 sm:text-sm font-medium"
                                    placeholder="Masukkan ID pengguna">
                            </div>
                        </div>

                        <!-- Password Input -->
                        <div class="space-y-1.5 input-group group">
                            <div class="flex items-center justify-between">
                                <label for="password" class="block text-xs font-bold uppercase tracking-wide text-slate-500 transition-colors duration-300">
                                    Password
                                </label>
                            </div>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                    <i class="ph ph-lock-key text-slate-400 text-lg transition-colors duration-300"></i>
                                </div>
                                <input type="password" id="password" name="password" required 
                                    class="block w-full pl-11 pr-12 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 placeholder-slate-400 focus:bg-white focus:outline-none focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 transition-all duration-300 sm:text-sm font-medium"
                                    placeholder="••••••••">
                                <button type="button" id="togglePassword" class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 hover:text-slate-600 focus:outline-none cursor-pointer transition-colors">
                                    <i class="ph ph-eye text-lg" id="toggleIcon"></i>
                                </button>
                            </div>
                        </div>

                        <div class="flex items-center justify-between pt-1">
                            <div class="flex items-center">
                                <input id="remember-me" name="remember-me" type="checkbox" 
                                    class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded cursor-pointer">
                                <label for="remember-me" class="ml-2 block text-xs font-medium text-slate-600 cursor-pointer select-none">
                                    Ingat saya
                                </label>
                            </div>
                            <div class="text-xs">
                                <a href="#" class="font-semibold text-primary-600 hover:text-primary-500 transition-colors">
                                    Lupa password?
                                </a>
                            </div>
                        </div>

                        <div>
                            <button type="submit" 
                                class="w-full flex justify-center py-3 px-4 border border-transparent rounded-xl shadow-lg shadow-primary-600/20 text-sm font-bold text-white bg-gradient-to-r from-primary-600 to-primary-700 hover:from-primary-700 hover:to-primary-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-all duration-300 transform hover:-translate-y-0.5">
                                Masuk Sistem <i class="ph-bold ph-arrow-right ml-2 text-lg"></i>
                            </button>
                        </div>
                    </form>

                    
                </div>
                
                <div class="mt-8 text-center">
                    <p class="text-[10px] text-slate-400 font-medium uppercase tracking-widest">
                        &copy; <?php echo date('Y'); ?> SD Lamaholot
                    </p>
                </div>
            </div>
        </div>

        <!-- RIGHT SIDE: VISUAL AREA (Static 100vh) -->
        <div class="hidden lg:block relative w-0 flex-1 h-full overflow-hidden mesh-bg">
            <!-- Glass Card Overlay -->
            <div class="absolute inset-0 flex flex-col items-center justify-center p-12 z-20">
                
                <!-- Decorative Circles -->
                <div class="absolute top-20 right-20 w-32 h-32 bg-white/10 rounded-full blur-2xl animate-float"></div>
                <div class="absolute bottom-20 left-20 w-64 h-64 bg-indigo-500/20 rounded-full blur-3xl animate-float" style="animation-delay: 2s"></div>

                <div class="w-full max-w-md bg-white/10 backdrop-blur-xl border border-white/20 rounded-3xl p-8 text-white shadow-2xl shadow-black/10 transform transition-all hover:scale-[1.02] duration-500 animate-slide-up">
                    <div class="mb-5 text-white/80">
                        <i class="ph-fill ph-quotes text-4xl opacity-50"></i>
                    </div>
                    <h3 class="text-2xl font-display font-bold leading-snug mb-3">
                        "Pendidikan adalah kunci untuk membuka pintu emas kebebasan."
                    </h3>
                    <p class="text-sm text-white/70 font-light mb-6 leading-relaxed">
                        Platform Rapor Online SD Lamaholot memudahkan evaluasi akademik secara terintegrasi, transparan, dan akuntabel.
                    </p>
                    
                    <div class="flex items-center gap-3 pt-4 border-t border-white/10">
                        <div class="flex -space-x-2">
                            <div class="w-8 h-8 rounded-full border border-white/30 bg-slate-200 flex items-center justify-center text-slate-500 text-xs font-bold">👩</div>
                            <div class="w-8 h-8 rounded-full border border-white/30 bg-slate-300 flex items-center justify-center text-slate-500 text-xs font-bold">👦</div>
                            <div class="w-8 h-8 rounded-full border border-white/30 bg-slate-400 flex items-center justify-center text-slate-500 text-xs font-bold">👨</div>
                        </div>
                        <div class="text-xs font-medium text-white/90">
                            Akses Guru & Siswa
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Overlay Gradient -->
            <div class="absolute inset-0 bg-gradient-to-t from-slate-900/20 to-transparent z-10"></div>
        </div>
    </div>

    <script>
        const togglePassword = document.querySelector('#togglePassword');
        const password = document.querySelector('#password');
        const toggleIcon = document.querySelector('#toggleIcon');

        togglePassword.addEventListener('click', function () {
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            
            if (type === 'text') {
                toggleIcon.classList.replace('ph-eye', 'ph-eye-slash');
                toggleIcon.classList.add('text-primary-600');
            } else {
                toggleIcon.classList.replace('ph-eye-slash', 'ph-eye');
                toggleIcon.classList.remove('text-primary-600');
            }
        });
    </script>
</body>
</html>
