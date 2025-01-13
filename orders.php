<?php 
require_once 'php_action/db_connect.php'; 
require_once 'includes/header.php'; 
?>
<div class="row">
	<div class="col-md-12">
		<ol class="breadcrumb">
		  <li><a href="dashboard.php">Home</a></li>		  
		  <li class="active">Orders</li>
		</ol>

		<?php 
		if(isset($_SESSION['success_message'])) { ?>
			<div class="alert alert-success alert-dismissible" role="alert">
				<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<?php 
					echo $_SESSION['success_message']; 
					unset($_SESSION['success_message']);
				?>
			</div>
		<?php } ?>

		<?php 
		if(isset($_SESSION['error_message'])) { ?>
			<div class="alert alert-danger alert-dismissible" role="alert">
				<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<?php 
					echo $_SESSION['error_message']; 
					unset($_SESSION['error_message']);
				?>
			</div>
		<?php } ?>

		<div class="panel panel-default">
			<div class="panel-heading">
				<div class="page-heading"> <i class="glyphicon glyphicon-edit"></i> Manage Orders</div>
			</div>
			<div class="panel-body">

				<div class="remove-messages"></div>

				<div class="div-action pull pull-right" style="padding-bottom:20px;">
					<button class="btn btn-default button1" data-toggle="modal" id="addOrderBtn" data-target="#addOrderModal"> <i class="glyphicon glyphicon-plus-sign"></i> Add Order </button>
				</div>

				<table class="table table-bordered table-striped" id="manageOrderTable">
					<thead>
						<tr>
							<th>Order Date</th>
							<th>Staff Name</th>
							<th>Contact</th>
							<th>Total Products</th>
							<th>Options</th>
						</tr>
					</thead>
				</table>

			</div>
		</div>
	</div>
</div>

<!-- add order -->
<div class="modal fade" id="addOrderModal" tabindex="-1" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">

    	<form class="form-horizontal" id="submitOrderForm" action="php_action/createOrder.php" method="POST">
	      <div class="modal-header">
	        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
	        <h4 class="modal-title"><i class="fa fa-plus"></i> Add Order</h4>
	      </div>

	      <div class="modal-body">

	      	<div id="add-order-messages"></div>
                <?php outputCSRFTokenField(); ?>

	        <div class="form-group">
	        	<label for="orderDate" class="col-sm-3 control-label">Order Date</label>
	        	<div class="col-sm-9">
	        		<input type="text" class="form-control" id="orderDate" name="orderDate" autocomplete="off" />
	        	</div>
	        </div>
	        <div class="form-group">
	        	<label for="clientName" class="col-sm-3 control-label">Staff Name</label>
	        	<div class="col-sm-9">
	        		<input type="text" class="form-control" id="clientName" name="clientName" placeholder="Staff Name" autocomplete="off" />
	        	</div>
	        </div>
	        <div class="form-group">
	        	<label for="clientContact" class="col-sm-3 control-label">Staff Contact</label>
	        	<div class="col-sm-9">
	        		<input type="text" class="form-control" id="clientContact" name="clientContact" placeholder="Staff Contact Number" autocomplete="off" />
	        	</div>
	        </div>
	        <div class="form-group">
	        	<label for="restockReason" class="col-sm-3 control-label">Restock Reason</label>
	        	<div class="col-sm-9">
	        		<select class="form-control" id="restockReason" name="restockReason">
	        			<option value="">~~SELECT~~</option>
	        			<option value="low_stock">Low Stock</option>
	        			<option value="damaged">Damaged Products</option>
	        			<option value="seasonal">Seasonal Restock</option>
	        			<option value="new_products">New Products</option>
	        			<option value="other">Other</option>
	        		</select>
	        	</div>
	        </div>

	        <table class="table" id="productTable">
	        	<thead>
	        		<tr>			  			
	        			<th style="width:40%;">Product</th>
	        			<th style="width:20%;">Rate</th>
	        			<th style="width:15%;">Quantity</th>			  			
	        			<th style="width:15%;">Total</th>			  			
	        			<th style="width:10%;"></th>
	        		</tr>
	        	</thead>
	        	<tbody>
	        		<?php
	        		$arrayNumber = 0;
	        		for($x = 1; $x < 2; $x++) { ?>
	        			<tr id="row<?php echo $x; ?>" class="<?php echo $arrayNumber; ?>">			  				
	        				<td style="margin-left:20px;">
	        					<div class="form-group">
	        						<select class="form-control" name="productName[]" id="productName<?php echo $x; ?>" onchange="getProductData(<?php echo $x; ?>)" >
	        							<option value="">~~SELECT~~</option>
	        							<?php
	        								$productSql = "SELECT * FROM product WHERE active = 1 AND status = 1 AND quantity != 0";
	        								$productData = $connect->query($productSql);

	        								while($row = $productData->fetch_array()) {									 		
	        									echo "<option value='".$row['product_id']."' id='changeProduct".$row['product_id']."'>".$row['product_name']."</option>";
	        								}
	        							?>
	        						</select>
	        					</div>
	        				</td>
	        				<td style="padding-left:20px;">			  					
	        					<input type="text" name="rate[]" id="rate<?php echo $x; ?>" autocomplete="off" disabled="true" class="form-control" />			  					
	        					<input type="hidden" name="rateValue[]" id="rateValue<?php echo $x; ?>" autocomplete="off" class="form-control" />			  					
	        				</td>
	        				<td style="padding-left:20px;">
	        					<div class="form-group">
	        						<input type="number" name="quantity[]" id="quantity<?php echo $x; ?>" onkeyup="getTotal(<?php echo $x ?>)" autocomplete="off" class="form-control" min="1" />
	        					</div>
	        				</td>
	        				<td style="padding-left:20px;">			  					
	        					<input type="text" name="total[]" id="total<?php echo $x; ?>" autocomplete="off" class="form-control" disabled="true" />			  					
	        					<input type="hidden" name="totalValue[]" id="totalValue<?php echo $x; ?>" autocomplete="off" class="form-control" />			  					
	        				</td>
	        				<td>
	        					<button class="btn btn-danger removeProductRowBtn" type="button" id="removeProductRowBtn" onclick="removeProductRow(<?php echo $x; ?>)"><i class="glyphicon glyphicon-trash"></i></button>
	        				</td>
	        			</tr>
	        		<?php
	        		$arrayNumber++;
	        		} // /for
	        		?>
	        	</tbody>			  	
	        </table>

	        <div class="form-group">
	        	<label for="subTotal" class="col-sm-3 control-label">Sub Amount</label>
	        	<div class="col-sm-9">
	        		<input type="text" class="form-control" id="subTotal" name="subTotal" disabled="true" />
	        		<input type="hidden" class="form-control" id="subTotalValue" name="subTotalValue" />
	        	</div>
	        </div>

	      </div>
	      
	      <div class="modal-footer">
	        <button type="button" class="btn btn-default" data-dismiss="modal"> <i class="glyphicon glyphicon-remove-sign"></i> Close</button>
	        <button type="submit" class="btn btn-primary" id="createOrderBtn" data-loading-text="Loading..." autocomplete="off"> <i class="glyphicon glyphicon-ok-sign"></i> Save Changes</button>
	      </div>
	      
	    </form>
    </div>
  </div>
