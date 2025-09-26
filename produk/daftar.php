<?php
require_once '../config/config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('auth/login.php');
}

// Get user's products
try {
    $database = new Database();
    $db = $database->getConnection();
    
    $stmt = $db->prepare("SELECT p.*, k.nama_kategori FROM produk p 
                         LEFT JOIN kategori k ON p.id_kategori = k.id_kategori 
                         ORDER BY p.created_at DESC");
    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $products = [];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Produk - Toko ABC</title>
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
        .navbar-brand {
            font-weight: bold;
        }
        .status-badge {
            font-size: 0.8rem;
        }
        .product-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 10px;
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
                <a class="nav-link active" href="daftar.php">
                    <i class="fas fa-list me-1"></i>Produk Saya
                </a>
                <a class="nav-link" href="../auth/logout.php">
                    <i class="fas fa-sign-out-alt me-1"></i>Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="container py-5">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="fw-bold">Daftar Produk</h2>
                    <a href="tambah.php" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Tambah Produk
                    </a>
                </div>

                <div class="card">
                    <div class="card-body">
                        <?php if (empty($products)): ?>
                            <div class="text-center py-5">
                                <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                                <h4 class="text-muted">Belum ada produk</h4>
                                <p class="text-muted">Mulai jual produk Anda dengan menambahkan produk pertama</p>
                                <a href="tambah.php" class="btn btn-primary">
                                    <i class="fas fa-plus me-2"></i>Tambah Produk Pertama
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Gambar</th>
                                            <th>Nama Produk</th>
                                            <th>Kategori</th>
                                            <th>Harga</th>
                                            <th>Stok</th>
                                            <th>Status</th>
                                            <th>Tanggal</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($products as $product): ?>
                                            <tr>
                                                <td>
                                                    <img src="<?php echo $product['gambar_produk'] ? '../uploads/' . $product['gambar_produk'] : '../assets/no-image.svg'; ?>" 
                                                         class="product-image" alt="<?php echo htmlspecialchars($product['nama_produk']); ?>">
                                                </td>
                                                <td>
                                                    <div>
                                                        <h6 class="mb-1"><?php echo htmlspecialchars($product['nama_produk']); ?></h6>
                                                        <small class="text-muted"><?php echo htmlspecialchars(substr($product['deskripsi'], 0, 50)) . '...'; ?></small>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge bg-secondary"><?php echo htmlspecialchars($product['nama_kategori']); ?></span>
                                                </td>
                                                <td>
                                                    <strong class="text-success"><?php echo formatRupiah($product['harga']); ?></strong>
                                                </td>
                                                <td>
                                                    <span class="badge bg-info"><?php echo $product['stok']; ?></span>
                                                </td>
                                                <td>
                                                    <?php
                                                    $status_class = '';
                                                    $status_text = '';
                                                    switch ($product['status']) {
                                                        case 'pending':
                                                            $status_class = 'bg-warning';
                                                            $status_text = 'Menunggu';
                                                            break;
                                                        case 'acc':
                                                            $status_class = 'bg-success';
                                                            $status_text = 'Disetujui';
                                                            break;
                                                        case 'ditolak':
                                                            $status_class = 'bg-danger';
                                                            $status_text = 'Ditolak';
                                                            break;
                                                    }
                                                    ?>
                                                    <span class="badge <?php echo $status_class; ?> status-badge"><?php echo $status_text; ?></span>
                                                </td>
                                                <td>
                                                    <small class="text-muted"><?php echo date('d/m/Y', strtotime($product['created_at'])); ?></small>
                                                </td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <?php if ($product['status'] == 'pending'): ?>
                                                            <a href="edit.php?id=<?php echo $product['id_produk']; ?>" 
                                                               class="btn btn-sm btn-outline-primary" title="Edit">
                                                                <i class="fas fa-edit"></i>
                                                            </a>
                                                        <?php endif; ?>
                                                        <a href="detail.php?id=<?php echo $product['id_produk']; ?>" 
                                                           class="btn btn-sm btn-outline-info" title="Detail">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <button onclick="deleteProduct(<?php echo $product['id_produk']; ?>)" 
                                                                class="btn btn-sm btn-outline-danger" title="Hapus">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function deleteProduct(id) {
            if (confirm('Apakah Anda yakin ingin menghapus produk ini?')) {
                fetch('hapus.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        id: id
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Gagal menghapus produk');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan');
                });
            }
        }
    </script>
</body>
</html>
