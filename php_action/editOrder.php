<?php 

require_once 'core.php';
require_once 'security_utils.php';

$valid['success'] = array('success' => false, 'messages' => array());

if($_POST) {
    $orderId = $_POST['orderId'];
    $orderDate = date('Y-m-d', strtotime($_POST['orderDate']));
    $clientName = $_POST['clientName'];
    $clientContact = $_POST['clientContact'];
    $restockReason = $_POST['restockReason'];

    try {
        $sql = "UPDATE orders SET order_date = ?, client_name = ?, client_contact = ?, restock_reason = ? WHERE order_id = ?";
        $stmt = SecurityUtils::prepareAndExecute($sql, "ssssi", [
            $orderDate,
            $clientName,
            $clientContact,
            $restockReason,
            $orderId
        ]);

        $readyToUpdateOrderItem = false;
        if($stmt->affected_rows > 0) {
            $readyToUpdateOrderItem = true;
        }

        if($readyToUpdateOrderItem) {
            // remove the order item data from order item table
            $removeOrderSql = "DELETE FROM order_item WHERE order_id = ?";
            SecurityUtils::prepareAndExecute($removeOrderSql, "i", [$orderId]);

            // update product quantity
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

                SecurityUtils::prepareAndExecute($orderItemSql, "iisss", [
                    $orderId,
                    $_POST['productName'][$x],
                    $_POST['quantity'][$x],
                    $_POST['rate'][$x],
                    $_POST['totalValue'][$x]
                ]);
            } // /for quantity

            $valid['success'] = true;
            $valid['messages'] = "Order Successfully Updated";
        } else {
            $valid['success'] = false;
            $valid['messages'] = "Error updating order";
        }
            
    } catch (Exception $e) {
        $valid['success'] = false;
        $valid['messages'] = "Error: " . $e->getMessage();
        error_log("Order update error: " . $e->getMessage());
    }

    $connect->close();
    echo json_encode($valid);
}