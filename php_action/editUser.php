<?php 	
require_once 'core.php';
require_once 'security_utils.php';
require_once 'AuditLogger.php';
require_once 'csrf_protection.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

$valid['success'] = array('success' => false, 'messages' => array());

if($_POST) {
    try {
        // Validate CSRF token
        if (!CSRFProtection::validateToken()) {
            throw new Exception("Invalid CSRF token");
        }

        $userId = isset($_POST['userId']) ? $_POST['userId'] : null;
        $editUserName = isset($_POST['editUserName']) ? $_POST['editUserName'] : null;
        $editEmail = isset($_POST['editUemail']) ? $_POST['editUemail'] : null;

        // Validate required fields
        if(empty($userId) || empty($editUserName) || empty($editEmail)) {
            throw new Exception("All fields are required");
        }

        // Get old user data for audit log
        $oldDataSql = "SELECT * FROM users WHERE user_id = ?";
        $oldStmt = SecurityUtils::prepareAndExecute($oldDataSql, "i", [$userId]);
        $oldData = $oldStmt->get_result()->fetch_assoc();

        if (!$oldData) {
            throw new Exception("User not found");
        }

        // Update user data
        $sql = "UPDATE users SET username = ?, email = ? WHERE user_id = ?";
        $stmt = SecurityUtils::prepareAndExecute($sql, "ssi", [
            $editUserName,
            $editEmail,
            $userId
        ]);

        if($stmt->affected_rows >= 0) {
            // Log the user update
            $logger = AuditLogger::getInstance();
            $logger->log(
                'update',
                'user',
                $userId,
                $oldData,
                [
                    'username' => $editUserName,
                    'email' => $editEmail
                ]
            );

            $valid['success'] = true;
            $valid['messages'] = "User successfully updated";
        } else {
            throw new Exception("Failed to update user");
        }

    } catch (Exception $e) {
        error_log("Error in editUser.php: " . $e->getMessage());
        error_log("Stack trace: " . $e->getTraceAsString());
        $valid['success'] = false;
        $valid['messages'] = $e->getMessage();
    }
    
} else {
    $valid['success'] = false;
    $valid['messages'] = "Invalid request method";
}

// Close the database connection
if (isset($connect)) {
    $connect->close();
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($valid);
