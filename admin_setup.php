<?php
require_once 'config/config.php';
require_once 'includes/auth.php';

// Initialize variables
$success = null;
$error = null;

// Check if admin setup has already been done
$stmt = $conn->prepare("SELECT COUNT(*) as admin_count FROM users WHERE role = 'admin'");
$stmt->execute();
$result = $stmt->get_result();
$admin_exists = $result->fetch_assoc()['admin_count'] > 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $admin_key = trim($_POST['admin_key']);
    
    // Verify admin setup key
    if ($admin_key !== ADMIN_SETUP_KEY) {
        $error = "Invalid admin setup key.";
    } elseif (empty($username) || empty($password) || empty($confirm_password) || 
        empty($first_name) || empty($last_name) || empty($email)) {
        $error = "All fields are required.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } elseif (strlen($password) < 8) {
        $error = "Password must be at least 8 characters long.";
    }
    
    if (empty($error)) {
        // Check if username or email already exists
        $stmt = $conn->prepare("SELECT user_id FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = "Username or email already exists.";
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert new admin user
            $stmt = $conn->prepare("INSERT INTO users (username, password, first_name, last_name, email, role, is_active) VALUES (?, ?, ?, ?, ?, 'admin', 1)");
            $stmt->bind_param("sssss", $username, $hashed_password, $first_name, $last_name, $email);
            
            if ($stmt->execute()) {
                $success = "Admin account created successfully! Redirecting to login page...";
                echo "<script>
                    setTimeout(function() {
                        window.location.href = 'login.php';
                    }, 3000);
                </script>";
            } else {
                $error = "Registration failed. Please try again.";
            }
        }
    }
}

// Show the form if:
// 1. No admin exists, OR
// 2. There was an error, OR
// 3. The page is being accessed directly (not after a POST request)
if (!$admin_exists || !empty($error) || $_SERVER['REQUEST_METHOD'] !== 'POST'): 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Setup - Online Grading System</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #1a237e, #0d47a1);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .setup-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
            padding: 2rem;
            width: 100%;
            max-width: 500px;
            margin: 2rem;
        }
        .form-floating {
            position: relative;
            margin-bottom: 1.5rem;
        }
        .password-container {
            position: relative;
        }
        .password-toggle {
            position: absolute;
            right: 10px;
            top: 20px;
            transform: translateY(-50%);
            cursor: pointer;
            color: #6c757d;
            z-index: 10;
            padding: 5px;
            background: transparent;
            border: none;
        }
        .password-toggle:hover {
            color: #0d47a1;
        }
        .btn-setup {
            background: #0d47a1;
            color: white;
            border: none;
            padding: 0.75rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        .btn-setup:hover {
            background: #1a237e;
            transform: translateY(-2px);
            color: white;
        }
    </style>
</head>
<body>
    <div class="setup-container">
        <h2 class="text-center mb-4">Admin Setup</h2>
        <p class="text-muted text-center mb-4">Create the initial administrator account</p>
        
        <?php if (!is_null($success)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo $success; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (!is_null($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo $error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <form action="admin_setup.php" method="POST" class="needs-validation" novalidate>
            <div class="form-floating">
                <input type="text" class="form-control" id="first_name" name="first_name" placeholder="First Name" required>
                <label for="first_name">First Name</label>
            </div>

            <div class="form-floating">
                <input type="text" class="form-control" id="last_name" name="last_name" placeholder="Last Name" required>
                <label for="last_name">Last Name</label>
            </div>

            <div class="form-floating">
                <input type="email" class="form-control" id="email" name="email" placeholder="Email" required>
                <label for="email">Email</label>
            </div>

            <div class="form-floating">
                <input type="text" class="form-control" id="username" name="username" placeholder="Username" required>
                <label for="username">Username</label>
            </div>

            <div class="form-floating">
                <div class="password-container">
                    <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
                    <button type="button" class="password-toggle" onclick="togglePasswordVisibility('password', 'togglePassword')">
                        <i class="fas fa-eye" id="togglePassword"></i>
                    </button>
                    <label for="password">Password</label>
                </div>
            </div>

            <div class="form-floating">
                <div class="password-container">
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Confirm Password" required>
                    <button type="button" class="password-toggle" onclick="togglePasswordVisibility('confirm_password', 'toggleConfirmPassword')">
                        <i class="fas fa-eye" id="toggleConfirmPassword"></i>
                    </button>
                    <label for="confirm_password">Confirm Password</label>
                </div>
            </div>

            <div class="form-floating">
                <input type="password" class="form-control" id="admin_key" name="admin_key" placeholder="Admin Setup Key" required>
                <label for="admin_key">Admin Setup Key</label>
            </div>

            <button type="submit" class="btn btn-setup w-100">
                <i class="fas fa-user-shield"></i> Create Admin Account
            </button>
        </form>
    </div>

    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script>
        function togglePasswordVisibility(inputId, toggleId) {
            const passwordInput = document.getElementById(inputId);
            const toggleIcon = document.getElementById(toggleId);
            
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

        // Form validation
        (function() {
            'use strict'
            var forms = document.querySelectorAll('.needs-validation')
            Array.prototype.slice.call(forms).forEach(function(form) {
                form.addEventListener('submit', function(event) {
                    if (!form.checkValidity()) {
                        event.preventDefault()
                        event.stopPropagation()
                    }
                    form.classList.add('was-validated')
                }, false)
            })
        })()
    </script>
</body>
</html>
<?php endif; ?> 