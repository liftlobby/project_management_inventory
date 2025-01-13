<?php 

require_once 'core.php';

if($_POST) {
    // Enable error reporting for debugging
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    
    $startDate = $_POST['startDate'];
    $date = DateTime::createFromFormat('m/d/Y', $startDate);
    $start_date = $date->format("Y-m-d");

    $endDate = $_POST['endDate'];
    $format = DateTime::createFromFormat('m/d/Y', $endDate);
    $end_date = $format->format("Y-m-d");

    error_log("Generating report from $start_date to $end_date");

    $sql = "SELECT o.order_id, o.order_date, o.client_name, o.client_contact, o.restock_reason,
            COUNT(oi.order_item_id) as total_items,
            GROUP_CONCAT(CONCAT(p.product_name, ' (', oi.quantity, ')') SEPARATOR ', ') as items
            FROM orders o
            LEFT JOIN order_item oi ON o.order_id = oi.order_id
            LEFT JOIN product p ON oi.product_id = p.product_id
            WHERE o.order_date >= ? AND o.order_date <= ? AND o.order_status = 1
            GROUP BY o.order_id
            ORDER BY o.order_date DESC";

    error_log("SQL Query: " . $sql);
    
    $stmt = $connect->prepare($sql);
    $stmt->bind_param("ss", $start_date, $end_date);
    $stmt->execute();
    $result = $stmt->get_result();

    error_log("Found " . $result->num_rows . " orders");

    $table = '
    <style>
        table { border-collapse: collapse; width: 100%; margin-bottom: 1em; }
        th, td { padding: 8px; text-align: left; border: 1px solid #ddd; }
        th { background-color: #f5f5f5; }
        .total-row { font-weight: bold; background-color: #f9f9f9; }
        .center { text-align: center; }
    </style>
    <h2 class="center">Order Report</h2>
    <p class="center">From: '.$startDate.' To: '.$endDate.'</p>
    <table>
        <tr>
            <th>Order Date</th>
            <th>Client Name</th>
            <th>Contact</th>
            <th>Items</th>
            <th>Total Items</th>
            <th>Restock Reason</th>
        </tr>';

    $totalItems = 0;
    
    if($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $table .= '<tr>
                <td>'.$row['order_date'].'</td>
                <td>'.$row['client_name'].'</td>
                <td>'.$row['client_contact'].'</td>
                <td>'.$row['items'].'</td>
                <td class="center">'.$row['total_items'].'</td>
                <td>'.$row['restock_reason'].'</td>
            </tr>';	
            $totalItems += $row['total_items'];
        }
    } else {
        $table .= '<tr><td colspan="6" class="center">No orders found in this date range</td></tr>';
    }
    
    $table .= '
        <tr class="total-row">
            <td colspan="4" class="center">Total Items</td>
            <td class="center">'.$totalItems.'</td>
            <td></td>
        </tr>
    </table>';

    $connect->close();
    
    echo $table;
}