<?php 	
require_once 'core.php';
require_once 'security_utils.php';

// Enable error logging
error_log("Fetching product data - Starting query");

// First, let's check all products regardless of status
$debugSql = "SELECT product_id, product_name, active, status, quantity FROM product";
try {
    $debugStmt = SecurityUtils::prepareAndExecute($debugSql, "", []);
    $debugResult = $debugStmt->get_result();
    error_log("Total products in database: " . $debugResult->num_rows);
    while($row = $debugResult->fetch_array(MYSQLI_ASSOC)) {
        error_log(sprintf(
            "DEBUG - Product found: ID=%s, Name='%s', Active=%s, Status=%s, Quantity=%s",
            $row['product_id'],
            $row['product_name'],
            $row['active'],
            $row['status'],
            $row['quantity']
        ));
    }
} catch (Exception $e) {
    error_log("Debug query error: " . $e->getMessage());
}

// Now get only active products
$sql = "SELECT p.product_id, p.product_name, p.active, p.status, p.quantity 
        FROM product p 
        WHERE p.status = 1 AND p.active = 1 AND p.quantity > 0
        ORDER BY p.product_name ASC";

try {
    error_log("Executing main query: " . $sql);
    $stmt = SecurityUtils::prepareAndExecute($sql, "", []);
    $result = $stmt->get_result();

    $data = array();
    while($row = $result->fetch_array(MYSQLI_ASSOC)) {
        error_log(sprintf(
            "Active product found: ID=%s, Name='%s', Active=%s, Status=%s, Quantity=%s",
            $row['product_id'],
            $row['product_name'],
            $row['active'],
            $row['status'],
            $row['quantity']
        ));
        $data[] = array($row['product_id'], $row['product_name']);
    }

    if (empty($data)) {
        error_log("No active products found that match criteria (status=1, active=1, quantity>0)");
    } else {
        error_log("Found " . count($data) . " active products");
    }

    echo json_encode($data);
} catch (Exception $e) {
    error_log("Error in fetchProductData.php: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    echo json_encode(array());
}