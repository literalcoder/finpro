<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'orgfinpro');
define('DB_USER', 'root');
define('DB_PASS', '');

// Application configuration
define('SITE_NAME', 'OrgFinPro');
define('SITE_URL', 'http://localhost:8082/origfinpro');

// File upload configuration
define('UPLOAD_PATH', __DIR__ . '/../uploads');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB

// Session configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
session_set_cookie_params([
    'secure' => true,
    'httponly' => true,
    'samesite' => 'Lax'
]);

// Email configuration
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your-email@gmail.com');
define('SMTP_PASSWORD', 'your-app-password');
define('SMTP_FROM_EMAIL', 'your-email@gmail.com');
define('SMTP_FROM_NAME', 'OrgFinPro');