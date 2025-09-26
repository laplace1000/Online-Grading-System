<?php
require_once 'config/config.php';
require_once 'includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $role = $_POST['role'];
    
    // Basic validation
    if (empty($username) || empty($password) || empty($confirm_password) || 
        empty($first_name) || empty($last_name) || empty($email) || empty($role)) {
        $_SESSION['error'] = "All fields are required.";
    } elseif ($password !== $confirm_password) {
        $_SESSION['error'] = "Passwords do not match.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Invalid email format.";
    } elseif (strlen($password) < 8) {
        $_SESSION['error'] = "Password must be at least 8 characters long.";
    } else {
        try {
            // Start transaction
            $conn->begin_transaction();

            // Check if username or email already exists
            $stmt = $conn->prepare("SELECT user_id FROM users WHERE username = ? OR email = ?");
            $stmt->bind_param("ss", $username, $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $_SESSION['error'] = "Username or email already exists.";
            } else {
                // Hash password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                // Insert new user
                $stmt = $conn->prepare("INSERT INTO users (username, password, first_name, last_name, email, role, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
                $stmt->bind_param("ssssss", $username, $hashed_password, $first_name, $last_name, $email, $role);
                
                if ($stmt->execute()) {
                    $user_id = $stmt->insert_id;
                    
                    // If registering as a parent, store additional info
                    if ($role === 'parent') {
                        $student_email = isset($_POST['student_email']) ? trim($_POST['student_email']) : '';
                        
                        if (!empty($student_email)) {
                            // Get student ID from email
                            $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ? AND role = 'student'");
                            $stmt->bind_param("s", $student_email);
                            $stmt->execute();
                            $result = $stmt->get_result();
                            
                            if ($row = $result->fetch_assoc()) {
                                $student_id = $row['user_id'];
                                $stmt = $conn->prepare("INSERT INTO parent_student (parent_id, student_id) VALUES (?, ?)");
                                $stmt->bind_param("ii", $user_id, $student_id);
                                $stmt->execute();
                            }
                        }
                    }

                    // Log the registration
                    $log_stmt = $conn->prepare("INSERT INTO activity_log (user_id, action_type, description) VALUES (?, 'register', ?)");
                    $description = "New {$role} account created";
                    $log_stmt->bind_param("is", $user_id, $description);
                    $log_stmt->execute();

                    // Commit transaction
                    $conn->commit();
                    
                    $_SESSION['success'] = "Registration successful! You can now login.";
                    header("Location: login.php");
                    exit();
                } else {
                    throw new Exception("Error executing registration query.");
                }
            }
        } catch (Exception $e) {
            // Rollback transaction on error
            $conn->rollback();
            $_SESSION['error'] = "Registration failed: " . $e->getMessage();
            error_log("Registration error: " . $e->getMessage());
        }
    }
    
    // If there was an error, redirect back to registration page
    if (isset($_SESSION['error'])) {
        header("Location: register.php");
        exit();
    }
}

// If accessed directly without POST data, redirect to registration page
header("Location: register.php");
exit();
?> 