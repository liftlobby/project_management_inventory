<?php 	
require_once 'core.php';
require_once 'security_utils.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

$sql = "SELECT o.order_id, o.order_date, o.client_name, o.client_contact, o.restock_reason,
        COUNT(oi.order_item_id) as total_items,
        GROUP_CONCAT(CONCAT(p.product_name, ' (', oi.quantity, ')') SEPARATOR ', ') as items
        FROM orders o
        LEFT JOIN order_item oi ON o.order_id = oi.order_id
        LEFT JOIN product p ON oi.product_id = p.product_id
        WHERE o.order_status = 1
        GROUP BY o.order_id
        ORDER BY o.order_date DESC";

if(!$result = $connect->query($sql)) {
    error_log("Error executing query: " . $connect->error);
    die("Error: " . $connect->error);
}

// Debug information
error_log("SQL Query: " . $sql);
error_log("Number of rows: " . $result->num_rows);

$output = array('data' => array());

if($result->num_rows > 0) { 
    while($row = $result->fetch_array()) {
        $orderId = $row[0];
        $orderDate = date('d/m/Y', strtotime($row[1]));
        $clientName = $row[2];
        $clientContact = $row[3];
        $totalItems = $row[5];
        $itemsList = $row[6];

        error_log("Processing order ID: " . $orderId . " with " . $totalItems . " items");

        // Action buttons
        $button = '
        <div class="btn-group">
            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                Action <span class="caret"></span>
            </button>
            <ul class="dropdown-menu">
                <li><a href="orders.php?o=editOrd&i='.$orderId.'" id="editOrderModalBtn"> <i class="glyphicon glyphicon-edit"></i> Edit</a></li>
                <li><a type="button" onclick="printOrder('.$orderId.')"> <i class="glyphicon glyphicon-print"></i> Print </a></li>
                <li><a type="button" data-toggle="modal" data-target="#removeOrderModal" id="removeOrderModalBtn" onclick="removeOrder('.$orderId.')"> <i class="glyphicon glyphicon-trash"></i> Remove</a></li>       
            </ul>
        </div>';

        // Create a tooltip with items list
        $itemsTooltip = '<span data-toggle="tooltip" title="'.$itemsList.'">'.$totalItems.' items</span>';

        $output['data'][] = array(
            $orderDate,  // order date
            $clientName,  // client name
            $clientContact,  // client contact
            $itemsTooltip,  // total items with tooltip
            $button  // action buttons
        );
    }
} else {
    error_log("No orders found with status = 1");
}

error_log("Final output: " . json_encode($output));

$connect->close();
echo json_encode($output);