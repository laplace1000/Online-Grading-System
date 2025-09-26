<?php
require_once 'config/config.php';
require_once 'includes/auth.php';
require_once 'includes/EmailPreferences.php';

// Ensure user is logged in
if (!$auth->isLoggedIn()) {
    header('Location: login.php');
    exit();
}

$user_id = $auth->getCurrentUserId();
$emailPreferences = new EmailPreferences($conn);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $preferences = [
        'assignment_submission' => isset($_POST['assignment_submission']) ? 1 : 0,
        'grade_updates' => isset($_POST['grade_updates']) ? 1 : 0,
        'course_announcements' => isset($_POST['course_announcements']) ? 1 : 0,
        'deadline_changes' => isset($_POST['deadline_changes']) ? 1 : 0,
        'grade_alerts' => isset($_POST['grade_alerts']) ? 1 : 0,
        'weekly_summary' => isset($_POST['weekly_summary']) ? 1 : 0
    ];
    
    if ($emailPreferences->updatePreferences($user_id, $preferences)) {
        $success_message = "Email preferences updated successfully!";
    } else {
        $error_message = "Failed to update email preferences.";
    }
}

// Get current preferences
$current_preferences = $emailPreferences->getPreferences($user_id);

// Get page title based on user role
$role = $auth->getCurrentUserRole();
$page_title = ($role === 'student') ? "Student Email Preferences" : "Email Notification Settings";

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-8 offset-md-2">
                <div class="card">
                    <div class="card-header">
                        <h4><?php echo $page_title; ?></h4>
                    </div>
                    <div class="card-body">
                        <?php if (isset($success_message)): ?>
                            <div class="alert alert-success">
                                <?php echo $success_message; ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (isset($error_message)): ?>
                            <div class="alert alert-danger">
                                <?php echo $error_message; ?>
                            </div>
                        <?php endif; ?>
                        
                        <form method="post" action="">
                            <div class="form-group">
                                <div class="custom-control custom-switch mb-3">
                                    <input type="checkbox" class="custom-control-input" id="assignment_submission" 
                                           name="assignment_submission" <?php echo $current_preferences['assignment_submission'] ? 'checked' : ''; ?>>
                                    <label class="custom-control-label" for="assignment_submission">
                                        Assignment Submission Notifications
                                        <small class="form-text text-muted">Receive notifications when assignments are submitted</small>
                                    </label>
                                </div>
                                
                                <div class="custom-control custom-switch mb-3">
                                    <input type="checkbox" class="custom-control-input" id="grade_updates" 
                                           name="grade_updates" <?php echo $current_preferences['grade_updates'] ? 'checked' : ''; ?>>
                                    <label class="custom-control-label" for="grade_updates">
                                        Grade Update Notifications
                                        <small class="form-text text-muted">Receive notifications when grades are posted or updated</small>
                                    </label>
                                </div>
                                
                                <div class="custom-control custom-switch mb-3">
                                    <input type="checkbox" class="custom-control-input" id="course_announcements" 
                                           name="course_announcements" <?php echo $current_preferences['course_announcements'] ? 'checked' : ''; ?>>
                                    <label class="custom-control-label" for="course_announcements">
                                        Course Announcements
                                        <small class="form-text text-muted">Receive notifications for course announcements</small>
                                    </label>
                                </div>
                                
                                <div class="custom-control custom-switch mb-3">
                                    <input type="checkbox" class="custom-control-input" id="deadline_changes" 
                                           name="deadline_changes" <?php echo $current_preferences['deadline_changes'] ? 'checked' : ''; ?>>
                                    <label class="custom-control-label" for="deadline_changes">
                                        Deadline Change Notifications
                                        <small class="form-text text-muted">Receive notifications when assignment deadlines change</small>
                                    </label>
                                </div>
                                
                                <div class="custom-control custom-switch mb-3">
                                    <input type="checkbox" class="custom-control-input" id="grade_alerts" 
                                           name="grade_alerts" <?php echo $current_preferences['grade_alerts'] ? 'checked' : ''; ?>>
                                    <label class="custom-control-label" for="grade_alerts">
                                        Grade Alert Notifications
                                        <small class="form-text text-muted">Receive notifications for low grades or performance alerts</small>
                                    </label>
                                </div>
                                
                                <div class="custom-control custom-switch mb-3">
                                    <input type="checkbox" class="custom-control-input" id="weekly_summary" 
                                           name="weekly_summary" <?php echo $current_preferences['weekly_summary'] ? 'checked' : ''; ?>>
                                    <label class="custom-control-label" for="weekly_summary">
                                        Weekly Summary Reports
                                        <small class="form-text text-muted">Receive weekly summary of grades and upcoming assignments</small>
                                    </label>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary">Save Preferences</button>
                                <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/js/bootstrap.bundle.min.js"></script>
</body>
</html> 