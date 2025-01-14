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
              <input type="text" class="form-control" id="clientContact" name="clientContact" placeholder="Contact Number" autocomplete="off" />
            </div>
          </div>
          <div class="form-group">
            <label for="restock_reason" class="col-sm-3 control-label">Restock Reason</label>
            <div class="col-sm-9">
              <textarea class="form-control" id="restock_reason" name="restock_reason" placeholder="Reason for restocking" rows="3"></textarea>
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
              <tr id="row1" class="0">
                <td style="margin-left:20px;">
                  <div class="form-group">
                    <select class="form-control" name="productName[]" id="productName1" onchange="getProductData(1)">
                      <option value="">~~SELECT~~</option>
                      <?php
                      $productSql = "SELECT * FROM product WHERE active = 1 AND status = 1";
                      $productData = $connect->query($productSql);
                      while($row = $productData->fetch_array()) {
                        echo "<option value='".$row['product_id']."' id='changeProduct".$row['product_id']."'>".$row['product_name']."</option>";
                      }
                      ?>
                    </select>
                  </div>
                </td>
                <td style="padding-left:20px;">
                  <input type="text" name="rate[]" id="rate1" autocomplete="off" disabled="true" class="form-control" />
                  <input type="hidden" name="rateValue[]" id="rateValue1" autocomplete="off" class="form-control" />
                </td>
                <td style="padding-left:20px;">
                  <div class="form-group">
                    <input type="number" name="quantity[]" id="quantity1" onkeyup="getTotal(1)" autocomplete="off" class="form-control" min="1" />
                  </div>
                </td>
                <td style="padding-left:20px;">
                  <input type="text" name="total[]" id="total1" autocomplete="off" class="form-control" disabled="true" />
                  <input type="hidden" name="totalValue[]" id="totalValue1" autocomplete="off" class="form-control" />
                </td>
                <td>
                  <button class="btn btn-danger removeProductRowBtn" type="button" onclick="removeProductRow(1)"><i class="glyphicon glyphicon-trash"></i></button>
                </td>
              </tr>
            </tbody>
          </table>
          <div class="form-group">
            <div class="col-sm-offset-3 col-sm-9">
              <button type="button" class="btn btn-primary" onclick="addRow()" id="addRowBtn" data-loading-text="Loading..."> <i class="glyphicon glyphicon-plus-sign"></i> Add Row </button>
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
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
    	<form class="form-horizontal" id="editOrderForm" action="php_action/editOrder.php" method="POST">
	      <div class="modal-header">
	        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
	        <h4 class="modal-title"><i class="fa fa-edit"></i> Edit Order</h4>
	      </div>
	      <div class="modal-body" style="max-height:450px; overflow:auto;">

	      	<div id="edit-order-messages"></div>

            <div class="form-group">
	        	<label for="editOrderDate" class="col-sm-2 control-label">Order Date</label>
	        	<div class="col-sm-10">
	        		<input type="text" class="form-control" id="editOrderDate" name="editOrderDate" autocomplete="off">
	        	</div>
	        </div> <!-- /form-group-->

	        <div class="form-group">
	        	<label for="editClientName" class="col-sm-2 control-label">Client Name</label>
	        	<div class="col-sm-10">
	        		<input type="text" class="form-control" id="editClientName" name="editClientName" placeholder="Client Name" autocomplete="off">
	        	</div>
	        </div> <!-- /form-group-->

	        <div class="form-group">
	        	<label for="editClientContact" class="col-sm-2 control-label">Client Contact</label>
	        	<div class="col-sm-10">
	        		<input type="text" class="form-control" id="editClientContact" name="editClientContact" placeholder="Contact Number" autocomplete="off">
	        	</div>
	        </div> <!-- /form-group-->	

            <div class="form-group">
                <label for="editRestockReason" class="col-sm-2 control-label">Restock Reason</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control" id="editRestockReason" name="editRestockReason" placeholder="Restock Reason" autocomplete="off">
                </div>
            </div> <!-- /form-group-->

	        <table class="table" id="editProductTable">
	        	<thead>
	        		<tr>			  			
	        			<th style="width:40%;">Product</th>
	        			<th style="width:15%;">Rate</th>
	        			<th style="width:15%;">Available</th>			  			
	        			<th style="width:15%;">Quantity</th>			  			
	        			<th style="width:15%;">Total</th>			  			
	        			<th style="width:10%;"></th>
	        		</tr>
	        	</thead>
	        	<tbody>
	        		<?php
	        		$arrayNumber = 0;
	        		for($x = 1; $x < 4; $x++) { ?>
	        			<tr id="row<?php echo $x; ?>" class="<?php echo $arrayNumber; ?>">		  				
	        				<td>
	        					<div class="form-group">
	        						<select class="form-control" name="editProductName[]" id="editProductName<?php echo $x; ?>" onchange="getEditProductData(<?php echo $x; ?>)">
	        							<option value="">~~SELECT~~</option>
	        							<?php
	        								$productSql = "SELECT * FROM product WHERE active = 1 AND status = 1 AND quantity != 0";
	        								$productData = $connect->query($productSql);

	        								while($row = $productData->fetch_array()) {									 		
	        									echo "<option value='".$row['product_id']."' id='changeProduct".$row['product_id']."'>".$row['product_name']."</option>";
	        								} // /while 
	        							?>
	        						</select>
	        					</div>
	        				</td>
	        				<td>			  					
	        					<input type="text" name="editRate[]" id="editRate<?php echo $x; ?>" autocomplete="off" disabled="true" class="form-control" />			  					
	        					<input type="hidden" name="editRateValue[]" id="editRateValue<?php echo $x; ?>" autocomplete="off" class="form-control" />			  					
	        				</td>
	        				<td>
	        					<div class="form-group">
	        						<span id="editAvailable<?php echo $x; ?>"></span>
	        					</div>
	        				</td>
	        				<td>
	        					<div class="form-group">
	        					<input type="number" name="editQuantity[]" id="editQuantity<?php echo $x; ?>" onchange="getEditTotal(<?php echo $x; ?>)" autocomplete="off" class="form-control" min="1" />
	        					</div>
	        				</td>
	        				<td>			  					
	        					<input type="text" name="editTotal[]" id="editTotal<?php echo $x; ?>" autocomplete="off" class="form-control" disabled="true" />			  					
	        					<input type="hidden" name="editTotalValue[]" id="editTotalValue<?php echo $x; ?>" autocomplete="off" class="form-control" />			  					
	        				</td>
	        				<td>
	        					<button class="btn btn-danger removeEditProductRowBtn" type="button" onclick="removeEditProductRow(<?php echo $x; ?>)"><i class="glyphicon glyphicon-trash"></i></button>
	        				</td>
	        			</tr>
	        		<?php
	        		$arrayNumber++;
	        		} // /for
	        		?>
	        	</tbody>			  	
	        </table>

            <div class="form-group">
                <label for="grandTotal" class="col-sm-3 control-label">Grand Total</label>
                <div class="col-sm-9">
                    <h4><span id="grandTotal">0.00</span></h4>
                </div>
            </div>

	        <div class="form-group editOrderFooter">
	        	<div class="col-sm-offset-2 col-sm-10">
	        		<button type="button" class="btn btn-default" onclick="addEditRow()" id="addRowBtn" data-loading-text="Loading..."> <i class="glyphicon glyphicon-plus-sign"></i> Add Row </button>
	        	</div>
	        </div>				
      	        
	      </div> <!-- /modal-body -->
	      
	      <div class="modal-footer editOrderFooter">
	        <button type="button" class="btn btn-default" data-dismiss="modal"> <i class="glyphicon glyphicon-remove-sign"></i> Close</button>
	        
	        <button type="submit" class="btn btn-success" id="editOrderBtn" data-loading-text="Loading..."> <i class="glyphicon glyphicon-ok-sign"></i> Save Changes</button>
	      </div> <!-- /modal-footer -->				     
	    </form> <!-- /.form -->				     	
    </div> <!-- /modal-content -->
  </div> <!-- /modal-dialog -->
</div> <!-- /modal -->

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