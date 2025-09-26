<?php
require_once '../config/config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    echo json_encode(['count' => 0]);
    exit;
}

$count = 0;
if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    $count = array_sum($_SESSION['cart']);
}

echo json_encode(['count' => $count]);
?>
