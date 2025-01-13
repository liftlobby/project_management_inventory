<?php 
session_start();

$document_root = $_SERVER['DOCUMENT_ROOT'];
$project_path = '/php-inventory-management-system';
require_once $document_root . $project_path . '/php_action/db_connect.php';

// echo $_SESSION['userId'];

if(!$_SESSION['userId']) {
	header('location:'.$store_url);	
} 

?>