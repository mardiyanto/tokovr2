<?php
require_once '../config/config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('auth/login.php');
}

// Get cart items
$cart_items = [];
$total = 0;

if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
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
        $cart_items = [];
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keranjang Belanja - Toko ABC</title>
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
        .quantity-input {
            width: 60px;
            text-align: center;
        }
        .empty-cart {
            text-align: center;
            padding: 60px 20px;
        }
        .empty-cart i {
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
                <a class="nav-link active" href="index.php">
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
                <h2 class="fw-bold mb-4">Keranjang Belanja</h2>
                
                <?php if (empty($cart_items)): ?>
                    <div class="card">
                        <div class="card-body">
                            <div class="empty-cart">
                                <i class="fas fa-shopping-cart"></i>
                                <h4 class="text-muted">Keranjang Anda Kosong</h4>
                                <p class="text-muted">Mulai belanja dan tambahkan produk ke keranjang</p>
                                <a href="../index.php" class="btn btn-primary">
                                    <i class="fas fa-shopping-bag me-2"></i>Mulai Belanja
                                </a>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="row">
                        <div class="col-lg-8">
                            <div class="card">
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Produk</th>
                                                    <th>Harga</th>
                                                    <th>Jumlah</th>
                                                    <th>Subtotal</th>
                                                    <th>Aksi</th>
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
                                                        <td>
                                                            <strong><?php echo formatRupiah($item['product']['harga']); ?></strong>
                                                        </td>
                                                        <td>
                                                            <div class="d-flex align-items-center">
                                                                <button class="btn btn-sm btn-outline-secondary" 
                                                                        onclick="updateQuantity(<?php echo $item['product']['id_produk']; ?>, <?php echo $item['quantity'] - 1; ?>)">
                                                                    <i class="fas fa-minus"></i>
                                                                </button>
                                                                <input type="number" class="form-control quantity-input mx-2" 
                                                                       value="<?php echo $item['quantity']; ?>" 
                                                                       onchange="updateQuantity(<?php echo $item['product']['id_produk']; ?>, this.value)"
                                                                       min="1" max="<?php echo $item['product']['stok']; ?>">
                                                                <button class="btn btn-sm btn-outline-secondary" 
                                                                        onclick="updateQuantity(<?php echo $item['product']['id_produk']; ?>, <?php echo $item['quantity'] + 1; ?>)">
                                                                    <i class="fas fa-plus"></i>
                                                                </button>
                                                            </div>
                                                            <small class="text-muted">Stok: <?php echo $item['product']['stok']; ?></small>
                                                        </td>
                                                        <td>
                                                            <strong class="text-success"><?php echo formatRupiah($item['subtotal']); ?></strong>
                                                        </td>
                                                        <td>
                                                            <button class="btn btn-sm btn-outline-danger" 
                                                                    onclick="removeFromCart(<?php echo $item['product']['id_produk']; ?>)">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-lg-4">
                            <div class="card">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="mb-0">
                                        <i class="fas fa-receipt me-2"></i>Ringkasan Belanja
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
                                    
                                    <a href="checkout.php" class="btn btn-primary w-100 mb-2">
                                        <i class="fas fa-credit-card me-2"></i>Checkout
                                    </a>
                                    
                                    <a href="../index.php" class="btn btn-outline-secondary w-100">
                                        <i class="fas fa-arrow-left me-2"></i>Lanjut Belanja
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
        function updateQuantity(productId, quantity) {
            if (quantity < 1) {
                removeFromCart(productId);
                return;
            }
            
            fetch('update.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    product_id: productId,
                    quantity: parseInt(quantity)
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Gagal mengupdate jumlah produk');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan');
            });
        }

        function removeFromCart(productId) {
            if (confirm('Apakah Anda yakin ingin menghapus produk ini dari keranjang?')) {
                fetch('remove.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        product_id: productId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Gagal menghapus produk dari keranjang');
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
