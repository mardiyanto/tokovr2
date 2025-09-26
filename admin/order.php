<?php
require_once '../config/config.php';

// Check if user is admin
if (!isLoggedIn() || !isAdmin()) {
    redirect('auth/login.php');
}

// Handle order status update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $order_id = (int)$_POST['order_id'];
    $action = $_POST['action'];
    
    if ($action === 'update_status') {
        $new_status = $_POST['status'];
        try {
            $database = new Database();
            $db = $database->getConnection();
            
            $stmt = $db->prepare("UPDATE `order` SET status_order = ? WHERE id_order = ?");
            $stmt->execute([$new_status, $order_id]);
            
            $message = 'Status order berhasil diperbarui!';
            $alert_type = 'success';
        } catch (Exception $e) {
            $message = 'Terjadi kesalahan: ' . $e->getMessage();
            $alert_type = 'danger';
        }
    }
}

// Get orders with customer info
try {
    $database = new Database();
    $db = $database->getConnection();
    
    $status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';
    $search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
    
    $sql = "SELECT o.*, k.nama_lengkap, k.email, k.no_hp
            FROM `order` o 
            LEFT JOIN konsumen k ON o.id_konsumen = k.id_konsumen";
    
    $where_conditions = [];
    $params = [];
    
    if ($status_filter !== 'all') {
        $where_conditions[] = "o.status_order = ?";
        $params[] = $status_filter;
    }
    
    if (!empty($search)) {
        $where_conditions[] = "(k.nama_lengkap LIKE ? OR k.email LIKE ? OR o.id_order LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }
    
    if (!empty($where_conditions)) {
        $sql .= " WHERE " . implode(" AND ", $where_conditions);
    }
    
    $sql .= " ORDER BY o.tanggal_order DESC";
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (Exception $e) {
    $orders = [];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Order - Toko ABC</title>
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
                        <h2 class="fw-bold">Kelola Order</h2>
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
                            <form method="GET" class="row g-3">
                                <div class="col-md-4">
                                    <select name="status" class="form-select" onchange="this.form.submit()">
                                        <option value="all" <?php echo $status_filter === 'all' ? 'selected' : ''; ?>>Semua Status</option>
                                        <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Menunggu Pembayaran</option>
                                        <option value="dibayar" <?php echo $status_filter === 'dibayar' ? 'selected' : ''; ?>>Sudah Dibayar</option>
                                        <option value="dikirim" <?php echo $status_filter === 'dikirim' ? 'selected' : ''; ?>>Sedang Dikirim</option>
                                        <option value="selesai" <?php echo $status_filter === 'selesai' ? 'selected' : ''; ?>>Selesai</option>
                                        <option value="batal" <?php echo $status_filter === 'batal' ? 'selected' : ''; ?>>Dibatalkan</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <input type="text" class="form-control" name="search" 
                                           placeholder="Cari berdasarkan nama, email, atau ID order..." 
                                           value="<?php echo htmlspecialchars($search); ?>">
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Orders Table -->
                    <div class="card">
                        <div class="card-body">
                            <?php if (empty($orders)): ?>
                                <div class="text-center py-5">
                                    <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                                    <h4 class="text-muted">Tidak ada order</h4>
                                    <p class="text-muted">Belum ada order yang ditemukan</p>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th>ID Order</th>
                                                <th>Konsumen</th>
                                                <th>Total Harga</th>
                                                <th>Status</th>
                                                <th>Tanggal</th>
                                                <th>Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($orders as $order): ?>
                                                <tr>
                                                    <td>
                                                        <strong>#<?php echo $order['id_order']; ?></strong>
                                                    </td>
                                                    <td>
                                                        <div>
                                                            <h6 class="mb-1"><?php echo htmlspecialchars($order['nama_lengkap']); ?></h6>
                                                            <small class="text-muted"><?php echo htmlspecialchars($order['email']); ?></small>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <strong class="text-success"><?php echo formatRupiah($order['total_harga']); ?></strong>
                                                    </td>
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
                                                        <span class="badge <?php echo $status_class; ?> status-badge"><?php echo $status_text; ?></span>
                                                    </td>
                                                    <td>
                                                        <small class="text-muted"><?php echo date('d/m/Y H:i', strtotime($order['tanggal_order'])); ?></small>
                                                    </td>
                                                    <td>
                                                        <div class="btn-group" role="group">
                                                            <a href="order_detail.php?id=<?php echo $order['id_order']; ?>" 
                                                               class="btn btn-sm btn-outline-primary" title="Detail">
                                                                <i class="fas fa-eye"></i>
                                                            </a>
                                                            <button type="button" class="btn btn-sm btn-outline-success" 
                                                                    data-bs-toggle="modal" data-bs-target="#statusModal<?php echo $order['id_order']; ?>" title="Update Status">
                                                                <i class="fas fa-edit"></i>
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                                
                                                <!-- Status Update Modal -->
                                                <div class="modal fade" id="statusModal<?php echo $order['id_order']; ?>" tabindex="-1">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">Update Status Order #<?php echo $order['id_order']; ?></h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <form method="POST">
                                                                <div class="modal-body">
                                                                    <input type="hidden" name="action" value="update_status">
                                                                    <input type="hidden" name="order_id" value="<?php echo $order['id_order']; ?>">
                                                                    <div class="mb-3">
                                                                        <label for="status" class="form-label">Status Order</label>
                                                                        <select class="form-select" name="status" required>
                                                                            <option value="pending" <?php echo $order['status_order'] === 'pending' ? 'selected' : ''; ?>>Menunggu Pembayaran</option>
                                                                            <option value="dibayar" <?php echo $order['status_order'] === 'dibayar' ? 'selected' : ''; ?>>Sudah Dibayar</option>
                                                                            <option value="dikirim" <?php echo $order['status_order'] === 'dikirim' ? 'selected' : ''; ?>>Sedang Dikirim</option>
                                                                            <option value="selesai" <?php echo $order['status_order'] === 'selesai' ? 'selected' : ''; ?>>Selesai</option>
                                                                            <option value="batal" <?php echo $order['status_order'] === 'batal' ? 'selected' : ''; ?>>Dibatalkan</option>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                                    <button type="submit" class="btn btn-primary">Update Status</button>
                                                                </div>
                                                            </form>
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
