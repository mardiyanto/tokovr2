<?php
/**
 * Installer untuk Aplikasi E-Commerce Toko ABC
 * Jalankan file ini sekali untuk setup awal
 */

// Check if already installed
if (file_exists('config/installed.txt')) {
    die('Aplikasi sudah terinstall! Hapus file config/installed.txt untuk install ulang.');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $db_host = $_POST['db_host'];
    $db_name = $_POST['db_name'];
    $db_user = $_POST['db_user'];
    $db_pass = $_POST['db_pass'];
    $base_url = $_POST['base_url'];
    
    try {
        // Test database connection
        $pdo = new PDO("mysql:host=$db_host", $db_user, $db_pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Create database if not exists
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db_name`");
        $pdo->exec("USE `$db_name`");
        
        // Read and execute SQL file
        $sql = file_get_contents('database.sql');
        $pdo->exec($sql);
        
        // Update config file
        $config_content = file_get_contents('config/database.php');
        $config_content = str_replace("private \$host = 'localhost';", "private \$host = '$db_host';", $config_content);
        $config_content = str_replace("private \$db_name = 'toko_abc';", "private \$db_name = '$db_name';", $config_content);
        $config_content = str_replace("private \$username = 'root';", "private \$username = '$db_user';", $config_content);
        $config_content = str_replace("private \$password = '';", "private \$password = '$db_pass';", $config_content);
        file_put_contents('config/database.php', $config_content);
        
        // Update base URL
        $config_content = file_get_contents('config/config.php');
        $config_content = str_replace("define('BASE_URL', 'http://localhost/tokovr2/');", "define('BASE_URL', '$base_url');", $config_content);
        file_put_contents('config/config.php', $config_content);
        
        // Create installed marker
        file_put_contents('config/installed.txt', date('Y-m-d H:i:s'));
        
        // Create uploads directory
        if (!file_exists('uploads')) {
            mkdir('uploads', 0777, true);
        }
        
        $success = 'Installasi berhasil! Silakan hapus file install.php untuk keamanan.';
        
    } catch (Exception $e) {
        $error = 'Error: ' . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Install - Toko ABC</title>
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
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <h2 class="fw-bold text-primary">Toko ABC</h2>
                            <p class="text-muted">Installer Aplikasi E-Commerce</p>
                        </div>

                        <?php if ($error): ?>
                            <div class="alert alert-danger" role="alert">
                                <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($success): ?>
                            <div class="alert alert-success" role="alert">
                                <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
                                <div class="mt-3">
                                    <a href="index.php" class="btn btn-primary">
                                        <i class="fas fa-home me-2"></i>Masuk ke Aplikasi
                                    </a>
                                </div>
                            </div>
                        <?php else: ?>
                            <form method="POST">
                                <div class="mb-3">
                                    <label for="db_host" class="form-label">Database Host</label>
                                    <input type="text" class="form-control" id="db_host" name="db_host" value="localhost" required>
                                </div>

                                <div class="mb-3">
                                    <label for="db_name" class="form-label">Database Name</label>
                                    <input type="text" class="form-control" id="db_name" name="db_name" value="toko_abc" required>
                                </div>

                                <div class="mb-3">
                                    <label for="db_user" class="form-label">Database Username</label>
                                    <input type="text" class="form-control" id="db_user" name="db_user" value="root" required>
                                </div>

                                <div class="mb-3">
                                    <label for="db_pass" class="form-label">Database Password</label>
                                    <input type="password" class="form-control" id="db_pass" name="db_pass">
                                </div>

                                <div class="mb-4">
                                    <label for="base_url" class="form-label">Base URL</label>
                                    <input type="url" class="form-control" id="base_url" name="base_url" value="http://localhost/tokovr2/" required>
                                    <div class="form-text">URL lengkap ke aplikasi (dengan trailing slash)</div>
                                </div>

                                <button type="submit" class="btn btn-primary w-100 py-2">
                                    <i class="fas fa-download me-2"></i>Install Aplikasi
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
