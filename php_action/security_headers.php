<?php
// Prevent this file from being accessed directly
if(!defined('SECURITY_HEADERS_INCLUDED')) {
    header('HTTP/1.0 403 Forbidden');
    exit;
}

// Function to set security headers
function setSecurityHeaders() {
    // Check if this is an AJAX request
    $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
              strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
              
    // For AJAX requests, only set essential security headers
    if ($isAjax) {
        // Prevent clickjacking
        header('X-Frame-Options: DENY');
        // Prevent MIME type sniffing
        header('X-Content-Type-Options: nosniff');
        // Remove PHP version
        header_remove('X-Powered-By');
        return;
    }
    
    // For regular page requests, set full security headers
    // Prevent clickjacking
    header('X-Frame-Options: SAMEORIGIN');
    
    // Prevent MIME type sniffing
    header('X-Content-Type-Options: nosniff');
    
    // Enable XSS protection
    header('X-XSS-Protection: 1; mode=block');
    
    // Referrer Policy
    header('Referrer-Policy: strict-origin-when-cross-origin');
    
    // Content Security Policy
    $cspHeader = "Content-Security-Policy: ".
        "default-src 'self'; " .
        "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://ajax.googleapis.com https://maxcdn.bootstrapcdn.com; " .
        "style-src 'self' 'unsafe-inline' https://maxcdn.bootstrapcdn.com; " .
        "img-src 'self' data: blob: *; " .
        "font-src 'self' https://maxcdn.bootstrapcdn.com; " .
        "connect-src 'self' blob:; " .
        "object-src 'self' blob:; " .
        "media-src 'self' blob:; " .
        "frame-src 'self'; " .
        "worker-src 'self' blob:; " .
        "frame-ancestors 'self'; " .
        "form-action 'self'; " .
        "base-uri 'self';";
    header($cspHeader);
    
    // Permissions Policy
    header("Permissions-Policy: " .
        "accelerometer=(), " .
        "camera=(), " .
        "geolocation=(), " .
        "gyroscope=(), " .
        "magnetometer=(), " .
        "microphone=(), " .
        "payment=(), " .
        "usb=()");
    
    // Remove PHP version
    header_remove('X-Powered-By');
}

// Call the function to set headers
setSecurityHeaders();
