<?php 	
require_once 'core.php';
require_once 'security_utils.php';

$sql = "SELECT product_id, product_name 
        FROM product 
        WHERE status = 1 AND active = 1 AND quantity > 0
        ORDER BY product_name ASC";

$stmt = SecurityUtils::prepareAndExecute($sql, "", []);
$result = $stmt->get_result();

$data = array();
while($row = $result->fetch_array()) {
    $data[] = array($row[0], $row[1]); // Return just ID and name as a simple array
}

$connect->close();
echo json_encode($data);