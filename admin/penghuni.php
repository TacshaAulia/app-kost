<?php
require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$message = '';

// Handle form submission untuk tambah/edit penghuni
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] == 'add') {
            $nama = $_POST['nama'];
            $no_ktp = $_POST['no_ktp'];
            $no_hp = $_POST['no_hp'];
            $tgl_masuk = $_POST['tgl_masuk'];
            
            $query = "INSERT INTO tb_penghuni (nama, no_ktp, no_hp, tgl_masuk) VALUES (?, ?, ?, ?)";
            $stmt = $db->prepare($query);
            
            if ($stmt->execute([$nama, $no_ktp, $no_hp, $tgl_masuk])) {
                $message = '<div class="alert alert-success">Penghuni berhasil ditambahkan!</div>';
            } else {
                $message = '<div class="alert alert-danger">Gagal menambahkan penghuni!</div>';
            }
        } elseif ($_POST['action'] == 'edit') {
            $id = $_POST['id'];
            $nama = $_POST['nama'];
            $no_ktp = $_POST['no_ktp'];
            $no_hp = $_POST['no_hp'];
            $tgl_masuk = $_POST['tgl_masuk'];
            
            $query = "UPDATE tb_penghuni SET nama = ?, no_ktp = ?, no_hp = ?, tgl_masuk = ? WHERE id = ?";
            $stmt = $db->prepare($query);
            
            if ($stmt->execute([$nama, $no_ktp, $no_hp, $tgl_masuk, $id])) {
                $message = '<div class="alert alert-success">Data penghuni berhasil diperbarui!</div>';
            } else {
                $message = '<div class="alert alert-danger">Gagal memperbarui data penghuni!</div>';
            }
        } elseif ($_POST['action'] == 'keluar') {
            $id = $_POST['id'];
            $tgl_keluar = $_POST['tgl_keluar'];
            
            // Update tanggal keluar di tb_penghuni
            $query1 = "UPDATE tb_penghuni SET tgl_keluar = ? WHERE id = ?";
            $stmt1 = $db->prepare($query1);
            
            // Update tanggal keluar di tb_kmr_penghuni
            $query2 = "UPDATE tb_kmr_penghuni SET tgl_keluar = ? WHERE id_penghuni = ? AND tgl_keluar IS NULL";
            $stmt2 = $db->prepare($query2);
            
            if ($stmt1->execute([$tgl_keluar, $id]) && $stmt2->execute([$tgl_keluar, $id])) {
                $message = '<div class="alert alert-success">Penghuni berhasil dicatat keluar!</div>';
            } else {
                $message = '<div class="alert alert-danger">Gagal mencatat keluar penghuni!</div>';
            }
        }
    }
}

// Query untuk menampilkan data penghuni
$query = "
    SELECT p.*, 
           k.nomor as nomor_kamar,
           CASE 
               WHEN p.tgl_keluar IS NULL THEN 'Aktif'
               ELSE 'Tidak Aktif'
           END as status
    FROM tb_penghuni p
    LEFT JOIN tb_kmr_penghuni kp ON p.id = kp.id_penghuni AND kp.tgl_keluar IS NULL
    LEFT JOIN tb_kamar k ON kp.id_kamar = k.id
    ORDER BY p.nama
