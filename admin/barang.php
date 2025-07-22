<?php
require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] == 'add_barang') {
            $nama = $_POST['nama'];
            $harga = $_POST['harga'];
            
            $query = "INSERT INTO tb_barang (nama, harga) VALUES (?, ?)";
            $stmt = $db->prepare($query);
            
            if ($stmt->execute([$nama, $harga])) {
                $message = '<div class="alert alert-success">Barang berhasil ditambahkan!</div>';
            } else {
                $message = '<div class="alert alert-danger">Gagal menambahkan barang!</div>';
            }
        } elseif ($_POST['action'] == 'add_bawaan') {
            $id_penghuni = $_POST['id_penghuni'];
            $id_barang = $_POST['id_barang'];
            
            // Cek apakah sudah ada
            $check_query = "SELECT id FROM tb_brng_bawaan WHERE id_penghuni = ? AND id_barang = ?";
            $check_stmt = $db->prepare($check_query);
            $check_stmt->execute([$id_penghuni, $id_barang]);
            
            if ($check_stmt->rowCount() == 0) {
                $query = "INSERT INTO tb_brng_bawaan (id_penghuni, id_barang) VALUES (?, ?)";
                $stmt = $db->prepare($query);
                
                if ($stmt->execute([$id_penghuni, $id_barang])) {
                    $message = '<div class="alert alert-success">Barang bawaan berhasil ditambahkan!</div>';
                } else {
                    $message = '<div class="alert alert-danger">Gagal menambahkan barang bawaan!</div>';
                }
            } else {
                $message = '<div class="alert alert-warning">Barang sudah ada dalam daftar bawaan penghuni!</div>';
            }
        }
    }
}

// Query untuk menampilkan data barang
$query_barang = "SELECT * FROM tb_barang ORDER BY nama";
$stmt_barang = $db->prepare($query_barang);
$stmt_barang->execute();

// Query untuk menampilkan barang bawaan penghuni
$query_bawaan = "
    SELECT bb.*, b.nama as nama_barang, b.harga as harga_barang,
           p.nama as nama_penghuni, k.nomor as nomor_kamar
    FROM tb_brng_bawaan bb
    JOIN tb_barang b ON bb.id_barang = b.id
    JOIN tb_penghuni p ON bb.id_penghuni = p.id
    LEFT JOIN tb_kmr_penghuni kp ON p.id = kp.id_penghuni AND kp.tgl_keluar IS NULL
    LEFT JOIN tb_kamar k ON kp.id_kamar = k.id
    WHERE p.tgl_keluar IS NULL
    ORDER BY p.nama, b.nama
";
$stmt_bawaan = $db->prepare($query_bawaan);
$stmt_bawaan->execute();

// Query untuk penghuni aktif
$query_penghuni = "
    SELECT p.* 
    FROM tb_penghuni p
    WHERE p.tgl_keluar IS NULL
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
    <title>Kelola Barang - Admin Panel</title>
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
            <h1>📦 Kelola Barang</h1>
            <p>Kelola data barang tambahan dan barang bawaan penghuni</p>
        </div>

        <?= $message ?>

        <!-- Form Tambah Barang -->
        <div class="card">
            <h2>➕ Tambah Barang Baru</h2>
            <form method="POST" action="">
                <input type="hidden" name="action" value="add_barang">
                <div class="grid">
                    <div class="form-group">
                        <label for="nama">Nama Barang</label>
                        <input type="text" id="nama" name="nama" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="harga">Harga (Rp)</label>
                        <input type="number" id="harga" name="harga" class="form-control" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-success">Tambah Barang</button>
            </form>
        </div>

        <!-- Form Tambah Barang Bawaan -->
        <div class="card">
            <h2>📋 Tambah Barang Bawaan Penghuni</h2>
            <form method="POST" action="">
                <input type="hidden" name="action" value="add_bawaan">
                <div class="grid">
                    <div class="form-group">
                        <label for="id_penghuni">Pilih Penghuni</label>
                        <select id="id_penghuni" name="id_penghuni" class="form-control" required>
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
                        <label for="id_barang">Pilih Barang</label>
                        <select id="id_barang" name="id_barang" class="form-control" required>
                            <option value="">Pilih Barang</option>
                            <?php 
                            $stmt_barang->execute();
                            while ($barang = $stmt_barang->fetch(PDO::FETCH_ASSOC)): 
                            ?>
                                <option value="<?= $barang['id'] ?>"><?= htmlspecialchars($barang['nama']) ?> - Rp <?= number_format($barang['harga'], 0, ',', '.') ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>
                <button type="submit" class="btn btn-success">Tambah Barang Bawaan</button>
            </form>
        </div>

        <!-- Tabel Data Barang -->
        <div class="card">
            <h2>📦 Data Barang</h2>
            <table class="table">
                <thead>
                    <tr>
                        <th>Nama Barang</th>
                        <th>Harga</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $stmt_barang->execute();
                    while ($row = $stmt_barang->fetch(PDO::FETCH_ASSOC)): 
                    ?>
                        <tr>
                            <td><?= htmlspecialchars($row['nama']) ?></td>
                            <td>Rp <?= number_format($row['harga'], 0, ',', '.') ?></td>
                            <td>
                                <button onclick="editBarang(<?= $row['id'] ?>)" class="btn btn-warning">Edit</button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <!-- Tabel Barang Bawaan -->
        <div class="card">
            <h2>📋 Barang Bawaan Penghuni</h2>
            <table class="table">
                <thead>
                    <tr>
                        <th>Penghuni</th>
                        <th>Kamar</th>
                        <th>Barang</th>
                        <th>Harga</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $stmt_bawaan->fetch(PDO::FETCH_ASSOC)): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['nama_penghuni']) ?></td>
                            <td><?= $row['nomor_kamar'] ?: '-' ?></td>
                            <td><?= htmlspecialchars($row['nama_barang']) ?></td>
                            <td>Rp <?= number_format($row['harga_barang'], 0, ',', '.') ?></td>
                            <td>
                                <button onclick="hapusBarangBawaan(<?= $row['id'] ?>)" class="btn btn-danger">Hapus</button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Edit Barang -->
    <div id="editModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000;">
        <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 2rem; border-radius: 15px; min-width: 400px;">
            <h3>Edit Barang</h3>
            <form method="POST" action="" id="editForm">
                <input type="hidden" name="action" value="edit_barang">
                <input type="hidden" name="id" id="edit_id">
                <div class="form-group">
                    <label for="edit_nama">Nama Barang</label>
                    <input type="text" id="edit_nama" name="nama" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="edit_harga">Harga (Rp)</label>
                    <input type="number" id="edit_harga" name="harga" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-success">Simpan</button>
                <button type="button" onclick="closeModal()" class="btn btn-danger">Batal</button>
            </form>
        </div>
    </div>

    <script>
        function editBarang(id) {
            document.getElementById('edit_id').value = id;
            document.getElementById('editModal').style.display = 'block';
        }

        function hapusBarangBawaan(id) {
            if (confirm('Yakin ingin menghapus barang bawaan ini?')) {
                // Implementasi hapus barang bawaan
                alert('Fitur hapus belum diimplementasi');
            }
        }

        function closeModal() {
            document.getElementById('editModal').style.display = 'none';
        }

        // Tutup modal jika klik di luar modal
        window.onclick = function(event) {
            if (event.target == document.getElementById('editModal')) {
                closeModal();
            }
        }
    </script>
</body>
</html> 