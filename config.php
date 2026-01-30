<?php
// Start session only if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database configuration
if (!defined('DB_HOST')) {
    define('DB_HOST', 'sql107.infinityfree.com');
    define('DB_NAME', 'if0_41018630_viig');
    define('DB_USER', 'if0_41018630');
    define('DB_PASS', 'VisionInvestors');

    // Create PDO connection
    try {
        $conn = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]
        );
    } catch (PDOException $e) {
        die("Connection failed: " . $e->getMessage());
    }

    // Site configuration
    define('SITE_NAME', 'VIIG System');
    define('SITE_URL', 'http://viig-netinvestment.great-site.net/');
    define('UPLOAD_PATH', 'uploads/');
    define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
    define('ALLOWED_TYPES', ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'gif']);

    // Create uploads directory if it doesn't exist
    if (!file_exists(UPLOAD_PATH)) {
        mkdir(UPLOAD_PATH, 0777, true);
    }
}

// Check if user is logged in
if (!function_exists('isLoggedIn')) {
    function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }
}

// Check if user is leader
if (!function_exists('isLeader')) {
    function isLeader() {
        return isset($_SESSION['role']) && $_SESSION['role'] == 'leader';
    }
}

// Sanitize input
if (!function_exists('sanitize')) {
    function sanitize($input) {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }
}

// Redirect function
if (!function_exists('redirect')) {
    function redirect($url) {
        header("Location: " . $url);
        exit();
    }
}

// Generate unique filename
if (!function_exists('generateFileName')) {
    function generateFileName($originalName) {
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        return time() . '_' . bin2hex(random_bytes(8)) . '.' . $extension;
    }
}
?>