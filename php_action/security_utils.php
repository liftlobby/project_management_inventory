<?php
require_once 'db_connect.php';

class SecurityUtils {
    private static $PEPPER = "MRDIY_2025"; // Change this to a random string in production
    private static $MAX_LOGIN_ATTEMPTS = 5;
    private static $LOCKOUT_TIME = 900; // 15 minutes in seconds
    
    // Argon2id password hashing
    public static function hashPassword($password) {
        $options = [
            'memory_cost' => 65536,  // 64MB
            'time_cost'   => 4,      // 4 iterations
            'threads'     => 3       // 3 parallel threads
        ];
        return password_hash($password . self::$PEPPER, PASSWORD_ARGON2ID, $options);
    }
    
    // Password verification
    public static function verifyPassword($password, $hash) {
        return password_verify($password . self::$PEPPER, $hash);
    }
    
    // Prepare and execute SQL statements safely
    public static function prepareAndExecute($sql, $types, $params) {
        global $connect;
        $stmt = $connect->prepare($sql);
        if ($stmt === false) {
            throw new Exception("Failed to prepare statement: " . $connect->error);
        }
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        return $stmt;
    }
    
    // Check login attempts
    public static function checkLoginAttempts($username) {
        global $connect;
        
        // Clean up old attempts first
        $sql = "DELETE FROM login_attempts WHERE attempt_time < (NOW() - INTERVAL 15 MINUTE)";
        self::prepareAndExecute($sql, "", []);
        
        // Count recent attempts
        $sql = "SELECT COUNT(*) as count FROM login_attempts WHERE username = ? AND attempt_time > (NOW() - INTERVAL 15 MINUTE)";
        $stmt = self::prepareAndExecute($sql, "s", [$username]);
        $result = $stmt->get_result()->fetch_assoc();
        
        return $result['count'] >= self::$MAX_LOGIN_ATTEMPTS;
    }
    
    // Record login attempt
    public static function recordLoginAttempt($username) {
        $sql = "INSERT INTO login_attempts (username, attempt_time) VALUES (?, NOW())";
        self::prepareAndExecute($sql, "s", [$username]);
    }
    
    // Generate CSRF token
    public static function generateCSRFToken() {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    
    // Verify CSRF token
    public static function verifyCSRFToken($token) {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
    
    // Password complexity check
    public static function isPasswordComplex($password) {
        // At least 8 characters
        if (strlen($password) < 8) return false;
        
        // Check for at least one uppercase letter
        if (!preg_match('/[A-Z]/', $password)) return false;
        
        // Check for at least one lowercase letter
        if (!preg_match('/[a-z]/', $password)) return false;
        
        // Check for at least one number
        if (!preg_match('/[0-9]/', $password)) return false;
        
        // Check for at least one special character
        if (!preg_match('/[!@#$%^&*()\-_=+{};:,<.>]/', $password)) return false;
        
        return true;
    }
}

// Create required database tables if they don't exist
function createSecurityTables() {
    global $connect;
    
    // Create login_attempts table
    $sql = "CREATE TABLE IF NOT EXISTS login_attempts (
        id INT(11) NOT NULL AUTO_INCREMENT,
        username VARCHAR(255) NOT NULL,
        attempt_time DATETIME NOT NULL,
        PRIMARY KEY (id),
        KEY username (username)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    
    if (!$connect->query($sql)) {
        throw new Exception("Failed to create login_attempts table: " . $connect->error);
    }
    
    // Add email column to users table if it doesn't exist
    $sql = "SHOW COLUMNS FROM users LIKE 'email'";
    $result = $connect->query($sql);
    if ($result->num_rows === 0) {
        $sql = "ALTER TABLE users ADD COLUMN email VARCHAR(255) AFTER password";
        if (!$connect->query($sql)) {
            throw new Exception("Failed to add email column: " . $connect->error);
        }
    }
}

// Create required tables
try {
    createSecurityTables();
} catch (Exception $e) {
    error_log("Security tables creation failed: " . $e->getMessage());
}
?>
