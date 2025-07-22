<?php
require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

// Query untuk kamar kosong
$query_kamar_kosong = "
    SELECT k.nomor, k.harga 
    FROM tb_kamar k 
    LEFT JOIN tb_kmr_penghuni kp ON k.id = kp.id_kamar AND kp.tgl_keluar IS NULL
    WHERE kp.id IS NULL
    ORDER BY k.nomor
";
$stmt_kamar_kosong = $db->prepare($query_kamar_kosong);
$stmt_kamar_kosong->execute();

// Query untuk kamar yang sebentar lagi harus bayar (7 hari ke depan)
$query_harus_bayar = "
    SELECT k.nomor, p.nama, p.tgl_masuk, k.harga,
           DATE_ADD(p.tgl_masuk, INTERVAL 1 MONTH) as tgl_bayar_berikutnya
    FROM tb_kmr_penghuni kp
    JOIN tb_kamar k ON kp.id_kamar = k.id
    JOIN tb_penghuni p ON kp.id_penghuni = p.id
    WHERE kp.tgl_keluar IS NULL 
    AND p.tgl_keluar IS NULL
    AND DATE_ADD(p.tgl_masuk, INTERVAL 1 MONTH) BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
    ORDER BY tgl_bayar_berikutnya
";
$stmt_harus_bayar = $db->prepare($query_harus_bayar);
$stmt_harus_bayar->execute();

// Query untuk kamar terlambat bayar (lebih dari 7 hari)
$query_terlambat = "
    SELECT k.nomor, p.nama, p.tgl_masuk, k.harga,
           DATE_ADD(p.tgl_masuk, INTERVAL 1 MONTH) as tgl_bayar_berikutnya,
           DATEDIFF(CURDATE(), DATE_ADD(p.tgl_masuk, INTERVAL 1 MONTH)) as hari_terlambat
    FROM tb_kmr_penghuni kp
    JOIN tb_kamar k ON kp.id_kamar = k.id
    JOIN tb_penghuni p ON kp.id_penghuni = p.id
    WHERE kp.tgl_keluar IS NULL 
    AND p.tgl_keluar IS NULL
    AND DATE_ADD(p.tgl_masuk, INTERVAL 1 MONTH) < CURDATE()
    ORDER BY tgl_bayar_berikutnya
";
$stmt_terlambat = $db->prepare($query_terlambat);
$stmt_terlambat->execute();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Pengelolaan Kos</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <a href="index.php" class="logo">🏠 Sistem Kos</a>
            <ul class="nav-links">
                <li><a href="index.php">Beranda</a></li>
                <li><a href="admin/">Admin Panel</a></li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <div class="card">
            <h1>Selamat Datang di Sistem Pengelolaan Kos</h1>
            <p>Informasi terkini tentang status kamar dan pembayaran</p>
        </div>

        <div class="grid">
            <!-- Kamar Kosong -->
            <div class="card">
                <h2>🏠 Kamar Kosong</h2>
                <?php if ($stmt_kamar_kosong->rowCount() > 0): ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Nomor Kamar</th>
                                <th>Harga Sewa</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $stmt_kamar_kosong->fetch(PDO::FETCH_ASSOC)): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($row['nomor']) ?></strong></td>
                                    <td>Rp <?= number_format($row['harga'], 0, ',', '.') ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>Tidak ada kamar kosong saat ini.</p>
                <?php endif; ?>
            </div>

            <!-- Kamar yang Harus Bayar -->
            <div class="card">
                <h2>⏰ Harus Bayar (7 Hari Kedepan)</h2>
                <?php if ($stmt_harus_bayar->rowCount() > 0): ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Kamar</th>
                                <th>Penghuni</th>
                                <th>Tanggal Bayar</th>
                                <th>Jumlah</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $stmt_harus_bayar->fetch(PDO::FETCH_ASSOC)): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($row['nomor']) ?></strong></td>
                                    <td><?= htmlspecialchars($row['nama']) ?></td>
                                    <td><?= date('d/m/Y', strtotime($row['tgl_bayar_berikutnya'])) ?></td>
                                    <td>Rp <?= number_format($row['harga'], 0, ',', '.') ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>Tidak ada pembayaran yang jatuh tempo dalam 7 hari ke depan.</p>
                <?php endif; ?>
            </div>

            <!-- Kamar Terlambat Bayar -->
            <div class="card">
                <h2>⚠️ Terlambat Bayar</h2>
                <?php if ($stmt_terlambat->rowCount() > 0): ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Kamar</th>
                                <th>Penghuni</th>
                                <th>Jatuh Tempo</th>
                                <th>Keterlambatan</th>
                                <th>Jumlah</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $stmt_terlambat->fetch(PDO::FETCH_ASSOC)): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($row['nomor']) ?></strong></td>
                                    <td><?= htmlspecialchars($row['nama']) ?></td>
                                    <td><?= date('d/m/Y', strtotime($row['tgl_bayar_berikutnya'])) ?></td>
                                    <td><span class="status-badge status-cicil"><?= $row['hari_terlambat'] ?> hari</span></td>
                                    <td>Rp <?= number_format($row['harga'], 0, ',', '.') ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>Tidak ada penghuni yang terlambat membayar.</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="card">
            <h2>📊 Statistik</h2>
            <div class="dashboard-stats">
                <?php
                // Total kamar
                $stmt_total_kamar = $db->query("SELECT COUNT(*) as total FROM tb_kamar");
                $total_kamar = $stmt_total_kamar->fetch(PDO::FETCH_ASSOC)['total'];

                // Kamar terisi
                $stmt_kamar_terisi = $db->query("
                    SELECT COUNT(DISTINCT kp.id_kamar) as total 
                    FROM tb_kmr_penghuni kp 
                    WHERE kp.tgl_keluar IS NULL
                ");
                $kamar_terisi = $stmt_kamar_terisi->fetch(PDO::FETCH_ASSOC)['total'];

                // Total penghuni aktif
                $stmt_penghuni_aktif = $db->query("
                    SELECT COUNT(*) as total 
                    FROM tb_penghuni 
                    WHERE tgl_keluar IS NULL
                ");
                $penghuni_aktif = $stmt_penghuni_aktif->fetch(PDO::FETCH_ASSOC)['total'];
                ?>
                <div class="stat-card">
                    <h3><?= $total_kamar ?></h3>
                    <p>Total Kamar</p>
                </div>
                <div class="stat-card">
                    <h3><?= $kamar_terisi ?></h3>
                    <p>Kamar Terisi</p>
                </div>
                <div class="stat-card">
                    <h3><?= $total_kamar - $kamar_terisi ?></h3>
                    <p>Kamar Kosong</p>
                </div>
                <div class="stat-card">
                    <h3><?= $penghuni_aktif ?></h3>
                    <p>Penghuni Aktif</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 