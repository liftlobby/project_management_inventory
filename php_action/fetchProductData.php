<?php 	
require_once 'core.php';
require_once 'security_utils.php';

$sql = "SELECT p.product_id, p.product_name, p.quantity, p.rate, 
        b.brand_name, c.categories_name
        FROM product p
        JOIN brands b ON p.brand_id = b.brand_id
        JOIN categories c ON p.categories_id = c.categories_id
        WHERE p.status = 1 AND p.active = 1 AND p.quantity > 0
        ORDER BY p.product_name ASC";

$stmt = SecurityUtils::prepareAndExecute($sql, "", []);
$result = $stmt->get_result();

$data = array();
while($row = $result->fetch_assoc()) {
    $data[] = array(
        'id' => $row['product_id'],
        'name' => $row['product_name'],
        'quantity' => $row['quantity'],
        'rate' => $row['rate'],
        'brand' => $row['brand_name'],
        'category' => $row['categories_name']
    );
}

$connect->close();
echo json_encode($data);