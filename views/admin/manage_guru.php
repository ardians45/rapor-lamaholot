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
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Kelola Guru - SD Lamaholot</title>
    <!-- CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root {
            --ntt-primary: #e74c3c;
            --ntt-secondary: #f39c12;
            --ntt-accent: #2c3e50;
            --card-bg: #ffffff;
            --shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            --shadow-lg: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            padding-bottom: 1rem;
        }

        .top-nav {
            background: white;
            box-shadow: var(--shadow);
            padding: 0.75rem 1rem;
            position: sticky;
            top: 0;
            z-index: 100;
            margin-bottom: 1.5rem;
        }

        .page-header {
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #e9ecef;
        }

        .action-buttons {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
        }

        @media (min-width: 576px) {
            .action-buttons {
                flex-direction: row;
                justify-content: space-between;
                align-items: center;
            }
        }

        .card {
            border-radius: 1rem;
            border: none;
            box-shadow: var(--shadow);
            margin-bottom: 1.5rem;
            overflow: hidden;
        }

        .card-header {
            background: linear-gradient(135deg, var(--ntt-primary) 0%, var(--ntt-secondary) 100%);
            color: white;
            padding: 1rem 1.5rem;
            border: none;
        }

        .table-container {
            overflow-x: auto;
        }

        .table {
            margin-bottom: 0;
        }

        .table th {
            background-color: #f8f9fa;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .btn {
            border-radius: 0.75rem;
            padding: 0.5rem 1rem;
            font-weight: 500;
        }

        .btn-add {
            background: var(--ntt-primary);
            border: none;
        }

        .btn-add:hover {
            background: #c0392b;
        }

        .action-cell {
            white-space: nowrap;
        }

        .action-btn {
            padding: 0.375rem 0.75rem;
            margin: 0.125rem;
        }

        .alert {
            border-radius: 0.75rem;
            border: none;
        }

        .mobile-card {
            display: none;
        }

        @media (max-width: 767.98px) {
            .mobile-card {
                display: block;
                margin-bottom: 1rem;
            }

            .desktop-table {
                display: none;
            }

            .card-body {
                padding: 1rem;
            }

            .page-header h3 {
                font-size: 1.5rem;
            }
        }

        .guru-item {
            background: white;
            border-radius: 1rem;
            padding: 1rem;
            margin-bottom: 1rem;
            box-shadow: var(--shadow);
            border-left: 4px solid var(--ntt-primary);
        }

        .guru-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.5rem;
        }

        .guru-name {
            font-weight: 600;
            font-size: 1.1rem;
        }

        .guru-details {
            display: grid;
            grid-template-columns: auto auto;
            gap: 1rem;
            margin: 0.5rem 0;
        }

        .detail-item {
            display: flex;
            flex-direction: column;
        }

        .detail-label {
            font-size: 0.8rem;
            color: #6c757d;
            margin-bottom: 0.25rem;
        }

        .detail-value {
            font-weight: 500;
        }

        .mobile-actions {
            display: flex;
            gap: 0.5rem;
            margin-top: 1rem;
            justify-content: center;
        }

        .dataTables_wrapper {
            padding: 1rem 0;
        }

        .dataTables_length, .dataTables_filter, .dataTables_info, .dataTables_paginate {
            padding: 0.5rem 0;
        }

        .dataTables_filter input {
            border-radius: 0.5rem !important;
            border: 1px solid #dee2e6;
            padding: 0.375rem 0.75rem;
        }
    </style>
</head>
<body>
    <div class="top-nav d-flex d-md-none">
        <a href="dashboard.php" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div class="ms-auto">
            <a href="../../actions/logout.php" class="btn btn-outline-danger btn-sm">
                <i class="bi bi-box-arrow-right"></i>
            </a>
        </div>
    </div>

    <div class="container-fluid">
        <div class="page-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-1">Kelola Data Guru</h2>
                    <p class="text-muted mb-0">Manajemen data guru SD Lamaholot</p>
                </div>
            </div>
        </div>

        <div class="action-buttons">
            <a href="add_guru.php" class="btn btn-add d-flex align-items-center justify-content-center">
                <i class="bi bi-plus-circle me-2"></i> Tambah Guru
            </a>
        </div>

        <?php
        if (isset($_GET['success'])) {
            echo '<div class="alert alert-success" role="alert">' . htmlspecialchars($_GET['success']) . '</div>';
        }
        if (isset($_GET['error'])) {
            echo '<div class="alert alert-danger" role="alert">' . htmlspecialchars($_GET['error']) . '</div>';
        }
        ?>

        <!-- Desktop Table View -->
        <div class="desktop-table">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="guruTable" class="table table-hover">
                            <thead>
                                <tr>
                                    <th><i class="bi bi-person-vcard"></i> NIP</th>
                                    <th><i class="bi bi-person-fill"></i> Nama Lengkap</th>
                                    <th><i class="bi bi-person-circle"></i> Username</th>
                                    <th><i class="bi bi-gear"></i> Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['nip']); ?></td>
                                    <td><?php echo htmlspecialchars($row['nama']); ?></td>
                                    <td><?php echo htmlspecialchars($row['username']); ?></td>
                                    <td class="action-cell">
                                        <a href="edit_guru.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-warning action-btn">
                                            <i class="bi bi-pencil-square"></i> Edit
                                        </a>
                                        <a href="../../actions/crud_guru.php?action=delete&id=<?php echo $row['id']; ?>" class="btn btn-sm btn-danger action-btn" onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?');">
                                            <i class="bi bi-trash"></i> Hapus
                                        </a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mobile Card View -->
        <div class="mobile-card">
            <?php
            // Reset result pointer to reuse for mobile view
            $result = $koneksi->query("SELECT g.id, g.nip, g.nama, u.username FROM guru g JOIN users u ON g.user_id = u.id ORDER BY g.nama ASC");
            ?>
            <?php while ($row = $result->fetch_assoc()): ?>
            <div class="guru-item">
                <div class="guru-header">
                    <div class="guru-name"><?php echo htmlspecialchars($row['nama']); ?></div>
                </div>
                <div class="guru-details">
                    <div class="detail-item">
                        <span class="detail-label">NIP</span>
                        <span class="detail-value"><?php echo htmlspecialchars($row['nip']); ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Username</span>
                        <span class="detail-value"><?php echo htmlspecialchars($row['username']); ?></span>
                    </div>
                </div>
                <div class="mobile-actions">
                    <a href="edit_guru.php?id=<?php echo $row['id']; ?>" class="btn btn-warning btn-sm">
                        <i class="bi bi-pencil-square"></i> Edit
                    </a>
                    <a href="../../actions/crud_guru.php?action=delete&id=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?');">
                        <i class="bi bi-trash"></i> Hapus
                    </a>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </div>

    <!-- JS -->
    <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#guruTable').DataTable({
                responsive: true,
                pageLength: 10,
                lengthMenu: [5, 10, 25, 50]
            });
        });
    </script>
</body>
</html>
