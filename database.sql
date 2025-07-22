-- Database untuk Aplikasi Pengelolaan Kos
CREATE DATABASE IF NOT EXISTS db_kos;
USE db_kos;

-- Tabel penghuni kost
CREATE TABLE tb_penghuni (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    no_ktp VARCHAR(16) UNIQUE NOT NULL,
    no_hp VARCHAR(15) NOT NULL,
    tgl_masuk DATE NOT NULL,
    tgl_keluar DATE NULL
);

-- Tabel kamar
CREATE TABLE tb_kamar (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nomor VARCHAR(10) UNIQUE NOT NULL,
    harga DECIMAL(10,2) NOT NULL
);

-- Tabel barang tambahan
CREATE TABLE tb_barang (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    harga DECIMAL(10,2) NOT NULL
);

-- Tabel penghuni menempati kamar
CREATE TABLE tb_kmr_penghuni (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_kamar INT NOT NULL,
    id_penghuni INT NOT NULL,
    tgl_masuk DATE NOT NULL,
    tgl_keluar DATE NULL,
    FOREIGN KEY (id_kamar) REFERENCES tb_kamar(id),
    FOREIGN KEY (id_penghuni) REFERENCES tb_penghuni(id)
);

-- Tabel barang bawaan penghuni
CREATE TABLE tb_brng_bawaan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_penghuni INT NOT NULL,
    id_barang INT NOT NULL,
    FOREIGN KEY (id_penghuni) REFERENCES tb_penghuni(id),
    FOREIGN KEY (id_barang) REFERENCES tb_barang(id)
);

-- Tabel tagihan
CREATE TABLE tb_tagihan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    bulan VARCHAR(7) NOT NULL, -- Format: YYYY-MM
    id_kmr_penghuni INT NOT NULL,
    jml_tagihan DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (id_kmr_penghuni) REFERENCES tb_kmr_penghuni(id)
);

-- Tabel pembayaran
CREATE TABLE tb_bayar (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_tagihan INT NOT NULL,
    jml_bayar DECIMAL(10,2) NOT NULL,
    status ENUM('lunas', 'cicil') NOT NULL DEFAULT 'cicil',
    tgl_bayar TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_tagihan) REFERENCES tb_tagihan(id)
);

-- Insert data contoh
INSERT INTO tb_kamar (nomor, harga) VALUES 
('A1', 800000),
('A2', 800000),
('A3', 900000),
('B1', 900000),
('B2', 1000000),
('B3', 1000000);

INSERT INTO tb_barang (nama, harga) VALUES 
('AC', 200000),
('Kipas Angin', 50000),
('TV', 150000),
('Kulkas', 300000),
('WiFi', 100000); 