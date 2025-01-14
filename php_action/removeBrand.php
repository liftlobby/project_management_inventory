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

        $brandId = $_POST['brandId>();

        // Debug logging
        error_log("Received POST data: " . print_r($_POST, true));

        // Validate inputs
        if(empty($brandId)) {
            throw new Exception("Brand ID is missing");
        }

        // Use prepared statement to prevent SQL injection
        $sql = "UPDATE brands SET brand_status = 2 WHERE brand_id = ?";
        $stmt = SecurityUtils::prepareAndExecute($sql, "i", [$brandId]);

        if($stmt) {
            $valid['success'] = true;
            $valid['messages'] = "Brand successfully removed";
        } else {
            throw new Exception("Failed to remove brand");
        }

    } catch (Exception $e) {
        error_log("Error in removeBrand.php: " . $e->getMessage());
        error_log("Stack trace: " . $e->getTraceAsString());
        $valid['success'] = false;
        $valid['messages'] = $e->getMessage();
    }
    
    echo json_encode($valid);
} else {
    header('location: ../index.php');
}