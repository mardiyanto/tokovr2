<?php
require_once '../config/config.php';

// Check if user is admin
if (!isLoggedIn() || !isAdmin()) {
    redirect('auth/login.php');
}

// Get statistics
try {
    $database = new Database();
    $db = $database->getConnection();
    
    // Total products
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM produk");
    $stmt->execute();
    $total_products = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Pending products
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM produk WHERE status = 'pending'");
    $stmt->execute();
    $pending_products = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Total orders
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM `order`");
    $stmt->execute();
    $total_orders = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Total customers
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM konsumen");
    $stmt->execute();
    $total_customers = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Recent orders
    $stmt = $db->prepare("SELECT o.*, k.nama_lengkap FROM `order` o 
                         LEFT JOIN konsumen k ON o.id_konsumen = k.id_konsumen 
                         ORDER BY o.tanggal_order DESC LIMIT 5");
    $stmt->execute();
    $recent_orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (Exception $e) {
    $total_products = $pending_products = $total_orders = $total_customers = 0;
    $recent_orders = [];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Toko ABC</title>
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
        .stat-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: white;
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
                        <a class="nav-link active" href="dashboard.php">
                            <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                        </a>
                        <a class="nav-link" href="produk.php">
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
                        <h2 class="fw-bold">Dashboard Admin</h2>
                        <div class="text-muted">
                            Selamat datang, <strong><?php echo $_SESSION['user_name']; ?></strong>
                        </div>
                    </div>
                    
                    <!-- Statistics Cards -->
                    <div class="row mb-4">
                        <div class="col-md-3 mb-3">
                            <div class="card stat-card">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="stat-icon bg-primary me-3">
                                            <i class="fas fa-box"></i>
                                        </div>
                                        <div>
                                            <h6 class="text-muted mb-1">Total Produk</h6>
                                            <h4 class="mb-0"><?php echo $total_products; ?></h4>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3 mb-3">
                            <div class="card stat-card">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="stat-icon bg-warning me-3">
                                            <i class="fas fa-clock"></i>
                                        </div>
                                        <div>
                                            <h6 class="text-muted mb-1">Menunggu Validasi</h6>
                                            <h4 class="mb-0"><?php echo $pending_products; ?></h4>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3 mb-3">
                            <div class="card stat-card">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="stat-icon bg-success me-3">
                                            <i class="fas fa-shopping-cart"></i>
                                        </div>
                                        <div>
                                            <h6 class="text-muted mb-1">Total Order</h6>
                                            <h4 class="mb-0"><?php echo $total_orders; ?></h4>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3 mb-3">
                            <div class="card stat-card">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="stat-icon bg-info me-3">
                                            <i class="fas fa-users"></i>
                                        </div>
                                        <div>
                                            <h6 class="text-muted mb-1">Total Konsumen</h6>
                                            <h4 class="mb-0"><?php echo $total_customers; ?></h4>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Recent Orders -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">
                                        <i class="fas fa-clock me-2"></i>Order Terbaru
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <?php if (empty($recent_orders)): ?>
                                        <div class="text-center py-4">
                                            <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                                            <h5 class="text-muted">Belum ada order</h5>
                                        </div>
                                    <?php else: ?>
                                        <div class="table-responsive">
                                            <table class="table table-hover">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>ID Order</th>
                                                        <th>Konsumen</th>
                                                        <th>Total</th>
                                                        <th>Status</th>
                                                        <th>Tanggal</th>
                                                        <th>Aksi</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($recent_orders as $order): ?>
                                                        <tr>
                                                            <td>#<?php echo $order['id_order']; ?></td>
                                                            <td><?php echo htmlspecialchars($order['nama_lengkap']); ?></td>
                                                            <td><?php echo formatRupiah($order['total_harga']); ?></td>
                                                            <td>
                                                                <?php
                                                                $status_class = '';
                                                                $status_text = '';
                                                                switch ($order['status_order']) {
                                                                    case 'pending':
                                                                        $status_class = 'bg-warning';
                                                                        $status_text = 'Menunggu';
                                                                        break;
                                                                    case 'dibayar':
                                                                        $status_class = 'bg-info';
                                                                        $status_text = 'Dibayar';
                                                                        break;
                                                                    case 'dikirim':
                                                                        $status_class = 'bg-primary';
                                                                        $status_text = 'Dikirim';
                                                                        break;
                                                                    case 'selesai':
                                                                        $status_class = 'bg-success';
                                                                        $status_text = 'Selesai';
                                                                        break;
                                                                    case 'batal':
                                                                        $status_class = 'bg-danger';
                                                                        $status_text = 'Batal';
                                                                        break;
                                                                }
                                                                ?>
                                                                <span class="badge <?php echo $status_class; ?>"><?php echo $status_text; ?></span>
                                                            </td>
                                                            <td><?php echo date('d/m/Y H:i', strtotime($order['tanggal_order'])); ?></td>
                                                            <td>
                                                                <a href="order_detail.php?id=<?php echo $order['id_order']; ?>" 
                                                                   class="btn btn-sm btn-outline-primary">
                                                                    <i class="fas fa-eye"></i>
                                                                </a>
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
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
