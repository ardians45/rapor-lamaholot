<?php
// Script aman untuk mereset password admin ke 'admin123'
// Hanya bisa diakses dari localhost untuk alasan keamanan

if ($_SERVER['REMOTE_ADDR'] !== '127.0.0.1' && $_SERVER['REMOTE_ADDR'] !== '::1') {
    die('Akses ditolak: Hanya bisa diakses dari localhost.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_reset'])) {
    require_once 'config/database.php';
    
    $new_password = 'admin123';
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
    
    $stmt = $koneksi->prepare("UPDATE users SET password = ? WHERE username = 'admin'");
    $stmt->bind_param("s", $hashed_password);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            $message = "<p style='color: green;'><strong>BERHASIL:</strong> Password admin telah direset ke '<strong>admin123</strong>'</p>";
        } else {
            $message = "<p style='color: orange;'><strong>INFO:</strong> Tidak ditemukan user dengan username 'admin'</p>";
        }
    } else {
        $message = "<p style='color: red;'><strong>GAGAL:</strong> " . $stmt->error . "</p>";
    }
    
    $stmt->close();
    $koneksi->close();
} else {
    $message = '';
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password Admin</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; padding: 20px; background: #f5f5f5; }
        .container { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .btn { background: #007cba; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; }
        .btn:hover { background: #005a87; }
        .info { background: #e7f3ff; padding: 15px; border-radius: 5px; margin: 15px 0; }
        .warning { background: #fff3cd; padding: 15px; border-radius: 5px; margin: 15px 0; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Reset Password Admin</h2>
        
        <?php if ($message): ?>
            <?php echo $message; ?>
            <p><a href="views/login.php">Klik di sini untuk login</a></p>
            <p><small style="color: #666;">Catatan: Hapus file ini setelah digunakan untuk alasan keamanan.</small></p>
        <?php else: ?>
            <div class="info">
                <p><strong>Username:</strong> admin</p>
                <p><strong>Password baru:</strong> admin123</p>
            </div>
            
            <div class="warning">
                <p><strong>PERINGATAN:</strong> Ini akan mengganti password admin saat ini. Pastikan Anda yakin ingin melanjutkan.</p>
            </div>
            
            <form method="POST" onsubmit="return confirm('Apakah Anda yakin ingin mereset password admin menjadi admin123?')">
                <input type="hidden" name="confirm_reset" value="1">
                <button type="submit" class="btn">Reset Password Admin</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>