<?php
define('DB_HOST', 'localhost');
define('DB_NAME', 'nyemazi_SoleMate');
define('DB_USER', 'nyemazi_SoleMate'); 
define('DB_PASS', 'cKqQHr4J9d3PvR8R5stp');

function getConnection() {
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
        return $conn;
    } catch(PDOException $e) {
        error_log("Connection failed: " . $e->getMessage());
        return null;
    }
}

// Test connection (uncomment for debugging)
// $conn = getConnection();
// if ($conn) echo "Connected successfully!";
?>
