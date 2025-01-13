<?php
// Set session cookie parameters before starting the session
$secure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
$httponly = true;
$samesite = 'Lax';
$path = '/php-inventory-management-system/';  // Updated path to match application root
$domain = '';

// PHP >= 7.3.0
if (PHP_VERSION_ID >= 70300) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => $path,
        'domain' => $domain,
        'secure' => $secure,
        'httponly' => $httponly,
        'samesite' => $samesite
    ]);
} else {
    // For older PHP versions
    session_set_cookie_params(0, $path.'; samesite='.$samesite, $domain, $secure, $httponly);
}
?>
