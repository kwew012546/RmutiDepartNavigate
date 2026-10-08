<?php
/**
 * Database Connection configuration
 * Supports custom configuration from config.local.php or environment variables,
 * with graceful fallback to default parameters.
 */

$localConfigFile = __DIR__ . '/config.local.php';

if (file_exists($localConfigFile)) {
    $dbConfig = require $localConfigFile;
} else {
    $dbConfig = [
        'host' => getenv('DB_HOST') ?: "db1-cluster.rmuti.ac.th",
        'user' => getenv('DB_USER') ?: "deptnavigator-fet-st",
        'pass' => getenv('DB_PASS') ?: "hVZUQP54uCVh",
        'name' => getenv('DB_NAME') ?: "deptnavigator_fet_st_db",
    ];
}

$servername = $dbConfig['host'];
$username   = $dbConfig['user'];
$password   = $dbConfig['pass'];
$dbname     = $dbConfig['name'];

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    error_log("Database connection error: " . $conn->connect_error);
    die("Database connection failed. Please contact the system administrator.");
}

$conn->set_charset("utf8mb4");
