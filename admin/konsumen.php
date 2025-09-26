<?php
require_once '../config/config.php';

// Check if user is admin
if (!isLoggedIn() || !isAdmin()) {
    redirect('auth/login.php');
}

// Handle customer actions
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $customer_id = (int)$_POST['customer_id'];
    $action = $_POST['action'];
    
    if ($action === 'delete') {
        try {
            $database = new Database();
            $db = $database->getConnection();
            
            // Check if customer has orders
            $stmt = $db->prepare("SELECT COUNT(*) as count FROM `order` WHERE id_konsumen = ?");
            $stmt->execute([$customer_id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result['count'] > 0) {
                $message = 'Konsumen tidak dapat dihapus karena memiliki order!';
                $alert_type = 'danger';
            } else {
            $stmt = $db->prepare("DELETE FROM konsumen WHERE id_konsumen = ?");
            $stmt->execute([$customer_id]);
            
            $message = 'Konsumen berhasil dihapus!';
            $alert_type = 'success';
            }
        } catch (Exception $e) {
            $message = 'Terjadi kesalahan: ' . $e->getMessage();
            $alert_type = 'danger';
        }
    }
}

// Get customers with statistics
try {
    $database = new Database();
    $db = $database->getConnection();
    
    $search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
    
    $sql = "SELECT k.*, 
                   COUNT(DISTINCT o.id_order) as total_orders,
                   COALESCE(SUM(o.total_harga), 0) as total_spent
            FROM konsumen k 
            LEFT JOIN `order` o ON k.id_konsumen = o.id_konsumen";
    
    $where_conditions = [];
    $params = [];
    
    if (!empty($search)) {
        $where_conditions[] = "(k.nama_lengkap LIKE ? OR k.email LIKE ? OR k.no_hp LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }
    
    if (!empty($where_conditions)) {
        $sql .= " WHERE " . implode(" AND ", $where_conditions);
    }
    
    $sql .= " GROUP BY k.id_konsumen ORDER BY k.tanggal_daftar DESC";
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (Exception $e) {
    $customers = [];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Konsumen - Toko ABC</title>
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
        .customer-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 18px;
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
                        <a class="nav-link" href="order.php">
                            <i class="fas fa-shopping-cart me-2"></i>Order
                        </a>
                        <a class="nav-link active" href="konsumen.php">
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
                        <h2 class="fw-bold">Kelola Konsumen</h2>
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
                    
                    <!-- Search -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <form method="GET" class="d-flex gap-2">
                                <input type="text" class="form-control" name="search" 
                                       placeholder="Cari berdasarkan nama, email, atau no. HP..." 
                                       value="<?php echo htmlspecialchars($search); ?>">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Customers Table -->
                    <div class="card">
                        <div class="card-body">
                            <?php if (empty($customers)): ?>
                                <div class="text-center py-5">
                                    <i class="fas fa-users fa-3x text-muted mb-3"></i>
                                    <h4 class="text-muted">Tidak ada konsumen</h4>
                                    <p class="text-muted">Belum ada konsumen yang terdaftar</p>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Konsumen</th>
                                                <th>Kontak</th>
                                                <th>Total Order</th>
                                                <th>Total Belanja</th>
                                                <th>Tanggal Daftar</th>
                                                <th>Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($customers as $customer): ?>
                                                <tr>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <div class="customer-avatar me-3">
                                                                <?php echo strtoupper(substr($customer['nama_lengkap'], 0, 1)); ?>
                                                            </div>
                                                            <div>
                                                                <h6 class="mb-1"><?php echo htmlspecialchars($customer['nama_lengkap']); ?></h6>
                                                                <small class="text-muted">ID: <?php echo $customer['id_konsumen']; ?></small>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div>
                                                            <div class="mb-1">
                                                                <i class="fas fa-envelope me-1"></i>
                                                                <?php echo htmlspecialchars($customer['email']); ?>
                                                            </div>
                                                            <div>
                                                                <i class="fas fa-phone me-1"></i>
                                                                <?php echo htmlspecialchars($customer['no_hp']); ?>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-info"><?php echo $customer['total_orders']; ?> order</span>
                                                    </td>
                                                    <td>
                                                        <strong class="text-success"><?php echo formatRupiah($customer['total_spent']); ?></strong>
                                                    </td>
                                                    <td>
                                                        <small class="text-muted"><?php echo date('d/m/Y', strtotime($customer['tanggal_daftar'])); ?></small>
                                                    </td>
                                                    <td>
                                                        <div class="btn-group" role="group">
                                                            <button type="button" class="btn btn-sm btn-outline-info" 
                                                                    data-bs-toggle="modal" data-bs-target="#detailModal<?php echo $customer['id_konsumen']; ?>" title="Detail">
                                                                <i class="fas fa-eye"></i>
                                                            </button>
                                                            <form method="POST" style="display: inline;">
                                                                <input type="hidden" name="action" value="delete">
                                                                <input type="hidden" name="customer_id" value="<?php echo $customer['id_konsumen']; ?>">
                                                                <button type="submit" class="btn btn-sm btn-outline-danger" 
                                                                        onclick="return confirm('Hapus konsumen ini?')" title="Hapus">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            </form>
                                                        </div>
                                                    </td>
                                                </tr>
                                                
                                                <!-- Detail Modal -->
                                                <div class="modal fade" id="detailModal<?php echo $customer['id_konsumen']; ?>" tabindex="-1">
                                                    <div class="modal-dialog modal-lg">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">Detail Konsumen</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <div class="row">
                                                                    <div class="col-md-4 text-center">
                                                                        <div class="customer-avatar mx-auto mb-3" style="width: 80px; height: 80px; font-size: 32px;">
                                                                            <?php echo strtoupper(substr($customer['nama_lengkap'], 0, 1)); ?>
                                                                        </div>
                                                                        <h5><?php echo htmlspecialchars($customer['nama_lengkap']); ?></h5>
                                                                        <p class="text-muted">ID: <?php echo $customer['id_konsumen']; ?></p>
                                                                    </div>
                                                                    <div class="col-md-8">
                                                                        <div class="row">
                                                                            <div class="col-6 mb-3">
                                                                                <strong>Email:</strong><br>
                                                                                <?php echo htmlspecialchars($customer['email']); ?>
                                                                            </div>
                                                                            <div class="col-6 mb-3">
                                                                                <strong>No. HP:</strong><br>
                                                                                <?php echo htmlspecialchars($customer['no_hp']); ?>
                                                                            </div>
                                                                            <div class="col-12 mb-3">
                                                                                <strong>Alamat:</strong><br>
                                                                                <?php echo htmlspecialchars($customer['alamat_lengkap']); ?>
                                                                            </div>
                                                                            <div class="col-6 mb-3">
                                                                                <strong>Total Order:</strong><br>
                                                                                <span class="badge bg-info"><?php echo $customer['total_orders']; ?> order</span>
                                                                            </div>
                                                                            <div class="col-6 mb-3">
                                                                                <strong>Total Belanja:</strong><br>
                                                                                <span class="text-success fw-bold"><?php echo formatRupiah($customer['total_spent']); ?></span>
                                                                            </div>
                                                                            <div class="col-6 mb-3">
                                                                                <strong>Tanggal Daftar:</strong><br>
                                                                                <?php echo date('d/m/Y H:i', strtotime($customer['tanggal_daftar'])); ?>
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
