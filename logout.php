<?php
session_start();
require_once 'config/config.php';
require_once 'includes/auth.php';

// Store user_id before clearing session
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

// Update last login time if user is logged in
if ($user_id) {
    $update_query = "UPDATE users SET last_login = NOW() WHERE user_id = ?";
    $stmt = $conn->prepare($update_query);
    
    // Check if prepare was successful
    if ($stmt) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->close();
    }
}

// Log the logout action if user was logged in
if ($user_id) {
    // Get IP address
    $ip_address = $_SERVER['REMOTE_ADDR'];
    if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'];
    }
    
    $log_sql = "INSERT INTO activity_log (user_id, action_type, description, ip_address) VALUES (?, 'logout', 'User logged out', ?)";
    $stmt = $conn->prepare($log_sql);
    
    // Check if prepare was successful
    if ($stmt) {
        $stmt->bind_param("is", $user_id, $ip_address);
        $stmt->execute();
        $stmt->close();
    }
}

// Clear all session variables
$_SESSION = array();

// Destroy the session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time()-3600, '/');
}

// Destroy the session
session_destroy();

// Perform logout
$auth->logout();

// Redirect to login page
header('Location: ' . SITE_URL . '/login.php');
exit();
?> 