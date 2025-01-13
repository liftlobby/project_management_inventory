<?php 	
require_once 'core.php';
require_once 'security_utils.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Return error response function
function sendErrorResponse($message) {
    error_log("Error in fetchSelectedCategories.php: " . $message);
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

    if(!isset($_POST['categoriesId'])) {
        sendErrorResponse("Category ID is required");
    }

    $categoriesId = $_POST['categoriesId'];

    $sql = "SELECT categories_id, categories_name, categories_active, categories_status FROM categories WHERE categories_id = ?";
    $stmt = $connect->prepare($sql);
    $stmt->bind_param("i", $categoriesId);
    $stmt->execute();
    $result = $stmt->get_result();

    if($result->num_rows > 0) { 
        $row = $result->fetch_assoc();
        $row['success'] = true;
        echo json_encode($row);
    } else {
        sendErrorResponse("Category not found");
    }
    
} catch (Exception $e) {
    error_log("Exception in fetchSelectedCategories.php: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    sendErrorResponse("An error occurred: " . $e->getMessage());
} finally {
    if (isset($stmt)) {
        $stmt->close();
    }
}