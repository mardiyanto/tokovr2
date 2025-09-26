<?php
require_once '../config/config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('auth/login.php');
}

// Check if cart is not empty
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    redirect('index.php');
}

$error = '';
$success = '';

// Get cart items and calculate total
$cart_items = [];
$total = 0;

try {
    $database = new Database();
    $db = $database->getConnection();
    
    $product_ids = array_keys($_SESSION['cart']);
    $placeholders = str_repeat('?,', count($product_ids) - 1) . '?';
    
    $stmt = $db->prepare("SELECT p.*, k.nama_kategori FROM produk p 
                         LEFT JOIN kategori k ON p.id_kategori = k.id_kategori 
                         WHERE p.id_produk IN ($placeholders) AND p.status = 'acc'");
    $stmt->execute($product_ids);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($products as $product) {
        $quantity = $_SESSION['cart'][$product['id_produk']];
        $subtotal = $product['harga'] * $quantity;
        $total += $subtotal;
        
        $cart_items[] = [
            'product' => $product,
            'quantity' => $quantity,
            'subtotal' => $subtotal
        ];
    }
} catch (Exception $e) {
    $error = 'Terjadi kesalahan: ' . $e->getMessage();
}

// Process checkout
if ($_SERVER['REQUEST_METHOD'] == 'POST' && empty($error)) {
    $metode_pembayaran = sanitize($_POST['metode_pembayaran']);
    
    if (empty($metode_pembayaran)) {
        $error = 'Pilih metode pembayaran!';
    } else {
        try {
            $database = new Database();
            $db = $database->getConnection();
            
            // Start transaction
            $db->beginTransaction();
            
            // Create order
            $stmt = $db->prepare("INSERT INTO `order` (id_konsumen, total_harga, status_order) VALUES (?, ?, 'pending')");
            $stmt->execute([$_SESSION['user_id'], $total]);
            $order_id = $db->lastInsertId();
            
            // Create order details
            foreach ($cart_items as $item) {
                $stmt = $db->prepare("INSERT INTO order_detail (id_order, id_produk, jumlah, harga_satuan, subtotal) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([
                    $order_id,
                    $item['product']['id_produk'],
                    $item['quantity'],
                    $item['product']['harga'],
                    $item['subtotal']
                ]);
                
                // Update stock
                $stmt = $db->prepare("UPDATE produk SET stok = stok - ? WHERE id_produk = ?");
                $stmt->execute([$item['quantity'], $item['product']['id_produk']]);
            }
            
            // Commit transaction
            $db->commit();
            
            // Clear cart
            unset($_SESSION['cart']);
            
            $success = 'Order berhasil dibuat! ID Order: #' . $order_id;
            
        } catch (Exception $e) {
            $db->rollback();
            $error = 'Terjadi kesalahan: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Toko ABC</title>
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
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
        }
        .payment-method {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .payment-method:hover {
            border-color: #667eea;
        }
        .payment-method.selected {
            border-color: #667eea;
            background-color: #f8f9ff;
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
                <a class="nav-link" href="index.php">
                    <i class="fas fa-shopping-cart me-1"></i>Keranjang
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
                <h2 class="fw-bold mb-4">Checkout</h2>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success" role="alert">
                        <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
                    </div>
                    <div class="text-center">
                        <a href="../order/riwayat.php" class="btn btn-primary">
                            <i class="fas fa-list me-2"></i>Lihat Riwayat Order
                        </a>
                    </div>
                <?php else: ?>
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
                                                    <th>Harga</th>
                                                    <th>Jumlah</th>
                                                    <th>Subtotal</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($cart_items as $item): ?>
                                                    <tr>
                                                        <td>
                                                            <div class="d-flex align-items-center">
                                                                <img src="<?php echo $item['product']['gambar_produk'] ? '../uploads/' . $item['product']['gambar_produk'] : '../assets/no-image.jpg'; ?>" 
                                                                     class="product-image me-3" alt="<?php echo htmlspecialchars($item['product']['nama_produk']); ?>">
                                                                <div>
                                                                    <h6 class="mb-1"><?php echo htmlspecialchars($item['product']['nama_produk']); ?></h6>
                                                                    <small class="text-muted"><?php echo htmlspecialchars($item['product']['nama_kategori']); ?></small>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td><?php echo formatRupiah($item['product']['harga']); ?></td>
                                                        <td><?php echo $item['quantity']; ?></td>
                                                        <td><strong><?php echo formatRupiah($item['subtotal']); ?></strong></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <!-- Payment Method -->
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">
                                        <i class="fas fa-credit-card me-2"></i>Metode Pembayaran
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <form method="POST" id="checkoutForm">
                                        <div class="payment-method" onclick="selectPayment('transfer')">
                                            <input type="radio" name="metode_pembayaran" value="transfer" id="transfer" style="display: none;">
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-university fa-2x text-primary me-3"></i>
                                                <div>
                                                    <h6 class="mb-1">Transfer Bank</h6>
                                                    <small class="text-muted">Transfer ke rekening BCA, Mandiri, atau BNI</small>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="payment-method" onclick="selectPayment('cod')">
                                            <input type="radio" name="metode_pembayaran" value="cod" id="cod" style="display: none;">
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-money-bill-wave fa-2x text-success me-3"></i>
                                                <div>
                                                    <h6 class="mb-1">Cash on Delivery (COD)</h6>
                                                    <small class="text-muted">Bayar saat barang diterima</small>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="payment-method" onclick="selectPayment('ewallet')">
                                            <input type="radio" name="metode_pembayaran" value="ewallet" id="ewallet" style="display: none;">
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-mobile-alt fa-2x text-warning me-3"></i>
                                                <div>
                                                    <h6 class="mb-1">E-Wallet</h6>
                                                    <small class="text-muted">GoPay, OVO, DANA, atau LinkAja</small>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-lg-4">
                            <div class="card">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="mb-0">
                                        <i class="fas fa-receipt me-2"></i>Ringkasan Order
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Subtotal:</span>
                                        <span><?php echo formatRupiah($total); ?></span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Ongkir:</span>
                                        <span>Rp 0</span>
                                    </div>
                                    <hr>
                                    <div class="d-flex justify-content-between mb-3">
                                        <strong>Total:</strong>
                                        <strong class="text-success"><?php echo formatRupiah($total); ?></strong>
                                    </div>
                                    
                                    <button type="submit" form="checkoutForm" class="btn btn-primary w-100 mb-2">
                                        <i class="fas fa-credit-card me-2"></i>Proses Order
                                    </button>
                                    
                                    <a href="index.php" class="btn btn-outline-secondary w-100">
                                        <i class="fas fa-arrow-left me-2"></i>Kembali ke Keranjang
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function selectPayment(method) {
            // Remove selected class from all payment methods
            document.querySelectorAll('.payment-method').forEach(el => {
                el.classList.remove('selected');
            });
            
            // Add selected class to clicked payment method
            event.currentTarget.classList.add('selected');
            
            // Check the radio button
            document.getElementById(method).checked = true;
        }

        // Form validation
        document.getElementById('checkoutForm').addEventListener('submit', function(e) {
            const selectedPayment = document.querySelector('input[name="metode_pembayaran"]:checked');
            if (!selectedPayment) {
                e.preventDefault();
                alert('Pilih metode pembayaran terlebih dahulu!');
            }
        });
    </script>
</body>
</html>
