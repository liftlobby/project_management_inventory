<?php
require_once 'core.php';

$sql = "SELECT product_id, product_name, product_image FROM product";
$result = $connect->query($sql);

echo "<pre>";
while($row = $result->fetch_assoc()) {
    echo "ID: " . $row['product_id'] . "\n";
    echo "Name: " . $row['product_name'] . "\n";
    echo "Image Path: " . $row['product_image'] . "\n";
    echo "-------------------\n";
}
echo "</pre>";

$connect->close();
?>
