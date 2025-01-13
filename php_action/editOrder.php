<?php 

require_once 'core.php';
require_once 'security_utils.php';

$valid['success'] = array('success' => false, 'messages' => array());

if($_POST) {
    $orderId = $_POST['orderId'];
    $orderDate = date('Y-m-d', strtotime($_POST['orderDate']));
    $clientName = $_POST['clientName'];
    $clientContact = $_POST['clientContact'];
    $paid = $_POST['paid'] ?? 0;
    $discount = $_POST['discount'] ?? 0;
    $paymentType = $_POST['paymentType'] ?? '';
    $paymentStatus = $_POST['paymentStatus'] ?? '';
    $restockReason = $_POST['restockReason'] ?? '';

    try {
        // Begin transaction
        $connect->begin_transaction();

        // Update orders table
        $sql = "UPDATE orders SET order_date = ?, client_name = ?, client_contact = ?, 
                paid = ?, discount = ?, payment_type = ?, payment_status = ?, restock_reason = ? 
                WHERE order_id = ?";
        
        $stmt = SecurityUtils::prepareAndExecute($sql, "ssssssssi", [
            $orderDate,
            $clientName,
            $clientContact,
            $paid,
            $discount,
            $paymentType,
            $paymentStatus,
            $restockReason,
            $orderId
        ]);

        if($stmt->affected_rows < 0) {
            throw new Exception("Failed to update order");
        }

        // Only proceed with order items if they exist
        if(isset($_POST['productName']) && is_array($_POST['productName'])) {
            // Remove existing order items
            $removeOrderSql = "DELETE FROM order_item WHERE order_id = ?";
            SecurityUtils::prepareAndExecute($removeOrderSql, "i", [$orderId]);

            // Add new order items
            for($x = 0; $x < count($_POST['productName']); $x++) {
                $updateProductQuantitySql = "UPDATE product 
                    SET quantity = quantity - ? 
                    WHERE product_id = ?";
                SecurityUtils::prepareAndExecute($updateProductQuantitySql, "si", [
                    $_POST['quantity'][$x],
                    $_POST['productName'][$x]
                ]);

                $orderItemSql = "INSERT INTO order_item (order_id, product_id, quantity, rate, total) 
                    VALUES (?, ?, ?, ?, ?)";
                SecurityUtils::prepareAndExecute($orderItemSql, "iisss", [
                    $orderId,
                    $_POST['productName'][$x],
                    $_POST['quantity'][$x],
                    $_POST['rate'][$x],
                    $_POST['totalValue'][$x]
                ]);
            }
        }

        // If we got here, commit the transaction
        $connect->commit();
        
        $valid['success'] = true;
        $valid['messages'] = "Order Successfully Updated";
        
    } catch (Exception $e) {
        // Something went wrong, rollback
        $connect->rollback();
        error_log("Error in editOrder.php: " . $e->getMessage());
        $valid['success'] = false;
        $valid['messages'] = "Error updating order: " . $e->getMessage();
    }

    $connect->close();
    echo json_encode($valid);
}