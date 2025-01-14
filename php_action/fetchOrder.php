<?php 	
require_once 'core.php';

header('Content-Type: application/json');

$sql = "SELECT 
            o.order_id,
            o.order_date,
            o.client_name,
            o.client_contact,
            o.restock_reason,
            o.order_type,
            COUNT(DISTINCT oi.order_item_id) as item_count,
            SUM(CAST(oi.quantity AS DECIMAL(10,2))) as total_quantity,
            GROUP_CONCAT(
                CONCAT(p.product_name, ' (', oi.quantity, ')')
                ORDER BY p.product_name
                SEPARATOR ', '
            ) as items_list
        FROM orders o
        LEFT JOIN order_item oi ON o.order_id = oi.order_id
        LEFT JOIN product p ON oi.product_id = p.product_id
        WHERE o.order_status = 1
        GROUP BY o.order_id, o.order_date, o.client_name, o.client_contact, o.restock_reason, o.order_type
        ORDER BY o.order_date DESC";

$result = $connect->query($sql);

if(!$result) {
    error_log("Error in query: " . $connect->error);
    die(json_encode(array(
        'data' => array(),
        'error' => 'Error fetching orders: ' . $connect->error
    )));
}

$output = array('data' => array());

while($row = $result->fetch_assoc()) {
    // Format date for display
    $orderDate = date('d/m/Y', strtotime($row['order_date']));
    
    // Format items info for tooltip
    $itemsInfo = $row['total_quantity'] . ' items';
    if($row['items_list']) {
        $itemsInfo = '<span data-toggle="tooltip" title="' . htmlspecialchars($row['items_list']) . '">' 
                   . $row['total_quantity'] . ' items</span>';
    }

    // Create action buttons
    $actionButtons = '<div class="btn-group">
                        <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                            Action <span class="caret"></span>
                        </button>
                        <ul class="dropdown-menu">
                            <li><a href="#" onclick="editOrder('.$row['order_id'].')"><i class="glyphicon glyphicon-edit"></i> Edit</a></li>
                            <li><a href="#" onclick="printOrder('.$row['order_id'].')"><i class="glyphicon glyphicon-print"></i> Print</a></li>
                            <li><a href="#" onclick="removeOrder('.$row['order_id'].')"><i class="glyphicon glyphicon-trash"></i> Remove</a></li>
                        </ul>
                    </div>';

    $output['data'][] = array(
        $row['order_id'],      // [0] Order ID (hidden)
        $orderDate,            // [1] Order Date
        $row['client_name'],   // [2] Staff Name
        $row['client_contact'],// [3] Contact
        $itemsInfo,            // [4] Total Items with tooltip
        $actionButtons         // [5] Action buttons
    );
}

$connect->close();
echo json_encode($output);