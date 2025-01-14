<?php
class SessionManager {
    private static $SESSION_TIMEOUT = 1800; // 30 minutes in seconds
    
    /**
     * Ensure session is started
     */
    public static function ensureSession() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    public static function validateSession() {
        self::ensureSession();
        
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
        self::ensureSession();
        
        if (!isset($_SESSION['userId'])) {
            header('location: index.php');
            exit();
        }
        self::validateSession();
    }
    
    /**
     * Destroy the current session
     */
    public static function destroySession() {
        self::ensureSession();
        session_unset();
        session_destroy();
    }
}
?>
