<?php 	
require_once 'core.php';
require_once 'security_utils.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

$valid['success'] = array('success' => false, 'messages' => array());

if($_POST) {	
    try {
        // Validate CSRF token first
        if (!CSRFProtection::validateToken()) {
            throw new Exception("Invalid CSRF token");
        }

        $brandName = $_POST['brandName'];
        $brandStatus = $_POST['brandStatus']; 

        // Debug logging
        error_log("Received POST data: " . print_r($_POST, true));

        // Validate inputs
        if(empty($brandName) || !isset($brandStatus)) {
            throw new Exception("Required fields are missing");
        }

        // Convert brandStatus to integer
        $brandStatus = intval($brandStatus);
        if($brandStatus !== 1 && $brandStatus !== 2) {
            throw new Exception("Invalid brand status");
        }

        // Use prepared statement to prevent SQL injection
        $sql = "INSERT INTO brands (brand_name, brand_active, brand_status) VALUES (?, ?, 1)";
        $stmt = SecurityUtils::prepareAndExecute($sql, "si", [$brandName, $brandStatus]);

        if($stmt) {
            $valid['success'] = true;
            $valid['messages'] = "Brand successfully created";
        } else {
            throw new Exception("Failed to create brand");
        }

    } catch (Exception $e) {
        error_log("Error in createBrand.php: " . $e->getMessage());
        error_log("Stack trace: " . $e->getTraceAsString());
        $valid['success'] = false;
        $valid['messages'] = $e->getMessage();
    }
    
    echo json_encode($valid);
} else {
    header('location: ../index.php');
}