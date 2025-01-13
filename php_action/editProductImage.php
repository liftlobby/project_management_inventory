<?php 	

require_once 'core.php';

$valid['success'] = array('success' => false, 'messages' => array());

if($_POST) {		
    $productId = $_POST['productId'];
     
    $type = explode('.', $_FILES['editProductImage']['name']);
    $type = strtolower($type[count($type)-1]);		
    $fileName = uniqid(rand()).'.'.$type;
    $uploadPath = '../assests/images/stock/'.$fileName; // Physical path for moving file
    $dbImagePath = 'assests/images/stock/'.$fileName; // Path to store in database, without '../'
    
    if(in_array($type, array('gif', 'jpg', 'jpeg', 'png'))) {
        if(is_uploaded_file($_FILES['editProductImage']['tmp_name'])) {			
            if(move_uploaded_file($_FILES['editProductImage']['tmp_name'], $uploadPath)) {
                // Store the web-accessible path without '../' prefix
                $sql = "UPDATE product SET product_image = ? WHERE product_id = ?";
                $stmt = $connect->prepare($sql);
                $stmt->bind_param("si", $dbImagePath, $productId);
                
                if($stmt->execute()) {									
                    $valid['success'] = true;
                    $valid['messages'] = "Successfully Updated";	
                } else {
                    $valid['success'] = false;
                    $valid['messages'] = "Error while updating product image";
                }
                $stmt->close();
            } else {
                $valid['success'] = false;
                $valid['messages'] = "Error moving uploaded file";
            }
        } else {
            $valid['success'] = false;
            $valid['messages'] = "Error with uploaded file";
        }
    } else {
        $valid['success'] = false;
        $valid['messages'] = "Invalid file type. Only GIF, JPG, JPEG, and PNG are allowed.";
    }
     
    $connect->close();
    echo json_encode($valid);
} // /if $_POST