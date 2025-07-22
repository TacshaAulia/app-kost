<?php
require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$message = '';

// Handle form submission untuk catat pembayaran
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action']) && $_POST['action'] == 'bayar') {
        $id_tagihan = $_POST['id_tagihan'];
        $jml_bayar = $_POST['jml_bayar'];
        $status = $_POST['status'];
        
        $query = "INSERT INTO tb_bayar (id_tagihan, jml_bayar, status) VALUES (?, ?, ?)";
        $stmt = $db->prepare($query);
        
        if ($stmt->execute([$id_tagihan, $jml_bayar, $status])) {
            $message = '<div class="alert alert-success">Pembayaran berhasil dicatat!</div>';
        } else {
            $message = '<div class="alert alert-danger">Gagal mencatat pembayaran!</div>';
        }
    }
}

// Query untuk menampilkan tagihan yang belum lunas
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
    HAVING sisa_tagihan > 0
    ORDER BY t.bulan DESC, p.nama
";
$stmt_tagihan = $db->prepare($query_tagihan);
$stmt_tagihan->execute();

// Query untuk menampilkan semua pembayaran
$query_pembayaran = "
    SELECT b.*, t.bulan, t.jml_tagihan, k.nomor as nomor_kamar, p.nama as nama_penghuni
    FROM tb_bayar b
    JOIN tb_tagihan t ON b.id_tagihan = t.id
    JOIN tb_kmr_penghuni kp ON t.id_kmr_penghuni = kp.id
    JOIN tb_kamar k ON kp.id_kamar = k.id
    JOIN tb_penghuni p ON kp.id_penghuni = p.id
    ORDER BY b.tgl_bayar DESC
";
$stmt_pembayaran = $db->prepare($query_pembayaran);
$stmt_pembayaran->execute();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pembayaran - Admin Panel</title>
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
            <h1>💰 Kelola Pembayaran</h1>
            <p>Catat pembayaran tagihan penghuni</p>
        </div>

        <?= $message ?>

        <!-- Tabel Tagihan Belum Lunas -->
        <div class="card">
            <h2>📋 Tagihan Belum Lunas</h2>
            <table class="table">
                <thead>
                    <tr>
                        <th>Bulan</th>
                        <th>Kamar</th>
                        <th>Penghuni</th>
                        <th>Total Tagihan</th>
                        <th>Sudah Bayar</th>
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
                            <td><strong>Rp <?= number_format($row['sisa_tagihan'], 0, ',', '.') ?></strong></td>
                            <td>
                                <span class="status-badge 
                                    <?= $row['status_pembayaran'] == 'Lunas' ? 'status-lunas' : 
                                        ($row['status_pembayaran'] == 'Cicil' ? 'status-cicil' : 'status-kosong') ?>">
                                    <?= $row['status_pembayaran'] ?>
                                </span>
                            </td>
                            <td>
                                <button onclick="catatPembayaran(<?= $row['id'] ?>, <?= $row['sisa_tagihan'] ?>)" 
                                        class="btn btn-success">Catat Bayar</button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <!-- Tabel Riwayat Pembayaran -->
        <div class="card">
            <h2>📊 Riwayat Pembayaran</h2>
            <table class="table">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Bulan Tagihan</th>
                        <th>Kamar</th>
                        <th>Penghuni</th>
                        <th>Jumlah Bayar</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $stmt_pembayaran->fetch(PDO::FETCH_ASSOC)): ?>
                        <tr>
                            <td><?= date('d/m/Y H:i', strtotime($row['tgl_bayar'])) ?></td>
                            <td><?= date('F Y', strtotime($row['bulan'] . '-01')) ?></td>
                            <td><strong><?= htmlspecialchars($row['nomor_kamar']) ?></strong></td>
                            <td><?= htmlspecialchars($row['nama_penghuni']) ?></td>
                            <td>Rp <?= number_format($row['jml_bayar'], 0, ',', '.') ?></td>
                            <td>
                                <span class="status-badge <?= $row['status'] == 'lunas' ? 'status-lunas' : 'status-cicil' ?>">
                                    <?= ucfirst($row['status']) ?>
                                </span>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Catat Pembayaran -->
    <div id="bayarModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000;">
        <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 2rem; border-radius: 15px; min-width: 400px;">
            <h3>Catat Pembayaran</h3>
            <form method="POST" action="" id="bayarForm">
                <input type="hidden" name="action" value="bayar">
                <input type="hidden" name="id_tagihan" id="bayar_id_tagihan">
                <div class="form-group">
                    <label for="bayar_jml">Jumlah Bayar (Rp)</label>
                    <input type="number" id="bayar_jml" name="jml_bayar" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="bayar_status">Status</label>
                    <select id="bayar_status" name="status" class="form-control" required>
                        <option value="cicil">Cicil</option>
                        <option value="lunas">Lunas</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-success">Catat Pembayaran</button>
                <button type="button" onclick="closeBayarModal()" class="btn btn-danger">Batal</button>
            </form>
        </div>
    </div>

    <script>
        function catatPembayaran(id_tagihan, sisa_tagihan) {
            document.getElementById('bayar_id_tagihan').value = id_tagihan;
            document.getElementById('bayar_jml').value = sisa_tagihan;
            document.getElementById('bayar_jml').max = sisa_tagihan;
            document.getElementById('bayarModal').style.display = 'block';
        }

        function closeBayarModal() {
            document.getElementById('bayarModal').style.display = 'none';
        }

        // Auto-set status to lunas if payment amount equals remaining amount
        document.getElementById('bayar_jml').addEventListener('input', function() {
            const jmlBayar = parseFloat(this.value) || 0;
            const sisaTagihan = parseFloat(this.max) || 0;
            const statusSelect = document.getElementById('bayar_status');
            
            if (jmlBayar >= sisaTagihan) {
                statusSelect.value = 'lunas';
            } else {
                statusSelect.value = 'cicil';
            }
        });

        // Tutup modal jika klik di luar modal
        window.onclick = function(event) {
            if (event.target == document.getElementById('bayarModal')) {
                closeBayarModal();
            }
        }
    </script>
</body>
</html> 