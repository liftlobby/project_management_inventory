<?php
// Only set session cookie parameters if session hasn't started
if (session_status() === PHP_SESSION_NONE) {
    $secure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
    $httponly = true;
    $samesite = 'Lax';
    $path = '/php-inventory-management-system/';
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
}
?>
