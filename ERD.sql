-- =====================================================
-- ENTITY RELATIONSHIP DIAGRAM (ERD)
-- SISTEM PENGELOLAAN KOS
-- =====================================================

/*
ERD SISTEM PENGELOLAAN KOS

┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   tb_penghuni   │    │    tb_kamar     │    │   tb_barang     │
├─────────────────┤    ├─────────────────┤    ├─────────────────┤
│ PK: id          │    │ PK: id          │    │ PK: id          │
│ nama            │    │ nomor           │    │ nama            │
│ no_ktp (UK)     │    │ harga           │    │ harga           │
│ no_hp           │    └─────────────────┘    └─────────────────┘
│ tgl_masuk       │              │                      │
│ tgl_keluar      │              │                      │
└─────────────────┘              │                      │
         │                       │                      │
         │                       │                      │
         │              ┌─────────────────┐             │
         │              │ tb_kmr_penghuni │             │
         │              ├─────────────────┤             │
         │              │ PK: id          │             │
         │              │ FK: id_kamar    │             │
         │              │ FK: id_penghuni │             │
         │              │ tgl_masuk       │             │
         │              │ tgl_keluar      │             │
         │              └─────────────────┘             │
         │                       │                      │
         │                       │                      │
         │                       │              ┌─────────────────┐
         │                       │              │ tb_brng_bawaan  │
         │                       │              ├─────────────────┤
         │                       │              │ PK: id          │
         │                       │              │ FK: id_penghuni │
         │                       │              │ FK: id_barang   │
         │                       │              └─────────────────┘
         │                       │
         │                       │
         │              ┌─────────────────┐
         │              │   tb_tagihan    │
         │              ├─────────────────┤
         │              │ PK: id          │
         │              │ bulan           │
         │              │ FK: id_kmr_penghuni │
         │              │ jml_tagihan     │
         │              └─────────────────┘
         │                       │
         │                       │
         │              ┌─────────────────┐
         │              │    tb_bayar     │
         │              ├─────────────────┤
         │              │ PK: id          │
         │              │ FK: id_tagihan  │
         │              │ jml_bayar       │
         │              │ status          │
         │              │ tgl_bayar       │
         │              └─────────────────┘

RELASI:
1. tb_penghuni (1) ─── (N) tb_kmr_penghuni
2. tb_kamar (1) ─── (N) tb_kmr_penghuni  
3. tb_penghuni (1) ─── (N) tb_brng_bawaan
4. tb_barang (1) ─── (N) tb_brng_bawaan
5. tb_kmr_penghuni (1) ─── (N) tb_tagihan
6. tb_tagihan (1) ─── (N) tb_bayar

KETERANGAN:
- PK = Primary Key
- FK = Foreign Key  
- UK = Unique Key
- (1) = One (Satu)
- (N) = Many (Banyak)
*/

-- =====================================================
-- DETAIL RELASI DAN CONSTRAINT
-- =====================================================

/*
1. tb_penghuni ─── tb_kmr_penghuni
   - Satu penghuni bisa menempati beberapa kamar (dalam waktu berbeda)
   - Relasi: One-to-Many
   - Constraint: id_penghuni di tb_kmr_penghuni → id di tb_penghuni

2. tb_kamar ─── tb_kmr_penghuni
   - Satu kamar bisa ditempati beberapa penghuni (dalam waktu berbeda)
   - Relasi: One-to-Many
   - Constraint: id_kamar di tb_kmr_penghuni → id di tb_kamar

3. tb_penghuni ─── tb_brng_bawaan
   - Satu penghuni bisa membawa beberapa barang
   - Relasi: One-to-Many
   - Constraint: id_penghuni di tb_brng_bawaan → id di tb_penghuni

4. tb_barang ─── tb_brng_bawaan
   - Satu barang bisa dibawa oleh beberapa penghuni
   - Relasi: One-to-Many
   - Constraint: id_barang di tb_brng_bawaan → id di tb_barang

5. tb_kmr_penghuni ─── tb_tagihan
   - Satu penempatan kamar bisa memiliki beberapa tagihan (per bulan)
   - Relasi: One-to-Many
   - Constraint: id_kmr_penghuni di tb_tagihan → id di tb_kmr_penghuni

6. tb_tagihan ─── tb_bayar
   - Satu tagihan bisa dibayar beberapa kali (cicil)
   - Relasi: One-to-Many
   - Constraint: id_tagihan di tb_bayar → id di tb_tagihan
*/