</div>

<!-- edit order -->
<div class="modal fade" id="editOrderModal" tabindex="-1" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
    	<form class="form-horizontal" id="editOrderForm" action="php_action/editOrder.php" method="POST">
	      <div class="modal-header">
	        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
	        <h4 class="modal-title"><i class="fa fa-edit"></i> Edit Order</h4>
	      </div>
	      <div class="modal-body">
	      	<div id="edit-order-messages"></div>
                <?php outputCSRFTokenField(); ?>
                <div class="form-group">
	        	<label for="orderDate" class="col-sm-3 control-label">Order Date</label>
	        	<div class="col-sm-9">
	        		<input type="text" class="form-control" id="orderDate" name="orderDate" autocomplete="off" />
	        	</div>
	        </div>
	        <div class="form-group">
	        	<label for="clientName" class="col-sm-3 control-label">Staff Name</label>
	        	<div class="col-sm-9">
	        		<input type="text" class="form-control" id="editClientName" name="clientName" autocomplete="off" />
	        	</div>
	        </div>
	        <div class="form-group">
	        	<label for="clientContact" class="col-sm-3 control-label">Staff Contact</label>
	        	<div class="col-sm-9">
	        		<input type="text" class="form-control" id="editClientContact" name="clientContact" autocomplete="off" />
	        	</div>
	        </div>
                <div class="form-group">
	        	<label for="orderStatus" class="col-sm-3 control-label">Order Status</label>
	        	<div class="col-sm-9">
	        		<select class="form-control" name="orderStatus" id="editOrderStatus">
	        			<option value="">~~SELECT~~</option>
	        			<option value="1">Completed</option>
	        			<option value="0">Pending</option>
	        		</select>
	        	</div>
	        </div>
	        <div class="form-group">
	        	<label for="totalAmount" class="col-sm-3 control-label">Total Amount</label>
	        	<div class="col-sm-9">
	        		<input type="text" class="form-control" id="editTotalAmount" name="totalAmount" disabled />
	        	</div>
	        </div>
		<div class="form-group">
			<input type="hidden" name="orderId" id="editOrderId" />
		</div>
	      </div>
	      <div class="modal-footer">
	        <button type="button" class="btn btn-default" data-dismiss="modal"> <i class="glyphicon glyphicon-remove-sign"></i> Close</button>
	        <button type="submit" class="btn btn-primary" id="editOrderBtn" data-loading-text="Loading..." autocomplete="off"> <i class="glyphicon glyphicon-ok-sign"></i> Save Changes</button>
	      </div>
	    </form>
    </div>
  </div>
</div>

<!-- remove order -->
<div class="modal fade" tabindex="-1" role="dialog" id="removeOrderModal">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title"><i class="glyphicon glyphicon-trash"></i> Remove Order</h4>
      </div>
      <div class="modal-body">
        <p>Do you really want to remove ?</p>
      </div>
      <div class="modal-footer removeOrderFooter">
        <button type="button" class="btn btn-default" data-dismiss="modal"> <i class="glyphicon glyphicon-remove-sign"></i> Close</button>
        <button type="button" class="btn btn-primary" id="removeOrderBtn" data-loading-text="Loading..."> <i class="glyphicon glyphicon-ok-sign"></i> Save changes</button>
      </div>
    </div>
  </div>
</div>

<script>
$(document).ready(function() {
    $('#manageOrderTable').DataTable({
        'ajax': 'php_action/fetchOrder.php',
        'order': [],
        'columns': [
            { data: 0 }, // order date
            { data: 1 }, // client name
            { data: 2 }, // contact
            { data: 3 }, // total items
            { data: 4 }  // action buttons
        ]
    });
});
</script>

<script src="custom/js/order.js"></script>

<?php require_once 'includes/footer.php'; ?>