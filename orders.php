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
							<th style="display:none;">Order ID</th>
							<th>Order Date</th>
							<th>Staff Name</th>
							<th>Contact</th>
							<th>Total Items</th>
							<th>Actions</th>
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
            <label class="col-sm-3 control-label">Order Date</label>
            <div class="col-sm-9">
              <input type="date" class="form-control" id="orderDate" name="orderDate" autocomplete="off" required />
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-3 control-label">Staff Name</label>
            <div class="col-sm-9">
              <input type="text" class="form-control" id="clientName" name="clientName" placeholder="Staff Name" autocomplete="off" required />
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-3 control-label">Contact</label>
            <div class="col-sm-9">
              <input type="text" class="form-control" id="clientContact" name="clientContact" placeholder="Contact" autocomplete="off" required />
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-3 control-label">Restock Reason</label>
            <div class="col-sm-9">
              <textarea class="form-control" id="restockReason" name="restockReason" rows="3" placeholder="Reason for restocking"></textarea>
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
              <tr id="row1" data-row="1">
                <td>
                  <select class="form-control" name="productName[]" id="productName1" required>
                    <option value="">~~SELECT~~</option>
                  </select>
                </td>
                <td>
                  <input type="text" name="rate[]" id="rate1" class="form-control" readonly />
                </td>
                <td>
                  <input type="number" name="quantity[]" id="quantity1" class="form-control" min="1" required />
                </td>
                <td>
                  <input type="text" name="total[]" id="total1" class="form-control" readonly />
                </td>
                <td>
                  <button type="button" class="btn btn-danger removeProductRowBtn" data-row="1"><i class="glyphicon glyphicon-trash"></i></button>
                </td>
              </tr>
            </tbody>
          </table>

          <div class="form-group">
            <div class="col-sm-offset-3 col-sm-9">
              <button type="button" class="btn btn-primary" id="addRowBtn">Add Product</button>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-primary" id="createOrderBtn">Save changes</button>
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
	        <h4 class="modal-title"><i class="glyphicon glyphicon-edit"></i> Edit Order</h4>
	      </div>
	      <div class="modal-body">
	      	<div class="edit-messages"></div>
            <div class="form-group">
	        	<label class="col-sm-3 control-label">Order Date</label>
	        	<div class="col-sm-9">
	        		<input type="date" class="form-control" id="editOrderDate" name="editOrderDate" autocomplete="off" required />
	        	</div>
	        </div> <!-- /form-group-->

	        <div class="form-group">
	        	<label class="col-sm-3 control-label">Staff Name</label>
	        	<div class="col-sm-9">
	        		<input type="text" class="form-control" id="editClientName" name="editClientName" placeholder="Staff Name" autocomplete="off" required />
	        	</div>
	        </div> <!-- /form-group-->

	        <div class="form-group">
	        	<label class="col-sm-3 control-label">Contact</label>
	        	<div class="col-sm-9">
	        		<input type="text" class="form-control" id="editClientContact" name="editClientContact" placeholder="Contact" autocomplete="off" required />
	        	</div>
	        </div> <!-- /form-group-->	

            <div class="form-group">
                <label class="col-sm-3 control-label">Restock Reason</label>
                <div class="col-sm-9">
                    <textarea class="form-control" id="editRestockReason" name="editRestockReason" rows="3" placeholder="Reason for restocking"></textarea>
                </div>
            </div> <!-- /form-group-->

	        <table class="table" id="editProductTable">
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
	        	</tbody>
	        </table>

	        <div class="form-group">
	            <div class="col-sm-offset-3 col-sm-9">
	                <button type="button" class="btn btn-primary" id="editAddRowBtn">Add Product</button>
	            </div>
	        </div>
	      </div>
	      <div class="modal-footer editOrderFooter">
	        <input type="hidden" name="orderId" id="orderId" />
	        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
	        <button type="submit" class="btn btn-primary">Update</button>
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
        <div class="removeOrderMessages"></div>
        <p>Do you really want to remove this order?</p>
      </div>
      <div class="modal-footer removeOrderFooter">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-danger" id="removeOrderBtn">Remove</button>
      </div>
    </div>
  </div>
</div>

<script src="custom/js/order.js"></script>

<?php require_once 'includes/footer.php'; ?>