-- =====================================================
-- BUSINESS RULES
-- =====================================================

/*
BUSINESS RULES:

1. PENGHUNI:
   - Setiap penghuni harus memiliki nomor KTP yang unik
   - Tanggal keluar diisi saat penghuni keluar dari kost
   - Penghuni aktif adalah yang tgl_keluar = NULL

2. KAMAR:
   - Setiap kamar memiliki nomor yang unik
   - Harga sewa kamar tidak boleh negatif
   - Kamar kosong adalah yang tidak ada di tb_kmr_penghuni dengan tgl_keluar = NULL

3. PENEMPATAN KAMAR:
   - Satu kamar hanya bisa ditempati satu penghuni pada waktu yang sama
   - Tanggal keluar diisi saat penghuni pindah kamar atau keluar kost
   - Penempatan aktif adalah yang tgl_keluar = NULL

4. BARANG BAWAAN:
   - Satu penghuni tidak bisa membawa barang yang sama dua kali
   - Barang bawaan hanya untuk penghuni aktif

5. TAGIHAN:
   - Tagihan dibuat per bulan (format: YYYY-MM)
   - Jumlah tagihan = Harga sewa kamar + Total harga barang bawaan
   - Tagihan yang sudah ada tidak bisa dibuat ulang untuk bulan yang sama

6. PEMBAYARAN:
   - Status pembayaran: 'lunas' atau 'cicil'
   - Total pembayaran tidak boleh melebihi jumlah tagihan
   - Tanggal pembayaran otomatis saat transaksi dibuat

7. PERHITUNGAN:
   - Tagihan = Harga Sewa Kamar + Σ(Harga Barang Bawaan)
   - Sisa Tagihan = Jumlah Tagihan - Σ(Total Pembayaran)
   - Status: Lunas (jika sisa = 0), Cicil (jika sisa > 0), Belum Bayar (jika belum ada pembayaran)
*/

-- =====================================================
-- INDEXES UNTUK PERFORMANCE
-- =====================================================

/*
INDEXES YANG DIREKOMENDASIKAN:

1. tb_penghuni:
   - INDEX pada no_ktp (UNIQUE)
   - INDEX pada tgl_keluar (untuk filter penghuni aktif)

2. tb_kamar:
   - INDEX pada nomor (UNIQUE)

3. tb_kmr_penghuni:
   - INDEX pada id_kamar + tgl_keluar (untuk cek kamar kosong)
   - INDEX pada id_penghuni + tgl_keluar (untuk cek penghuni aktif)

4. tb_tagihan:
   - INDEX pada bulan (untuk filter per bulan)
   - INDEX pada id_kmr_penghuni + bulan (UNIQUE)

5. tb_bayar:
   - INDEX pada id_tagihan (untuk hitung total pembayaran)
   - INDEX pada tgl_bayar (untuk riwayat pembayaran)

6. tb_brng_bawaan:
   - INDEX pada id_penghuni + id_barang (UNIQUE)
*/

-- =====================================================
-- QUERY CONTOH UNTUK REPORTING
-- =====================================================

/*
QUERY UNTUK LAPORAN:

1. Kamar Kosong:
SELECT k.nomor, k.harga 
FROM tb_kamar k 
LEFT JOIN tb_kmr_penghuni kp ON k.id = kp.id_kamar AND kp.tgl_keluar IS NULL
WHERE kp.id IS NULL;

2. Penghuni Terlambat Bayar:
SELECT k.nomor, p.nama, 
       DATEDIFF(CURDATE(), DATE_ADD(p.tgl_masuk, INTERVAL 1 MONTH)) as hari_terlambat
FROM tb_kmr_penghuni kp
JOIN tb_kamar k ON kp.id_kamar = k.id
JOIN tb_penghuni p ON kp.id_penghuni = p.id
WHERE kp.tgl_keluar IS NULL 
AND p.tgl_keluar IS NULL
AND DATE_ADD(p.tgl_masuk, INTERVAL 1 MONTH) < CURDATE();

3. Total Pendapatan per Bulan:
SELECT t.bulan, SUM(t.jml_tagihan) as total_tagihan, 
       SUM(COALESCE(b.total_bayar, 0)) as total_pendapatan
FROM tb_tagihan t
LEFT JOIN (
    SELECT id_tagihan, SUM(jml_bayar) as total_bayar
    FROM tb_bayar
    GROUP BY id_tagihan
) b ON t.id = b.id_tagihan
GROUP BY t.bulan
ORDER BY t.bulan DESC;
*/ 