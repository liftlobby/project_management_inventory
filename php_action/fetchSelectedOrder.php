<?php
require_once 'core.php';

// Set JSON header
header('Content-Type: application/json');

$response = array(
    'success' => false,
    'messages' => array(),
    'order' => array(),
    'orderItems' => array(),
    'products' => array()
);

if($_POST && isset($_POST['orderId'])) {
    $orderId = intval($_POST['orderId']); // Sanitize input

    // Get order details
    $sql = "SELECT 
                order_id,
                order_date,
                client_name,
                client_contact,
                restock_reason,
                order_status,
                order_type
            FROM orders 
            WHERE order_id = ? 
            AND order_status = 1";
            
    $stmt = $connect->prepare($sql);
    $stmt->bind_param("i", $orderId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if($result && $orderData = $result->fetch_assoc()) {
        // Format date for display
        $orderData['order_date'] = date('Y-m-d', strtotime($orderData['order_date']));
        $response['order'] = array($orderData);
        
        // Get order items with product details
        $orderItemSql = "SELECT 
                            oi.order_item_id,
                            oi.order_id,
                            oi.product_id,
                            oi.quantity,
                            p.product_name,
                            p.rate,
                            p.product_image,
                            p.brand_id,
                            p.categories_id
                        FROM order_item oi
                        INNER JOIN product p ON oi.product_id = p.product_id 
                        WHERE oi.order_id = ?
                        ORDER BY p.product_name";
                        
        $stmt = $connect->prepare($orderItemSql);
        $stmt->bind_param("i", $orderId);
        $stmt->execute();
        $itemResult = $stmt->get_result();
        
        while($row = $itemResult->fetch_assoc()) {
            $response['orderItems'][] = array(
                "order_item_id" => $row['order_item_id'],
                "order_id" => $row['order_id'],
                "product_id" => $row['product_id'],
                "quantity" => $row['quantity'],
                "product_name" => $row['product_name'],
                "rate" => $row['rate']
            );
        }
        
        // Get available products
        $productSql = "SELECT 
                        p.product_id,
                        p.product_name,
                        p.product_image,
                        p.brand_id,
                        p.categories_id,
                        p.quantity,
                        p.rate,
                        b.brand_name,
                        c.categories_name
                      FROM product p
                      LEFT JOIN brands b ON p.brand_id = b.brand_id
                      LEFT JOIN categories c ON p.categories_id = c.categories_id
                      WHERE p.active = 1 
                      AND p.status = 1 
                      ORDER BY p.product_name";
                      
        $productResult = $connect->query($productSql);
        
        while($row = $productResult->fetch_assoc()) {
            $response['products'][] = array(
                "product_id" => $row['product_id'],
                "product_name" => $row['product_name'],
                "product_image" => $row['product_image'],
                "brand_id" => $row['brand_id'],
                "brand_name" => $row['brand_name'],
                "categories_id" => $row['categories_id'],
                "categories_name" => $row['categories_name'],
                "quantity" => $row['quantity'],
                "rate" => $row['rate']
            );
        }
        
        $response['success'] = true;
    } else {
        $response['messages'][] = "Order not found or inactive";
    }
} else {
    $response['messages'][] = "Invalid request parameters";
}

$connect->close();
echo json_encode($response);
