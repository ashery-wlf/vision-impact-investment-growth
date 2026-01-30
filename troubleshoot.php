<?php
// Database troubleshooting script
// This helps identify the correct database connection details

echo "=== VIIG System Database Troubleshooting ===\n\n";

// Current config
$host = 'sql107.inf';
$user = 'if0_41018630';
$pass = 'VisionInvestors';

echo "Testing connection with current credentials:\n";
echo "Host: " . $host . "\n";
echo "User: " . $user . "\n";
echo "Password: ***\n\n";

// List of possible database names to try
$possibleDatabases = [
    'if0_41018630_viig',
    'if0_41018630_viigdb',
    'if0_41018630_db',
    'if0_41018630_mysql',
    'viig_db',
    'VIIG_db'
];

echo "Attempting to connect to possible databases:\n";
echo "==========================================\n\n";

foreach ($possibleDatabases as $dbName) {
    try {
        $conn = new PDO(
            "mysql:host=" . $host . ";dbname=" . $dbName . ";charset=utf8mb4",
            $user,
            $pass,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        echo "✓ SUCCESS: Database '$dbName' is accessible!\n";
        echo "  USE THIS IN config.php: define('DB_NAME', '$dbName');\n\n";
        
        // Show tables
        $result = $conn->query("SHOW TABLES");
        $tables = $result->fetchAll();
        if (!empty($tables)) {
            echo "  Tables found: " . count($tables) . "\n";
            foreach ($tables as $table) {
                echo "    - " . $table[0] . "\n";
            }
        } else {
            echo "  (Database is empty - you need to import db.sql)\n";
        }
    } catch (PDOException $e) {
        echo "✗ FAILED: '$dbName' - " . $e->getMessage() . "\n\n";
    }
}

echo "\n\n=== INSTRUCTIONS ===\n";
echo "1. Once you find a working database above, update config.php line 8 with the correct name\n";
echo "2. Then import db.sql into that database using phpMyAdmin\n";
echo "3. Delete this file (troubleshoot.php) after testing\n";
?>
