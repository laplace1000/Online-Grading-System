<?php
// Enable error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Test database connection
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'online_grading_system';

try {
    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    echo "Database connection successful!<br>";
    
    // Test messages table
    $result = $conn->query("DESCRIBE messages");
    if ($result === false) {
        throw new Exception("Messages table not found: " . $conn->error);
    }
    
    echo "Messages table exists!<br>";
    echo "<pre>";
    while ($row = $result->fetch_assoc()) {
        print_r($row);
    }
    echo "</pre>";
    
    // Test users table
    $result = $conn->query("DESCRIBE users");
    if ($result === false) {
        throw new Exception("Users table not found: " . $conn->error);
    }
    
    echo "Users table exists!<br>";
    echo "<pre>";
    while ($row = $result->fetch_assoc()) {
        print_r($row);
    }
    echo "</pre>";
    
    // Test session
    session_start();
    echo "Session started successfully!<br>";
    echo "Session ID: " . session_id() . "<br>";
    
    // Test file includes
    if (file_exists('../config/config.php')) {
        echo "config.php exists!<br>";
    } else {
        echo "config.php not found!<br>";
    }
    
    if (file_exists('../includes/auth.php')) {
        echo "auth.php exists!<br>";
    } else {
        echo "auth.php not found!<br>";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
} 