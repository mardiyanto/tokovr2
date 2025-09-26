# Toko ABC - Aplikasi E-Commerce

Aplikasi e-commerce berbasis website mobile dengan PHP, MySQL, dan Bootstrap 5. Aplikasi ini memungkinkan konsumen untuk menjual dan membeli produk dengan sistem validasi admin.

## Fitur Utama

### Untuk Konsumen
- **Registrasi & Login** - Sistem autentikasi yang aman
- **Jual Produk** - Upload produk dengan gambar dan deskripsi
- **Belanja** - Browse produk, tambah ke keranjang, dan checkout
- **Riwayat Order** - Melihat status dan detail pesanan
- **Profil** - Mengelola data pribadi

### Untuk Admin
- **Dashboard** - Statistik dan overview sistem
- **Validasi Produk** - Menyetujui atau menolak produk yang diupload konsumen
- **Kelola Kategori** - Menambah, mengedit, dan menghapus kategori produk
- **Kelola Order** - Memantau dan mengelola pesanan dengan update status
- **Kelola Konsumen** - Melihat data konsumen dan statistik belanja

## Teknologi yang Digunakan

- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+
- **Frontend**: Bootstrap 5, HTML5, CSS3, JavaScript
- **Icons**: Font Awesome 6
- **Security**: Password hashing, SQL injection prevention

## Struktur Database

### Tabel Utama
- `admin` - Data administrator
- `konsumen` - Data konsumen/penjual
- `kategori` - Kategori produk
- `produk` - Data produk
- `order` - Data pesanan
- `order_detail` - Detail item dalam pesanan

## Instalasi

### 1. Persyaratan Sistem
- PHP 7.4 atau lebih baru
- MySQL 5.7 atau lebih baru
- Web server (Apache/Nginx)
- Extension PHP: PDO, PDO_MySQL, GD

### 2. Download dan Setup
```bash
# Clone atau download aplikasi
git clone [repository-url] toko-abc
cd toko-abc

# Atau extract file zip ke folder web server
```

### 3. Instalasi Otomatis (Recommended)
1. Akses `http://localhost/tokovr2/install.php`
2. Isi form konfigurasi database
3. Klik "Install Aplikasi"
4. Hapus file `install.php` setelah instalasi selesai

### 4. Instalasi Manual
```sql
-- Import file database.sql ke MySQL
mysql -u root -p < database.sql
```

Edit file `config/database.php`:
```php
private $host = 'localhost';
private $db_name = 'toko_abc';
private $username = 'root';
private $password = 'your_password';
```

### 5. Set Permissions
```bash
# Set permission untuk folder uploads
chmod 755 uploads/
chmod 755 assets/
```

### 6. Akses Aplikasi
- **Website**: http://localhost/tokovr2/
- **Admin**: http://localhost/tokovr2/auth/login.php
  - Email: admin@tokoabc.com
  - Password: password

## Struktur File

```
tokovr2/
├── config/
│   ├── config.php          # Konfigurasi aplikasi
│   └── database.php        # Koneksi database
├── auth/
│   ├── login.php           # Halaman login
│   ├── register.php        # Halaman registrasi
│   └── logout.php          # Logout
├── admin/
│   ├── dashboard.php       # Dashboard admin
│   ├── produk.php          # Validasi produk
│   ├── kategori.php        # Kelola kategori
│   ├── order.php           # Kelola order
│   ├── order_detail.php    # Detail order admin
│   └── konsumen.php        # Kelola konsumen
├── produk/
│   ├── tambah.php          # Tambah produk
│   ├── daftar.php          # Daftar produk
│   ├── edit.php            # Edit produk
│   └── hapus.php           # Hapus produk
├── cart/
│   ├── index.php           # Keranjang belanja
│   ├── add.php             # Tambah ke keranjang
│   ├── update.php          # Update keranjang
│   ├── remove.php          # Hapus dari keranjang
│   └── checkout.php        # Checkout
├── order/
│   ├── riwayat.php         # Riwayat order
│   ├── detail.php          # Detail order
│   ├── cancel.php          # Batalkan order
│   ├── pay.php             # Bayar order
│   └── complete.php        # Selesaikan order
├── profil/
│   └── index.php           # Profil konsumen
├── assets/
│   └── no-image.svg        # Gambar default
├── uploads/                # Folder upload gambar
├── database.sql            # Struktur database
├── index.php               # Halaman utama
└── README.md               # Dokumentasi
```

## Cara Penggunaan

### 1. Sebagai Konsumen
1. **Daftar Akun**: Klik "Daftar" di halaman utama
2. **Login**: Masukkan email dan password
3. **Jual Produk**: Klik "Jual Produk" untuk upload produk
4. **Belanja**: Browse produk, tambah ke keranjang, dan checkout
5. **Kelola Order**: Lihat riwayat dan status pesanan

### 2. Sebagai Admin
1. **Login Admin**: Pilih "Admin" saat login
2. **Validasi Produk**: Setujui atau tolak produk yang diupload
3. **Kelola Kategori**: Tambah, edit, atau hapus kategori
4. **Pantau Order**: Lihat dan kelola pesanan konsumen

## Fitur Keamanan

- **Password Hashing**: Menggunakan `password_hash()` dan `password_verify()`
- **SQL Injection Prevention**: Menggunakan prepared statements
- **XSS Protection**: Sanitasi input dengan `htmlspecialchars()`
- **Session Management**: Pengelolaan session yang aman
- **File Upload Validation**: Validasi tipe dan ukuran file

## Customization

### Mengubah Tema
Edit file CSS di setiap halaman atau buat file CSS terpisah:
```css
:root {
    --primary-color: #667eea;
    --secondary-color: #764ba2;
}
```

### Menambah Fitur
1. Buat file PHP baru di folder yang sesuai
2. Tambah route di navigation
3. Update database jika diperlukan
4. Test fitur baru

## Troubleshooting

### Error Koneksi Database
- Pastikan MySQL service berjalan
- Cek konfigurasi di `config/database.php`
- Pastikan database `toko_abc` sudah dibuat

### Error Upload Gambar
- Pastikan folder `uploads/` ada dan writable
- Cek permission folder (755 atau 777)
- Pastikan extension GD terinstall

### Error Session
- Pastikan session_start() dipanggil
- Cek konfigurasi PHP session
- Pastikan folder session writable

## Kontribusi

1. Fork repository
2. Buat feature branch
3. Commit perubahan
4. Push ke branch
5. Buat Pull Request

## Lisensi

Aplikasi ini dibuat untuk keperluan edukasi dan komersial. Silakan gunakan sesuai kebutuhan.

## Support

Untuk pertanyaan atau bantuan, silakan hubungi:
- Email: support@tokoabc.com
- Website: https://tokoabc.com

---

**Dibuat dengan ❤️ menggunakan PHP, MySQL, dan Bootstrap 5**
Admin: email admin@tokoabc.com, password password