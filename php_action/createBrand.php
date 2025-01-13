<?php 	
require_once 'core.php';
require_once 'security_utils.php';

$valid['success'] = array('success' => false, 'messages' => array());

if($_POST) {	
    try {
        // Verify CSRF token
        if (!isset($_POST['csrf_token'])) {
            throw new Exception("CSRF token is missing");
        }

        if (!CSRFProtection::validateToken()) {
            throw new Exception("Invalid CSRF token");
        }

        $brandName = $_POST['brandName'];
        $brandStatus = $_POST['brandStatus']; 

        if(empty($brandName) || empty($brandStatus)) {
            throw new Exception("Required fields are missing");
        }

        $sql = "INSERT INTO brands (brand_name, brand_active, brand_status) VALUES (?, ?, 1)";
        $stmt = SecurityUtils::prepareAndExecute($sql, "ss", [$brandName, $brandStatus]);
        
        if($stmt->affected_rows > 0) {
            $valid['success'] = true;
            $valid['messages'] = "Successfully Added";	
        } else {
            throw new Exception("Error while adding the brand");
        }
    } catch (Exception $e) {
        $valid['success'] = false;
        $valid['messages'] = $e->getMessage();
        error_log("Brand creation error: " . $e->getMessage());
    }

    echo json_encode($valid);
} // /if $_POST