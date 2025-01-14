<?php 
// Start session first
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Configure session
require_once 'session_config.php';

// Define security headers constant
define('SECURITY_HEADERS_INCLUDED', true);

// Include security headers and CSRF protection
require_once 'security_headers.php';
require_once 'csrf_utils.php';
require_once 'recaptcha_utils.php';

// Include database connection
require_once 'db_connect.php';

// Base URL for redirects
$base_url = '/php-inventory-management-system/';

// Check if user is logged in
if(!isset($_SESSION['userId'])) {
    header('location: ' . $base_url . 'index.php');	
    exit();
}

// Get the current page
$current_page = basename($_SERVER['PHP_SELF']);

// Pages that don't require CSRF verification
$csrf_exempt_pages = [
    'dashboard.php',
    'fetchBrand.php',    // AJAX data loading doesn't need CSRF
    'fetchCategories.php',
    'fetchProduct.php',
    'fetchSelectedBrand.php',  // Added this to exempt list
    'fetchSelectedCategories.php',  // Added this to exempt list
    'fetchSelectedProduct.php',
    'fetchProduct.php',
    'fetchSelectedProduct.php',
    'fetchSelectedOrder.php',  // Adding this to exempt list
    'fetchOrder.php',  // Adding this to exempt list
    'printOrder.php',  // Adding this to exempt list
    'removeOrder.php',  // Adding this to exempt list
    'getOrderReport.php',  // Adding this to exempt list
    'fetchProductData.php',  // Adding this for order management
    'fetchSelectedProduct.php',  // Adding this for order management
    'getTotal.php'  // Adding this for order management
];

// Pages that don't require MFA verification
$mfa_exempt_pages = ['verify-mfa.php'];

// Check if MFA is pending and redirect if necessary
if(isset($_SESSION['mfa_pending']) && $_SESSION['mfa_pending'] === true && !in_array($current_page, $mfa_exempt_pages)) {
    header('location: ' . $base_url . 'verify-mfa.php');
    exit();
}

// Debug logging
error_log("Request to: " . $current_page);
error_log("Request method: " . $_SERVER['REQUEST_METHOD']);
error_log("CSRF exempt check: " . (in_array($current_page, $csrf_exempt_pages) ? 'true' : 'false'));

// Verify CSRF token for POST requests, except for exempt pages
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !in_array($current_page, $csrf_exempt_pages)) {
    try {
        if (!isset($_POST['csrf_token'])) {
            error_log("CSRF token missing in request to: " . $current_page);
            http_response_code(403);
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                die(json_encode([
                    'success' => false,
                    'messages' => 'Invalid request: CSRF token missing'
                ]));
            } else {
                die('Invalid request: CSRF token missing');
            }
        }
        
        if (!CSRFProtection::validateToken()) {
            error_log("CSRF validation failed for: " . $current_page);
            http_response_code(403);
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                die(json_encode([
                    'success' => false,
                    'messages' => 'Invalid request: CSRF validation failed'
                ]));
            } else {
                die('Invalid request: CSRF validation failed');
            }
        }
    } catch (Exception $e) {
        error_log("CSRF Error: " . $e->getMessage());
        http_response_code(500);
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            die(json_encode([
                'success' => false,
                'messages' => 'Server error during request validation'
            ]));
        } else {
            die('Server error during request validation');
        }
    }
}
?>