";
$stmt = $db->prepare($query);
$stmt->execute();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Penghuni - Admin Panel</title>
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
            <h1>👥 Kelola Penghuni</h1>
            <p>Kelola data penghuni kost</p>
        </div>

        <?= $message ?>

        <!-- Form Tambah Penghuni -->
        <div class="card">
            <h2>➕ Tambah Penghuni Baru</h2>
            <form method="POST" action="">
                <input type="hidden" name="action" value="add">
                <div class="grid">
                    <div class="form-group">
                        <label for="nama">Nama Lengkap</label>
                        <input type="text" id="nama" name="nama" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="no_ktp">Nomor KTP</label>
                        <input type="text" id="no_ktp" name="no_ktp" class="form-control" maxlength="16" required>
                    </div>
                    <div class="form-group">
                        <label for="no_hp">Nomor Handphone</label>
                        <input type="text" id="no_hp" name="no_hp" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="tgl_masuk">Tanggal Masuk</label>
                        <input type="date" id="tgl_masuk" name="tgl_masuk" class="form-control" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-success">Tambah Penghuni</button>
            </form>
        </div>

        <!-- Tabel Data Penghuni -->
        <div class="card">
            <h2>📋 Data Penghuni</h2>
            <table class="table">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>No. KTP</th>
                        <th>No. HP</th>
                        <th>Tanggal Masuk</th>
                        <th>Tanggal Keluar</th>
                        <th>Kamar</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['nama']) ?></td>
                            <td><?= htmlspecialchars($row['no_ktp']) ?></td>
                            <td><?= htmlspecialchars($row['no_hp']) ?></td>
                            <td><?= date('d/m/Y', strtotime($row['tgl_masuk'])) ?></td>
                            <td><?= $row['tgl_keluar'] ? date('d/m/Y', strtotime($row['tgl_keluar'])) : '-' ?></td>
                            <td><?= $row['nomor_kamar'] ?: '-' ?></td>
                            <td>
                                <span class="status-badge <?= $row['status'] == 'Aktif' ? 'status-berpenghuni' : 'status-kosong' ?>">
                                    <?= $row['status'] ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($row['status'] == 'Aktif'): ?>
                                    <button onclick="editPenghuni(<?= $row['id'] ?>)" class="btn btn-warning">Edit</button>
                                    <button onclick="keluarPenghuni(<?= $row['id'] ?>)" class="btn btn-danger">Keluar</button>
                                <?php else: ?>
                                    <button onclick="editPenghuni(<?= $row['id'] ?>)" class="btn btn-warning">Edit</button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Edit Penghuni -->
    <div id="editModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000;">
        <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 2rem; border-radius: 15px; min-width: 400px;">
            <h3>Edit Penghuni</h3>
            <form method="POST" action="" id="editForm">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" id="edit_id">
                <div class="form-group">
                    <label for="edit_nama">Nama Lengkap</label>
                    <input type="text" id="edit_nama" name="nama" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="edit_no_ktp">Nomor KTP</label>
                    <input type="text" id="edit_no_ktp" name="no_ktp" class="form-control" maxlength="16" required>
                </div>
                <div class="form-group">
                    <label for="edit_no_hp">Nomor Handphone</label>
                    <input type="text" id="edit_no_hp" name="no_hp" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="edit_tgl_masuk">Tanggal Masuk</label>
                    <input type="date" id="edit_tgl_masuk" name="tgl_masuk" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-success">Simpan</button>
                <button type="button" onclick="closeModal()" class="btn btn-danger">Batal</button>
            </form>
        </div>
    </div>

    <!-- Modal Keluar Penghuni -->
    <div id="keluarModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000;">
        <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 2rem; border-radius: 15px; min-width: 400px;">
            <h3>Catat Keluar Penghuni</h3>
            <form method="POST" action="" id="keluarForm">
                <input type="hidden" name="action" value="keluar">
                <input type="hidden" name="id" id="keluar_id">
                <div class="form-group">
                    <label for="keluar_tgl">Tanggal Keluar</label>
                    <input type="date" id="keluar_tgl" name="tgl_keluar" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-success">Catat Keluar</button>
                <button type="button" onclick="closeKeluarModal()" class="btn btn-danger">Batal</button>
            </form>
        </div>
    </div>

    <script>
        function editPenghuni(id) {
            // Ambil data penghuni berdasarkan ID (implementasi sederhana)
            document.getElementById('edit_id').value = id;
            document.getElementById('editModal').style.display = 'block';
        }

        function keluarPenghuni(id) {
            document.getElementById('keluar_id').value = id;
            document.getElementById('keluar_tgl').value = new Date().toISOString().split('T')[0];
            document.getElementById('keluarModal').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('editModal').style.display = 'none';
        }

        function closeKeluarModal() {
            document.getElementById('keluarModal').style.display = 'none';
        }

        // Tutup modal jika klik di luar modal
        window.onclick = function(event) {
            if (event.target == document.getElementById('editModal')) {
                closeModal();
            }
            if (event.target == document.getElementById('keluarModal')) {
                closeKeluarModal();
            }
        }
    </script>
</body>
</html> 