<?php
require_once '../config/config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('auth/login.php');
}

// Get user's orders
try {
    $database = new Database();
    $db = $database->getConnection();
    
    $stmt = $db->prepare("SELECT o.*, 
                         COUNT(od.id_detail) as total_items,
                         SUM(od.subtotal) as total_amount
                         FROM `order` o 
                         LEFT JOIN order_detail od ON o.id_order = od.id_order 
                         WHERE o.id_konsumen = ? 
                         GROUP BY o.id_order 
                         ORDER BY o.tanggal_order DESC");
    $stmt->execute([$_SESSION['user_id']]);
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
    <title>Riwayat Order - Toko ABC</title>
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
        .order-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .order-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }
        .empty-orders {
            text-align: center;
            padding: 60px 20px;
        }
        .empty-orders i {
            font-size: 4rem;
            color: #dee2e6;
            margin-bottom: 1rem;
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
                <a class="nav-link" href="../cart/index.php">
                    <i class="fas fa-shopping-cart me-1"></i>Keranjang
                </a>
                <a class="nav-link active" href="riwayat.php">
                    <i class="fas fa-list me-1"></i>Riwayat Order
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
                <h2 class="fw-bold mb-4">Riwayat Order</h2>
                
                <?php if (empty($orders)): ?>
                    <div class="card">
                        <div class="card-body">
                            <div class="empty-orders">
                                <i class="fas fa-shopping-bag"></i>
                                <h4 class="text-muted">Belum Ada Order</h4>
                                <p class="text-muted">Mulai belanja dan buat order pertama Anda</p>
                                <a href="../index.php" class="btn btn-primary">
                                    <i class="fas fa-shopping-bag me-2"></i>Mulai Belanja
                                </a>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="row">
                        <?php foreach ($orders as $order): ?>
                            <div class="col-lg-6 mb-4">
                                <div class="card order-card">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <div>
                                                <h5 class="card-title mb-1">Order #<?php echo $order['id_order']; ?></h5>
                                                <small class="text-muted">
                                                    <i class="fas fa-calendar me-1"></i>
                                                    <?php echo date('d/m/Y H:i', strtotime($order['tanggal_order'])); ?>
                                                </small>
                                            </div>
                                            <div>
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
                                            </div>
                                        </div>
                                        
                                        <div class="row mb-3">
                                            <div class="col-6">
                                                <small class="text-muted">Total Items</small>
                                                <div class="fw-bold"><?php echo $order['total_items']; ?> item</div>
                                            </div>
                                            <div class="col-6">
                                                <small class="text-muted">Total Harga</small>
                                                <div class="fw-bold text-success"><?php echo formatRupiah($order['total_harga']); ?></div>
                                            </div>
                                        </div>
                                        
                                        <div class="d-flex gap-2">
                                            <a href="detail.php?id=<?php echo $order['id_order']; ?>" 
                                               class="btn btn-outline-primary btn-sm flex-fill">
                                                <i class="fas fa-eye me-1"></i>Detail
                                            </a>
                                            <?php if ($order['status_order'] == 'pending'): ?>
                                                <button class="btn btn-outline-danger btn-sm" 
                                                        onclick="cancelOrder(<?php echo $order['id_order']; ?>)">
                                                    <i class="fas fa-times me-1"></i>Batal
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function cancelOrder(orderId) {
            if (confirm('Apakah Anda yakin ingin membatalkan order ini?')) {
                fetch('cancel.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        order_id: orderId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Gagal membatalkan order');
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
