<?php 

require_once 'core.php';

$orderId = $_POST['orderId'];

$sql = "SELECT orders.order_date, orders.client_name, orders.client_contact, orders.restock_reason,
        order_item.rate, order_item.quantity, order_item.total,
        product.product_name 
FROM orders 
INNER JOIN order_item ON orders.order_id = order_item.order_id 
INNER JOIN product ON order_item.product_id = product.product_id 
WHERE orders.order_id = $orderId";

$orderResult = $connect->query($sql);
$orderData = $orderResult->fetch_array();

$orderDate = $orderData[0];
$clientName = $orderData[1];
$clientContact = $orderData[2]; 
$restockReason = $orderData[3];

$table = '
<table align="center" cellpadding="0" cellspacing="0" style="width:100%;">
    <tr>
        <td colspan="5">
            <center style="font-size:18px">Order Summary</center>
        </td>
    </tr>
    <tr>
        <td colspan="5">
            <center style="font-size:14px">
                Order Date: '.$orderDate.'<br/> 
                Client Name: '.$clientName.'<br/> 
                Contact: '.$clientContact.'<br/>
                Restock Reason: '.$restockReason.'
            </center>
        </td>
    </tr>
    
    <tr>
        <td><center>Product Name</center></td>
        <td><center>Rate</center></td>
        <td><center>Quantity</center></td>
        <td><center>Total</center></td>
    </tr>';

    $orderResult = $connect->query($sql);
    $totalAmount = 0;
    while($row = $orderResult->fetch_array()) {
        $table .= '<tr>
            <td><center>'.$row[7].'</center></td>
            <td><center>'.$row[4].'</center></td>
            <td><center>'.$row[5].'</center></td>
            <td><center>'.$row[6].'</center></td>
        </tr>';
        $totalAmount += $row[6];
    }
    
    $table .= '<tr>
        <td colspan="3"><center>Total Amount</center></td>
        <td><center>'.$totalAmount.'</center></td>
    </tr>
</table>
';

$connect->close();

echo $table;