<?php 	
require_once 'core.php';
require_once 'security_utils.php';

if(!isset($_POST['orderId'])) {
    die(json_encode(array('error' => 'Order ID is required')));
}

$orderId = $_POST['orderId'];
$valid = array('order' => array(), 'order_item' => array());

// Fetch order details
$sql = "SELECT o.order_id, o.order_date, o.client_name, o.client_contact, o.restock_reason 
        FROM orders o
        WHERE o.order_id = ?";

$stmt = SecurityUtils::prepareAndExecute($sql, "i", [$orderId]);
$result = $stmt->get_result();

if($result->num_rows > 0) {
    $valid['order'] = $result->fetch_assoc();

    // Fetch order items with product details
    $itemSql = "SELECT oi.order_item_id, oi.product_id, oi.quantity,
                p.product_name, p.rate,
                (oi.quantity * p.rate) as total
                FROM order_item oi
                JOIN product p ON oi.product_id = p.product_id
                WHERE oi.order_id = ?";

    $itemStmt = SecurityUtils::prepareAndExecute($itemSql, "i", [$orderId]);
    $itemResult = $itemStmt->get_result();
    
    while($item = $itemResult->fetch_assoc()) {
        $valid['order_item'][] = $item;
    }
}

$connect->close();
echo json_encode($valid);