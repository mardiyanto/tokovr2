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
    $product_id = isset($input['id']) ? (int)$input['id'] : 0;
    
    if ($product_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid product ID']);
        exit;
    }
    
    try {
        $database = new Database();
        $db = $database->getConnection();
        
        // Get product data first
        $stmt = $db->prepare("SELECT gambar_produk FROM produk WHERE id_produk = ?");
        $stmt->execute([$product_id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($product) {
            // Delete product
            $stmt = $db->prepare("DELETE FROM produk WHERE id_produk = ?");
            $stmt->execute([$product_id]);
            
            // Delete image file if exists
            if (!empty($product['gambar_produk']) && file_exists(UPLOAD_DIR . $product['gambar_produk'])) {
                unlink(UPLOAD_DIR . $product['gambar_produk']);
            }
            
            echo json_encode(['success' => true, 'message' => 'Product deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Product not found']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>
