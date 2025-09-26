<?php
require_once 'config/config.php';
require_once 'includes/auth.php';

// Initialize variables as null
$success = null;
$error = null;

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
        $error = "All fields are required.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } elseif (strlen($password) < 8) {
        $error = "Password must be at least 8 characters long.";
    }
    
    // If no errors, proceed with registration
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
            
            // Insert new user
            $stmt = $conn->prepare("INSERT INTO users (username, password, first_name, last_name, email, role) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssss", $username, $hashed_password, $first_name, $last_name, $email, $role);
            
            if ($stmt->execute()) {
                $success = "Registration successful! You can now login.";
            } else {
                $error = "Registration failed. Please try again.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Online Grading System</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #4a90e2, #357abd);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .register-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            padding: 2rem;
            width: 100%;
            max-width: 500px;
            margin: 2rem;
        }
        .form-floating {
            position: relative;
            margin-bottom: 1.5rem;
        }
        .form-floating > .form-control,
        .form-floating > .form-select {
            height: 60px;
            line-height: 1.25;
            padding: 1rem 0.75rem 0;
            font-size: 1rem;
            border: 2px solid #dee2e6;
            border-radius: 0.375rem;
            appearance: none;
        }
        .form-floating > label {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            padding: 1rem 0.75rem;
            overflow: hidden;
            text-align: start;
            text-overflow: ellipsis;
            white-space: nowrap;
            pointer-events: none;
            border: 1px solid transparent;
            transform-origin: 0 0;
            transition: opacity .2s ease-in-out, transform .2s ease-in-out;
            color: #6c757d;
            margin: 0;
        }
        .form-floating > .form-control::placeholder,
        .form-floating > .form-select::placeholder {
            color: transparent;
        }
        .form-floating > .form-control:focus,
        .form-floating > .form-control:not(:placeholder-shown),
        .form-floating > .form-select:focus,
        .form-floating > .form-select:not([value=""]):valid {
            padding-top: 1.625rem;
            padding-bottom: 0.625rem;
        }
        .form-floating > .form-control:focus ~ label,
        .form-floating > .form-control:not(:placeholder-shown) ~ label,
        .form-floating > .form-select:focus ~ label,
        .form-floating > .form-select:not([value=""]):valid ~ label {
            opacity: 0.85;
            transform: scale(0.85) translateY(-1rem);
            background: white;
            height: auto;
            padding: 0.25rem 0.5rem;
            color: #4a90e2;
            margin-left: 0.5rem;
            margin-top: -0.5rem;
        }
        .form-control:focus {
            border-color: #4a90e2;
            box-shadow: 0 0 0 0.2rem rgba(74, 144, 226, 0.25);
            outline: 0;
        }
        .btn-register {
            background: #4a90e2;
            border: none;
            padding: 0.75rem;
            font-weight: 500;
            transition: all 0.3s ease;
            height: 50px;
            margin-top: 1rem;
        }
        .btn-register:hover {
            background: #357abd;
            transform: translateY(-2px);
        }
        .invalid-feedback {
            display: none;
            font-size: 0.875rem;
            color: #dc3545;
            margin-top: 0.25rem;
        }
        .form-control.is-invalid ~ .invalid-feedback {
            display: block;
        }
        .form-control.is-invalid,
        .form-select.is-invalid {
            border-color: #dc3545;
            padding-right: calc(1.5em + 0.75rem);
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23dc3545'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath stroke-linecap='round' d='M6 3.75v2.5'/%3e%3ccircle cx='6' cy='8.25' r='.75'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right calc(0.375em + 0.1875rem) center;
            background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
        }
        .form-select {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23343a40' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m2 5 6 6 6-6'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 0.75rem center;
            background-size: 16px 12px;
        }
        .password-container {
            position: relative;
            margin-bottom: 0.5rem;
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
            display: flex;
            align-items: center;
        }
        .password-toggle:hover {
            color: #4a90e2;
        }
        .form-floating > .password-container > .form-control {
            padding-right: 40px;
        }
        .password-requirements {
            position: absolute;
            background: white;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 10px;
            margin-top: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            z-index: 100;
            width: 100%;
            display: none;
        }
        .password-requirements.show {
            display: block;
        }
        .password-requirements div {
            margin-bottom: 5px;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
        }
        .password-requirements div:last-child {
            margin-bottom: 0;
        }
        .password-requirements i {
            width: 20px;
            margin-right: 8px;
            text-align: center;
        }
        .password-strength {
            height: 4px;
            margin-top: 5px;
            border-radius: 2px;
            transition: all 0.3s ease;
            position: absolute;
            bottom: -4px;
            left: 0;
            width: 0;
        }
        .weak {
            background: #e74c3c;
            width: 30%;
        }
        .medium {
            background: #f1c40f;
            width: 60%;
        }
        .strong {
            background: #2ecc71;
            width: 100%;
        }
        .text-success {
            color: #2ecc71 !important;
        }
        .text-danger {
            color: #e74c3c !important;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <h2 class="text-center mb-4">Create Account</h2>
        
        <?php if (!is_null($success) && $_SERVER['REQUEST_METHOD'] === 'POST'): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo $success; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (!is_null($error) && $_SERVER['REQUEST_METHOD'] === 'POST'): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo $error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <form id="registerForm" action="register.php" method="POST" class="needs-validation" novalidate>
            <div class="form-floating">
                <input type="text" class="form-control" id="first_name" name="first_name" placeholder="First Name" required 
                       value="<?php echo isset($_POST['first_name']) ? htmlspecialchars($_POST['first_name']) : ''; ?>">
                <label for="first_name">First Name</label>
                <div class="invalid-feedback">Please enter your first name.</div>
            </div>

            <div class="form-floating">
                <input type="text" class="form-control" id="last_name" name="last_name" placeholder="Last Name" required
                       value="<?php echo isset($_POST['last_name']) ? htmlspecialchars($_POST['last_name']) : ''; ?>">
                <label for="last_name">Last Name</label>
                <div class="invalid-feedback">Please enter your last name.</div>
            </div>

            <div class="form-floating">
                <input type="email" class="form-control" id="email" name="email" placeholder="Email" required
                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                <label for="email">Email</label>
                <div class="invalid-feedback">Please enter a valid email address.</div>
            </div>

            <div class="form-floating">
                <input type="text" class="form-control" id="username" name="username" placeholder="Username" required
                       value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                <label for="username">Username</label>
                <div class="invalid-feedback">Please enter a username.</div>
            </div>

            <div class="form-floating">
                <div class="password-container">
                    <input type="password" class="form-control" id="password" name="password" 
                        placeholder="Password" required pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[!@#$%^&*]).{8,}"
                        title="Must contain at least one number, one uppercase letter, one lowercase letter, one special character, and at least 8 characters">
                    <button type="button" class="password-toggle" onclick="togglePasswordVisibility('password', 'togglePassword')">
                        <i class="fas fa-eye" id="togglePassword"></i>
                    </button>
                    <label for="password">Password</label>
                    <div class="password-strength" id="password-strength"></div>
                    <div class="password-requirements mt-2 small" id="password-requirements">
                        <div id="length-check"><i class="fas fa-times text-danger"></i> At least 8 characters</div>
                        <div id="uppercase-check"><i class="fas fa-times text-danger"></i> One uppercase letter</div>
                        <div id="lowercase-check"><i class="fas fa-times text-danger"></i> One lowercase letter</div>
                        <div id="number-check"><i class="fas fa-times text-danger"></i> One number</div>
                        <div id="special-check"><i class="fas fa-times text-danger"></i> One special character</div>
                    </div>
                </div>
            </div>

            <div class="form-floating">
                <div class="password-container">
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                        placeholder="Confirm Password" required>
                    <button type="button" class="password-toggle" onclick="togglePasswordVisibility('confirm_password', 'toggleConfirmPassword')">
                        <i class="fas fa-eye" id="toggleConfirmPassword"></i>
                    </button>
                    <label for="confirm_password">Confirm Password</label>
                    <div class="invalid-feedback" id="confirm-password-feedback">
                        Passwords do not match.
                    </div>
                </div>
            </div>

            <div class="form-floating">
                <select class="form-select" id="role" name="role" required>
                    <option value="">Select Role</option>
                    <option value="student" <?php echo (isset($_POST['role']) && $_POST['role'] === 'student') ? 'selected' : ''; ?>>Student</option>
                    <option value="teacher" <?php echo (isset($_POST['role']) && $_POST['role'] === 'teacher') ? 'selected' : ''; ?>>Teacher</option>
                    <option value="parent" <?php echo (isset($_POST['role']) && $_POST['role'] === 'parent') ? 'selected' : ''; ?>>Parent</option>
                </select>
                <label for="role">Role</label>
                <div class="invalid-feedback">Please select a role.</div>
            </div>

            <button type="submit" class="btn btn-primary btn-register w-100">
                <i class="fas fa-user-plus"></i> Register
            </button>
        </form>

        <div class="text-center mt-3">
            <p class="mb-0">Already have an account? <a href="login.php">Login here</a></p>
        </div>
    </div>

    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script>
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

                    // Password validation
                    const password = document.getElementById('password')
                    const confirmPassword = document.getElementById('confirm_password')
                    
                    if (password.value.length < 8) {
                        password.setCustomValidity('Password must be at least 8 characters long')
                        event.preventDefault()
                    } else {
                        password.setCustomValidity('')
                    }

                    if (password.value !== confirmPassword.value) {
                        confirmPassword.setCustomValidity('Passwords do not match')
                        event.preventDefault()
                    } else {
                        confirmPassword.setCustomValidity('')
                    }

                    form.classList.add('was-validated')
                }, false)
            })
        })()

        // Real-time password validation
        document.getElementById('password').addEventListener('input', function() {
            const confirmPassword = document.getElementById('confirm_password')
            if (this.value.length < 8) {
                this.setCustomValidity('Password must be at least 8 characters long')
            } else {
                this.setCustomValidity('')
            }
            // Check password match
            if (confirmPassword.value) {
                if (this.value !== confirmPassword.value) {
                    confirmPassword.setCustomValidity('Passwords do not match')
                } else {
                    confirmPassword.setCustomValidity('')
                }
            }
        })

        // Real-time password matching
        document.getElementById('confirm_password').addEventListener('input', function() {
            const password = document.getElementById('password')
            if (this.value !== password.value) {
                this.setCustomValidity('Passwords do not match')
            } else {
                this.setCustomValidity('')
            }
        })

        document.addEventListener('DOMContentLoaded', function() {
            const passwordInput = document.getElementById('password');
            const confirmPasswordInput = document.getElementById('confirm_password');
            const passwordStrength = document.getElementById('password-strength');
            const confirmFeedback = document.getElementById('confirm-password-feedback');

            // Get all check elements
            const lengthCheck = document.getElementById('length-check').querySelector('i');
            const uppercaseCheck = document.getElementById('uppercase-check').querySelector('i');
            const lowercaseCheck = document.getElementById('lowercase-check').querySelector('i');
            const numberCheck = document.getElementById('number-check').querySelector('i');
            const specialCheck = document.getElementById('special-check').querySelector('i');

            // Password strength and requirements checker
            passwordInput.addEventListener('input', function() {
                const password = this.value;
                let strength = 0;
                
                // Check length
                if(password.length >= 8) {
                    lengthCheck.className = 'fas fa-check text-success';
                    strength += 1;
                } else {
                    lengthCheck.className = 'fas fa-times text-danger';
                }
                
                // Check lowercase letters
                if(password.match(/[a-z]/)) {
                    lowercaseCheck.className = 'fas fa-check text-success';
                    strength += 1;
                } else {
                    lowercaseCheck.className = 'fas fa-times text-danger';
                }
                
                // Check uppercase letters
                if(password.match(/[A-Z]/)) {
                    uppercaseCheck.className = 'fas fa-check text-success';
                    strength += 1;
                } else {
                    uppercaseCheck.className = 'fas fa-times text-danger';
                }
                
                // Check numbers
                if(password.match(/[0-9]/)) {
                    numberCheck.className = 'fas fa-check text-success';
                    strength += 1;
                } else {
                    numberCheck.className = 'fas fa-times text-danger';
                }
                
                // Check special characters
                if(password.match(/[!@#$%^&*]/)) {
                    specialCheck.className = 'fas fa-check text-success';
                    strength += 1;
                } else {
                    specialCheck.className = 'fas fa-times text-danger';
                }

                // Update strength indicator
                passwordStrength.className = 'password-strength';
                if(strength < 3) {
                    passwordStrength.classList.add('weak');
                    passwordStrength.title = 'Weak Password';
                } else if(strength < 5) {
                    passwordStrength.classList.add('medium');
                    passwordStrength.title = 'Medium Strength Password';
                } else {
                    passwordStrength.classList.add('strong');
                    passwordStrength.title = 'Strong Password';
                }

                // Update form validation
                if(strength < 5) {
                    this.setCustomValidity('Please meet all password requirements');
                } else {
                    this.setCustomValidity('');
                }
            });

            // Confirm password validation
            confirmPasswordInput.addEventListener('input', function() {
                if(this.value !== passwordInput.value) {
                    this.setCustomValidity('Passwords do not match');
                    confirmFeedback.textContent = 'Passwords do not match';
                } else {
                    this.setCustomValidity('');
                    confirmFeedback.textContent = '';
                }
            });

            // Form submission validation
            document.getElementById('registerForm').addEventListener('submit', function(e) {
                const password = passwordInput.value;
                
                // Check for common passwords
                const commonPasswords = [
                    'password123', 'admin123', '12345678', 'qwerty123',
                    'letmein123', 'welcome123', 'monkey123', 'football123'
                ];
                
                if(commonPasswords.includes(password.toLowerCase())) {
                    e.preventDefault();
                    alert('Please use a less common password for better security.');
                    return;
                }
                
                // Ensure all requirements are met
                if(password.length < 8 || 
                   !password.match(/[A-Z]/) || 
                   !password.match(/[a-z]/) || 
                   !password.match(/[0-9]/) || 
                   !password.match(/[!@#$%^&*]/)) {
                    e.preventDefault();
                    alert('Please meet all password requirements.');
                    return;
                }
            });
        });

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

        // Add this to your existing JavaScript
        document.getElementById('password').addEventListener('focus', function() {
            document.getElementById('password-requirements').classList.add('show');
        });

        document.getElementById('password').addEventListener('blur', function(e) {
            // Check if the related target is within the requirements box
            if (!e.relatedTarget || !e.relatedTarget.closest('.password-requirements')) {
                document.getElementById('password-requirements').classList.remove('show');
            }
        });

        // Prevent the requirements box from closing when clicking inside it
        document.getElementById('password-requirements').addEventListener('mousedown', function(e) {
            e.preventDefault();
        });
    </script>
</body>
</html> 