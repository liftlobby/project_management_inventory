<?php 	

require_once 'core.php';
require_once 'security_utils.php';

// Return error response function
function sendErrorResponse($message) {
    die(json_encode(array(
        'success' => false,
        'messages' => $message
    )));
}

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendErrorResponse("Invalid request method");
}

// Verify CSRF token
if (!isset($_POST['csrf_token'])) {
    sendErrorResponse("CSRF token is missing");
}

if (!CSRFProtection::validateToken()) {
    sendErrorResponse("Invalid CSRF token");
}

if(!isset($_POST['userid'])) {
    sendErrorResponse("User ID is required");
}

$userid = $_POST['userid'];
$sql = "SELECT * FROM users WHERE user_id = ?";
$stmt = SecurityUtils::prepareAndExecute($sql, "i", [$userid]);
$result = $stmt->get_result();

$row = array();
if($result->num_rows > 0) { 
    $row = $result->fetch_array();
    $row['success'] = true;
} else {
    sendErrorResponse("User not found");
}

if (isset($connect)) {
    $connect->close();
}

echo json_encode($row);