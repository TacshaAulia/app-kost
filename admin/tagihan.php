<?php
require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

// Query untuk menampilkan semua tagihan
$query_tagihan = "
    SELECT t.*, k.nomor as nomor_kamar, p.nama as nama_penghuni,
           COALESCE(SUM(b.jml_bayar), 0) as total_bayar,
           (t.jml_tagihan - COALESCE(SUM(b.jml_bayar), 0)) as sisa_tagihan,
           CASE 
               WHEN COALESCE(SUM(b.jml_bayar), 0) >= t.jml_tagihan THEN 'Lunas'
               WHEN COALESCE(SUM(b.jml_bayar), 0) > 0 THEN 'Cicil'
               ELSE 'Belum Bayar'
           END as status_pembayaran
    FROM tb_tagihan t
    JOIN tb_kmr_penghuni kp ON t.id_kmr_penghuni = kp.id
    JOIN tb_kamar k ON kp.id_kamar = k.id
    JOIN tb_penghuni p ON kp.id_penghuni = p.id
    LEFT JOIN tb_bayar b ON t.id = b.id_tagihan
    GROUP BY t.id
    ORDER BY t.bulan DESC, p.nama
";
$stmt_tagihan = $db->prepare($query_tagihan);
$stmt_tagihan->execute();

// Statistik tagihan
$stmt_total_tagihan = $db->query("SELECT COUNT(*) as total FROM tb_tagihan");
$total_tagihan = $stmt_total_tagihan->fetch(PDO::FETCH_ASSOC)['total'];

$stmt_lunas = $db->query("
    SELECT COUNT(*) as total 
    FROM tb_tagihan t
    LEFT JOIN tb_bayar b ON t.id = b.id_tagihan
    GROUP BY t.id
    HAVING COALESCE(SUM(b.jml_bayar), 0) >= t.jml_tagihan
");
$tagihan_lunas = $stmt_lunas->rowCount();

$stmt_belum_lunas = $db->query("
    SELECT COUNT(*) as total 
    FROM tb_tagihan t
    LEFT JOIN tb_bayar b ON t.id = b.id_tagihan
    GROUP BY t.id
    HAVING COALESCE(SUM(b.jml_bayar), 0) < t.jml_tagihan
");
$tagihan_belum_lunas = $stmt_belum_lunas->rowCount();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Tagihan - Admin Panel</title>
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
            <h1>📋 Kelola Tagihan</h1>
            <p>Lihat dan kelola data tagihan penghuni</p>
        </div>

        <!-- Statistik Tagihan -->
        <div class="card">
            <h2>📊 Statistik Tagihan</h2>
            <div class="dashboard-stats">
                <div class="stat-card">
                    <h3><?= $total_tagihan ?></h3>
                    <p>Total Tagihan</p>
                </div>
                <div class="stat-card">
                    <h3><?= $tagihan_lunas ?></h3>
                    <p>Tagihan Lunas</p>
                </div>
                <div class="stat-card">
                    <h3><?= $tagihan_belum_lunas ?></h3>
                    <p>Tagihan Belum Lunas</p>
                </div>
                <div class="stat-card">
                    <h3>Generate</h3>
                    <p><a href="generate_tagihan.php" class="btn btn-success" style="color: white; text-decoration: none;">Buat Tagihan</a></p>
                </div>
            </div>
        </div>

        <!-- Tabel Tagihan -->
        <div class="card">
            <h2>📋 Data Tagihan</h2>
            <table class="table">
                <thead>
                    <tr>
                        <th>Bulan</th>
                        <th>Kamar</th>
                        <th>Penghuni</th>
                        <th>Jumlah Tagihan</th>
                        <th>Total Bayar</th>
                        <th>Sisa</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $stmt_tagihan->fetch(PDO::FETCH_ASSOC)): ?>
                        <tr>
                            <td><?= date('F Y', strtotime($row['bulan'] . '-01')) ?></td>
                            <td><strong><?= htmlspecialchars($row['nomor_kamar']) ?></strong></td>
                            <td><?= htmlspecialchars($row['nama_penghuni']) ?></td>
                            <td>Rp <?= number_format($row['jml_tagihan'], 0, ',', '.') ?></td>
                            <td>Rp <?= number_format($row['total_bayar'], 0, ',', '.') ?></td>
                            <td>Rp <?= number_format($row['sisa_tagihan'], 0, ',', '.') ?></td>
                            <td>
                                <span class="status-badge 
                                    <?= $row['status_pembayaran'] == 'Lunas' ? 'status-lunas' : 
                                        ($row['status_pembayaran'] == 'Cicil' ? 'status-cicil' : 'status-kosong') ?>">
                                    <?= $row['status_pembayaran'] ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($row['status_pembayaran'] != 'Lunas'): ?>
                                    <a href="pembayaran.php" class="btn btn-success">Bayar</a>
                                <?php endif; ?>
                                <button onclick="lihatDetail(<?= $row['id'] ?>)" class="btn btn-warning">Detail</button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <!-- Filter Tagihan -->
        <div class="card">
            <h2>🔍 Filter Tagihan</h2>
            <div class="grid">
                <div class="card">
                    <h3>Status Pembayaran</h3>
                    <ul>
                        <li><a href="?status=lunas">Tagihan Lunas</a></li>
                        <li><a href="?status=cicil">Tagihan Cicil</a></li>
                        <li><a href="?status=belum">Tagihan Belum Bayar</a></li>
                    </ul>
                </div>
                <div class="card">
                    <h3>Bulan</h3>
                    <ul>
                        <li><a href="?bulan=<?= date('Y-m') ?>">Bulan Ini</a></li>
                        <li><a href="?bulan=<?= date('Y-m', strtotime('-1 month')) ?>">Bulan Lalu</a></li>
                        <li><a href="?bulan=<?= date('Y-m', strtotime('-2 month')) ?>">2 Bulan Lalu</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <script>
        function lihatDetail(id) {
            // Implementasi untuk melihat detail tagihan
            alert('Fitur detail tagihan akan menampilkan rincian pembayaran');
        }
    </script>
</body>
</html> 