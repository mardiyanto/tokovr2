<?php
require_once '../config/config.php';

// Check if user is admin
if (!isLoggedIn() || !isAdmin()) {
    redirect('auth/login.php');
}

// Get order ID
$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($order_id <= 0) {
    redirect('order.php');
}

// Get order details
try {
    $database = new Database();
    $db = $database->getConnection();
    
    // Get order info
    $stmt = $db->prepare("SELECT o.*, k.nama_lengkap, k.email, k.no_hp, k.alamat_lengkap 
                         FROM `order` o 
                         LEFT JOIN konsumen k ON o.id_konsumen = k.id_konsumen 
                         WHERE o.id_order = ?");
    $stmt->execute([$order_id]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$order) {
        redirect('order.php');
    }
    
    // Get order items
    $stmt = $db->prepare("SELECT od.*, p.nama_produk, p.gambar_produk, k.nama_kategori 
                         FROM order_detail od 
                         LEFT JOIN produk p ON od.id_produk = p.id_produk 
                         LEFT JOIN kategori k ON p.id_kategori = k.id_kategori 
                         WHERE od.id_order = ?");
    $stmt->execute([$order_id]);
    $order_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (Exception $e) {
    redirect('order.php');
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Order - Toko ABC</title>
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
            font-size: 0.9rem;
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
                        <a class="nav-link" href="produk.php">
                            <i class="fas fa-box me-2"></i>Validasi Produk
                        </a>
                        <a class="nav-link" href="kategori.php">
                            <i class="fas fa-tags me-2"></i>Kategori
                        </a>
                        <a class="nav-link active" href="order.php">
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
                        <h2 class="fw-bold">Detail Order #<?php echo $order['id_order']; ?></h2>
                        <a href="order.php" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Kembali
                        </a>
                    </div>
                    
                    <div class="row">
                        <div class="col-lg-8">
                            <!-- Order Items -->
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="mb-0">
                                        <i class="fas fa-shopping-bag me-2"></i>Item Pesanan
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Produk</th>
                                                    <th>Harga Satuan</th>
                                                    <th>Jumlah</th>
                                                    <th>Subtotal</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($order_items as $item): ?>
                                                    <tr>
                                                        <td>
                                                            <div class="d-flex align-items-center">
                                                                <img src="<?php echo $item['gambar_produk'] ? '../uploads/' . $item['gambar_produk'] : '../assets/no-image.svg'; ?>" 
                                                                     class="product-image me-3" alt="<?php echo htmlspecialchars($item['nama_produk']); ?>">
                                                                <div>
                                                                    <h6 class="mb-1"><?php echo htmlspecialchars($item['nama_produk']); ?></h6>
                                                                    <small class="text-muted"><?php echo htmlspecialchars($item['nama_kategori']); ?></small>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td><?php echo formatRupiah($item['harga_satuan']); ?></td>
                                                        <td><?php echo $item['jumlah']; ?></td>
                                                        <td><strong><?php echo formatRupiah($item['subtotal']); ?></strong></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-lg-4">
                            <!-- Order Summary -->
                            <div class="card mb-4">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="mb-0">
                                        <i class="fas fa-receipt me-2"></i>Ringkasan Order
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <small class="text-muted">ID Order</small>
                                        <div class="fw-bold">#<?php echo $order['id_order']; ?></div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <small class="text-muted">Tanggal Order</small>
                                        <div class="fw-bold"><?php echo date('d/m/Y H:i', strtotime($order['tanggal_order'])); ?></div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <small class="text-muted">Status</small>
                                        <div>
                                            <?php
                                            $status_class = '';
                                            $status_text = '';
                                            switch ($order['status_order']) {
                                                case 'pending':
                                                    $status_class = 'bg-warning';
                                                    $status_text = 'Menunggu Pembayaran';
                                                    break;
                                                case 'dibayar':
                                                    $status_class = 'bg-info';
                                                    $status_text = 'Sudah Dibayar';
                                                    break;
                                                case 'dikirim':
                                                    $status_class = 'bg-primary';
                                                    $status_text = 'Sedang Dikirim';
                                                    break;
                                                case 'selesai':
                                                    $status_class = 'bg-success';
                                                    $status_text = 'Selesai';
                                                    break;
                                                case 'batal':
                                                    $status_class = 'bg-danger';
                                                    $status_text = 'Dibatalkan';
                                                    break;
                                            }
                                            ?>
                                            <span class="badge <?php echo $status_class; ?> status-badge"><?php echo $status_text; ?></span>
                                        </div>
                                    </div>
                                    
                                    <hr>
                                    
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Subtotal:</span>
                                        <span><?php echo formatRupiah($order['total_harga']); ?></span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Ongkir:</span>
                                        <span>Rp 0</span>
                                    </div>
                                    <hr>
                                    <div class="d-flex justify-content-between mb-3">
                                        <strong>Total:</strong>
                                        <strong class="text-success"><?php echo formatRupiah($order['total_harga']); ?></strong>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Customer Info -->
                            <div class="card">
                                <div class="card-header bg-info text-white">
                                    <h5 class="mb-0">
                                        <i class="fas fa-user me-2"></i>Info Konsumen
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <small class="text-muted">Nama Lengkap</small>
                                        <div class="fw-bold"><?php echo htmlspecialchars($order['nama_lengkap']); ?></div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <small class="text-muted">Email</small>
                                        <div><?php echo htmlspecialchars($order['email']); ?></div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <small class="text-muted">No. HP</small>
                                        <div><?php echo htmlspecialchars($order['no_hp']); ?></div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <small class="text-muted">Alamat</small>
                                        <div><?php echo htmlspecialchars($order['alamat_lengkap']); ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
