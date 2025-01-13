<?php
session_start();

class SessionManager {
    private static $SESSION_TIMEOUT = 1800; // 30 minutes in seconds
    
    public static function validateSession() {
        if (isset($_SESSION['last_activity']) && 
            (time() - $_SESSION['last_activity'] > self::$SESSION_TIMEOUT)) {
            // Session has expired
            session_unset();
            session_destroy();
            header('location: index.php?timeout=1');
            exit();
        }
        
        // Update last activity timestamp
        $_SESSION['last_activity'] = time();
    }
    
    public static function requireLogin() {
        if (!isset($_SESSION['userId'])) {
            header('location: index.php');
            exit();
        }
        self::validateSession();
    }
}
?>
