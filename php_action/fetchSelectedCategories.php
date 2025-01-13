<?php 	
require_once 'core.php';
require_once 'security_utils.php';

if(!isset($_POST['categoriesId'])) {
    die(json_encode(array('error' => 'Category ID is required')));
}

$categoriesId = $_POST['categoriesId'];

$sql = "SELECT c.categories_id, c.categories_name, c.categories_active, c.categories_status,
        COUNT(p.product_id) as product_count
        FROM categories c
        LEFT JOIN product p ON c.categories_id = p.categories_id
        WHERE c.categories_id = ?
        GROUP BY c.categories_id";

$stmt = SecurityUtils::prepareAndExecute($sql, "i", [$categoriesId]);
$result = $stmt->get_result();

$data = array();
if($result->num_rows > 0) { 
    $data = $result->fetch_assoc();
}

$connect->close();
echo json_encode($data);