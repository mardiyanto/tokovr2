<?php
require_once 'config/config.php';

// Get products with category and only approved products
try {
    $database = new Database();
    $db = $database->getConnection();
    
    $search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
    $kategori = isset($_GET['kategori']) ? (int)$_GET['kategori'] : 0;
    
    $sql = "SELECT p.*, k.nama_kategori FROM produk p 
            LEFT JOIN kategori k ON p.id_kategori = k.id_kategori 
            WHERE p.status = 'acc'";
    
    $params = [];
    
    if (!empty($search)) {
        $sql .= " AND (p.nama_produk LIKE ? OR p.deskripsi LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }
    
    if ($kategori > 0) {
        $sql .= " AND p.id_kategori = ?";
        $params[] = $kategori;
    }
    
    $sql .= " ORDER BY p.created_at DESC";
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get categories for filter
    $stmt = $db->prepare("SELECT * FROM kategori ORDER BY nama_kategori");
    $stmt->execute();
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (Exception $e) {
    $products = [];
    $categories = [];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Toko ABC - E-Commerce Terpercaya</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .hero-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 80px 0;
        }
        .product-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: none;
            border-radius: 15px;
            overflow: hidden;
        }
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        .product-image {
            height: 200px;
            object-fit: cover;
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 10px;
        }
        .navbar-brand {
            font-weight: bold;
            font-size: 1.5rem;
        }
        .search-box {
            border-radius: 25px;
            border: 2px solid #e9ecef;
        }
        .search-box:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .category-filter {
            border-radius: 10px;
        }
        .cart-badge {
            position: absolute;
            top: -8px;
            right: -8px;
            background: #dc3545;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            font-size: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand text-primary" href="index.php">
                <i class="fas fa-store me-2"></i>Toko ABC
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Beranda</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="produk/tambah.php">Jual Produk</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="order/riwayat.php">Riwayat Order</a>
                    </li>
                </ul>
                
                <ul class="navbar-nav">
                    <?php if (isLoggedIn()): ?>
                        <li class="nav-item position-relative me-3">
                            <a class="nav-link" href="cart/index.php">
                                <i class="fas fa-shopping-cart"></i>
                                <span class="cart-badge" id="cart-count">0</span>
                            </a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user me-1"></i><?php echo $_SESSION['user_name']; ?>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="profil/index.php">Profil</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="auth/logout.php">Logout</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="auth/login.php">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="btn btn-primary btn-sm ms-2" href="auth/register.php">Daftar</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h1 class="display-4 fw-bold mb-4">Selamat Datang di Toko ABC</h1>
                    <p class="lead mb-4">Temukan produk terbaik dengan harga terjangkau. Belanja mudah, aman, dan terpercaya.</p>
                    <?php if (!isLoggedIn()): ?>
                        <a href="auth/register.php" class="btn btn-light btn-lg">
                            <i class="fas fa-user-plus me-2"></i>Daftar Sekarang
                        </a>
                    <?php endif; ?>
                </div>
                <div class="col-lg-6 text-center">
                    <i class="fas fa-shopping-bag" style="font-size: 200px; opacity: 0.3;"></i>
                </div>
            </div>
        </div>
    </section>

    <!-- Search and Filter -->
    <section class="py-4 bg-light">
        <div class="container">
            <div class="row">
                <div class="col-md-8">
                    <form method="GET" class="d-flex">
                        <input type="text" class="form-control search-box me-2" name="search" 
                               placeholder="Cari produk..." value="<?php echo htmlspecialchars($search); ?>">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i>
                        </button>
                    </form>
                </div>
                <div class="col-md-4">
                    <form method="GET" class="d-flex">
                        <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">
                        <select name="kategori" class="form-select category-filter me-2" onchange="this.form.submit()">
                            <option value="0">Semua Kategori</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['id_kategori']; ?>" 
                                        <?php echo $kategori == $category['id_kategori'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($category['nama_kategori']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Products Section -->
    <section class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-12 mb-4">
                    <h2 class="fw-bold">Produk Terbaru</h2>
                    <p class="text-muted">Temukan produk pilihan terbaik untuk kebutuhan Anda</p>
                </div>
            </div>
            
            <?php if (empty($products)): ?>
                <div class="row">
                    <div class="col-12 text-center py-5">
                        <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                        <h4 class="text-muted">Tidak ada produk ditemukan</h4>
                        <p class="text-muted">Coba gunakan kata kunci lain atau pilih kategori yang berbeda</p>
                    </div>
                </div>
            <?php else: ?>
                <div class="row">
                    <?php foreach ($products as $product): ?>
                        <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                            <div class="card product-card h-100">
                                <img src="<?php echo $product['gambar_produk'] ? 'uploads/' . $product['gambar_produk'] : 'assets/no-image.svg'; ?>" 
                                     class="card-img-top product-image" alt="<?php echo htmlspecialchars($product['nama_produk']); ?>">
                                <div class="card-body d-flex flex-column">
                                    <h6 class="card-title fw-bold"><?php echo htmlspecialchars($product['nama_produk']); ?></h6>
                                    <p class="card-text text-muted small flex-grow-1">
                                        <?php echo htmlspecialchars(substr($product['deskripsi'], 0, 100)) . '...'; ?>
                                    </p>
                                    <div class="mt-auto">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="badge bg-primary"><?php echo htmlspecialchars($product['nama_kategori']); ?></span>
                                            <span class="text-success fw-bold"><?php echo formatRupiah($product['harga']); ?></span>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <small class="text-muted">Stok: <?php echo $product['stok']; ?></small>
                                            <?php if (isLoggedIn() && $product['stok'] > 0): ?>
                                                <button class="btn btn-primary btn-sm" onclick="addToCart(<?php echo $product['id_produk']; ?>)">
                                                    <i class="fas fa-cart-plus"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5>Toko ABC</h5>
                    <p class="mb-0">E-Commerce terpercaya untuk kebutuhan Anda</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="mb-0">&copy; 2024 Toko ABC. All rights reserved.</p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Add to cart function
        function addToCart(productId) {
            fetch('cart/add.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    product_id: productId,
                    quantity: 1
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success message
                    const alert = document.createElement('div');
                    alert.className = 'alert alert-success alert-dismissible fade show position-fixed';
                    alert.style.top = '20px';
                    alert.style.right = '20px';
                    alert.style.zIndex = '9999';
                    alert.innerHTML = `
                        <i class="fas fa-check-circle me-2"></i>Produk ditambahkan ke keranjang!
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    `;
                    document.body.appendChild(alert);
                    
                    // Update cart count
                    updateCartCount();
                    
                    // Auto remove alert after 3 seconds
                    setTimeout(() => {
                        alert.remove();
                    }, 3000);
                } else {
                    alert('Gagal menambahkan produk ke keranjang');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan');
            });
        }

        // Update cart count
        function updateCartCount() {
            fetch('cart/count.php')
            .then(response => response.json())
            .then(data => {
                document.getElementById('cart-count').textContent = data.count;
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }

        // Load cart count on page load
        document.addEventListener('DOMContentLoaded', function() {
            updateCartCount();
        });
    </script>
</body>
</html>
