<?php 
require_once 'php_action/core.php';
require_once 'includes/header.php'; 
?>

<div class="row">
	<div class="col-md-12">
		<ol class="breadcrumb">
		  <li><a href="dashboard.php">Home</a></li>		  
		  <li class="active">Brand</li>
		</ol>

		<div class="panel panel-default">
			<div class="panel-heading">
				<div class="page-heading"> <i class="glyphicon glyphicon-edit"></i> Manage Brand</div>
			</div>
			<div class="panel-body">
				<div class="remove-messages"></div>

				<div class="div-action pull pull-right" style="padding-bottom:20px;">
					<button class="btn btn-default button1" data-toggle="modal" id="addBrandModalBtn" data-target="#addBrandModal"> 
						<i class="glyphicon glyphicon-plus-sign"></i> Add Brand 
					</button>
				</div>
				
				<table class="table" id="manageBrandTable">
					<thead>
						<tr>							
							<th>Brand Name</th>
							<th>Status</th>
							<th style="width:15%;">Options</th>
						</tr>
					</thead>
				</table>
			</div>
		</div>		
	</div>
</div>

<!-- Add Brand -->
<div class="modal fade" id="addBrandModal" tabindex="-1" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
    	<form class="form-horizontal" id="submitBrandForm" action="php_action/createBrand.php" method="POST">
	      <div class="modal-header">
	        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
	        <h4 class="modal-title"><i class="fa fa-plus"></i> Add Brand</h4>
	      </div>
	      <div class="modal-body">
	      	<div id="add-brand-messages"></div>
	      	<?php echo CSRFProtection::getTokenField(); ?>
	        <div class="form-group">
	        	<label for="brandName" class="col-sm-3 control-label">Brand Name: </label>
	        	<div class="col-sm-9">
				      <input type="text" class="form-control" id="brandName" name="brandName" placeholder="Brand Name" autocomplete="off">
				    </div>
	        </div>
	        <div class="form-group">
	        	<label for="brandStatus" class="col-sm-3 control-label">Status: </label>
	        	<div class="col-sm-9">
				      <select class="form-control" id="brandStatus" name="brandStatus">
				      	<option value="">~~SELECT~~</option>
				      	<option value="1">Available</option>
				      	<option value="2">Not Available</option>
				      </select>
				    </div>
	        </div>
	      </div>
	      
	      <div class="modal-footer">
	        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
	        <button type="submit" class="btn btn-primary" id="createBrandBtn" data-loading-text="Loading..." autocomplete="off">Save Changes</button>
	      </div>
	    </form>
    </div>
  </div>
</div>

<!-- Edit Brand -->
<div class="modal fade" id="editBrandModal" tabindex="-1" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
    	<form class="form-horizontal" id="editBrandForm" action="php_action/editBrand.php" method="POST">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title"><i class="fa fa-edit"></i> Edit Brand</h4>
			</div>
			<div class="modal-body">
				<div id="edit-brand-messages"></div>
				<div class="modal-loading div-hide" style="width:50px; margin:auto;padding-top:50px; padding-bottom:50px;">
					<i class="fa fa-spinner fa-pulse fa-3x fa-fw"></i>
					<span class="sr-only">Loading...</span>
				</div>

				<div class="edit-brand-result">
					<?php echo CSRFProtection::getTokenField(); ?>
					<div class="form-group">
						<label for="editBrandName" class="col-sm-3 control-label">Brand Name: </label>
						<div class="col-sm-9">
							<input type="text" class="form-control" id="editBrandName" name="editBrandName" placeholder="Brand Name" autocomplete="off">
						</div>
					</div>
					<div class="form-group">
						<label for="editBrandStatus" class="col-sm-3 control-label">Status: </label>
						<div class="col-sm-9">
							<select class="form-control" id="editBrandStatus" name="editBrandStatus">
								<option value="">~~SELECT~~</option>
								<option value="1">Available</option>
								<option value="2">Not Available</option>
							</select>
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer editBrandFooter">
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
				<button type="submit" class="btn btn-primary" id="editBrandBtn" data-loading-text="Loading..." autocomplete="off">Save Changes</button>
			</div>
		</form>
    </div>
  </div>
</div>

<!-- Remove Brand -->
<div class="modal fade" id="removeMemberModal" tabindex="-1" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title"><i class="glyphicon glyphicon-trash"></i> Remove Brand</h4>
      </div>
      <div class="modal-body">
        <?php echo CSRFProtection::getTokenField(); ?>
        <p>Do you really want to remove ?</p>
      </div>
      <div class="modal-footer removeBrandFooter">
        <button type="button" class="btn btn-default" data-dismiss="modal"> <i class="glyphicon glyphicon-remove-sign"></i> Close</button>
        <button type="button" class="btn btn-primary" id="removeBrandBtn" data-loading-text="Loading..."> <i class="glyphicon glyphicon-ok-sign"></i> Save changes</button>
      </div>
    </div>
  </div>
</div>

<script src="custom/js/brand.js"></script>

<?php require_once 'includes/footer.php'; ?>