<?php 	
require_once 'core.php';
require_once 'security_utils.php';

// Define if request is AJAX
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
          strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

// Ensure proper content type for JSON responses
if ($isAjax) {
    header('Content-Type: application/json');
}

if($_POST) {	
    try {
        // Debug logging
        error_log("POST data received: " . print_r($_POST, true));
        
        $orderDate = date('Y-m-d', strtotime($_POST['orderDate']));	
        $clientName = $_POST['clientName'];
        $clientContact = $_POST['clientContact'];
        $restockReason = isset($_POST['restock_reason']) ? $_POST['restock_reason'] : '';
        $userid = $_SESSION['userId'];

        // Validate product array is not empty
        if(!isset($_POST['productName']) || empty($_POST['productName'])) {
            $valid['success'] = false;
            $valid['messages'] = "No products selected";
            echo json_encode($valid);
            exit();
        }

        error_log("Products to validate: " . print_r($_POST['productName'], true));

        // First, validate that all products exist
        $validProducts = true;
        $invalidProducts = array();
        
        for($x = 0; $x < count($_POST['productName']); $x++) {
            $productId = $_POST['productName'][$x];
            
            // Skip empty product selections
            if(empty($productId)) {
                continue;
            }

            error_log("Validating product ID: " . $productId);
            
            $checkSql = "SELECT product_id FROM product WHERE product_id = ? AND active = 1 AND status = 1";
            $stmt = $connect->prepare($checkSql);
            $stmt->bind_param("i", $productId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if($result->num_rows == 0) {
                $validProducts = false;
                $invalidProducts[] = $productId;
                error_log("Invalid product found: " . $productId);
            }
            $stmt->close();
        }

        if(!$validProducts) {
            $valid['success'] = false;
            $valid['messages'] = "Invalid or inactive products selected: " . implode(", ", $invalidProducts);
            echo json_encode($valid);
            exit();
        }

        // If all products are valid, proceed with order creation
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
            $success = true;
            for($x = 0; $x < count($_POST['productName']); $x++) {
                $productId = $_POST['productName'][$x];
                
                // Skip empty product selections
                if(empty($productId)) {
                    continue;
                }
                
                $quantity = $_POST['quantity'][$x];
                
                // Validate quantity
                if(empty($quantity) || $quantity <= 0) {
                    continue;
                }
                
                $orderItemSql = "INSERT INTO order_item (order_id, product_id, quantity) 
                VALUES (?, ?, ?)";
                
                $stmt2 = $connect->prepare($orderItemSql);
                $stmt2->bind_param("iis", 
                    $order_id,
                    $productId,
                    $quantity
                );
                
                if(!$stmt2->execute()) {
                    $success = false;
                    error_log("Failed to add order item: " . $stmt2->error);
                }
                $stmt2->close();
            }

            if($success) {
                $valid['success'] = true;
                $valid['messages'] = "Restock Order Successfully Created";
                $valid['order_id'] = $order_id;
                
                // Set session message for non-AJAX requests
                if(!$isAjax) {
                    $_SESSION['success_message'] = "Restock Order Successfully Created";
                }
            } else {
                // If order items failed, delete the order
                $deleteSql = "DELETE FROM orders WHERE order_id = ?";
                $stmt3 = $connect->prepare($deleteSql);
                $stmt3->bind_param("i", $order_id);
                $stmt3->execute();
                $stmt3->close();
                
                $valid['success'] = false;
                $valid['messages'] = "Error while adding order items";
                
                // Set session message for non-AJAX requests
                if(!$isAjax) {
                    $_SESSION['error_message'] = "Error while adding order items";
                }
            }
        } else {
            $valid['success'] = false;
            $valid['messages'] = "Error while creating restock order";
            error_log("Order creation error: " . $stmt->error);
        }

        $stmt->close();
        
    } catch(Exception $e) {
        $valid['success'] = false;
        $valid['messages'] = $e->getMessage();
        error_log("Order creation exception: " . $e->getMessage());
        
        // Set session message for non-AJAX requests
        if(!$isAjax) {
            $_SESSION['error_message'] = $e->getMessage();
        }
    }
    
    $connect->close();

    // For AJAX requests, return JSON
    if($isAjax) {
        echo json_encode($valid);
    } else {
        // For regular form submissions, redirect back to orders page
        header('Location: ../orders.php');
        exit();
    }
}