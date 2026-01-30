<?php
// InfinityFree MySQL Hostname Checker
// This script tries multiple possible hostnames

$username = 'if0_41018630';
$password = 'VisionInvestors';
$database = 'if0_41018630_viig';  // Change this to your actual database name if different

// Try different hostnames
$hostnames = [
    'sql107.inf',
    'sql107.infinityfree.com',
    'localhost',
    'sql.infinityfree.net',
    'sql1.infinityfree.net',
    '127.0.0.1',
];

echo "=== InfinityFree MySQL Hostname Checker ===\n\n";
echo "Database Name: $database\n";
echo "Username: $username\n";
echo "Testing hostnames...\n\n";

foreach ($hostnames as $host) {
    echo "Testing: $host ... ";
    try {
        $conn = new PDO(
            "mysql:host=$host;dbname=$database;charset=utf8mb4",
            $username,
            $password,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_TIMEOUT => 3
            ]
        );
        echo "✓ SUCCESS!\n";
        echo "  Use this hostname in config.php: $host\n\n";
        
        // Show database info
        $result = $conn->query("SELECT VERSION()");
        $version = $result->fetch()[0];
        echo "  MySQL Version: $version\n";
        echo "  Database: $database\n\n";
        exit;
    } catch (Exception $e) {
        echo "✗ Failed\n";
    }
}

echo "\n\n⚠️  IMPORTANT CHECKLIST:\n";
echo "1. Have you created the database '$database' in InfinityFree?\n";
echo "2. Is the database name exactly correct?\n";
echo "3. Try these steps:\n";
echo "   a) Log into InfinityFree control panel\n";
echo "   b) Go to MySQL Databases\n";
echo "   c) Create a new database (if not already created)\n";
echo "   d) Come back and try this page again\n\n";
echo "If still having issues, check your InfinityFree account email for\n";
echo "the exact database credentials they provided.\n";
?>
