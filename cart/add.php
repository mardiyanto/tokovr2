<?php
require_once '../config/config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $product_id = isset($input['product_id']) ? (int)$input['product_id'] : 0;
    $quantity = isset($input['quantity']) ? (int)$input['quantity'] : 1;
    
    if ($product_id <= 0 || $quantity <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid product ID or quantity']);
        exit;
    }
    
    try {
        $database = new Database();
        $db = $database->getConnection();
        
        // Check if product exists and is approved
        $stmt = $db->prepare("SELECT stok FROM produk WHERE id_produk = ? AND status = 'acc'");
        $stmt->execute([$product_id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$product) {
            echo json_encode(['success' => false, 'message' => 'Product not found or not approved']);
            exit;
        }
        
        // Check stock availability
        if ($product['stok'] < $quantity) {
            echo json_encode(['success' => false, 'message' => 'Insufficient stock']);
            exit;
        }
        
        // Initialize cart if not exists
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
        
        // Add to cart
        if (isset($_SESSION['cart'][$product_id])) {
            $new_quantity = $_SESSION['cart'][$product_id] + $quantity;
            if ($new_quantity > $product['stok']) {
                echo json_encode(['success' => false, 'message' => 'Insufficient stock']);
                exit;
            }
            $_SESSION['cart'][$product_id] = $new_quantity;
        } else {
            $_SESSION['cart'][$product_id] = $quantity;
        }
        
        echo json_encode(['success' => true, 'message' => 'Product added to cart']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>
