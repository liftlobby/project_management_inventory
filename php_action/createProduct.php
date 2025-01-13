<?php 	
require_once 'core.php';
require_once 'security_utils.php';

$valid['success'] = array('success' => false, 'messages' => array());

if($_POST) {	
    $productName = $_POST['productName'];
    $quantity = $_POST['quantity'];
    $rate = $_POST['rate'];
    $brandName = $_POST['brandName'];
    $categoryName = $_POST['categoryName'];
    $productStatus = $_POST['productStatus'];

    try {
        $type = explode('.', $_FILES['productImage']['name']);
        $type = strtolower($type[count($type)-1]);		
        $url = '../assests/images/stock/'.uniqid(rand()).'.'.$type;
        
        // Validate file type
        $allowedTypes = array('gif', 'jpg', 'jpeg', 'png');
        if(!in_array($type, $allowedTypes)) {
            $valid['success'] = false;
            $valid['messages'] = "Invalid file type. Only GIF, JPG, JPEG, and PNG are allowed.";
            echo json_encode($valid);
            exit();
        }
        
        if(is_uploaded_file($_FILES['productImage']['tmp_name'])) {			
            if(move_uploaded_file($_FILES['productImage']['tmp_name'], $url)) {
                
                $sql = "INSERT INTO product (product_name, product_image, brand_id, categories_id, quantity, rate, active, status) VALUES (?, ?, ?, ?, ?, ?, ?, 1)";
                $stmt = SecurityUtils::prepareAndExecute($sql, "sssssss", [
                    $productName, 
                    $url, 
                    $brandName, 
                    $categoryName, 
                    $quantity, 
                    $rate, 
                    $productStatus
                ]);
                
                if($stmt->affected_rows > 0) {
                    $valid['success'] = true;
                    $valid['messages'] = "Successfully Added";	
                } else {
                    $valid['success'] = false;
                    $valid['messages'] = "Error while adding the product";
                }
            } else {
                $valid['success'] = false;
                $valid['messages'] = "Error while uploading image";
            }
        }
    } catch (Exception $e) {
        $valid['success'] = false;
        $valid['messages'] = "Error: " . $e->getMessage();
        error_log("Product creation error: " . $e->getMessage());
    }

    $connect->close();
    echo json_encode($valid);
} // /if $_POST