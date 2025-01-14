<?php 	
require_once 'core.php';
require_once 'security_utils.php';

if($_POST) {	
    try {
        $orderDate = date('Y-m-d', strtotime($_POST['orderDate']));	
        $clientName = $_POST['clientName'];
        $clientContact = $_POST['clientContact'];
        $restockReason = $_POST['restockReason'];
        $userid = $_SESSION['userId'];

        // Insert order with order_type = 'restock'
        $sql = "INSERT INTO orders (order_date, client_name, client_contact, restock_reason, 
                order_status, user_id, order_type) 
                VALUES (?, ?, ?, ?, 1, ?, 'restock')";
        
        $stmt = $connect->prepare($sql);
        $stmt->bind_param("ssssi", 
            $orderDate, 
            $clientName, 
            $clientContact,
            $restockReason,
            $userid
        );
        
        if($stmt->execute()) {
            $order_id = $connect->insert_id;
            
            // Add order items - no quantity update since this is a restock order
            for($x = 0; $x < count($_POST['productName']); $x++) {
                $orderItemSql = "INSERT INTO order_item (order_id, product_id, quantity) 
                VALUES (?, ?, ?)";
                
                $stmt2 = $connect->prepare($orderItemSql);
                $stmt2->bind_param("iis", 
                    $order_id,
                    $_POST['productName'][$x],
                    $_POST['quantity'][$x]
                );
                $stmt2->execute();
                $stmt2->close();
            }

            $valid['success'] = true;
            $valid['messages'] = "Restock Order Successfully Created";
            $valid['order_id'] = $order_id;
        } else {
            $valid['success'] = false;
            $valid['messages'] = "Error while creating restock order";
        }

        $stmt->close();
        
    } catch(Exception $e) {
        $valid['success'] = false;
        $valid['messages'] = $e->getMessage();
    }
    
    $connect->close();
    echo json_encode($valid);
}