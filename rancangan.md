rancangan lengkap sistem informasi arisan ibu-ibu berbasis PHP, MySQL, dan Bootstrap 5 css,js online, yang responsif (mobile & desktop friendly) dan memiliki fitur pengundian otomatis acak setiap bulan.
✅ Fitur Sistem

Manajemen Anggota Arisan

Tambah/edit/hapus anggota.

Lihat daftar anggota.

Iuran Bulanan

Setiap anggota setor iuran Rp50.000 per bulan.

Admin bisa input status pembayaran tiap anggota.

Pengundian Pemenang

1 anggota diundi secara acak setiap bulan.

Hanya anggota yang sudah bayar iuran yang bisa diundi.

Sistem acak nama, dan simpan pemenang bulan itu.

Histori Arisan

Riwayat pemenang tiap bulan.

Status pembayaran per bulan per anggota.

Responsif di Semua Perangkat

Menggunakan Bootstrap 5.

📁 Struktur Folder
mardi/
├── config/
│   └── db.php
│
├── includes/
│   ├── header.php
│   └── footer.php
│
├── pages/
│   ├── dashboard.php
│   ├── anggota.php
│   ├── tambah_anggota.php
│   ├── edit_anggota.php
│   ├── iuran.php
│   ├── undi.php
│   └── histori.php
│
├── index.php
├── login.php
└── logout.php

🛢️ Struktur Database (MySQL)

Database Name: db_arisan

1. Tabel: anggota
CREATE TABLE anggota (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nama VARCHAR(100) NOT NULL,
  no_hp VARCHAR(15),
  alamat TEXT,
  tanggal_gabung DATE
);

2. Tabel: iuran
CREATE TABLE iuran (
  id INT AUTO_INCREMENT PRIMARY KEY,
  id_anggota INT,
  bulan VARCHAR(20),
  tahun INT,
  jumlah INT DEFAULT 50000,
  status ENUM('LUNAS', 'BELUM') DEFAULT 'BELUM',
  FOREIGN KEY (id_anggota) REFERENCES anggota(id)
);

3. Tabel: pemenang
CREATE TABLE pemenang (
  id INT AUTO_INCREMENT PRIMARY KEY,
  id_anggota INT,
  bulan VARCHAR(20),
  tahun INT,
  tanggal_undi DATE,
  FOREIGN KEY (id_anggota) REFERENCES anggota(id)
);

⚙️ Alur Sistem

Admin Login
Admin Menambah Anggota
Setiap bulan: Admin cek siapa yang sudah membayar
Klik tombol "Undi" → sistem akan pilih 1 nama dari yang sudah bayar
Pemenang disimpan di tabel pemenang
Riwayat iuran & pemenang bisa dilihat kapan saja
