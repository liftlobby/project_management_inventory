<?php require_once 'includes/header.php'; ?>

<?php 

$sql = "SELECT * FROM product WHERE status = 1";
$query = $connect->query($sql);
$countProduct = $query->num_rows;

$orderSql = "SELECT * FROM orders WHERE order_status = 1";
$orderQuery = $connect->query($orderSql);
$countOrder = $orderQuery->num_rows;

// Count total products ordered
$totalProducts = 0;
$orderItemSql = "SELECT SUM(order_item.quantity) as total FROM order_item 
                 INNER JOIN orders ON order_item.order_id = orders.order_id 
                 WHERE orders.order_status = 1";
$orderItemQuery = $connect->query($orderItemSql);
$orderItemResult = $orderItemQuery->fetch_assoc();
$totalProducts = $orderItemResult['total'] ? $orderItemResult['total'] : 0;

$lowStockSql = "SELECT * FROM product WHERE quantity <= 3 AND status = 1";
$lowStockQuery = $connect->query($lowStockSql);
$countLowStock = $lowStockQuery->num_rows;

// Get total orders per user
$userwisesql = "SELECT users.username, COUNT(orders.order_id) as totalorder 
                FROM orders 
                INNER JOIN users ON orders.user_id = users.user_id 
                WHERE orders.order_status = 1 
                GROUP BY orders.user_id";
$userwiseQuery = $connect->query($userwisesql);

$connect->close();

?>

<style type="text/css">
	.ui-datepicker-calendar {
		display: none;
	}
</style>

<!-- fullCalendar 2.2.5-->
    <link rel="stylesheet" href="assests/plugins/fullcalendar/fullcalendar.min.css">
    <link rel="stylesheet" href="assests/plugins/fullcalendar/fullcalendar.print.css" media="print">

<div class="row">
	<div class="col-md-4">
		<div class="panel panel-success">
			<div class="panel-heading">
				<a href="product.php" style="text-decoration:none;color:black;">
					Total Product
					<span class="badge pull pull-right"><?php echo $countProduct; ?></span>	
				</a>
			</div> <!--/panel-heading-->
		</div> <!--/panel-->
	</div> <!--/col-md-4-->
	
	<div class="col-md-4">
		<div class="panel panel-info">
			<div class="panel-heading">
				<a href="orders.php?o=manord" style="text-decoration:none;color:black;">
					Total Orders
					<span class="badge pull pull-right"><?php echo $countOrder; ?></span>
				</a>
			</div> <!--/panel-heading-->
		</div> <!--/panel-->
	</div> <!--/col-md-4-->
	
	<div class="col-md-4">
		<div class="panel panel-danger">
			<div class="panel-heading">
				<a href="product.php" style="text-decoration:none;color:black;">
					Low Stock
					<span class="badge pull pull-right"><?php echo $countLowStock; ?></span>	
				</a>
			</div> <!--/panel-heading-->
		</div> <!--/panel-->
	</div> <!--/col-md-4-->
	
	<div class="col-md-4">
		<div class="panel panel-info">
			<div class="panel-heading">
				<a href="orders.php?o=manord" style="text-decoration:none;color:black;">
					Total Products Ordered
					<span class="badge pull pull-right"><?php echo $totalProducts; ?></span>
				</a>
			</div> <!--/panel-heading-->
		</div> <!--/panel-->
	</div> <!--/col-md-4-->

	<div class="col-md-8">
		<div class="panel panel-default">
			<div class="panel-heading"> <i class="glyphicon glyphicon-calendar"></i> User Wise Order</div>
			<div class="panel-body">
				<table class="table">
					<thead>
						<tr>
							<th style="width:40%;">Name</th>
							<th style="width:20%;">Orders</th>
						</tr>
					</thead>
					<tbody>
						<?php while ($orderResult = $userwiseQuery->fetch_assoc()) { ?>
							<tr>
								<td><?php echo $orderResult['username']; ?></td>
								<td><?php echo $orderResult['totalorder']; ?></td>
							</tr>
						<?php } ?>
					</tbody>
				</table>
			</div>
		</div>
	</div>

</div> <!--/row-->

<?php require_once 'includes/footer.php'; ?>