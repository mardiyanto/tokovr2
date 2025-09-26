-- Database untuk Aplikasi E-Commerce Toko ABC
-- Created by: Programmer Handal
-- Date: 2024

CREATE DATABASE IF NOT EXISTS toko_abc;
USE toko_abc;

-- Table Admin
CREATE TABLE admin (
    id_admin INT AUTO_INCREMENT PRIMARY KEY,
    nama_admin VARCHAR(100) NOT NULL,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    role ENUM('superadmin', 'staff') DEFAULT 'staff',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Table Kategori
CREATE TABLE kategori (
    id_kategori INT AUTO_INCREMENT PRIMARY KEY,
    nama_kategori VARCHAR(100) NOT NULL,
    deskripsi TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Table Konsumen
CREATE TABLE konsumen (
    id_konsumen INT AUTO_INCREMENT PRIMARY KEY,
    nama_lengkap VARCHAR(100) NOT NULL,
    alamat_lengkap TEXT NOT NULL,
    no_hp VARCHAR(20) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    tanggal_daftar TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table Produk
CREATE TABLE produk (
    id_produk INT AUTO_INCREMENT PRIMARY KEY,
    id_kategori INT NOT NULL,
    nama_produk VARCHAR(200) NOT NULL,
    deskripsi TEXT,
    harga DECIMAL(10,2) NOT NULL,
    stok INT DEFAULT 0,
    gambar_produk VARCHAR(255),
    status ENUM('pending', 'acc', 'ditolak') DEFAULT 'pending',
    validated_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_kategori) REFERENCES kategori(id_kategori) ON DELETE CASCADE,
    FOREIGN KEY (validated_by) REFERENCES admin(id_admin) ON DELETE SET NULL
);

-- Table Order
CREATE TABLE `order` (
    id_order INT AUTO_INCREMENT PRIMARY KEY,
    id_konsumen INT NOT NULL,
    tanggal_order TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    total_harga DECIMAL(10,2) NOT NULL,
    status_order ENUM('pending', 'dibayar', 'dikirim', 'selesai', 'batal') DEFAULT 'pending',
    FOREIGN KEY (id_konsumen) REFERENCES konsumen(id_konsumen) ON DELETE CASCADE
);

-- Table Order Detail
CREATE TABLE order_detail (
    id_detail INT AUTO_INCREMENT PRIMARY KEY,
    id_order INT NOT NULL,
    id_produk INT NOT NULL,
    jumlah INT NOT NULL,
    harga_satuan DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (id_order) REFERENCES `order`(id_order) ON DELETE CASCADE,
    FOREIGN KEY (id_produk) REFERENCES produk(id_produk) ON DELETE CASCADE
);

-- Insert data admin default
INSERT INTO admin (nama_admin, username, password, email, role) VALUES
('Super Admin', 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@tokoabc.com', 'superadmin');

-- Insert data kategori default
INSERT INTO kategori (nama_kategori, deskripsi) VALUES
('Elektronik', 'Produk elektronik dan gadget'),
('Fashion', 'Pakaian dan aksesoris fashion'),
('Makanan', 'Makanan dan minuman'),
('Kesehatan', 'Produk kesehatan dan kecantikan'),
('Olahraga', 'Perlengkapan olahraga dan fitness');

-- Insert data konsumen contoh
INSERT INTO konsumen (nama_lengkap, alamat_lengkap, no_hp, email, password) VALUES
('John Doe', 'Jl. Contoh No. 123, Jakarta', '081234567890', 'john@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- Insert data produk contoh
INSERT INTO produk (id_kategori, nama_produk, deskripsi, harga, stok, gambar_produk, status, validated_by) VALUES
(1, 'Smartphone Samsung Galaxy', 'Smartphone terbaru dengan fitur canggih', 5000000, 10, 'smartphone.jpg', 'acc', 1),
(2, 'Kaos Polo Premium', 'Kaos polo berkualitas tinggi', 150000, 50, 'kaos.jpg', 'acc', 1),
(3, 'Kopi Arabika Premium', 'Kopi arabika pilihan terbaik', 75000, 30, 'kopi.jpg', 'acc', 1);
