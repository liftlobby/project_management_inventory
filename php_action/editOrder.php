<?php 

require_once 'core.php';
require_once 'security_utils.php';

$valid['success'] = array('success' => false, 'messages' => array());

if($_POST) {
    try {
        $orderId = $_POST['orderId'];
        $orderDate = date('Y-m-d', strtotime($_POST['orderDate']));
        $clientName = $_POST['clientName'];
        $clientContact = $_POST['clientContact'];
        $restockReason = $_POST['restockReason'];

        // Update order details - only essential fields for restock orders
        $sql = "UPDATE orders SET 
                order_date = ?, 
                client_name = ?, 
                client_contact = ?,
                restock_reason = ?
                WHERE order_id = ?";

        $stmt = $connect->prepare($sql);
        $stmt->bind_param("ssssi", 
            $orderDate, 
            $clientName, 
            $clientContact,
            $restockReason, 
            $orderId
        );
        
        if($stmt->execute()) {
            // Remove existing order items
            $removeOrderSql = "DELETE FROM order_item WHERE order_id = ?";
            $stmt2 = $connect->prepare($removeOrderSql);
            $stmt2->bind_param("i", $orderId);
            $stmt2->execute();
            $stmt2->close();

            // Add new order items - no quantity updates since this is a restock order
            for($x = 0; $x < count($_POST['productName']); $x++) {
                $orderItemSql = "INSERT INTO order_item (order_id, product_id, quantity) 
                    VALUES (?, ?, ?)";
                
                $stmt3 = $connect->prepare($orderItemSql);
                $stmt3->bind_param("iis", 
                    $orderId,
                    $_POST['productName'][$x],
                    $_POST['quantity'][$x]
                );
                $stmt3->execute();
                $stmt3->close();
            }

            $valid['success'] = true;
            $valid['messages'] = "Restock Order Successfully Updated";
        } else {
            $valid['success'] = false;
            $valid['messages'] = "Error while updating restock order";
        }

        $stmt->close();
        
    } catch(Exception $e) {
        $valid['success'] = false;
        $valid['messages'] = $e->getMessage();
    }
    
    $connect->close();
    echo json_encode($valid);
}