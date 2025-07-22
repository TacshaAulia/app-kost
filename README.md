# 🏠 Sistem Pengelolaan Kos

Aplikasi web untuk mengelola data kos, penghuni, kamar, tagihan, dan pembayaran. Dibuat dengan PHP Native dan MySQL.

## 📋 Fitur Utama

### Halaman Depan (Public)
- ✅ Tampilan kamar kosong dan harga sewa
- ✅ Informasi kamar yang harus bayar (7 hari ke depan)
- ✅ Daftar kamar terlambat bayar
- ✅ Statistik dashboard (total kamar, terisi, kosong, penghuni aktif)

### Admin Panel
- ✅ **Kelola Penghuni**: Tambah, edit, catat keluar masuk penghuni
- ✅ **Kelola Kamar**: Tambah, edit kamar, tempatkan penghuni
- ✅ **Kelola Barang**: Tambah barang tambahan, kelola barang bawaan penghuni
- ✅ **Generate Tagihan**: Buat tagihan otomatis per bulan
- ✅ **Kelola Pembayaran**: Catat pembayaran, status lunas/cicil
- ✅ **Kelola Tagihan**: Lihat dan filter tagihan

## 🗄️ Struktur Database

### Tabel Utama
1. **tb_penghuni** - Data penghuni kost
2. **tb_kamar** - Data kamar dan harga sewa
3. **tb_barang** - Data barang tambahan
4. **tb_kmr_penghuni** - Relasi penghuni dengan kamar
5. **tb_brng_bawaan** - Barang bawaan penghuni
6. **tb_tagihan** - Tagihan bulanan
7. **tb_bayar** - Data pembayaran

## 🚀 Cara Instalasi

### 1. Persyaratan Sistem
- PHP 7.4 atau lebih tinggi
- MySQL 5.7 atau lebih tinggi
- Web server (Apache/Nginx)

### 2. Langkah Instalasi

1. **Clone atau download project**
   ```bash
   git clone [repository-url]
   cd sistem-kos
   ```

2. **Setup Database**
   - Buat database MySQL baru
   - Import file `database.sql`
   - Atau jalankan query SQL secara manual

3. **Konfigurasi Database**
   - Edit file `config/database.php`
   - Sesuaikan host, username, password, dan nama database

4. **Setup Web Server**
   - Letakkan file di folder web server
   - Pastikan folder dapat diakses via browser

5. **Test Aplikasi**
   - Buka `http://localhost/sistem-kos`
   - Akses admin panel di `http://localhost/sistem-kos/admin/`

## 📁 Struktur File

```
sistem-kos/
├── config/
│   └── database.php          # Konfigurasi database
├── assets/
│   └── css/
│       └── style.css         # Stylesheet aplikasi
├── admin/
│   ├── index.php             # Dashboard admin
│   ├── penghuni.php          # Kelola penghuni
│   ├── kamar.php             # Kelola kamar
│   ├── barang.php            # Kelola barang
│   ├── tagihan.php           # Kelola tagihan
│   ├── pembayaran.php        # Kelola pembayaran
│   └── generate_tagihan.php  # Generate tagihan
├── database.sql              # File SQL database
├── index.php                 # Halaman depan
└── README.md                 # Dokumentasi
```

## 🔧 Cara Penggunaan

### 1. Setup Awal
1. **Tambah Kamar**: Masuk ke Admin Panel → Kelola Kamar → Tambah kamar baru
2. **Tambah Barang**: Admin Panel → Kelola Barang → Tambah barang tambahan
3. **Tambah Penghuni**: Admin Panel → Kelola Penghuni → Tambah penghuni baru
4. **Tempatkan Penghuni**: Admin Panel → Kelola Kamar → Tempatkan penghuni ke kamar
5. **Tambah Barang Bawaan**: Admin Panel → Kelola Barang → Tambah barang bawaan penghuni

### 2. Generate Tagihan
1. Masuk ke Admin Panel → Generate Tagihan
2. Pilih bulan yang akan dibuat tagihan
3. Klik "Generate Tagihan"
4. Sistem akan membuat tagihan otomatis untuk semua penghuni aktif

### 3. Catat Pembayaran
1. Masuk ke Admin Panel → Kelola Pembayaran
2. Pilih tagihan yang akan dibayar
3. Masukkan jumlah pembayaran
4. Pilih status (cicil/lunas)
5. Klik "Catat Pembayaran"

## 💡 Fitur Khusus

### Perhitungan Tagihan
- **Tagihan = Harga Sewa Kamar + Total Harga Barang Bawaan**
- Tagihan dibuat per bulan (format: YYYY-MM)
- Tagihan yang sudah ada tidak akan dibuat ulang

### Status Pembayaran
- **Lunas**: Total bayar ≥ Jumlah tagihan
- **Cicil**: Total bayar > 0 tapi < Jumlah tagihan
- **Belum Bayar**: Total bayar = 0

### Pencatatan Keluar
- **Pindah Kamar**: Update `tgl_keluar` di `tb_kmr_penghuni`
- **Keluar Kost**: Update `tgl_keluar` di `tb_penghuni` dan `tb_kmr_penghuni`

## 🎨 Desain UI/UX

- **Responsive Design**: Mendukung desktop, tablet, dan mobile
- **Modern UI**: Menggunakan gradient, shadow, dan animasi
- **User-Friendly**: Interface yang intuitif dan mudah digunakan
- **Color Coding**: Status dengan warna yang berbeda (hijau=lunas, kuning=cicil, biru=kosong)

## 🔒 Keamanan

- **SQL Injection Protection**: Menggunakan prepared statements
- **XSS Protection**: Escape output HTML
- **Input Validation**: Validasi input form
- **Error Handling**: Penanganan error yang aman

## 🐛 Troubleshooting

### Masalah Umum

1. **Koneksi Database Error**
   - Periksa konfigurasi di `config/database.php`
   - Pastikan MySQL server berjalan
   - Cek username dan password database

2. **Halaman Tidak Muncul**
   - Periksa permission folder
   - Pastikan PHP dan web server berjalan
   - Cek error log web server

3. **Generate Tagihan Gagal**
   - Pastikan ada penghuni aktif
   - Cek data kamar dan barang sudah benar
   - Periksa relasi antar tabel

## 📞 Support

Jika ada pertanyaan atau masalah, silakan:
1. Periksa dokumentasi ini
2. Cek error log
3. Pastikan semua persyaratan terpenuhi

## 📝 Lisensi

Project ini dibuat untuk keperluan akademis. Silakan digunakan dan dimodifikasi sesuai kebutuhan.

---

**Dibuat dengan ❤️ menggunakan PHP Native dan MySQL** 