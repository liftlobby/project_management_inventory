<?php
class CSRFProtection {
    /**
     * Generate a new CSRF token and store it in the session
     * @return string The generated token
     */
    public static function generateToken() {
        // Use a single token per session instead of multiple tokens
        if (!isset($_SESSION['csrf_token'])) {
            // Generate a cryptographically secure random token
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            $_SESSION['csrf_token_time'] = time();
        }
        
        return $_SESSION['csrf_token'];
    }

    /**
     * Get HTML for CSRF token field
     */
    public static function getTokenField() {
        $token = self::generateToken();
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
    }

    /**
     * Validate CSRF token from current request
     * @return bool True if token is valid, false otherwise
     */
    public static function validateToken() {
        $token = $_POST['csrf_token'] ?? '';
        return self::validateTokenValue($token);
    }

    /**
     * Validate a specific CSRF token value
     * @param string $token The token to validate
     * @return bool True if token is valid, false otherwise
     */
    public static function validateTokenValue($token) {
        if (empty($token) || !isset($_SESSION['csrf_token'])) {
            error_log("CSRF validation failed: Token empty or no token in session");
            return false;
        }

        if ($token !== $_SESSION['csrf_token']) {
            error_log("CSRF validation failed: Token mismatch");
            error_log("Received token: " . $token);
            error_log("Session token: " . $_SESSION['csrf_token']);
            return false;
        }

        // Check if token is not expired (2 hours lifetime)
        if (time() - $_SESSION['csrf_token_time'] > 7200) {
            error_log("CSRF validation failed: Token expired");
            unset($_SESSION['csrf_token']);
            unset($_SESSION['csrf_token_time']);
            return false;
        }

        return true;
    }

    /**
     * Verify CSRF token from POST request
     * Exits with 403 status if token is invalid
     */
    public static function verifyRequest() {
        // Skip CSRF check for GET requests
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            return;
        }

        if (!self::validateToken()) {
            error_log("CSRF verification failed for " . $_SERVER['REQUEST_URI']);
            http_response_code(403);
            die(json_encode([
                'success' => false,
                'messages' => 'Security token validation failed. Please refresh the page and try again.'
            ]));
        }
    }
}
