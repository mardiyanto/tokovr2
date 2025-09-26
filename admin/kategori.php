<?php
require_once '../config/config.php';

// Check if user is admin
if (!isLoggedIn() || !isAdmin()) {
    redirect('auth/login.php');
}

$error = '';
$success = '';

// Handle add category
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $nama_kategori = sanitize($_POST['nama_kategori']);
    $deskripsi = sanitize($_POST['deskripsi']);
    
    if (empty($nama_kategori)) {
        $error = 'Nama kategori harus diisi!';
    } else {
        try {
            $database = new Database();
            $db = $database->getConnection();
            
            $stmt = $db->prepare("INSERT INTO kategori (nama_kategori, deskripsi) VALUES (?, ?)");
            $stmt->execute([$nama_kategori, $deskripsi]);
            
            $success = 'Kategori berhasil ditambahkan!';
        } catch (Exception $e) {
            $error = 'Terjadi kesalahan: ' . $e->getMessage();
        }
    }
}

// Handle edit category
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit') {
    $id_kategori = (int)$_POST['id_kategori'];
    $nama_kategori = sanitize($_POST['nama_kategori']);
    $deskripsi = sanitize($_POST['deskripsi']);
    
    if (empty($nama_kategori)) {
        $error = 'Nama kategori harus diisi!';
    } else {
        try {
            $database = new Database();
            $db = $database->getConnection();
            
            $stmt = $db->prepare("UPDATE kategori SET nama_kategori = ?, deskripsi = ? WHERE id_kategori = ?");
            $stmt->execute([$nama_kategori, $deskripsi, $id_kategori]);
            
            $success = 'Kategori berhasil diperbarui!';
        } catch (Exception $e) {
            $error = 'Terjadi kesalahan: ' . $e->getMessage();
        }
    }
}

// Handle delete category
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $id_kategori = (int)$_POST['id_kategori'];
    
    try {
        $database = new Database();
        $db = $database->getConnection();
        
        // Check if category is used by products
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM produk WHERE id_kategori = ?");
        $stmt->execute([$id_kategori]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result['count'] > 0) {
            $error = 'Kategori tidak dapat dihapus karena masih digunakan oleh produk!';
        } else {
            $stmt = $db->prepare("DELETE FROM kategori WHERE id_kategori = ?");
            $stmt->execute([$id_kategori]);
            
            $success = 'Kategori berhasil dihapus!';
        }
    } catch (Exception $e) {
        $error = 'Terjadi kesalahan: ' . $e->getMessage();
    }
}

// Get categories
try {
    $database = new Database();
    $db = $database->getConnection();
    
    $stmt = $db->prepare("SELECT k.*, COUNT(p.id_produk) as product_count 
                         FROM kategori k 
                         LEFT JOIN produk p ON k.id_kategori = p.id_kategori 
                         GROUP BY k.id_kategori 
                         ORDER BY k.nama_kategori");
    $stmt->execute();
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $categories = [];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Kategori - Toko ABC</title>
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
                        <a class="nav-link active" href="kategori.php">
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
                        <h2 class="fw-bold">Kelola Kategori</h2>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                            <i class="fas fa-plus me-2"></i>Tambah Kategori
                        </button>
                    </div>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($success): ?>
                        <div class="alert alert-success" role="alert">
                            <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Categories Table -->
                    <div class="card">
                        <div class="card-body">
                            <?php if (empty($categories)): ?>
                                <div class="text-center py-5">
                                    <i class="fas fa-tags fa-3x text-muted mb-3"></i>
                                    <h4 class="text-muted">Belum ada kategori</h4>
                                    <p class="text-muted">Tambahkan kategori pertama untuk mengorganisir produk</p>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th>ID</th>
                                                <th>Nama Kategori</th>
                                                <th>Deskripsi</th>
                                                <th>Jumlah Produk</th>
                                                <th>Tanggal Dibuat</th>
                                                <th>Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($categories as $category): ?>
                                                <tr>
                                                    <td><?php echo $category['id_kategori']; ?></td>
                                                    <td>
                                                        <strong><?php echo htmlspecialchars($category['nama_kategori']); ?></strong>
                                                    </td>
                                                    <td>
                                                        <span class="text-muted"><?php echo htmlspecialchars($category['deskripsi']); ?></span>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-info"><?php echo $category['product_count']; ?> produk</span>
                                                    </td>
                                                    <td>
                                                        <small class="text-muted"><?php echo date('d/m/Y', strtotime($category['created_at'])); ?></small>
                                                    </td>
                                                    <td>
                                                        <div class="btn-group" role="group">
                                                            <button type="button" class="btn btn-sm btn-outline-primary" 
                                                                    onclick="editCategory(<?php echo $category['id_kategori']; ?>, '<?php echo htmlspecialchars($category['nama_kategori']); ?>', '<?php echo htmlspecialchars($category['deskripsi']); ?>')">
                                                                <i class="fas fa-edit"></i>
                                                            </button>
                                                            <form method="POST" style="display: inline;">
                                                                <input type="hidden" name="action" value="delete">
                                                                <input type="hidden" name="id_kategori" value="<?php echo $category['id_kategori']; ?>">
                                                                <button type="submit" class="btn btn-sm btn-outline-danger" 
                                                                        onclick="return confirm('Hapus kategori ini?')">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            </form>
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
    </div>

    <!-- Add Category Modal -->
    <div class="modal fade" id="addCategoryModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Kategori</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add">
                        <div class="mb-3">
                            <label for="nama_kategori" class="form-label">Nama Kategori</label>
                            <input type="text" class="form-control" id="nama_kategori" name="nama_kategori" required>
                        </div>
                        <div class="mb-3">
                            <label for="deskripsi" class="form-label">Deskripsi</label>
                            <textarea class="form-control" id="deskripsi" name="deskripsi" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Category Modal -->
    <div class="modal fade" id="editCategoryModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Kategori</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="id_kategori" id="edit_id_kategori">
                        <div class="mb-3">
                            <label for="edit_nama_kategori" class="form-label">Nama Kategori</label>
                            <input type="text" class="form-control" id="edit_nama_kategori" name="nama_kategori" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_deskripsi" class="form-label">Deskripsi</label>
                            <textarea class="form-control" id="edit_deskripsi" name="deskripsi" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editCategory(id, nama, deskripsi) {
            document.getElementById('edit_id_kategori').value = id;
            document.getElementById('edit_nama_kategori').value = nama;
            document.getElementById('edit_deskripsi').value = deskripsi;
            
            var editModal = new bootstrap.Modal(document.getElementById('editCategoryModal'));
            editModal.show();
        }
    </script>
</body>
</html>
