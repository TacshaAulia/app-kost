<?php
require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] == 'add') {
            $nomor = $_POST['nomor'];
            $harga = $_POST['harga'];
            
            $query = "INSERT INTO tb_kamar (nomor, harga) VALUES (?, ?)";
            $stmt = $db->prepare($query);
            
            if ($stmt->execute([$nomor, $harga])) {
                $message = '<div class="alert alert-success">Kamar berhasil ditambahkan!</div>';
            } else {
                $message = '<div class="alert alert-danger">Gagal menambahkan kamar!</div>';
            }
        } elseif ($_POST['action'] == 'edit') {
            $id = $_POST['id'];
            $nomor = $_POST['nomor'];
            $harga = $_POST['harga'];
            
            $query = "UPDATE tb_kamar SET nomor = ?, harga = ? WHERE id = ?";
            $stmt = $db->prepare($query);
            
            if ($stmt->execute([$nomor, $harga, $id])) {
                $message = '<div class="alert alert-success">Data kamar berhasil diperbarui!</div>';
            } else {
                $message = '<div class="alert alert-danger">Gagal memperbarui data kamar!</div>';
            }
        } elseif ($_POST['action'] == 'place') {
            $id_kamar = $_POST['id_kamar'];
            $id_penghuni = $_POST['id_penghuni'];
            $tgl_masuk = $_POST['tgl_masuk'];
            
            $query = "INSERT INTO tb_kmr_penghuni (id_kamar, id_penghuni, tgl_masuk) VALUES (?, ?, ?)";
            $stmt = $db->prepare($query);
            
            if ($stmt->execute([$id_kamar, $id_penghuni, $tgl_masuk])) {
                $message = '<div class="alert alert-success">Penghuni berhasil ditempatkan di kamar!</div>';
            } else {
                $message = '<div class="alert alert-danger">Gagal menempatkan penghuni!</div>';
            }
        }
    }
}

// Query untuk menampilkan data kamar
$query = "
    SELECT k.*, 
           p.nama as nama_penghuni,
           p.id as id_penghuni,
           CASE 
               WHEN kp.tgl_keluar IS NULL AND p.tgl_keluar IS NULL THEN 'Terisi'
               ELSE 'Kosong'
           END as status
    FROM tb_kamar k
    LEFT JOIN tb_kmr_penghuni kp ON k.id = kp.id_kamar AND kp.tgl_keluar IS NULL
    LEFT JOIN tb_penghuni p ON kp.id_penghuni = p.id AND p.tgl_keluar IS NULL
    ORDER BY k.nomor
";
$stmt = $db->prepare($query);
$stmt->execute();

// Query untuk penghuni yang belum ditempatkan
$query_penghuni = "
    SELECT p.* 
    FROM tb_penghuni p
    LEFT JOIN tb_kmr_penghuni kp ON p.id = kp.id_penghuni AND kp.tgl_keluar IS NULL
    WHERE p.tgl_keluar IS NULL AND kp.id IS NULL
    ORDER BY p.nama
";
$stmt_penghuni = $db->prepare($query_penghuni);
$stmt_penghuni->execute();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Kamar - Admin Panel</title>
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
            <h1>🏠 Kelola Kamar</h1>
            <p>Kelola data kamar dan penempatan penghuni</p>
        </div>

        <?= $message ?>

        <!-- Form Tambah Kamar -->
        <div class="card">
            <h2>➕ Tambah Kamar Baru</h2>
            <form method="POST" action="">
                <input type="hidden" name="action" value="add">
                <div class="grid">
                    <div class="form-group">
                        <label for="nomor">Nomor Kamar</label>
                        <input type="text" id="nomor" name="nomor" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="harga">Harga Sewa (Rp)</label>
                        <input type="number" id="harga" name="harga" class="form-control" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-success">Tambah Kamar</button>
            </form>
        </div>

        <!-- Tabel Data Kamar -->
        <div class="card">
            <h2>📋 Data Kamar</h2>
            <table class="table">
                <thead>
                    <tr>
                        <th>Nomor Kamar</th>
                        <th>Harga Sewa</th>
                        <th>Penghuni</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($row['nomor']) ?></strong></td>
                            <td>Rp <?= number_format($row['harga'], 0, ',', '.') ?></td>
                            <td><?= $row['nama_penghuni'] ?: '-' ?></td>
                            <td>
                                <span class="status-badge <?= $row['status'] == 'Terisi' ? 'status-berpenghuni' : 'status-kosong' ?>">
                                    <?= $row['status'] ?>
                                </span>
                            </td>
                            <td>
                                <button onclick="editKamar(<?= $row['id'] ?>)" class="btn btn-warning">Edit</button>
                                <?php if ($row['status'] == 'Kosong'): ?>
                                    <button onclick="placePenghuni(<?= $row['id'] ?>)" class="btn btn-success">Tempatkan</button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Edit Kamar -->
    <div id="editModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000;">
        <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 2rem; border-radius: 15px; min-width: 400px;">
            <h3>Edit Kamar</h3>
            <form method="POST" action="" id="editForm">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" id="edit_id">
                <div class="form-group">
                    <label for="edit_nomor">Nomor Kamar</label>
                    <input type="text" id="edit_nomor" name="nomor" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="edit_harga">Harga Sewa (Rp)</label>
                    <input type="number" id="edit_harga" name="harga" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-success">Simpan</button>
                <button type="button" onclick="closeModal()" class="btn btn-danger">Batal</button>
            </form>
        </div>
    </div>

    <!-- Modal Tempatkan Penghuni -->
    <div id="placeModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000;">
        <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 2rem; border-radius: 15px; min-width: 400px;">
            <h3>Tempatkan Penghuni</h3>
            <form method="POST" action="" id="placeForm">
                <input type="hidden" name="action" value="place">
                <input type="hidden" name="id_kamar" id="place_id_kamar">
                <div class="form-group">
                    <label for="place_id_penghuni">Pilih Penghuni</label>
                    <select id="place_id_penghuni" name="id_penghuni" class="form-control" required>
                        <option value="">Pilih Penghuni</option>
                        <?php 
                        $stmt_penghuni->execute();
                        while ($penghuni = $stmt_penghuni->fetch(PDO::FETCH_ASSOC)): 
                        ?>
                            <option value="<?= $penghuni['id'] ?>"><?= htmlspecialchars($penghuni['nama']) ?> (<?= htmlspecialchars($penghuni['no_ktp']) ?>)</option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="place_tgl_masuk">Tanggal Masuk</label>
                    <input type="date" id="place_tgl_masuk" name="tgl_masuk" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-success">Tempatkan</button>
                <button type="button" onclick="closePlaceModal()" class="btn btn-danger">Batal</button>
            </form>
        </div>
    </div>

    <script>
        function editKamar(id) {
            document.getElementById('edit_id').value = id;
            document.getElementById('editModal').style.display = 'block';
        }

        function placePenghuni(id) {
            document.getElementById('place_id_kamar').value = id;
            document.getElementById('place_tgl_masuk').value = new Date().toISOString().split('T')[0];
            document.getElementById('placeModal').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('editModal').style.display = 'none';
        }

        function closePlaceModal() {
            document.getElementById('placeModal').style.display = 'none';
        }

        // Tutup modal jika klik di luar modal
        window.onclick = function(event) {
            if (event.target == document.getElementById('editModal')) {
                closeModal();
            }
            if (event.target == document.getElementById('placeModal')) {
                closePlaceModal();
            }
        }
    </script>
</body>
</html> 