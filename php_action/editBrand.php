<?php 	
require_once 'core.php';
require_once 'security_utils.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

$valid['success'] = array('success' => false, 'messages' => array());

if($_POST) {	
    try {
        $brandName = $_POST['editBrandName'];
        $brandStatus = $_POST['editBrandStatus']; 
        $brandId = $_POST['brandId'];

        // Debug logging
        error_log("Received POST data: " . print_r($_POST, true));

        // Validate inputs
        if(empty($brandName) || !isset($brandStatus) || empty($brandId)) {
            throw new Exception("Required fields are missing");
        }

        // Convert brandStatus to integer
        $brandStatus = intval($brandStatus);
        if($brandStatus !== 1 && $brandStatus !== 2) {
            throw new Exception("Invalid brand status");
        }

        // Use prepared statement to prevent SQL injection
        $sql = "UPDATE brands SET brand_name = ?, brand_active = ? WHERE brand_id = ?";
        $stmt = SecurityUtils::prepareAndExecute($sql, "sii", [$brandName, $brandStatus, $brandId]);

        if($stmt) {
            $valid['success'] = true;
            $valid['messages'] = "Brand successfully updated";
        } else {
            throw new Exception("Failed to update brand");
        }

    } catch (Exception $e) {
        error_log("Error in editBrand.php: " . $e->getMessage());
        error_log("Stack trace: " . $e->getTraceAsString());
        $valid['success'] = false;
        $valid['messages'] = $e->getMessage();
    }
    
    echo json_encode($valid);
} else {
    header('location: ../index.php');
    exit();
}