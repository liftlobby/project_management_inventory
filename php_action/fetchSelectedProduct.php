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

// Validate product ID
if(!isset($_POST['productId'])) {
    sendErrorResponse("Product ID is required");
}

$productId = $_POST['productId'];

// Prepare and execute query
$sql = "SELECT p.product_id, p.product_name, p.product_image, p.brand_id, 
        p.categories_id, p.quantity, p.rate, p.active, p.status,
        b.brand_name, c.categories_name
        FROM product p
        JOIN brands b ON p.brand_id = b.brand_id
        JOIN categories c ON p.categories_id = c.categories_id
        WHERE p.product_id = ? AND p.status = 1";

try {
    $stmt = SecurityUtils::prepareAndExecute($sql, "i", [$productId]);
    $result = $stmt->get_result();

    if($result->num_rows > 0) { 
        $data = $result->fetch_assoc();
        $data['success'] = true;
        
        // Format image URL if exists
        if($data['product_image']) {
            // Remove any path prefixes and ensure correct path
            $data['product_image'] = preg_replace('/^(\.\.\/|stock\/)/', '', $data['product_image']);
            if (!preg_match('/^assests\/images\/stock\//', $data['product_image'])) {
                $data['product_image'] = 'assests/images/stock/' . basename($data['product_image']);
            }
        } else {
            $data['product_image'] = 'assests/images/photo_default.png';
        }
    } else {
        sendErrorResponse("Product not found or inactive");
    }
} catch (Exception $e) {
    error_log("Error in fetchSelectedProduct.php: " . $e->getMessage());
    sendErrorResponse("Failed to fetch product details. Please try again.");
}

// Close database connection
if (isset($connect)) {
    $connect->close();
}

// Return response
echo json_encode($data);