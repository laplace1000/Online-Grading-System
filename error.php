<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Get error details
$error_message = isset($_GET['message']) ? $_GET['message'] : 'An unknown error occurred.';
$error_type = isset($_GET['type']) ? $_GET['type'] : '500';
$error_title = 'Error';

switch ($error_type) {
    case '404':
        $error_title = 'Page Not Found';
        break;
    case '403':
        $error_title = 'Access Denied';
        break;
    case '500':
    default:
        $error_title = 'Internal Server Error';
        break;
}

// Log the error
error_log("Error {$error_type}: {$error_message}");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $error_title; ?> - Online Grading System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-body text-center p-5">
                        <h1 class="display-1 text-danger mb-4"><?php echo $error_type; ?></h1>
                        <h2 class="mb-4"><?php echo $error_title; ?></h2>
                        <p class="lead mb-4"><?php echo htmlspecialchars($error_message); ?></p>
                        <div class="mb-4">
                            <p>Please try the following:</p>
                            <ul class="list-unstyled">
                                <li><i class="fas fa-sync me-2"></i>Refresh the page</li>
                                <li><i class="fas fa-trash me-2"></i>Clear your browser cache</li>
                                <li><i class="fas fa-clock me-2"></i>Try again in a few minutes</li>
                                <?php if ($error_type == '500'): ?>
                                    <li><i class="fas fa-envelope me-2"></i>Contact the administrator if the problem persists</li>
                                <?php endif; ?>
                            </ul>
                        </div>
                        <div class="d-flex justify-content-center gap-3">
                            <a href="/Online_grading_systems/" class="btn btn-primary">
                                <i class="fas fa-home me-2"></i>Go to Homepage
                            </a>
                            <button onclick="window.history.back()" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Go Back
                            </button>
                            <?php if ($error_type == '500'): ?>
                                <button onclick="location.reload()" class="btn btn-info">
                                    <i class="fas fa-sync me-2"></i>Retry
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php if (ini_get('display_errors')): ?>
                    <div class="card mt-4">
                        <div class="card-header">
                            <h5 class="mb-0">Debug Information</h5>
                        </div>
                        <div class="card-body">
                            <pre class="mb-0"><code><?php print_r(error_get_last()); ?></code></pre>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html> 