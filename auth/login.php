<?php
require_once '../config/config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    $user_type = $_POST['user_type'];
    
    if (empty($email) || empty($password)) {
        $error = 'Email dan password harus diisi!';
    } else {
        try {
            $database = new Database();
            $db = $database->getConnection();
            
            if ($user_type === 'admin') {
                // Admin login
                $stmt = $db->prepare("SELECT id_admin, nama_admin, username, password, role FROM admin WHERE email = ?");
                $stmt->execute([$email]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($user && password_verify($password, $user['password'])) {
                    $_SESSION['user_id'] = $user['id_admin'];
                    $_SESSION['user_name'] = $user['nama_admin'];
                    $_SESSION['user_role'] = 'admin';
                    $_SESSION['user_username'] = $user['username'];
                    redirect('admin/dashboard.php');
                } else {
                    $error = 'Email atau password salah!';
                }
            } else {
                // Konsumen login
                $stmt = $db->prepare("SELECT id_konsumen, nama_lengkap, email, password FROM konsumen WHERE email = ?");
                $stmt->execute([$email]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($user && password_verify($password, $user['password'])) {
                    $_SESSION['user_id'] = $user['id_konsumen'];
                    $_SESSION['user_name'] = $user['nama_lengkap'];
                    $_SESSION['user_role'] = 'konsumen';
                    $_SESSION['user_email'] = $user['email'];
                    redirect('index.php');
                } else {
                    $error = 'Email atau password salah!';
                }
            }
        } catch (Exception $e) {
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
    <title>Login - Toko ABC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 10px;
        }
        .form-control {
            border-radius: 10px;
            border: 2px solid #e9ecef;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .user-type-btn {
            border-radius: 10px;
            transition: all 0.3s ease;
        }
        .user-type-btn.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-4">
                <div class="card">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <h2 class="fw-bold text-primary">Toko ABC</h2>
                            <p class="text-muted">Masuk ke Akun Anda</p>
                        </div>

                        <?php if ($error): ?>
                            <div class="alert alert-danger" role="alert">
                                <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Login Sebagai</label>
                                <div class="btn-group w-100" role="group">
                                    <input type="radio" class="btn-check" name="user_type" id="konsumen" value="konsumen" checked>
                                    <label class="btn btn-outline-primary user-type-btn" for="konsumen">
                                        <i class="fas fa-user me-2"></i>Konsumen
                                    </label>

                                    <input type="radio" class="btn-check" name="user_type" id="admin" value="admin">
                                    <label class="btn btn-outline-primary user-type-btn" for="admin">
                                        <i class="fas fa-user-shield me-2"></i>Admin
                                    </label>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="password" class="form-label">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 py-2 mb-3">
                                <i class="fas fa-sign-in-alt me-2"></i>Login
                            </button>

                            <div class="text-center">
                                <p class="mb-0">Belum punya akun? <a href="register.php" class="text-primary fw-bold">Daftar di sini</a></p>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Handle user type selection
        document.querySelectorAll('input[name="user_type"]').forEach(radio => {
            radio.addEventListener('change', function() {
                document.querySelectorAll('.user-type-btn').forEach(btn => {
                    btn.classList.remove('active');
                });
                if (this.checked) {
                    this.nextElementSibling.classList.add('active');
                }
            });
        });

        // Set initial active state
        document.querySelector('input[name="user_type"]:checked').nextElementSibling.classList.add('active');
    </script>
</body>
</html>
