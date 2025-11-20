<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Login - Rapor Online SD Lamaholot</title>
    <!-- Bootstrap 5 CSS via CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons via CDN -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root {
            --ntt-primary: #e74c3c;
            --ntt-secondary: #f39c12;
            --ntt-accent: #2c3e50;
            --ntt-bg: linear-gradient(135deg, #e74c3c 0%, #f39c12 100%);
            --card-bg: rgba(255, 255, 255, 0.95);
            --shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.2);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            background: var(--ntt-bg);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            position: relative;
            padding: 1rem;
            overflow-x: hidden;
        }

        body::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image:
                radial-gradient(circle at 10% 20%, rgba(255,255,255,0.1) 0%, transparent 20%),
                radial-gradient(circle at 90% 80%, rgba(255,255,255,0.1) 0%, transparent 20%);
            z-index: 0;
        }

        .login-container {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 400px;
            background: var(--card-bg);
            backdrop-filter: blur(10px);
            border-radius: 1.5rem;
            box-shadow: var(--shadow);
            border: 1px solid rgba(255, 255, 255, 0.2);
            overflow: hidden;
            padding: 2rem 1.5rem;
            margin: 1.5rem 0;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .school-logo {
            width: 100px;
            height: 100px;
            background: var(--ntt-bg);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            font-size: 2.5rem;
            color: white;
            box-shadow: 0 0.5rem 1.5rem rgba(231, 76, 60, 0.4);
        }

        .login-header {
            text-align: center;
            margin-bottom: 1.5rem;
        }

        .login-header h1 {
            color: var(--ntt-accent);
            font-size: 1.5rem;
            margin-bottom: 0.25rem;
            font-weight: 700;
        }

        .login-header p {
            color: #7f8c8d;
            font-size: 0.9rem;
        }

        .form-label {
            font-weight: 600;
            color: var(--ntt-accent);
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }

        .form-control {
            border-radius: 1rem;
            padding: 0.875rem 1rem;
            border: 2px solid #e0e0e0;
            transition: all 0.3s ease;
            font-size: 1rem;
            height: auto;
        }

        .form-control:focus {
            border-color: var(--ntt-primary);
            box-shadow: 0 0 0 0.25rem rgba(231, 76, 60, 0.25);
        }

        .input-group .btn {
            border-radius: 0 1rem 1rem 0;
            border: 2px solid #e0e0e0;
            border-left: none;
            background: white;
        }

        .btn-login {
            background: var(--ntt-bg);
            border: none;
            padding: 1rem;
            font-size: 1.1rem;
            font-weight: 600;
            border-radius: 1rem;
            transition: all 0.3s ease;
            box-shadow: 0 0.5rem 1.5rem rgba(231, 76, 60, 0.3);
            width: 100%;
            margin-top: 0.5rem;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 0.75rem 2rem rgba(231, 76, 60, 0.4);
            background: linear-gradient(135deg, #c0392b 0%, #d35400 100%);
        }

        .welcome-note {
            text-align: center;
            margin-top: 1.5rem;
            padding: 1rem;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 1rem;
            border-left: 4px solid var(--ntt-primary);
            font-size: 0.85rem;
        }

        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 0.75rem;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
            border: 1px solid #f5c6cb;
        }

        .input-group {
            display: flex;
            align-items: stretch;
        }

        .input-group-text {
            background: #f8f9fa;
            border: 2px solid #e0e0e0;
            border-right: none;
            border-radius: 1rem 0 0 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            min-width: 50px; /* Fixed width for icon container */
            text-align: center;
        }

        .input-group > .form-control {
            border-left: none;
            border-radius: 0 1rem 1rem 0;
            flex: 1; /* Take remaining space */
        }

        .input-group .btn {
            border-radius: 0 1rem 1rem 0;
            border: 2px solid #e0e0e0;
            border-left: none;
            background: white;
        }

        /* Mobile-first responsive design */
        @media (min-width: 576px) {
            .login-container {
                padding: 2.5rem;
                margin: 2rem 0;
            }

            .login-header h1 {
                font-size: 1.8rem;
            }

            .login-header p {
                font-size: 1.1rem;
            }

            .form-control {
                font-size: 1rem;
                padding: 1rem 1.25rem;
            }

            .welcome-note {
                font-size: 1rem;
            }
        }

        @media (min-width: 768px) {
            .login-container {
                max-width: 450px;
                padding: 3rem 2.5rem;
            }
        }

        /* Animation for entrance */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .login-container {
            animation: fadeInUp 0.6s ease-out;
        }

        .form-floating > label {
            color: #6c757d;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="school-logo">
            <i class="bi bi-journal-bookmark"></i>
        </div>

        <div class="login-header">
            <h1>SD LAMAHOLOT</h1>
            <p>Sistem Informasi Rapor Online</p>
        </div>

        <?php
        // Menampilkan pesan error jika ada
        if (isset($_GET['error'])) {
            echo '<div class="error-message" role="alert">' . htmlspecialchars($_GET['error']) . '</div>';
        }
        ?>

        <form action="../actions/auth.php" method="POST">
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                    <input type="text" class="form-control" id="username" name="username" required placeholder="Masukkan username">
                </div>
            </div>
            <div class="mb-4">
                <label for="password" class="form-label">Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                    <input type="password" class="form-control" id="password" name="password" required placeholder="Masukkan password">
                    <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                        <i class="bi bi-eye-slash" id="toggleIcon"></i>
                    </button>
                </div>
            </div>

            <script>
                const togglePassword = document.querySelector('#togglePassword');
                const password = document.querySelector('#password');
                const toggleIcon = document.querySelector('#toggleIcon');

                togglePassword.addEventListener('click', function (e) {
                    // toggle the type attribute
                    const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
                    password.setAttribute('type', type);

                    // toggle the eye / eye slash icon
                    if (type === 'password') {
                        toggleIcon.classList.remove('bi-eye');
                        toggleIcon.classList.add('bi-eye-slash');
                    } else {
                        toggleIcon.classList.remove('bi-eye-slash');
                        toggleIcon.classList.add('bi-eye');
                    }
                });
            </script>

            <button type="submit" class="btn btn-login">
                <i class="bi bi-box-arrow-in-right me-2"></i>
                Masuk ke Sistem
            </button>
        </form>

        <div class="welcome-note mt-4">
            <p class="mb-0">
                <i class="bi bi-lightbulb text-warning me-2"></i>
                Selamat datang di sistem rapor online SD Lamaholot.
                Gunakan akun yang telah terdaftar untuk mengakses sistem.
            </p>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle via CDN -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
