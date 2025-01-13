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

        $sql = "INSERT INTO orders (order_date, client_name, client_contact, restock_reason, order_status, user_id) 
                VALUES (?, ?, ?, ?, 1, ?)";
        
        $stmt = SecurityUtils::prepareAndExecute($sql, "ssssi", [
            $orderDate, 
            $clientName, 
            $clientContact,
            $restockReason,
            $userid
        ]);

        if($stmt->affected_rows > 0) {
            $order_id = $connect->insert_id;

            $success = true;
            
            for($x = 0; $x < count($_POST['productName']); $x++) {
                // Update product quantity
                $updateProductQuantitySql = "UPDATE product SET quantity = quantity - ? WHERE product_id = ?";
                SecurityUtils::prepareAndExecute($updateProductQuantitySql, "si", [
                    $_POST['quantity'][$x], 
                    $_POST['productName'][$x]
                ]);

                // add order item
                $orderItemSql = "INSERT INTO order_item (order_id, product_id, quantity, rate, total) 
                VALUES (?, ?, ?, ?, ?)";

                $stmt = SecurityUtils::prepareAndExecute($orderItemSql, "iisss", [
                    $order_id,
                    $_POST['productName'][$x],
                    $_POST['quantity'][$x],
                    $_POST['rateValue'][$x],
                    $_POST['totalValue'][$x]
                ]);

                if($stmt->affected_rows <= 0) {
                    $success = false;
                }
            }

            if($success) {
                $_SESSION['success_message'] = 'Order successfully created';
                header('location: ../orders.php');
                exit();
            } else {
                $_SESSION['error_message'] = 'Error adding order items';
                header('location: ../orders.php');
                exit();
            }
        } else {
            $_SESSION['error_message'] = 'Error creating order';
            header('location: ../orders.php');
            exit();
        }

    } catch (Exception $e) {
        error_log("Order creation error: " . $e->getMessage());
        $_SESSION['error_message'] = 'Error: ' . $e->getMessage();
        header('location: ../orders.php');
        exit();
    }
    
    $connect->close();
}