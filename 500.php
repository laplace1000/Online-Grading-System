<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Log the error
if (isset($_SERVER['HTTP_REFERER'])) {
    error_log("500 error occurred. Referrer: " . $_SERVER['HTTP_REFERER']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>500 - Internal Server Error</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-body text-center p-5">
                        <h1 class="display-1 text-danger mb-4">500</h1>
                        <h2 class="mb-4">Internal Server Error</h2>
                        <p class="lead mb-4">The server encountered an internal error or misconfiguration and was unable to complete your request.</p>
                        <div class="mb-4">
                            <p>Please try the following:</p>
                            <ul class="list-unstyled">
                                <li>Refresh the page</li>
                                <li>Clear your browser cache</li>
                                <li>Try again in a few minutes</li>
                            </ul>
                        </div>
                        <div>
                            <a href="/Online_grading_systems/" class="btn btn-primary">Go to Homepage</a>
                            <button onclick="window.history.back()" class="btn btn-secondary">Go Back</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 