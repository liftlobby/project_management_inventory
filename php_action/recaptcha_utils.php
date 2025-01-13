<?php
class ReCaptchaV3 {
    private static $config;
    
    public static function init() {
        if (!self::$config) {
            self::$config = require_once __DIR__ . '/../config/recaptcha_config.php';
        }
    }
    
    /**
     * Check if ReCAPTCHA is enabled
     */
    public static function isEnabled() {
        self::init();
        return self::$config['enabled'] ?? false;
    }
    
    /**
     * Get the script tag with site key
     */
    public static function getScript() {
        self::init();
        if (!self::isEnabled()) {
            return ''; // Don't output script if disabled
        }
        return sprintf(
            '<script src="https://www.google.com/recaptcha/api.js?render=%s"></script>',
            htmlspecialchars(self::$config['site_key'])
        );
    }
    
    /**
     * Get the JavaScript code for form protection
     * @param string $action The action name (e.g., 'login', 'form_submit')
     * @param string $formId The ID of the form to protect
     */
    public static function getFormProtection($action, $formId) {
        self::init();
        if (!self::isEnabled()) {
            return ''; // Don't add protection if disabled
        }
        
        $siteKey = htmlspecialchars(self::$config['site_key']);
        return <<<HTML
        <script>
        document.getElementById('$formId').addEventListener('submit', function(e) {
            e.preventDefault();
            grecaptcha.ready(function() {
                grecaptcha.execute('$siteKey', {
                    action: '$action'
                }).then(function(token) {
                    // Add token to form
                    let tokenInput = document.createElement('input');
                    tokenInput.type = 'hidden';
                    tokenInput.name = 'recaptcha_token';
                    tokenInput.value = token;
                    document.getElementById('$formId').appendChild(tokenInput);
                    
                    // Submit the form
                    document.getElementById('$formId').submit();
                });
            });
        });
        </script>
HTML;
    }
    
    /**
     * Verify the ReCAPTCHA token
     * @param string $token The ReCAPTCHA token
     * @param string $action The expected action
     * @return array ['success' => bool, 'score' => float, 'error' => string]
     */
    public static function verify($token, $action) {
        self::init();
        
        // If ReCAPTCHA is disabled, always return success
        if (!self::isEnabled()) {
            return ['success' => true, 'score' => 1.0, 'error' => ''];
        }
        
        $result = ['success' => false, 'score' => 0.0, 'error' => ''];
        
        if (empty($token)) {
            $result['error'] = 'No token provided';
            return $result;
        }
        
        $data = [
            'secret' => self::$config['secret_key'],
            'response' => $token,
            'remoteip' => $_SERVER['REMOTE_ADDR']
        ];
        
        $options = [
            'http' => [
                'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                'method' => 'POST',
                'content' => http_build_query($data),
                'timeout' => self::$config['timeout']
            ]
        ];
        
        $context = stream_context_create($options);
        $response = @file_get_contents(self::$config['verify_url'], false, $context);
        
        if ($response === false) {
            $result['error'] = 'Failed to verify token';
            return $result;
        }
        
        $response_data = json_decode($response, true);
        
        if (!$response_data) {
            $result['error'] = 'Invalid response from verification server';
            return $result;
        }
        
        // Check if verification was successful
        if (!isset($response_data['success']) || $response_data['success'] !== true) {
            $result['error'] = 'Verification failed';
            return $result;
        }
        
        // Check score
        if (!isset($response_data['score'])) {
            $result['error'] = 'No score returned';
            return $result;
        }
        
        $result['score'] = $response_data['score'];
        
        // Check action
        if (!isset($response_data['action']) || $response_data['action'] !== $action) {
            $result['error'] = 'Action mismatch';
            return $result;
        }
        
        // Check if score meets minimum threshold
        if ($response_data['score'] >= self::$config['min_score']) {
            $result['success'] = true;
        } else {
            $result['error'] = 'Score too low';
        }
        
        return $result;
    }
    
    /**
     * Log verification result for analysis
     */
    public static function logVerification($ip, $action, $score, $success) {
        if (!self::isEnabled()) {
            return; // Don't log if disabled
        }
        
        error_log(sprintf(
            'ReCAPTCHA v3: IP=%s, Action=%s, Score=%.2f, Success=%s',
            $ip,
            $action,
            $score,
            $success ? 'true' : 'false'
        ));
    }
}
