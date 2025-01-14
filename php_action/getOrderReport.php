<?php 
require_once 'core.php';

if($_POST) {
    header('Content-Type: text/html');
    
    try {
        if(!isset($_POST['startDate']) || !isset($_POST['endDate'])) {
            echo '<div class="alert alert-danger">Start date and end date are required</div>';
            exit();
        }

        $startDate = $_POST['startDate'];
        $endDate = $_POST['endDate'];

        $start_date = date('Y-m-d', strtotime($startDate));
        $end_date = date('Y-m-d', strtotime($endDate));

        if(!$start_date || !$end_date) {
            echo '<div class="alert alert-danger">Invalid date format</div>';
            exit();
        }

        $sql = "SELECT 
                o.order_id, o.order_date, o.client_name, o.client_contact, o.restock_reason,
                p.product_name, oi.quantity,
                u.username
            FROM orders o
            INNER JOIN order_item oi ON o.order_id = oi.order_id
            INNER JOIN product p ON p.product_id = oi.product_id 
            INNER JOIN users u ON o.user_id = u.user_id
            WHERE o.order_date BETWEEN ? AND ?
            ORDER BY o.order_date DESC, o.order_id DESC";

        $stmt = $connect->prepare($sql);
        
        if(!$stmt) {
            echo '<div class="alert alert-danger">'.$connect->error.'</div>';
            exit();
        }

        $stmt->bind_param("ss", $start_date, $end_date);
        
        if(!$stmt->execute()) {
            echo '<div class="alert alert-danger">'.$stmt->error.'</div>';
            exit();
        }

        $result = $stmt->get_result();
        ?>
        <style>
            .report-table {
                border: 2px solid #ddd !important;
                margin-bottom: 20px;
            }
            .report-table th,
            .report-table td {
                border: 1px solid #ddd !important;
                padding: 12px !important;
            }
            .report-table th {
                background-color: #f5f5f5 !important;
                border-bottom: 2px solid #ddd !important;
            }
            @media print {
                .report-table {
                    border: 2px solid #000 !important;
                }
                .report-table th,
                .report-table td {
                    border: 1px solid #000 !important;
                }
                .report-table th {
                    background-color: #f5f5f5 !important;
                    -webkit-print-color-adjust: exact;
                }
            }
        </style>
        
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">Order Report</h3>
                <p class="text-muted">Period: <?php echo date('F j, Y', strtotime($startDate)); ?> to <?php echo date('F j, Y', strtotime($endDate)); ?></p>
            </div>
            <div class="panel-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped report-table">
                        <thead>
                            <tr>
                                <th>Order Date</th>
                                <th>Client Name</th>
                                <th>Contact</th>
                                <th>Product</th>
                                <th>Quantity</th>
                                <th>Reason</th>
                                <th>Created By</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if($result->num_rows > 0) {
                                while($row = $result->fetch_assoc()) {
                                    ?>
                                    <tr>
                                        <td><?php echo date('F j, Y', strtotime($row['order_date'])); ?></td>
                                        <td><?php echo htmlspecialchars($row['client_name']); ?></td>
                                        <td><?php echo htmlspecialchars($row['client_contact']); ?></td>
                                        <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                                        <td><?php echo htmlspecialchars($row['quantity']); ?></td>
                                        <td><?php echo htmlspecialchars($row['restock_reason']); ?></td>
                                        <td><?php echo htmlspecialchars($row['username']); ?></td>
                                    </tr>
                                    <?php
                                }
                            } else {
                                ?>
                                <tr>
                                    <td colspan="7" class="text-center">No orders found for the selected period</td>
                                </tr>
                                <?php
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
                <div class="text-muted">
                    <p>Report generated on: <?php echo date('F j, Y g:i A'); ?></p>
                </div>
            </div>
        </div>
        <?php
        
        $stmt->close();
        $connect->close();

    } catch(Exception $e) {
        echo '<div class="alert alert-danger">Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
    }
} else {
    echo '<div class="alert alert-danger">Invalid request method</div>';
}