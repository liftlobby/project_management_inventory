<?php 	
require_once 'core.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Return error response function
function sendErrorResponse($message) {
    error_log("Error in fetchSelectedBrand.php: " . $message);
    http_response_code(400);
    die(json_encode(array(
        'success' => false,
        'messages' => $message
    )));
}

try {
    // Debug logging
    error_log("Request Method: " . $_SERVER['REQUEST_METHOD']);
    error_log("POST Data: " . print_r($_POST, true));

    // Check if it's a POST request
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        sendErrorResponse("Invalid request method");
    }

    if(!isset($_POST['brandId'])) {
        sendErrorResponse("Brand ID is required");
    }

    $brandId = $_POST['brandId'];

    $sql = "SELECT brand_id, brand_name, brand_active, brand_status FROM brands WHERE brand_id = ?";
    $stmt = $connect->prepare($sql);
    $stmt->bind_param("i", $brandId);
    $stmt->execute();
    $result = $stmt->get_result();

    if($result->num_rows > 0) { 
        $row = $result->fetch_assoc();
        $row['success'] = true;
        echo json_encode($row);
    } else {
        sendErrorResponse("Brand not found");
    }
    
} catch (Exception $e) {
    error_log("Exception in fetchSelectedBrand.php: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    sendErrorResponse("An error occurred: " . $e->getMessage());
} finally {
    if (isset($stmt)) {
        $stmt->close();
    }
}