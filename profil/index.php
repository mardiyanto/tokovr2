<?php
require_once '../../config/config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('auth/login.php');
}

$error = '';
$success = '';

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_lengkap = sanitize($_POST['nama_lengkap']);
    $alamat_lengkap = sanitize($_POST['alamat_lengkap']);
    $no_hp = sanitize($_POST['no_hp']);
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validation
    if (empty($nama_lengkap) || empty($alamat_lengkap) || empty($no_hp) || empty($email)) {
        $error = 'Semua field harus diisi!';
    } elseif (!empty($password) && $password !== $confirm_password) {
        $error = 'Password tidak sama!';
    } elseif (!empty($password) && strlen($password) < 6) {
        $error = 'Password minimal 6 karakter!';
    } else {
        try {
            $database = new Database();
            $db = $database->getConnection();
            
            // Check if email already exists (except current user)
            $stmt = $db->prepare("SELECT id_konsumen FROM konsumen WHERE email = ? AND id_konsumen != ?");
            $stmt->execute([$email, $_SESSION['user_id']]);
            
            if ($stmt->rowCount() > 0) {
                $error = 'Email sudah digunakan oleh konsumen lain!';
            } else {
                // Update profile
                if (!empty($password)) {
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $db->prepare("UPDATE konsumen SET nama_lengkap = ?, alamat_lengkap = ?, no_hp = ?, email = ?, password = ? WHERE id_konsumen = ?");
                    $stmt->execute([$nama_lengkap, $alamat_lengkap, $no_hp, $email, $hashed_password, $_SESSION['user_id']]);
                } else {
                    $stmt = $db->prepare("UPDATE konsumen SET nama_lengkap = ?, alamat_lengkap = ?, no_hp = ?, email = ? WHERE id_konsumen = ?");
                    $stmt->execute([$nama_lengkap, $alamat_lengkap, $no_hp, $email, $_SESSION['user_id']]);
                }
                
                // Update session
                $_SESSION['user_name'] = $nama_lengkap;
                $_SESSION['user_email'] = $email;
                
                $success = 'Profil berhasil diperbarui!';
            }
        } catch (Exception $e) {
            $error = 'Terjadi kesalahan: ' . $e->getMessage();
        }
    }
}

// Get user data
try {
    $database = new Database();
    $db = $database->getConnection();
    
    $stmt = $db->prepare("SELECT * FROM konsumen WHERE id_konsumen = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        redirect('auth/logout.php');
    }
} catch (Exception $e) {
    redirect('auth/logout.php');
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil - Toko ABC</title>
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
        .profile-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 48px;
            margin: 0 auto 20px;
        }
        .form-control {
            border-radius: 10px;
            border: 2px solid #e9ecef;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand text-primary" href="../../index.php">
                <i class="fas fa-store me-2"></i>Toko ABC
            </a>
            
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="../../index.php">
                    <i class="fas fa-home me-1"></i>Beranda
                </a>
                <a class="nav-link" href="../produk/daftar.php">
                    <i class="fas fa-list me-1"></i>Produk Saya
                </a>
                <a class="nav-link" href="../cart/index.php">
                    <i class="fas fa-shopping-cart me-1"></i>Keranjang
                </a>
                <a class="nav-link active" href="index.php">
                    <i class="fas fa-user me-1"></i>Profil
                </a>
                <a class="nav-link" href="../../auth/logout.php">
                    <i class="fas fa-sign-out-alt me-1"></i>Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">
                            <i class="fas fa-user me-2"></i>Profil Saya
                        </h4>
                    </div>
                    <div class="card-body p-4">
                        <!-- Profile Avatar -->
                        <div class="text-center mb-4">
                            <div class="profile-avatar">
                                <?php echo strtoupper(substr($user['nama_lengkap'], 0, 1)); ?>
                            </div>
                            <h5><?php echo htmlspecialchars($user['nama_lengkap']); ?></h5>
                            <p class="text-muted"><?php echo htmlspecialchars($user['email']); ?></p>
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

                        <form method="POST">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="nama_lengkap" class="form-label">Nama Lengkap</label>
                                        <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap" 
                                               value="<?php echo htmlspecialchars($user['nama_lengkap']); ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email</label>
                                        <input type="email" class="form-control" id="email" name="email" 
                                               value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="alamat_lengkap" class="form-label">Alamat Lengkap</label>
                                <textarea class="form-control" id="alamat_lengkap" name="alamat_lengkap" rows="3" required><?php echo htmlspecialchars($user['alamat_lengkap']); ?></textarea>
                            </div>

                            <div class="mb-3">
                                <label for="no_hp" class="form-label">No. HP</label>
                                <input type="tel" class="form-control" id="no_hp" name="no_hp" 
                                       value="<?php echo htmlspecialchars($user['no_hp']); ?>" required>
                            </div>

                            <hr class="my-4">
                            <h6 class="text-muted mb-3">Ubah Password (Opsional)</h6>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="password" class="form-label">Password Baru</label>
                                        <input type="password" class="form-control" id="password" name="password">
                                        <div class="form-text">Kosongkan jika tidak ingin mengubah password</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="confirm_password" class="form-label">Konfirmasi Password</label>
                                        <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                                    </div>
                                </div>
                            </div>

                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="../../index.php" class="btn btn-secondary me-md-2">
                                    <i class="fas fa-arrow-left me-2"></i>Kembali
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Simpan Perubahan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
