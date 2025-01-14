<?php 

require_once 'php_action/core.php';
require_once 'php_action/AuditLogger.php';
require_once 'php_action/session_manager.php';

// Ensure session is available
SessionManager::ensureSession();

// Log the logout event before destroying session
if(isset($_SESSION['userId'])) {
    $auditLogger = AuditLogger::getInstance();
    $auditLogger->log(
        'logout',
        'user',
        $_SESSION['userId'],
        null,
        [
            'username' => $_SESSION['username'],
            'ip_address' => $_SERVER['REMOTE_ADDR'],
            'user_agent' => $_SERVER['HTTP_USER_AGENT']
        ]
    );
}

// Destroy session
SessionManager::destroySession();

header('location:'.$store_url);	

?>