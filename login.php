<?php
require_once 'config/config.php';
require_once 'includes/auth.php';

// Check if already logged in
if ($auth->isLoggedIn()) {
    $redirect_path = '/login.php'; // Default path
    switch($_SESSION['role']) {
        case 'admin':
            $redirect_path = '/admin/dashboard.php';
            break;
        case 'teacher':
            $redirect_path = '/teacher/dashboard.php';
            break;
        case 'student':
            $redirect_path = '/student/dashboard.php';
            break;
        case 'parent':
            $redirect_path = '/parent/dashboard.php';
            break;
    }
    header('Location: ' . BASE_URL . $redirect_path);
    exit();
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? '';
    
    if (empty($username) || empty($password) || empty($role)) {
        $error = "All fields are required.";
    } else {
        // Get user from database
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? AND role = ? AND is_active = 1");
        $stmt->bind_param("ss", $username, $role);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        
        if ($user && password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['first_name'] = $user['first_name'];
            $_SESSION['last_name'] = $user['last_name'];
            $_SESSION['email'] = $user['email'];
            
            // Update last login time
            $stmt = $conn->prepare("UPDATE users SET last_login = NOW() WHERE user_id = ?");
            $stmt->bind_param("i", $user['user_id']);
            $stmt->execute();
            
            // Redirect based on role
            $redirect_path = '/login.php'; // Default path
            switch($user['role']) {
                case 'admin':
                    $redirect_path = '/admin/dashboard.php';
                    break;
                case 'teacher':
                    $redirect_path = '/teacher/dashboard.php';
                    break;
                case 'student':
                    $redirect_path = '/student/dashboard.php';
                    break;
                case 'parent':
                    $redirect_path = '/parent/check_setup.php';
                    break;
            }
            
            header('Location: ' . BASE_URL . $redirect_path);
            exit();
        } else {
            $error = "Invalid username or password for the selected role.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body {
            background: linear-gradient(120deg, #3498db, #8e44ad);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .login-container {
            background: rgba(255, 255, 255, 0.9);
            border-radius: 10px;
            padding: 2rem;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.2);
        }

        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .login-header i {
            font-size: 3rem;
            color: #3498db;
            margin-bottom: 1rem;
        }

        .login-header h2 {
            color: #2c3e50;
            font-size: 1.8rem;
            margin-bottom: 0.5rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-control {
            border-radius: 25px;
            padding: 0.75rem 1.25rem;
            border: 2px solid #e0e0e0;
            transition: all 0.3s;
        }

        .form-control:focus {
            border-color: #3498db;
            box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
        }

        .btn-login {
            background: #3498db;
            border: none;
            border-radius: 25px;
            padding: 0.75rem;
            font-size: 1rem;
            font-weight: 600;
            width: 100%;
            color: white;
            transition: all 0.3s;
        }

        .btn-login:hover {
            background: #2980b9;
            transform: translateY(-2px);
        }

        .alert {
            border-radius: 10px;
            margin-bottom: 1.5rem;
        }

        .form-select {
            border-radius: 25px;
            padding: 0.75rem 1.25rem;
            border: 2px solid #e0e0e0;
            transition: all 0.3s;
        }

        .form-select:focus {
            border-color: #3498db;
            box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
        }

        .nav-links {
            text-align: center;
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid #e0e0e0;
        }

        .nav-links a {
            color: #3498db;
            text-decoration: none;
            margin: 0 10px;
            font-size: 0.9rem;
            transition: all 0.3s;
        }

        .nav-links a:hover {
            color: #2980b9;
            text-decoration: underline;
        }

        .password-container {
            position: relative;
        }
        .password-toggle {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #6c757d;
        }
        .password-toggle:hover {
            color: #495057;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <i class="fas fa-graduation-cap"></i>
            <h2>Welcome Back!</h2>
            <p class="text-muted">Please login to continue</p>
        </div>
        
        <?php if (!is_null($error) && $_SERVER['REQUEST_METHOD'] === 'POST'): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" class="needs-validation" novalidate>
            <div class="form-group">
                <input type="text" class="form-control" name="username" placeholder="Username" required>
            </div>
            
            <div class="form-group">
                <div class="password-container position-relative">
                    <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
                    <span class="password-toggle" onclick="togglePasswordVisibility()">
                        <i class="fas fa-eye" id="togglePassword"></i>
                    </span>
                </div>
            </div>
            
            <div class="form-floating">
                <select class="form-select" id="role" name="role" required>
                    <option value="">Select Role</option>
                    <option value="admin">Administrator</option>
                    <option value="teacher">Teacher</option>
                    <option value="student">Student</option>
                    <option value="parent">Parent</option>
                </select>
                <label for="role">Role</label>
            </div>
            
            <button type="submit" class="btn btn-login">
                <i class="fas fa-sign-in-alt"></i> Login
            </button>
        </form>

        <div class="nav-links">
            <a href="index.php"><i class="fas fa-home"></i> Back to Homepage</a>
            <a href="register.php"><i class="fas fa-user-plus"></i> Create Account</a>
        </div>
    </div>

    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            form.addEventListener('submit', function(event) {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            });
        });

        function togglePasswordVisibility() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('togglePassword');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }

        // Add event listener for form submission
        document.querySelector('form').addEventListener('submit', function(e) {
            const passwordInput = document.getElementById('password');
            const password = passwordInput.value;

            // Basic password validation
            if (password.length < 8) {
                e.preventDefault();
                alert('Password must be at least 8 characters long.');
                return;
            }

            // Additional security check - prevent common passwords
            const commonPasswords = ['password', '12345678', 'qwerty123'];
            if (commonPasswords.includes(password.toLowerCase())) {
                e.preventDefault();
                alert('Please use a more secure password.');
                return;
            }
        });
    </script>
</body>
</html> 