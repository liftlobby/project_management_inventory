<?php 	

// Only create connection if it doesn't exist
if (!isset($connect)) {
    $localhost = "localhost";
    $username = "root";
    $password = "";
    $dbname = "store";
    $store_url = "http://localhost/php-inventory-management-system/";

    // db connection
    $connect = new mysqli($localhost, $username, $password, $dbname);
    // check connection
    if($connect->connect_error) {
        error_log("Database connection failed: " . $connect->connect_error);
        die("Connection Failed : " . $connect->connect_error);
    } else {
        error_log("Database connected successfully");
    }
}
?>