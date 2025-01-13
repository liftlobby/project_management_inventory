<?php 	
require_once 'core.php';
require_once 'security_utils.php';

if(!isset($_POST['brandId'])) {
    die(json_encode(array('error' => 'Brand ID is required')));
}

$brandId = $_POST['brandId'];

$sql = "SELECT b.brand_id, b.brand_name, b.brand_active, b.brand_status,
        COUNT(p.product_id) as product_count
        FROM brands b
        LEFT JOIN product p ON b.brand_id = p.brand_id
        WHERE b.brand_id = ?
        GROUP BY b.brand_id";

$stmt = SecurityUtils::prepareAndExecute($sql, "i", [$brandId]);
$result = $stmt->get_result();

$data = array();
if($result->num_rows > 0) { 
    $data = $result->fetch_assoc();
}

$connect->close();
echo json_encode($data);