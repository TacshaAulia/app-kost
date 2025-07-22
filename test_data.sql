-- =====================================================
-- DATA TESTING UNTUK DEMO APLIKASI
-- SISTEM PENGELOLAAN KOS
-- =====================================================

USE db_kos;

-- Hapus data existing (jika ada)
DELETE FROM tb_bayar;
DELETE FROM tb_tagihan;
DELETE FROM tb_brng_bawaan;
DELETE FROM tb_kmr_penghuni;
DELETE FROM tb_penghuni;
DELETE FROM tb_barang;
DELETE FROM tb_kamar;

-- Reset auto increment
ALTER TABLE tb_bayar AUTO_INCREMENT = 1;
ALTER TABLE tb_tagihan AUTO_INCREMENT = 1;
ALTER TABLE tb_brng_bawaan AUTO_INCREMENT = 1;
ALTER TABLE tb_kmr_penghuni AUTO_INCREMENT = 1;
ALTER TABLE tb_penghuni AUTO_INCREMENT = 1;
ALTER TABLE tb_barang AUTO_INCREMENT = 1;
ALTER TABLE tb_kamar AUTO_INCREMENT = 1;

-- Insert data kamar
INSERT INTO tb_kamar (nomor, harga) VALUES 
('A1', 800000),
('A2', 800000),
('A3', 900000),
('B1', 900000),
('B2', 1000000),
('B3', 1000000),
('C1', 1200000),
('C2', 1200000);

-- Insert data barang
INSERT INTO tb_barang (nama, harga) VALUES 
('AC', 200000),
('Kipas Angin', 50000),
('TV', 150000),
('Kulkas', 300000),
('WiFi', 100000),
('Mesin Cuci', 250000),
('Kompor Gas', 75000),
('Dispenser', 80000);

-- Insert data penghuni
INSERT INTO tb_penghuni (nama, no_ktp, no_hp, tgl_masuk, tgl_keluar) VALUES 
('Ahmad Rizki', '1234567890123456', '081234567890', '2024-01-15', NULL),
('Siti Nurhaliza', '2345678901234567', '082345678901', '2024-02-01', NULL),
('Budi Santoso', '3456789012345678', '083456789012', '2024-01-20', NULL),
('Dewi Sartika', '4567890123456789', '084567890123', '2024-02-10', NULL),
('Rudi Hermawan', '5678901234567890', '085678901234', '2024-01-25', '2024-03-15'),
('Maya Indah', '6789012345678901', '086789012345', '2024-02-05', NULL),
('Agus Setiawan', '7890123456789012', '087890123456', '2024-01-30', NULL),
('Nina Safitri', '8901234567890123', '088901234567', '2024-02-15', NULL);

-- Insert data penempatan kamar
INSERT INTO tb_kmr_penghuni (id_kamar, id_penghuni, tgl_masuk, tgl_keluar) VALUES 
(1, 1, '2024-01-15', NULL),  -- Ahmad di A1
(2, 2, '2024-02-01', NULL),  -- Siti di A2
(3, 3, '2024-01-20', NULL),  -- Budi di A3
(4, 4, '2024-02-10', NULL),  -- Dewi di B1
(5, 5, '2024-01-25', '2024-03-15'), -- Rudi di B2 (sudah keluar)
(6, 6, '2024-02-05', NULL),  -- Maya di B3
(7, 7, '2024-01-30', NULL),  -- Agus di C1
(8, 8, '2024-02-15', NULL);  -- Nina di C2

-- Insert data barang bawaan
INSERT INTO tb_brng_bawaan (id_penghuni, id_barang) VALUES 
(1, 1), -- Ahmad bawa AC
(1, 3), -- Ahmad bawa TV
(2, 2), -- Siti bawa Kipas Angin
(2, 5), -- Siti bawa WiFi
(3, 1), -- Budi bawa AC
(3, 4), -- Budi bawa Kulkas
(4, 3), -- Dewi bawa TV
(4, 5), -- Dewi bawa WiFi
(6, 1), -- Maya bawa AC
(6, 6), -- Maya bawa Mesin Cuci
(7, 2), -- Agus bawa Kipas Angin
(7, 7), -- Agus bawa Kompor Gas
(8, 3), -- Nina bawa TV
(8, 8); -- Nina bawa Dispenser

-- Insert data tagihan (untuk bulan Januari 2024)
INSERT INTO tb_tagihan (bulan, id_kmr_penghuni, jml_tagihan) VALUES 
('2024-01', 1, 1150000), -- Ahmad: 800k kamar + 350k barang (AC+TV)
('2024-01', 3, 1200000), -- Budi: 900k kamar + 300k barang (AC+Kulkas)
('2024-01', 5, 1000000), -- Rudi: 1000k kamar (tidak ada barang)
('2024-01', 7, 975000),  -- Agus: 1200k kamar + 125k barang (Kipas+Kompor)

-- Insert data tagihan (untuk bulan Februari 2024)
INSERT INTO tb_tagihan (bulan, id_kmr_penghuni, jml_tagihan) VALUES 
('2024-02', 1, 1150000), -- Ahmad
('2024-02', 2, 950000),  -- Siti: 800k kamar + 150k barang (Kipas+WiFi)
('2024-02', 3, 1200000), -- Budi
('2024-02', 4, 1050000), -- Dewi: 900k kamar + 150k barang (TV+WiFi)
('2024-02', 6, 1450000), -- Maya: 1000k kamar + 450k barang (AC+Mesin Cuci)
('2024-02', 7, 975000),  -- Agus
('2024-02', 8, 1350000); -- Nina: 1200k kamar + 150k barang (TV+Dispenser)

