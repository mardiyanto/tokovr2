<?php
require_once '../config/config.php';

// Check if user is admin
if (!isLoggedIn() || !isAdmin()) {
    redirect('auth/login.php');
}

// Handle product validation
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $product_id = (int)$_POST['product_id'];
    $action = $_POST['action'];
    
    if ($action === 'approve' || $action === 'reject') {
        try {
            $database = new Database();
            $db = $database->getConnection();
            
            $status = $action === 'approve' ? 'acc' : 'ditolak';
            $stmt = $db->prepare("UPDATE produk SET status = ?, validated_by = ? WHERE id_produk = ?");
            $stmt->execute([$status, $_SESSION['user_id'], $product_id]);
            
            $message = $action === 'approve' ? 'Produk berhasil disetujui!' : 'Produk berhasil ditolak!';
            $alert_type = $action === 'approve' ? 'success' : 'warning';
        } catch (Exception $e) {
            $message = 'Terjadi kesalahan: ' . $e->getMessage();
            $alert_type = 'danger';
        }
    }
}

// Get products for validation
try {
    $database = new Database();
    $db = $database->getConnection();
    
    $status_filter = isset($_GET['status']) ? $_GET['status'] : 'pending';
    
    $sql = "SELECT p.*, k.nama_kategori, a.nama_admin as validator 
            FROM produk p 
            LEFT JOIN kategori k ON p.id_kategori = k.id_kategori 
            LEFT JOIN admin a ON p.validated_by = a.id_admin";
    
    if ($status_filter !== 'all') {
        $sql .= " WHERE p.status = ?";
        $params = [$status_filter];
    } else {
        $params = [];
    }
    
    $sql .= " ORDER BY p.created_at DESC";
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
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
    <title>Validasi Produk - Toko ABC</title>
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
        .product-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 10px;
        }
        .status-badge {
            font-size: 0.8rem;
        }
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            border-radius: 10px;
            margin-bottom: 5px;
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            color: white;
            background-color: rgba(255,255,255,0.1);
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 sidebar p-0">
                <div class="p-3">
                    <h4 class="text-white mb-4">
                        <i class="fas fa-store me-2"></i>Toko ABC
                    </h4>
                    <nav class="nav flex-column">
                        <a class="nav-link" href="dashboard.php">
                            <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                        </a>
                        <a class="nav-link active" href="produk.php">
                            <i class="fas fa-box me-2"></i>Validasi Produk
                        </a>
                        <a class="nav-link" href="kategori.php">
                            <i class="fas fa-tags me-2"></i>Kategori
                        </a>
                        <a class="nav-link" href="order.php">
                            <i class="fas fa-shopping-cart me-2"></i>Order
                        </a>
                        <a class="nav-link" href="konsumen.php">
                            <i class="fas fa-users me-2"></i>Konsumen
                        </a>
                        <hr class="text-white">
                        <a class="nav-link" href="../index.php">
                            <i class="fas fa-home me-2"></i>Kembali ke Website
                        </a>
                        <a class="nav-link" href="../auth/logout.php">
                            <i class="fas fa-sign-out-alt me-2"></i>Logout
                        </a>
                    </nav>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-9 col-lg-10">
                <div class="p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2 class="fw-bold">Validasi Produk</h2>
                        <div class="text-muted">
                            Selamat datang, <strong><?php echo $_SESSION['user_name']; ?></strong>
                        </div>
                    </div>
                    
                    <?php if (isset($message)): ?>
                        <div class="alert alert-<?php echo $alert_type; ?> alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i><?php echo $message; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Filter -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <form method="GET" class="d-flex gap-2">
                                <select name="status" class="form-select" style="width: auto;" onchange="this.form.submit()">
                                    <option value="all" <?php echo $status_filter === 'all' ? 'selected' : ''; ?>>Semua Status</option>
                                    <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Menunggu Validasi</option>
                                    <option value="acc" <?php echo $status_filter === 'acc' ? 'selected' : ''; ?>>Disetujui</option>
                                    <option value="ditolak" <?php echo $status_filter === 'ditolak' ? 'selected' : ''; ?>>Ditolak</option>
                                </select>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Products Table -->
                    <div class="card">
                        <div class="card-body">
                            <?php if (empty($products)): ?>
                                <div class="text-center py-5">
                                    <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                                    <h4 class="text-muted">Tidak ada produk</h4>
                                    <p class="text-muted">Belum ada produk yang perlu divalidasi</p>
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
                                                <th>Validator</th>
                                                <th>Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($products as $product): ?>
                                                <tr>
                                                    <td>
                                                        <img src="<?php echo $product['gambar_produk'] ? '../uploads/' . $product['gambar_produk'] : '../assets/no-image.jpg'; ?>" 
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
                                                        <small class="text-muted"><?php echo $product['validator'] ? htmlspecialchars($product['validator']) : '-'; ?></small>
                                                    </td>
                                                    <td>
                                                        <div class="btn-group" role="group">
                                                            <button type="button" class="btn btn-sm btn-outline-info" 
                                                                    data-bs-toggle="modal" data-bs-target="#detailModal<?php echo $product['id_produk']; ?>">
                                                                <i class="fas fa-eye"></i>
                                                            </button>
                                                            <?php if ($product['status'] === 'pending'): ?>
                                                                <form method="POST" style="display: inline;">
                                                                    <input type="hidden" name="product_id" value="<?php echo $product['id_produk']; ?>">
                                                                    <input type="hidden" name="action" value="approve">
                                                                    <button type="submit" class="btn btn-sm btn-outline-success" 
                                                                            onclick="return confirm('Setujui produk ini?')">
                                                                        <i class="fas fa-check"></i>
                                                                    </button>
                                                                </form>
                                                                <form method="POST" style="display: inline;">
                                                                    <input type="hidden" name="product_id" value="<?php echo $product['id_produk']; ?>">
                                                                    <input type="hidden" name="action" value="reject">
                                                                    <button type="submit" class="btn btn-sm btn-outline-danger" 
                                                                            onclick="return confirm('Tolak produk ini?')">
                                                                        <i class="fas fa-times"></i>
                                                                    </button>
                                                                </form>
                                                            <?php endif; ?>
                                                        </div>
                                                    </td>
                                                </tr>
                                                
                                                <!-- Detail Modal -->
                                                <div class="modal fade" id="detailModal<?php echo $product['id_produk']; ?>" tabindex="-1">
                                                    <div class="modal-dialog modal-lg">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">Detail Produk</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <div class="row">
                                                                    <div class="col-md-4">
                                                                        <img src="<?php echo $product['gambar_produk'] ? '../uploads/' . $product['gambar_produk'] : '../assets/no-image.jpg'; ?>" 
                                                                             class="img-fluid rounded" alt="<?php echo htmlspecialchars($product['nama_produk']); ?>">
                                                                    </div>
                                                                    <div class="col-md-8">
                                                                        <h5><?php echo htmlspecialchars($product['nama_produk']); ?></h5>
                                                                        <p class="text-muted"><?php echo htmlspecialchars($product['deskripsi']); ?></p>
                                                                        <div class="row">
                                                                            <div class="col-6">
                                                                                <strong>Kategori:</strong><br>
                                                                                <span class="badge bg-secondary"><?php echo htmlspecialchars($product['nama_kategori']); ?></span>
                                                                            </div>
                                                                            <div class="col-6">
                                                                                <strong>Harga:</strong><br>
                                                                                <span class="text-success fw-bold"><?php echo formatRupiah($product['harga']); ?></span>
                                                                            </div>
                                                                        </div>
                                                                        <div class="row mt-2">
                                                                            <div class="col-6">
                                                                                <strong>Stok:</strong><br>
                                                                                <span class="badge bg-info"><?php echo $product['stok']; ?></span>
                                                                            </div>
                                                                            <div class="col-6">
                                                                                <strong>Status:</strong><br>
                                                                                <span class="badge <?php echo $status_class; ?>"><?php echo $status_text; ?></span>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
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
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
