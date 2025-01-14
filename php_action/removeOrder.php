<?php 	
require_once 'core.php';
require_once 'csrf_utils.php';

// Set JSON header
header('Content-Type: application/json');

$valid = array('success' => false, 'messages' => array());

// Validate CSRF token
if (!CSRFProtection::validateToken()) {
    $valid['success'] = false;
    $valid['messages'] = "Invalid CSRF token";
    echo json_encode($valid);
    exit();
}

// Validate order ID
if(isset($_POST['orderId']) && !empty($_POST['orderId'])) { 
    $orderId = intval($_POST['orderId']);
    
    try {
        // Start transaction
        $connect->begin_transaction();

        // First, delete order items
        $deleteItemsSql = "DELETE FROM order_item WHERE order_id = ?";
        $stmt = $connect->prepare($deleteItemsSql);
        if (!$stmt) {
            throw new Exception("Error preparing delete items query: " . $connect->error);
        }
        $stmt->bind_param("i", $orderId);
        $itemResult = $stmt->execute();
        $stmt->close();

        if (!$itemResult) {
            throw new Exception("Error deleting order items: " . $connect->error);
        }

        // Then, delete the order
        $deleteOrderSql = "DELETE FROM orders WHERE order_id = ?";
        $stmt = $connect->prepare($deleteOrderSql);
        if (!$stmt) {
            throw new Exception("Error preparing delete order query: " . $connect->error);
        }
        $stmt->bind_param("i", $orderId);
        $orderResult = $stmt->execute();
        $stmt->close();

        if (!$orderResult) {
            throw new Exception("Error deleting order: " . $connect->error);
        }

        // If both operations succeeded, commit the transaction
        $connect->commit();
        $valid['success'] = true;
        $valid['messages'] = "Order Successfully Removed";

    } catch (Exception $e) {
        // If any operation failed, rollback the transaction
        $connect->rollback();
        $valid['success'] = false;
        $valid['messages'] = "Error: " . $e->getMessage();
        error_log("Exception removing order: " . $e->getMessage());
    }
} else {
    $valid['success'] = false;
    $valid['messages'] = "Invalid order ID";
}

$connect->close();
echo json_encode($valid);