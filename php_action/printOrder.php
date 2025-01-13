<?php 
// Prevent any output before our JSON response
ob_start();

require_once 'core.php';

// Function to handle errors and return JSON response
function handleError($message, $error = null) {
    if ($error) {
        error_log("Error in printOrder.php: " . print_r($error, true));
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

    $orderId = intval($_POST['orderId']); // Convert to integer
    if ($orderId <= 0) {
        handleError('Invalid order ID');
    }

    // Prepare and execute query to fetch order details
    $sql = "SELECT orders.order_date, orders.client_name, orders.client_contact, orders.restock_reason,
            order_item.rate, order_item.quantity, order_item.total,
            product.product_name 
            FROM orders 
            INNER JOIN order_item ON orders.order_id = order_item.order_id 
            INNER JOIN product ON order_item.product_id = product.product_id 
            WHERE orders.order_id = ?";

    $stmt = $connect->prepare($sql);
    if (!$stmt) {
        handleError('Database prepare error', $connect->error);
    }

    $stmt->bind_param("i", $orderId);
    if (!$stmt->execute()) {
        handleError('Error executing query', $stmt->error);
    }

    $result = $stmt->get_result();
    if ($result->num_rows == 0) {
        handleError('Order not found');
    }

    // Get first row for order details
    $orderData = $result->fetch_array();
    $orderDate = date('F j, Y', strtotime($orderData[0])); // Format date nicely
    $clientName = $orderData[1];
    $clientContact = $orderData[2]; 
    $restockReason = $orderData[3];

    // Build HTML table with better styling
    $table = '
    <style>
        .print-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .print-table th, .print-table td { padding: 8px; text-align: center; border: 1px solid #ddd; }
        .print-table th { background-color: #f5f5f5; }
        .print-header { font-size: 24px; font-weight: bold; margin-bottom: 20px; text-align: center; }
        .print-details { font-size: 14px; margin-bottom: 20px; text-align: center; line-height: 1.5; }
        .print-total { font-weight: bold; }
    </style>
    <div class="print-header">Order Summary</div>
    <div class="print-details">
        Order Date: '.htmlspecialchars($orderDate).'<br> 
        Staff Name: '.htmlspecialchars($clientName).'<br> 
        Contact: '.htmlspecialchars($clientContact).'<br>
        Restock Reason: '.htmlspecialchars($restockReason).'
    </div>
    <table class="print-table">
        <thead>
            <tr>
                <th>Product Name</th>
                <th>Rate</th>
                <th>Quantity</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>';

    // Reset result pointer
    $result->data_seek(0);
    $totalAmount = 0;
    
    while($row = $result->fetch_array()) {
        $table .= '<tr>
            <td>'.htmlspecialchars($row['product_name']).'</td>
            <td>$'.htmlspecialchars(number_format((float)$row['rate'], 2)).'</td>
            <td>'.htmlspecialchars($row['quantity']).'</td>
            <td>$'.htmlspecialchars(number_format((float)$row['total'], 2)).'</td>
        </tr>';
        $totalAmount += (float)$row['total'];
    }

    $table .= '
        </tbody>
        <tfoot>
            <tr class="print-total">
                <td colspan="3">Total Amount</td>
                <td>$'.htmlspecialchars(number_format($totalAmount, 2)).'</td>
            </tr>
        </tfoot>
    </table>';

    // Return success response with table HTML
    ob_clean();
    header('Content-Type: application/json');
    echo json_encode(array(
        'success' => true,
        'messages' => 'Order printed successfully',
        'html' => $table
    ));

    $stmt->close();
    $connect->close();

} catch (Exception $e) {
    handleError('Server error: ' . $e->getMessage());
}