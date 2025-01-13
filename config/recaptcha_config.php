<?php
// ReCAPTCHA v3 Configuration
$config = [
    // Get these from Google ReCAPTCHA Admin Console
    // Visit: https://www.google.com/recaptcha/admin
    'site_key' => '6LfAP7YqAAAAANq9wfpM5r55RErZR9jymXZtumj3',  // Replace with your site key
    'secret_key' => '6LfAP7YqAAAAAFurgeBf9zDiDKQu36UWfgm1ySPu', // Replace with your secret key
    
    // ReCAPTCHA v3 specific settings
    'min_score' => 0.5,  // Minimum score to consider human (0.0 to 1.0)
    'action_login' => 'login',  // Action name for login
    'action_form' => 'form_submit',  // Action name for general forms
    
    // API Configuration
    'verify_url' => 'https://www.google.com/recaptcha/api/siteverify',
    'timeout' => 5  // Request timeout in seconds
];

// Validate required keys
if (empty($config['site_key']) || empty($config['secret_key'])) {
    error_log('ReCAPTCHA keys not configured. Please add your site key and secret key in config/recaptcha_config.php');
    // For development, you can disable ReCAPTCHA
    $config['enabled'] = false;
} else {
    $config['enabled'] = true;
}

return $config;
