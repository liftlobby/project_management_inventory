<?php 	
require_once 'core.php';
require_once 'security_utils.php';

if(!isset($_POST['productId'])) {
    die(json_encode(array('error' => 'Product ID is required')));
}

$productId = $_POST['productId'];

$sql = "SELECT p.product_id, p.product_name, p.product_image, p.brand_id, 
        p.categories_id, p.quantity, p.rate, p.active, p.status,
        b.brand_name, c.categories_name
        FROM product p
        JOIN brands b ON p.brand_id = b.brand_id
        JOIN categories c ON p.categories_id = c.categories_id
        WHERE p.product_id = ?";

$stmt = SecurityUtils::prepareAndExecute($sql, "i", [$productId]);
$result = $stmt->get_result();

$data = array();
if($result->num_rows > 0) { 
    $data = $result->fetch_assoc();
    
    // Format image URL
    if($data['product_image']) {
        $data['product_image'] = substr($data['product_image'], 3); // Remove '../' from start
    }
}

$connect->close();
echo json_encode($data);