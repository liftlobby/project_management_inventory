<?php
// Security Configuration
return [
    'PEPPER' => 'MRDIY_2025', // Change this to a random string in production
    'MAX_LOGIN_ATTEMPTS' => 5,
    'LOCKOUT_TIME' => 900, // 15 minutes in seconds
    'MEMORY_COST' => 65536, // 64MB
    'TIME_COST' => 4,      // 4 iterations
    'THREADS' => 3,        // 3 parallel threads
    'MIN_PASSWORD_LENGTH' => 8,
    
    // MFA Settings
    'MFA_CODE_LENGTH' => 6,
    'MFA_CODE_EXPIRY' => 600, // 10 minutes in seconds
    
    // Email Settings
    'SMTP_HOST' => 'smtp.gmail.com',
    'SMTP_PORT' => 587,
    'SMTP_USERNAME' => 'kaizen20020222@gmail.com',
    'SMTP_PASSWORD' => 'hffp nlwh faqn jmet',
    'SMTP_FROM_EMAIL' => 'kaizen20020222@gmail.com',
    'SMTP_FROM_NAME' => 'Inventory System'
];
