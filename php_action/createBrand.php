<?php 	
require_once 'core.php';
require_once 'security_utils.php';

$valid['success'] = array('success' => false, 'messages' => array());

if($_POST) {	
    $brandName = $_POST['brandName'];
    $brandStatus = $_POST['brandStatus']; 

    try {
        $sql = "INSERT INTO brands (brand_name, brand_active, brand_status) VALUES (?, ?, 1)";
        $stmt = SecurityUtils::prepareAndExecute($sql, "ss", [$brandName, $brandStatus]);
        
        if($stmt->affected_rows > 0) {
            $valid['success'] = true;
            $valid['messages'] = "Successfully Added";	
        } else {
            $valid['success'] = false;
            $valid['messages'] = "Error while adding the brand";
        }
    } catch (Exception $e) {
        $valid['success'] = false;
        $valid['messages'] = "Error: " . $e->getMessage();
        error_log("Brand creation error: " . $e->getMessage());
    }

    $connect->close();
    echo json_encode($valid);
} // /if $_POST