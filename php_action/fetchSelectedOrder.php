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
    // Enable error reporting for debugging
    error_reporting(E_ALL);
    ini_set('display_errors', 0); // Don't display errors, log them instead
    
    error_log("fetchSelectedOrder.php started");
    error_log("POST data: " . print_r($_POST, true));

    // Check if order ID is provided
    if(!isset($_POST['orderId'])) {
        handleError('Order ID not provided');
    }

    $orderId = intval($_POST['orderId']); // Convert to integer
    error_log("Order ID after conversion: " . $orderId);
    
    if ($orderId <= 0) {
        handleError('Invalid order ID');
    }

    // Check database connection
    if (!$connect) {
        handleError('Database connection failed', mysqli_connect_error());
    }
    error_log("Database connection successful");

    // Prepare and execute query to fetch order details
    $sql = "SELECT o.order_id, o.order_date, o.client_name, o.client_contact, o.order_status, 
            COALESCE(SUM(oi.total), 0) as total_amount
            FROM orders o 
            LEFT JOIN order_item oi ON o.order_id = oi.order_id
            WHERE o.order_id = ?
            GROUP BY o.order_id";
    
    error_log("SQL Query: " . $sql);
    error_log("Order ID for query: " . $orderId);

    $stmt = $connect->prepare($sql);
    if (!$stmt) {
        handleError('Database prepare error', $connect->error);
    }
    error_log("Statement prepared successfully");

    $stmt->bind_param("i", $orderId);
    if (!$stmt->execute()) {
        handleError('Error executing query', $stmt->error);
    }
    error_log("Query executed successfully");

    $result = $stmt->get_result();
    error_log("Number of rows returned: " . $result->num_rows);

    if($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        error_log("Row data: " . print_r($row, true));
        
        // Format the response data
        $response = array(
            'success' => true,
            'orderId' => $row['order_id'],
            'orderDate' => date('m/d/Y', strtotime($row['order_date'])),
            'clientName' => $row['client_name'],
            'clientContact' => $row['client_contact'],
            'orderStatus' => $row['order_status'],
            'totalAmount' => $row['total_amount']
        );
        
        error_log("Response data: " . print_r($response, true));
        
        ob_clean(); // Clear any output
        header('Content-Type: application/json');
        echo json_encode($response);
    } else {
        handleError('Order not found');
    }

    $stmt->close();
    $connect->close();

} catch (Exception $e) {
    error_log("Exception caught in fetchSelectedOrder.php");
    error_log("Error message: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    handleError('Server error: ' . $e->getMessage());
} catch (Error $e) {
    error_log("Error caught in fetchSelectedOrder.php");
    error_log("Error message: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    handleError('Server error: ' . $e->getMessage());
}
