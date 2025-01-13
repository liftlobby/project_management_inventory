<?php
require_once 'db_connect.php';

class SecurityUtils {
    private static $config;
    
    public static function init() {
        if (!isset(self::$config)) {
            self::$config = require_once __DIR__ . '/../config/security_config.php';
            self::createMFATables();
        }
    }
    
    public static function getConfig($key) {
        if (!isset(self::$config)) {
            self::init();
        }
        return self::$config[$key] ?? null;
    }
    
    // Prepare and execute SQL statements safely
    public static function prepareAndExecute($sql, $types, $params) {
        global $connect;
        
        try {
            $stmt = $connect->prepare($sql);
            if ($stmt === false) {
                throw new Exception("Failed to prepare statement: " . $connect->error);
            }
            
            if (!empty($params)) {
                $stmt->bind_param($types, ...$params);
            }
            
            if (!$stmt->execute()) {
                throw new Exception("Failed to execute statement: " . $stmt->error);
            }
            
            return $stmt;
        } catch (Exception $e) {
            error_log("Database error in prepareAndExecute: " . $e->getMessage());
            throw $e;
        }
    }
    
    // Generate MFA code
    public static function generateMFACode() {
        if (!isset(self::$config)) {
            self::init();
        }
        
        $length = self::getConfig('MFA_CODE_LENGTH');
        $code = '';
        for ($i = 0; $i < $length; $i++) {
            $code .= mt_rand(0, 9);
        }
        error_log("Generated MFA code: " . $code);
        return $code;
    }
    
    // Store MFA code
    public static function storeMFACode($userId, $code) {
        if (!isset(self::$config)) {
            self::init();
        }
        
        try {
            error_log("Attempting to store MFA code for user: " . $userId);
            
            // First, clean up expired codes
            $expiry = self::getConfig('MFA_CODE_EXPIRY');
            $sql = "DELETE FROM mfa_codes WHERE created_at < (NOW() - INTERVAL ? SECOND)";
            self::prepareAndExecute($sql, "i", [$expiry]);
            
            // Then store new code
            $sql = "INSERT INTO mfa_codes (user_id, code, created_at) VALUES (?, ?, NOW())";
            self::prepareAndExecute($sql, "is", [$userId, $code]);
            
            error_log("Successfully stored MFA code");
            return true;
        } catch (Exception $e) {
            error_log("Failed to store MFA code: " . $e->getMessage());
            return false;
        }
    }
    
    // Verify MFA code
    public static function verifyMFACode($userId, $code) {
        if (!isset(self::$config)) {
            self::init();
        }
        
        try {
            error_log("Attempting to verify MFA code for user: " . $userId);
            
            $expiry = self::getConfig('MFA_CODE_EXPIRY');
            $sql = "SELECT * FROM mfa_codes WHERE user_id = ? AND code = ? AND 
                    created_at > (NOW() - INTERVAL ? SECOND)";
            $stmt = self::prepareAndExecute($sql, "isi", [$userId, $code, $expiry]);
            $result = $stmt->get_result();
            
            if ($result->num_rows === 1) {
                error_log("Valid MFA code found");
                // Delete the used code
                $sql = "DELETE FROM mfa_codes WHERE user_id = ? AND code = ?";
                self::prepareAndExecute($sql, "is", [$userId, $code]);
                return true;
            }
            
            error_log("No valid MFA code found");
            return false;
        } catch (Exception $e) {
            error_log("Failed to verify MFA code: " . $e->getMessage());
            return false;
        }
    }
    
    // Create MFA tables if they don't exist
    private static function createMFATables() {
        global $connect;
        
        try {
            error_log("Attempting to create MFA tables");
            
            $sql = "CREATE TABLE IF NOT EXISTS mfa_codes (
                id INT PRIMARY KEY AUTO_INCREMENT,
                user_id INT NOT NULL,
                code VARCHAR(10) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX (user_id),
                INDEX (created_at)
            )";
            
            if (!$connect->query($sql)) {
                throw new Exception("Failed to create MFA tables: " . $connect->error);
            }
            
            error_log("MFA tables created successfully");
        } catch (Exception $e) {
            error_log("Failed to create MFA tables: " . $e->getMessage());
            throw $e;
        }
    }
    
    // Password verification with pepper
    public static function verifyPassword($password, $hash) {
        if (!isset(self::$config)) {
            self::init();
        }
        return password_verify($password . self::getConfig('PEPPER'), $hash);
    }
    
    // Check login attempts
    public static function checkLoginAttempts($username) {
        if (!isset(self::$config)) {
            self::init();
        }
        
        try {
            // Clean up old attempts
            $lockoutTime = self::getConfig('LOCKOUT_TIME');
            $sql = "DELETE FROM login_attempts WHERE attempt_time < (NOW() - INTERVAL ? SECOND)";
            self::prepareAndExecute($sql, "i", [$lockoutTime]);
            
            // Count recent attempts
            $sql = "SELECT COUNT(*) as count FROM login_attempts WHERE username = ? AND 
                    attempt_time > (NOW() - INTERVAL ? SECOND)";
            $stmt = self::prepareAndExecute($sql, "si", [$username, $lockoutTime]);
            $result = $stmt->get_result()->fetch_assoc();
            
            return $result['count'] >= self::getConfig('MAX_LOGIN_ATTEMPTS');
        } catch (Exception $e) {
            error_log("Failed to check login attempts: " . $e->getMessage());
            return false;
        }
    }
    
    // Record login attempt
    public static function recordLoginAttempt($username) {
        try {
            $sql = "INSERT INTO login_attempts (username, attempt_time) VALUES (?, NOW())";
            self::prepareAndExecute($sql, "s", [$username]);
        } catch (Exception $e) {
            error_log("Failed to record login attempt: " . $e->getMessage());
        }
    }
    
    // Argon2id password hashing
    public static function hashPassword($password) {
        if (!isset(self::$config)) {
            self::init();
        }
        
        $options = [
            'memory_cost' => self::getConfig('MEMORY_COST'),  
            'time_cost'   => self::getConfig('TIME_COST'),     
            'threads'     => self::getConfig('THREADS')       
        ];
        return password_hash($password . self::getConfig('PEPPER'), PASSWORD_ARGON2ID, $options);
    }
    
    // Password complexity check
    public static function isPasswordComplex($password) {
        if (!isset(self::$config)) {
            self::init();
        }
        
        // At least 8 characters
        if (strlen($password) < self::getConfig('MIN_PASSWORD_LENGTH')) return false;
        
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

// Initialize SecurityUtils
SecurityUtils::init();
?>
