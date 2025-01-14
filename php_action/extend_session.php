<?php
require_once 'session_manager.php';

// Update last activity time
SessionManager::validateSession();

// Return success response
header('Content-Type: application/json');
echo json_encode(['success' => true]);
?>
