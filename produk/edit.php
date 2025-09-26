<?php
require_once '../config/config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('auth/login.php');
}

$error = '';
$success = '';

// Get product ID
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($product_id <= 0) {
    redirect('daftar.php');
}

// Get product data
try {
    $database = new Database();
    $db = $database->getConnection();
    
    $stmt = $db->prepare("SELECT * FROM produk WHERE id_produk = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$product) {
        redirect('daftar.php');
    }
    
    // Check if product is still pending (can be edited)
    if ($product['status'] !== 'pending') {
        $error = 'Produk yang sudah disetujui atau ditolak tidak dapat diedit!';
    }
} catch (Exception $e) {
    redirect('daftar.php');
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && empty($error)) {
    $id_kategori = (int)$_POST['id_kategori'];
    $nama_produk = sanitize($_POST['nama_produk']);
    $deskripsi = sanitize($_POST['deskripsi']);
    $harga = (float)$_POST['harga'];
    $stok = (int)$_POST['stok'];
    
    // Validation
    if (empty($nama_produk) || empty($deskripsi) || $harga <= 0 || $stok < 0) {
        $error = 'Semua field harus diisi dengan benar!';
    } else {
        try {
            $database = new Database();
            $db = $database->getConnection();
            
            // Handle file upload
            $gambar_produk = $product['gambar_produk']; // Keep existing image
            
            if (isset($_FILES['gambar_produk']) && $_FILES['gambar_produk']['error'] == 0) {
                $upload_dir = UPLOAD_DIR;
                $file_extension = strtolower(pathinfo($_FILES['gambar_produk']['name'], PATHINFO_EXTENSION));
                $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
                
                if (in_array($file_extension, $allowed_extensions)) {
                    // Delete old image if exists
                    if (!empty($product['gambar_produk']) && file_exists($upload_dir . $product['gambar_produk'])) {
                        unlink($upload_dir . $product['gambar_produk']);
                    }
                    
                    $gambar_produk = uniqid() . '.' . $file_extension;
                    $upload_path = $upload_dir . $gambar_produk;
                    
                    if (move_uploaded_file($_FILES['gambar_produk']['tmp_name'], $upload_path)) {
                        // File uploaded successfully
                    } else {
                        $error = 'Gagal mengupload gambar!';
                    }
                } else {
                    $error = 'Format gambar tidak didukung! Gunakan JPG, PNG, atau GIF.';
                }
            }
            
            if (empty($error)) {
                // Update product
                $stmt = $db->prepare("UPDATE produk SET id_kategori = ?, nama_produk = ?, deskripsi = ?, harga = ?, stok = ?, gambar_produk = ?, updated_at = CURRENT_TIMESTAMP WHERE id_produk = ?");
                $stmt->execute([$id_kategori, $nama_produk, $deskripsi, $harga, $stok, $gambar_produk, $product_id]);
                
                $success = 'Produk berhasil diperbarui!';
                
                // Refresh product data
                $stmt = $db->prepare("SELECT * FROM produk WHERE id_produk = ?");
                $stmt->execute([$product_id]);
                $product = $stmt->fetch(PDO::FETCH_ASSOC);
            }
        } catch (Exception $e) {
            $error = 'Terjadi kesalahan: ' . $e->getMessage();
        }
    }
}

// Get categories
try {
    $database = new Database();
    $db = $database->getConnection();
    $stmt = $db->prepare("SELECT * FROM kategori ORDER BY nama_kategori");
    $stmt->execute();
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $categories = [];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Produk - Toko ABC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 10px;
        }
        .form-control, .form-select {
            border-radius: 10px;
            border: 2px solid #e9ecef;
        }
        .form-control:focus, .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .navbar-brand {
            font-weight: bold;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand text-primary" href="../index.php">
                <i class="fas fa-store me-2"></i>Toko ABC
            </a>
            
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="../index.php">
                    <i class="fas fa-home me-1"></i>Beranda
                </a>
                <a class="nav-link" href="daftar.php">
                    <i class="fas fa-list me-1"></i>Produk Saya
                </a>
                <a class="nav-link" href="../auth/logout.php">
                    <i class="fas fa-sign-out-alt me-1"></i>Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">
                            <i class="fas fa-edit me-2"></i>Edit Produk
                        </h4>
                    </div>
                    <div class="card-body p-4">
                        <?php if ($error): ?>
                            <div class="alert alert-danger" role="alert">
                                <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($success): ?>
                            <div class="alert alert-success" role="alert">
                                <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" enctype="multipart/form-data">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="nama_produk" class="form-label">Nama Produk</label>
                                        <input type="text" class="form-control" id="nama_produk" name="nama_produk" 
                                               value="<?php echo htmlspecialchars($product['nama_produk']); ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="id_kategori" class="form-label">Kategori</label>
                                        <select class="form-select" id="id_kategori" name="id_kategori" required>
                                            <option value="">Pilih Kategori</option>
                                            <?php foreach ($categories as $category): ?>
                                                <option value="<?php echo $category['id_kategori']; ?>" 
                                                        <?php echo $product['id_kategori'] == $category['id_kategori'] ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($category['nama_kategori']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="deskripsi" class="form-label">Deskripsi Produk</label>
                                <textarea class="form-control" id="deskripsi" name="deskripsi" rows="4" required><?php echo htmlspecialchars($product['deskripsi']); ?></textarea>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="harga" class="form-label">Harga (Rp)</label>
                                        <input type="number" class="form-control" id="harga" name="harga" 
                                               value="<?php echo $product['harga']; ?>" min="0" step="100" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="stok" class="form-label">Stok</label>
                                        <input type="number" class="form-control" id="stok" name="stok" 
                                               value="<?php echo $product['stok']; ?>" min="0" required>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="gambar_produk" class="form-label">Gambar Produk</label>
                                <?php if (!empty($product['gambar_produk'])): ?>
                                    <div class="mb-2">
                                        <img src="../uploads/<?php echo $product['gambar_produk']; ?>" 
                                             class="img-thumbnail" style="max-width: 200px; max-height: 200px;" alt="Current image">
                                        <p class="text-muted small">Gambar saat ini</p>
                                    </div>
                                <?php endif; ?>
                                <input type="file" class="form-control" id="gambar_produk" name="gambar_produk" accept="image/*">
                                <div class="form-text">Format yang didukung: JPG, PNG, GIF. Maksimal 2MB. Kosongkan jika tidak ingin mengubah gambar.</div>
                            </div>

                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="daftar.php" class="btn btn-secondary me-md-2">
                                    <i class="fas fa-arrow-left me-2"></i>Kembali
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Update Produk
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Format harga input
        document.getElementById('harga').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            e.target.value = value;
        });

        // Preview gambar
        document.getElementById('gambar_produk').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    // Create preview if not exists
                    let preview = document.getElementById('image-preview');
                    if (!preview) {
                        preview = document.createElement('div');
                        preview.id = 'image-preview';
                        preview.className = 'mt-2';
                        e.target.parentNode.appendChild(preview);
                    }
                    preview.innerHTML = `
                        <img src="${e.target.result}" class="img-thumbnail" style="max-width: 200px; max-height: 200px;">
                        <p class="text-muted small">Gambar baru</p>
                    `;
                };
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>
</html>
