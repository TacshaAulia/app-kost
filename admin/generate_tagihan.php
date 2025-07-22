<?php
require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$message = '';

// Handle generate tagihan
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['generate'])) {
    $bulan = $_POST['bulan']; // Format: YYYY-MM
    
    try {
        $db->beginTransaction();
        
        // Ambil semua penghuni aktif yang belum ada tagihan untuk bulan tersebut
        $query = "
            SELECT kp.id as id_kmr_penghuni, k.harga as harga_kamar,
                   COALESCE(SUM(b.harga), 0) as total_barang
            FROM tb_kmr_penghuni kp
            JOIN tb_kamar k ON kp.id_kamar = k.id
            JOIN tb_penghuni p ON kp.id_penghuni = p.id
            LEFT JOIN tb_brng_bawaan bb ON p.id = bb.id_penghuni
            LEFT JOIN tb_barang b ON bb.id_barang = b.id
            WHERE kp.tgl_keluar IS NULL 
            AND p.tgl_keluar IS NULL
            AND NOT EXISTS (
                SELECT 1 FROM tb_tagihan t 
                WHERE t.id_kmr_penghuni = kp.id 
                AND t.bulan = ?
            )
            GROUP BY kp.id, k.harga
        ";
        
        $stmt = $db->prepare($query);
        $stmt->execute([$bulan]);
        
        $count = 0;
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $total_tagihan = $row['harga_kamar'] + $row['total_barang'];
            
            $insert_query = "INSERT INTO tb_tagihan (bulan, id_kmr_penghuni, jml_tagihan) VALUES (?, ?, ?)";
            $insert_stmt = $db->prepare($insert_query);
            $insert_stmt->execute([$bulan, $row['id_kmr_penghuni'], $total_tagihan]);
            
            $count++;
        }
        
        $db->commit();
        $message = '<div class="alert alert-success">Berhasil generate ' . $count . ' tagihan untuk bulan ' . date('F Y', strtotime($bulan . '-01')) . '!</div>';
        
    } catch (Exception $e) {
        $db->rollBack();
        $message = '<div class="alert alert-danger">Gagal generate tagihan: ' . $e->getMessage() . '</div>';
    }
}

// Query untuk menampilkan tagihan yang sudah ada
$query_tagihan = "
    SELECT t.*, k.nomor as nomor_kamar, p.nama as nama_penghuni,
           COALESCE(SUM(b.jml_bayar), 0) as total_bayar,
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
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate Tagihan - Admin Panel</title>
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
            <h1>🔄 Generate Tagihan</h1>
            <p>Generate tagihan bulanan untuk semua penghuni aktif</p>
        </div>

        <?= $message ?>

        <!-- Form Generate Tagihan -->
        <div class="card">
            <h2>📋 Generate Tagihan Baru</h2>
            <form method="POST" action="">
                <div class="grid">
                    <div class="form-group">
                        <label for="bulan">Pilih Bulan</label>
                        <input type="month" id="bulan" name="bulan" class="form-control" required 
                               value="<?= date('Y-m') ?>">
                    </div>
                </div>
                <button type="submit" name="generate" class="btn btn-success" 
                        onclick="return confirm('Yakin ingin generate tagihan untuk bulan ini?')">
                    Generate Tagihan
                </button>
            </form>
        </div>

        <!-- Tabel Tagihan -->
        <div class="card">
            <h2>📊 Data Tagihan</h2>
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
                            <td>Rp <?= number_format($row['jml_tagihan'] - $row['total_bayar'], 0, ',', '.') ?></td>
                            <td>
                                <span class="status-badge 
                                    <?= $row['status_pembayaran'] == 'Lunas' ? 'status-lunas' : 
                                        ($row['status_pembayaran'] == 'Cicil' ? 'status-cicil' : 'status-kosong') ?>">
                                    <?= $row['status_pembayaran'] ?>
                                </span>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <!-- Informasi Tagihan -->
        <div class="card">
            <h2>ℹ️ Informasi Generate Tagihan</h2>
            <div class="grid">
                <div class="card">
                    <h3>📋 Cara Kerja</h3>
                    <ul>
                        <li>Tagihan dibuat otomatis untuk semua penghuni aktif</li>
                        <li>Jumlah tagihan = Harga sewa kamar + Total harga barang bawaan</li>
                        <li>Tagihan dibuat per bulan (YYYY-MM)</li>
                        <li>Tagihan yang sudah ada tidak akan dibuat ulang</li>
                    </ul>
                </div>
                <div class="card">
                    <h3>⚠️ Perhatian</h3>
                    <ul>
                        <li>Pastikan data penghuni dan kamar sudah benar</li>
                        <li>Pastikan barang bawaan penghuni sudah dicatat</li>
                        <li>Tagihan yang sudah dibuat tidak bisa dihapus</li>
                        <li>Generate tagihan hanya untuk penghuni aktif</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 