-- Insert data tagihan (untuk bulan Maret 2024)
INSERT INTO tb_tagihan (bulan, id_kmr_penghuni, jml_tagihan) VALUES 
('2024-03', 1, 1150000), -- Ahmad
('2024-03', 2, 950000),  -- Siti
('2024-03', 3, 1200000), -- Budi
('2024-03', 4, 1050000), -- Dewi
('2024-03', 6, 1450000), -- Maya
('2024-03', 7, 975000),  -- Agus
('2024-03', 8, 1350000); -- Nina

-- Insert data pembayaran (beberapa sudah lunas, beberapa cicil)
INSERT INTO tb_bayar (id_tagihan, jml_bayar, status, tgl_bayar) VALUES 
-- Pembayaran Januari 2024
(1, 1150000, 'lunas', '2024-01-15 10:30:00'),    -- Ahmad lunas
(2, 600000, 'cicil', '2024-01-20 14:15:00'),     -- Budi cicil
(2, 600000, 'lunas', '2024-01-25 09:45:00'),     -- Budi lunas
(3, 1000000, 'lunas', '2024-01-30 16:20:00'),    -- Rudi lunas
(4, 975000, 'lunas', '2024-01-28 11:10:00'),     -- Agus lunas

-- Pembayaran Februari 2024
(5, 1150000, 'lunas', '2024-02-15 13:25:00'),    -- Ahmad lunas
(6, 950000, 'lunas', '2024-02-01 08:30:00'),     -- Siti lunas
(7, 1200000, 'lunas', '2024-02-20 15:45:00'),    -- Budi lunas
(8, 500000, 'cicil', '2024-02-10 12:00:00'),     -- Dewi cicil
(9, 1450000, 'lunas', '2024-02-25 10:15:00'),    -- Maya lunas
(10, 975000, 'lunas', '2024-02-28 14:30:00'),    -- Agus lunas
(11, 1350000, 'lunas', '2024-02-15 16:45:00'),   -- Nina lunas

-- Pembayaran Maret 2024 (beberapa belum bayar)
(12, 1150000, 'lunas', '2024-03-15 09:20:00'),   -- Ahmad lunas
(13, 950000, 'lunas', '2024-03-01 11:30:00'),    -- Siti lunas
(14, 1200000, 'lunas', '2024-03-20 13:45:00'),   -- Budi lunas
(15, 500000, 'cicil', '2024-03-10 15:10:00'),    -- Dewi cicil (masih ada sisa)
(16, 1450000, 'lunas', '2024-03-25 08:55:00'),   -- Maya lunas
(17, 975000, 'lunas', '2024-03-28 12:20:00'),    -- Agus lunas
-- Nina belum bayar Maret 2024

-- =====================================================
-- VERIFIKASI DATA
-- =====================================================

-- Cek jumlah data
SELECT 'Kamar' as tabel, COUNT(*) as jumlah FROM tb_kamar
UNION ALL
SELECT 'Barang', COUNT(*) FROM tb_barang
UNION ALL
SELECT 'Penghuni', COUNT(*) FROM tb_penghuni
UNION ALL
SELECT 'Penempatan', COUNT(*) FROM tb_kmr_penghuni
UNION ALL
SELECT 'Barang Bawaan', COUNT(*) FROM tb_brng_bawaan
UNION ALL
SELECT 'Tagihan', COUNT(*) FROM tb_tagihan
UNION ALL
SELECT 'Pembayaran', COUNT(*) FROM tb_bayar;

-- Cek kamar kosong
SELECT 'Kamar Kosong:' as info;
SELECT k.nomor, k.harga 
FROM tb_kamar k 
LEFT JOIN tb_kmr_penghuni kp ON k.id = kp.id_kamar AND kp.tgl_keluar IS NULL
WHERE kp.id IS NULL;

-- Cek penghuni aktif
SELECT 'Penghuni Aktif:' as info;
SELECT p.nama, k.nomor as kamar, p.tgl_masuk
FROM tb_penghuni p
LEFT JOIN tb_kmr_penghuni kp ON p.id = kp.id_penghuni AND kp.tgl_keluar IS NULL
LEFT JOIN tb_kamar k ON kp.id_kamar = k.id
WHERE p.tgl_keluar IS NULL;

-- Cek tagihan yang belum lunas
SELECT 'Tagihan Belum Lunas:' as info;
SELECT t.bulan, k.nomor as kamar, p.nama, t.jml_tagihan,
       COALESCE(SUM(b.jml_bayar), 0) as total_bayar,
       (t.jml_tagihan - COALESCE(SUM(b.jml_bayar), 0)) as sisa
FROM tb_tagihan t
JOIN tb_kmr_penghuni kp ON t.id_kmr_penghuni = kp.id
JOIN tb_kamar k ON kp.id_kamar = k.id
JOIN tb_penghuni p ON kp.id_penghuni = p.id
LEFT JOIN tb_bayar b ON t.id = b.id_tagihan
GROUP BY t.id
HAVING sisa > 0
ORDER BY t.bulan DESC, p.nama; 