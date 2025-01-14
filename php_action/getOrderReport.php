<?php 
require_once 'core.php';

// Function to handle errors and return JSON response
function handleError($message, $error = null) {
    if ($error) {
        error_log("Error in getOrderReport.php: " . print_r($error, true));
    }
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'messages' => $message]);
    exit();
}

if($_POST) {
    try {
        // Validate input dates
        if(!isset($_POST['startDate']) || !isset($_POST['endDate'])) {
            handleError('Start date and end date are required');
        }

        $startDate = $_POST['startDate'];
        $endDate = $_POST['endDate'];

        // Try different date formats
        $date_formats = ['m/d/Y', 'Y-m-d', 'd/m/Y'];
        $start_date = null;
        $end_date = null;

        foreach($date_formats as $format) {
            $temp_date = DateTime::createFromFormat($format, $startDate);
            if($temp_date !== false) {
                $start_date = $temp_date->format('Y-m-d');
                break;
            }
        }

        foreach($date_formats as $format) {
            $temp_date = DateTime::createFromFormat($format, $endDate);
            if($temp_date !== false) {
                $end_date = $temp_date->format('Y-m-d');
                break;
            }
        }

        if(!$start_date || !$end_date) {
            handleError('Invalid date format. Please use YYYY-MM-DD format.');
        }

        error_log("Generating report from $start_date to $end_date");

        $sql = "SELECT o.order_id, o.order_date, o.client_name, o.client_contact, o.restock_reason,
                COUNT(oi.order_item_id) as total_items,
                SUM(oi.total) as order_total,
                GROUP_CONCAT(CONCAT(p.product_name, ' (', oi.quantity, ')') SEPARATOR ', ') as items
                FROM orders o
                LEFT JOIN order_item oi ON o.order_id = oi.order_id
                LEFT JOIN product p ON oi.product_id = p.product_id
                WHERE o.order_date >= ? AND o.order_date <= ? AND o.order_status = 1
                GROUP BY o.order_id
                ORDER BY o.order_date DESC";

        $stmt = $connect->prepare($sql);
        if (!$stmt) {
            handleError('Database prepare error', $connect->error);
        }

        $stmt->bind_param("ss", $start_date, $end_date);
        if (!$stmt->execute()) {
            handleError('Error executing query', $stmt->error);
        }

        $result = $stmt->get_result();
        error_log("Found " . $result->num_rows . " orders");

        $total_orders = 0;
        $grand_total = 0;

        $table = '
        <style>
            .report-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
            .report-table th, .report-table td { padding: 8px; text-align: left; border: 1px solid #ddd; }
            .report-table th { background-color: #f5f5f5; font-weight: bold; }
            .report-header { text-align: center; margin-bottom: 20px; }
            .report-header h2 { margin-bottom: 5px; }
            .report-header p { color: #666; }
            .total-row { font-weight: bold; background-color: #f9f9f9; }
            .currency { text-align: right; }
            .items-column { max-width: 300px; word-wrap: break-word; }
        </style>
        <div class="report-header">
            <h2>Order Report</h2>
            <p>From: '.htmlspecialchars($startDate).' To: '.htmlspecialchars($endDate).'</p>
        </div>
        <table class="report-table">
            <thead>
                <tr>
                    <th>Order Date</th>
                    <th>Staff Name</th>
                    <th>Contact</th>
                    <th>Restock Reason</th>
                    <th>Items</th>
                    <th>Total Amount</th>
                </tr>
            </thead>
            <tbody>';

        while($row = $result->fetch_assoc()) {
            $total_orders++;
            $grand_total += $row['order_total'];
            
            $table .= '<tr>
                <td>'.date('F j, Y', strtotime($row['order_date'])).'</td>
                <td>'.htmlspecialchars($row['client_name']).'</td>
                <td>'.htmlspecialchars($row['client_contact']).'</td>
                <td>'.htmlspecialchars($row['restock_reason']).'</td>
                <td class="items-column">'.htmlspecialchars($row['items']).'</td>
                <td class="currency">$'.number_format($row['order_total'], 2).'</td>
            </tr>';
        }

        $table .= '
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <td colspan="4">Totals</td>
                    <td>'.$total_orders.' Orders</td>
                    <td class="currency">$'.number_format($grand_total, 2).'</td>
                </tr>
            </tfoot>
        </table>';

        $stmt->close();
        $connect->close();

        // Return success response
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'messages' => 'Report generated successfully',
            'html' => $table
        ]);

    } catch (Exception $e) {
        handleError('Server error: ' . $e->getMessage());
    }
} else {
    handleError('Invalid request method');
}