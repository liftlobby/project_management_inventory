<?php 	

require_once 'core.php';
require_once 'security_utils.php';

$valid['success'] = array('success' => false, 'messages' => array());

if($_POST) {	
	$categoriesName = $_POST['categoriesName'];
  $categoriesStatus = $_POST['categoriesStatus']; 

	try {
		$sql = "INSERT INTO categories (categories_name, categories_active, categories_status) VALUES (?, ?, 1)";
		$stmt = SecurityUtils::prepareAndExecute($sql, "ss", [$categoriesName, $categoriesStatus]);
		
		if($stmt->affected_rows > 0) {
			$valid['success'] = true;
			$valid['messages'] = "Successfully Added";	
		} else {
			$valid['success'] = false;
			$valid['messages'] = "Error while adding the category";
		}
	} catch (Exception $e) {
		$valid['success'] = false;
		$valid['messages'] = "Error: " . $e->getMessage();
		error_log("Category creation error: " . $e->getMessage());
	}

	$connect->close();
	echo json_encode($valid);
} // /if $_POST