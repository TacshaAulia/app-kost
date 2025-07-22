<?php
require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

// Query untuk statistik dashboard
$stmt_total_kamar = $db->query("SELECT COUNT(*) as total FROM tb_kamar");
$total_kamar = $stmt_total_kamar->fetch(PDO::FETCH_ASSOC)['total'];

$stmt_kamar_terisi = $db->query("
    SELECT COUNT(DISTINCT kp.id_kamar) as total 
    FROM tb_kmr_penghuni kp 
    WHERE kp.tgl_keluar IS NULL
");
$kamar_terisi = $stmt_kamar_terisi->fetch(PDO::FETCH_ASSOC)['total'];

$stmt_penghuni_aktif = $db->query("
    SELECT COUNT(*) as total 
    FROM tb_penghuni 
    WHERE tgl_keluar IS NULL
");
$penghuni_aktif = $stmt_penghuni_aktif->fetch(PDO::FETCH_ASSOC)['total'];

$stmt_tagihan_pending = $db->query("
    SELECT COUNT(*) as total 
    FROM tb_tagihan t
    LEFT JOIN tb_bayar b ON t.id = b.id_tagihan
    WHERE b.id IS NULL OR b.status = 'cicil'
");
$tagihan_pending = $stmt_tagihan_pending->fetch(PDO::FETCH_ASSOC)['total'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Sistem Pengelolaan Kos</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <a href="../index.php" class="logo">🏠 Sistem Kos</a>
            <ul class="nav-links">
                <li><a href="../index.php">Beranda</a></li>
                <li><a href="index.php">Admin Panel</a></li>
                <li><a href="penghuni.php">Penghuni</a></li>
                <li><a href="kamar.php">Kamar</a></li>
                <li><a href="barang.php">Barang</a></li>
                <li><a href="tagihan.php">Tagihan</a></li>
                <li><a href="pembayaran.php">Pembayaran</a></li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <div class="card">
            <h1>👨‍💼 Admin Panel</h1>
            <p>Kelola data kos, penghuni, dan pembayaran</p>
        </div>

        <!-- Statistik Dashboard -->
        <div class="card">
            <h2>📊 Statistik Dashboard</h2>
            <div class="dashboard-stats">
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
                <div class="stat-card">
                    <h3><?= $tagihan_pending ?></h3>
                    <p>Tagihan Pending</p>
                </div>
            </div>
        </div>

        <!-- Menu Utama -->
        <div class="grid">
            <div class="card">
                <h2>👥 Kelola Penghuni</h2>
                <p>Kelola data penghuni kost, tambah penghuni baru, dan catat keluar masuk</p>
                <a href="penghuni.php" class="btn">Kelola Penghuni</a>
            </div>

            <div class="card">
                <h2>🏠 Kelola Kamar</h2>
                <p>Kelola data kamar, harga sewa, dan status kamar</p>
                <a href="kamar.php" class="btn">Kelola Kamar</a>
            </div>

            <div class="card">
                <h2>📦 Kelola Barang</h2>
                <p>Kelola data barang tambahan dan harga barang</p>
                <a href="barang.php" class="btn">Kelola Barang</a>
            </div>

            <div class="card">
                <h2>📋 Kelola Tagihan</h2>
                <p>Generate tagihan bulanan dan kelola data tagihan</p>
                <a href="tagihan.php" class="btn">Kelola Tagihan</a>
            </div>

            <div class="card">
                <h2>💰 Kelola Pembayaran</h2>
                <p>Catat pembayaran penghuni dan kelola status pembayaran</p>
                <a href="pembayaran.php" class="btn">Kelola Pembayaran</a>
            </div>

            <div class="card">
                <h2>🔄 Generate Tagihan</h2>
                <p>Generate tagihan otomatis untuk semua penghuni aktif</p>
                <a href="generate_tagihan.php" class="btn btn-success">Generate Tagihan</a>
            </div>
        </div>

        <!-- Informasi Terkini -->
        <div class="card">
            <h2>📈 Informasi Terkini</h2>
            <div class="grid">
                <?php
                // Kamar kosong
                $stmt_kamar_kosong = $db->query("
                    SELECT k.nomor, k.harga 
                    FROM tb_kamar k 
                    LEFT JOIN tb_kmr_penghuni kp ON k.id = kp.id_kamar AND kp.tgl_keluar IS NULL
                    WHERE kp.id IS NULL
                    ORDER BY k.nomor
                    LIMIT 5
                ");
                ?>
                <div class="card">
                    <h3>🏠 Kamar Kosong</h3>
                    <?php if ($stmt_kamar_kosong->rowCount() > 0): ?>
                        <ul>
                            <?php while ($row = $stmt_kamar_kosong->fetch(PDO::FETCH_ASSOC)): ?>
                                <li><?= htmlspecialchars($row['nomor']) ?> - Rp <?= number_format($row['harga'], 0, ',', '.') ?></li>
                            <?php endwhile; ?>
                        </ul>
                    <?php else: ?>
                        <p>Tidak ada kamar kosong</p>
                    <?php endif; ?>
                </div>

                <?php
                // Tagihan terlambat
                $stmt_terlambat = $db->query("
                    SELECT k.nomor, p.nama, 
                           DATEDIFF(CURDATE(), DATE_ADD(p.tgl_masuk, INTERVAL 1 MONTH)) as hari_terlambat
                    FROM tb_kmr_penghuni kp
                    JOIN tb_kamar k ON kp.id_kamar = k.id
                    JOIN tb_penghuni p ON kp.id_penghuni = p.id
                    WHERE kp.tgl_keluar IS NULL 
                    AND p.tgl_keluar IS NULL
                    AND DATE_ADD(p.tgl_masuk, INTERVAL 1 MONTH) < CURDATE()
                    ORDER BY tgl_masuk
                    LIMIT 5
                ");
                ?>
                <div class="card">
                    <h3>⚠️ Terlambat Bayar</h3>
                    <?php if ($stmt_terlambat->rowCount() > 0): ?>
                        <ul>
                            <?php while ($row = $stmt_terlambat->fetch(PDO::FETCH_ASSOC)): ?>
                                <li><?= htmlspecialchars($row['nomor']) ?> - <?= htmlspecialchars($row['nama']) ?> (<?= $row['hari_terlambat'] ?> hari)</li>
                            <?php endwhile; ?>
                        </ul>
                    <?php else: ?>
                        <p>Tidak ada yang terlambat</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 