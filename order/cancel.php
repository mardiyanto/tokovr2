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
    $order_id = isset($input['order_id']) ? (int)$input['order_id'] : 0;
    
    if ($order_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid order ID']);
        exit;
    }
    
    try {
        $database = new Database();
        $db = $database->getConnection();
        
        // Check if order exists and belongs to user
        $stmt = $db->prepare("SELECT status_order FROM `order` WHERE id_order = ? AND id_konsumen = ?");
        $stmt->execute([$order_id, $_SESSION['user_id']]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$order) {
            echo json_encode(['success' => false, 'message' => 'Order not found']);
            exit;
        }
        
        if ($order['status_order'] !== 'pending') {
            echo json_encode(['success' => false, 'message' => 'Order cannot be cancelled']);
            exit;
        }
        
        // Start transaction
        $db->beginTransaction();
        
        // Get order items to restore stock
        $stmt = $db->prepare("SELECT id_produk, jumlah FROM order_detail WHERE id_order = ?");
        $stmt->execute([$order_id]);
        $order_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Restore stock
        foreach ($order_items as $item) {
            $stmt = $db->prepare("UPDATE produk SET stok = stok + ? WHERE id_produk = ?");
            $stmt->execute([$item['jumlah'], $item['id_produk']]);
        }
        
        // Update order status
        $stmt = $db->prepare("UPDATE `order` SET status_order = 'batal' WHERE id_order = ?");
        $stmt->execute([$order_id]);
        
        // Commit transaction
        $db->commit();
        
        echo json_encode(['success' => true, 'message' => 'Order cancelled successfully']);
    } catch (Exception $e) {
        $db->rollback();
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>
