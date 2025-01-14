<?php
// Prevent any output before our JSON response
ob_start();

require_once 'core.php';
require_once 'db_connect.php';

// Function to handle errors and return JSON response
function handleError($message, $error = null) {
    if ($error) {
        error_log("Error in fetchSelectedOrder.php: " . print_r($error, true));
        if (is_object($error) && method_exists($error, 'getTraceAsString')) {
            error_log("Stack trace: " . $error->getTraceAsString());
        }
    }
    ob_clean(); // Clear any output
    header('Content-Type: application/json');
    echo json_encode(array('success' => false, 'messages' => $message));
    exit();
}

try {
    // Check if order ID is provided
    if(!isset($_POST['orderId'])) {
        handleError('Order ID not provided');
    }

    $orderId = intval($_POST['orderId']);
    
    if ($orderId <= 0) {
        handleError('Invalid order ID');
    }

    // Fetch order details
    $orderSql = "SELECT o.*, oi.order_item_id, oi.product_id, oi.quantity, oi.rate, oi.total,
                 p.product_name, p.quantity as available_quantity
                 FROM orders o
                 LEFT JOIN order_item oi ON o.order_id = oi.order_id
                 LEFT JOIN product p ON oi.product_id = p.product_id
                 WHERE o.order_id = ?";
    
    $stmt = $connect->prepare($orderSql);
    if (!$stmt) {
        handleError('Failed to prepare order query', $connect->error);
    }
    
    $stmt->bind_param("i", $orderId);
    if (!$stmt->execute()) {
        handleError('Failed to execute order query', $stmt->error);
    }
    
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        handleError('Order not found');
    }

    // Initialize response arrays
    $order = null;
    $order_items = array();
    
    // Process results
    while ($row = $result->fetch_assoc()) {
        if (!$order) {
            $order = array(
                'order_id' => $row['order_id'],
                'order_date' => $row['order_date'],
                'client_name' => $row['client_name'],
                'client_contact' => $row['client_contact'],
                'restock_reason' => $row['restock_reason'],
                'order_status' => $row['order_status']
            );
        }
        
        if ($row['order_item_id']) {
            $order_items[] = array(
                'order_item_id' => $row['order_item_id'],
                'product_id' => $row['product_id'],
                'product_name' => $row['product_name'],
                'quantity' => $row['quantity'],
                'rate' => $row['rate'],
                'total' => $row['total'],
                'available_quantity' => $row['available_quantity']
            );
        }
    }
    
    // Fetch all active products for dropdown
    $productSql = "SELECT product_id, product_name, quantity as available_quantity, rate 
                   FROM product 
                   WHERE active = 1 AND status = 1 
                   ORDER BY product_name ASC";
    
    $productResult = $connect->query($productSql);
    if (!$productResult) {
        handleError('Failed to fetch products', $connect->error);
    }
    
    $products = array();
    while ($row = $productResult->fetch_assoc()) {
        $products[] = $row;
    }

    // Send success response
    ob_clean(); // Clear any output
    header('Content-Type: application/json');
    echo json_encode(array(
        'success' => true,
        'order' => $order,
        'order_items' => $order_items,
        'products' => $products
    ));

} catch (Exception $e) {
    handleError('Server error: ' . $e->getMessage(), $e);
}

// Close connections
if (isset($stmt)) $stmt->close();
if (isset($connect)) $connect->close();
