<?php 
require_once $_SERVER['DOCUMENT_ROOT'].'/php-inventory-management-system/php_action/core.php';
?>

<?php 
// Helper function to output CSRF token field
function outputCSRFTokenField() {
    echo CSRFProtection::getTokenField();
}

// Get current page name
$current_page = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html>
<head>

	<title>Stock Management System</title>

	<!-- bootstrap -->
	<link rel="stylesheet" href="assests/bootstrap/css/bootstrap.min.css">
	<!-- bootstrap theme-->
	<link rel="stylesheet" href="assests/bootstrap/css/bootstrap-theme.min.css">
	<!-- font awesome -->
	<link rel="stylesheet" href="assests/font-awesome/css/font-awesome.min.css">

  <!-- custom css -->
  <link rel="stylesheet" href="custom/css/custom.css">

	<!-- DataTables -->
  <link rel="stylesheet" href="assests/plugins/datatables/jquery.dataTables.min.css">

  <!-- file input -->
  <link rel="stylesheet" href="assests/plugins/fileinput/css/fileinput.min.css">

  <!-- jquery -->
	<script src="assests/jquery/jquery.min.js"></script>
  <!-- jquery ui -->  
  <link rel="stylesheet" href="assests/jquery-ui/jquery-ui.min.css">
  <script src="assests/jquery-ui/jquery-ui.min.js"></script>

  <!-- bootstrap js -->
	<script src="assests/bootstrap/js/bootstrap.min.js"></script>

</head>
<body>


	<nav class="navbar navbar-default navbar-static-top">
		<div class="container">
    <!-- Brand and toggle get grouped for better mobile display -->
    <div class="navbar-header">
      <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
        <span class="sr-only">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>
      <a class="navbar-brand" href="dashboard.php">Stock Management System</a>
    </div>

    <!-- Collect the nav links, forms, and other content for toggling -->
    <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">      

      <ul class="nav navbar-nav">        

      	<li id="navDashboard" class="<?php echo ($current_page == 'dashboard.php' ? 'active' : ''); ?>">
          <a href="dashboard.php"><i class="glyphicon glyphicon-list-alt"></i> Dashboard</a>
        </li>        
        <?php if(isset($_SESSION['userId']) && $_SESSION['userId']==1) { ?>
        <li id="navBrand" class="<?php echo ($current_page == 'brand.php' ? 'active' : ''); ?>">
          <a href="brand.php"><i class="glyphicon glyphicon-btc"></i>  Brand</a>
        </li>        
		<?php } ?>
		<?php if(isset($_SESSION['userId']) && $_SESSION['userId']==1) { ?>
        <li id="navCategories" class="<?php echo ($current_page == 'categories.php' ? 'active' : ''); ?>">
          <a href="categories.php"> <i class="glyphicon glyphicon-th-list"></i> Category</a>
        </li>        
		<?php } ?>
		<?php if(isset($_SESSION['userId']) && $_SESSION['userId']==1) { ?>
        <li id="navProduct" class="<?php echo ($current_page == 'product.php' ? 'active' : ''); ?>">
          <a href="product.php"> <i class="glyphicon glyphicon-ruble"></i> Product </a>
        </li> 
		<?php } ?>
		
        <li class="dropdown" id="navOrder">
          <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"> <i class="glyphicon glyphicon-shopping-cart"></i> Orders <span class="caret"></span></a>
          <ul class="dropdown-menu">            
            <li id="topNavAddOrder" class="<?php echo ($current_page == 'orders.php' && $_GET['o'] == 'add' ? 'active' : ''); ?>">
              <a href="orders.php?o=add"> <i class="glyphicon glyphicon-plus"></i> Add Orders</a>
            </li>            
            <li id="topNavManageOrder" class="<?php echo ($current_page == 'orders.php' && $_GET['o'] == 'manord' ? 'active' : ''); ?>">
              <a href="orders.php?o=manord"> <i class="glyphicon glyphicon-edit"></i> Manage Orders</a>
            </li>            
          </ul>
        </li> 
		
		<?php  if(isset($_SESSION['userId']) && $_SESSION['userId']==1) { ?>
        <li id="navReport" class="<?php echo ($current_page == 'report.php' ? 'active' : ''); ?>">
          <a href="report.php"> <i class="glyphicon glyphicon-check"></i> Report </a>
        </li>
		<?php } ?> 
    <?php  if(isset($_SESSION['userId']) && $_SESSION['userId']==1) { ?>
        <li id="importbrand" class="<?php echo ($current_page == 'importbrand.php' ? 'active' : ''); ?>">
          <a href="importbrand.php"> <i class="glyphicon glyphicon-check"></i> Import Brand </a>
        </li>
		<?php } ?>   
        <li class="dropdown" id="navSetting">
          <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"> <i class="glyphicon glyphicon-user"></i> <span class="caret"></span></a>
          <ul class="dropdown-menu">    
			<?php if(isset($_SESSION['userId']) && $_SESSION['userId']==1) { ?>
            <li id="topNavSetting" class="<?php echo ($current_page == 'setting.php' ? 'active' : ''); ?>">
              <a href="setting.php"> <i class="glyphicon glyphicon-wrench"></i> Setting</a>
            </li>
            <li id="topNavUser" class="<?php echo ($current_page == 'user.php' ? 'active' : ''); ?>">
              <a href="user.php"> <i class="glyphicon glyphicon-wrench"></i> Add User</a>
            </li>
<?php } ?>              
            <li id="topNavLogout">
              <a href="logout.php"> <i class="glyphicon glyphicon-log-out"></i> Logout</a>
            </li>            
          </ul>
        </li>        
           
      </ul>
    </div><!-- /.navbar-collapse -->
  </div><!-- /.container-fluid -->
	</nav>

	<div class="container">