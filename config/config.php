<?php
/**
 * Konfigurasi Aplikasi
 * Aplikasi E-Commerce Toko ABC
 */

// Start session
session_start();

// Base URL
define('BASE_URL', 'http://localhost/tokovr2/');

// Upload directory
define('UPLOAD_DIR', 'uploads/');

// Create upload directory if not exists
if (!file_exists(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0777, true);
}

// Include database config
require_once 'config/database.php';

// Helper functions
function redirect($url) {
    header("Location: " . BASE_URL . $url);
    exit();
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function formatRupiah($angka) {
    return 'Rp ' . number_format($angka, 0, ',', '.');
}

function generateToken() {
    return bin2hex(random_bytes(32));
}

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
