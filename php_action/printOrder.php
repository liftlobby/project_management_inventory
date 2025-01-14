<?php 
require_once 'core.php';

// Function to handle errors and return JSON response
function handleError($message, $error = null) {
    if ($error) {
        error_log("Error in printOrder.php: " . print_r($error, true));
    }
    header('Content-Type: application/json');
    echo json_encode(array('success' => false, 'messages' => $message));
    exit();
}

try {
    // Check if order ID is provided
    if(!isset($_POST['orderId'])) {
        handleError('Order ID not provided');
    }

    $orderId = intval($_POST['orderId']); // Convert to integer
    if ($orderId <= 0) {
        handleError('Invalid order ID');
    }

    // First get the order details
    $orderSql = "SELECT order_id, order_date, client_name, client_contact, restock_reason 
                 FROM orders 
                 WHERE order_id = ? AND order_status = 1";

    $orderStmt = $connect->prepare($orderSql);
    if (!$orderStmt) {
        handleError('Database prepare error', $connect->error);
    }

    $orderStmt->bind_param("i", $orderId);
    if (!$orderStmt->execute()) {
        handleError('Error executing query', $orderStmt->error);
    }

    $orderResult = $orderStmt->get_result();
    if ($orderResult->num_rows == 0) {
        handleError('Order not found or has been removed');
    }

    $orderInfo = $orderResult->fetch_assoc();
    
    // Then get the order items
    $itemsSql = "SELECT p.product_name, p.rate, oi.quantity, (p.rate * oi.quantity) as total
                 FROM order_item oi
                 JOIN product p ON oi.product_id = p.product_id
                 WHERE oi.order_id = ?";

    $itemsStmt = $connect->prepare($itemsSql);
    if (!$itemsStmt) {
        handleError('Database prepare error', $connect->error);
    }

    $itemsStmt->bind_param("i", $orderId);
    if (!$itemsStmt->execute()) {
        handleError('Error executing query', $itemsStmt->error);
    }

    $itemsResult = $itemsStmt->get_result();
    
    // Prepare response data
    $orderItems = array();
    $total = 0;
    
    while($item = $itemsResult->fetch_assoc()) {
        $orderItems[] = array(
            'productName' => $item['product_name'],
            'rate' => $item['rate'],
            'quantity' => $item['quantity'],
            'total' => $item['total']
        );
        $total += floatval($item['total']);
    }

    // Format the response
    $response = array(
        'success' => true,
        'orderInfo' => array(
            'orderDate' => date('d/m/Y', strtotime($orderInfo['order_date'])),
            'clientName' => $orderInfo['client_name'],
            'clientContact' => $orderInfo['client_contact'],
            'restockReason' => $orderInfo['restock_reason']
        ),
        'orderItems' => $orderItems,
        'orderTotal' => number_format($total, 2)
    );

    // Send JSON response
    header('Content-Type: application/json');
    echo json_encode($response);

} catch (Exception $e) {
    handleError('Server error: ' . $e->getMessage(), $e);
}