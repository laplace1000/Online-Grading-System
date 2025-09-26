<?php
require_once 'config/config.php';
require_once 'includes/auth.php';
require_once 'includes/EmailNotification.php';

// Ensure user is logged in and is a student
if (!$auth->isLoggedIn() || !$auth->hasRole('student')) {
    header('Location: login.php');
    exit();
}

$user_id = $auth->getCurrentUserId();
$email = new EmailNotification($conn);

// Get student's courses
$stmt = $conn->prepare("
    SELECT c.course_id, c.course_code, c.course_name, 
           COALESCE(AVG(g.score/a.total_points * 100), 0) as current_grade
    FROM courses c
    JOIN sections s ON s.course_id = c.course_id
    JOIN enrollments e ON e.section_id = s.section_id
    LEFT JOIN assignments a ON a.section_id = s.section_id
    LEFT JOIN grades g ON g.assignment_id = a.assignment_id AND g.student_id = e.student_id
    WHERE e.student_id = ?
    GROUP BY c.course_id
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$courses = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Handle simulation request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $course_id = $_POST['course_id'];
    $simulation_type = $_POST['simulation_type'];
    $target_grade = $_POST['target_grade'] ?? null;
    $assumed_scores = $_POST['assumed_scores'] ?? [];
    
    // Get current grade and remaining assignments
    $stmt = $conn->prepare("
        SELECT a.assignment_id, a.title, a.total_points, g.score,
               (SELECT COUNT(*) FROM assignments WHERE section_id = a.section_id AND due_date > NOW()) as remaining_assignments,
               (SELECT SUM(total_points) FROM assignments WHERE section_id = a.section_id AND due_date > NOW()) as remaining_points
        FROM sections s
        JOIN assignments a ON a.section_id = s.section_id
        LEFT JOIN grades g ON g.assignment_id = a.assignment_id AND g.student_id = ?
        WHERE s.course_id = ?
    ");
    $stmt->bind_param("ii", $user_id, $course_id);
    $stmt->execute();
    $assignments = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    // Calculate current and projected grades
    $current_total = 0;
    $current_earned = 0;
    $remaining_total = 0;
    $projected_earned = 0;
    
    foreach ($assignments as $assignment) {
        if ($assignment['score'] !== null) {
            $current_total += $assignment['total_points'];
            $current_earned += $assignment['score'];
        } else {
            $remaining_total += $assignment['total_points'];
            // Calculate projected score based on simulation type
            if ($simulation_type === 'target') {
                $projected_earned += ($assignment['total_points'] * ($target_grade / 100));
            } elseif ($simulation_type === 'custom' && isset($assumed_scores[$assignment['assignment_id']])) {
                $projected_earned += $assumed_scores[$assignment['assignment_id']];
            }
        }
    }
    
    $current_grade = ($current_earned / $current_total) * 100;
    $projected_grade = (($current_earned + $projected_earned) / ($current_total + $remaining_total)) * 100;
    
    // Generate recommendations
    $recommendations = [];
    if ($projected_grade < $target_grade) {
        $needed_per_assignment = ($target_grade - $projected_grade) / $assignments['remaining_assignments'];
        $recommendations[] = "To reach your target grade, you need to score {$needed_per_assignment}% higher on each remaining assignment.";
        $recommendations[] = "Consider seeking additional help during office hours.";
        $recommendations[] = "Review previous assignments where you scored below target.";
    }
    
    // Send simulation results email
    $template = [
        'subject' => 'Grade Simulation Results - {course_code}',
        'body' => '<h2>Grade Simulation Results</h2><p>Current Grade: {current_grade}%<br>Projected Grade: {projected_grade}%</p>'
    ];
    
    $variables = [
        'course_code' => $courses[0]['course_code'],
        'current_grade' => number_format($current_grade, 1),
        'projected_grade' => number_format($projected_grade, 1),
        'simulation_description' => $simulation_type === 'target' ? "Target grade of {$target_grade}%" : "Custom score simulation",
        'recommendations' => implode("<br>", $recommendations)
    ];
    
    $email->sendTestEmail($user_id, $template, $variables);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grade Simulation</title>
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
                        <h4>Grade Simulation Tool</h4>
                    </div>
                    <div class="card-body">
                        <form method="post" action="">
                            <div class="form-group">
                                <label for="course_id">Select Course</label>
                                <select class="form-control" id="course_id" name="course_id" required>
                                    <?php foreach ($courses as $course): ?>
                                        <option value="<?php echo $course['course_id']; ?>">
                                            <?php echo $course['course_code'] . ' - ' . $course['course_name']; ?>
                                            (Current: <?php echo number_format($course['current_grade'], 1); ?>%)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label>Simulation Type</label>
                                <div class="custom-control custom-radio">
                                    <input type="radio" class="custom-control-input" id="target_grade" 
                                           name="simulation_type" value="target" checked>
                                    <label class="custom-control-label" for="target_grade">Target Grade</label>
                                </div>
                                <div class="custom-control custom-radio">
                                    <input type="radio" class="custom-control-input" id="custom_scores" 
                                           name="simulation_type" value="custom">
                                    <label class="custom-control-label" for="custom_scores">Custom Scores</label>
                                </div>
                            </div>
                            
                            <div id="target_grade_input" class="form-group">
                                <label for="target_grade_value">Target Grade (%)</label>
                                <input type="number" class="form-control" id="target_grade_value" 
                                       name="target_grade" min="0" max="100" step="0.1">
                            </div>
                            
                            <div id="custom_scores_input" class="form-group" style="display: none;">
                                <label>Enter Expected Scores</label>
                                <?php foreach ($assignments as $assignment): ?>
                                    <?php if ($assignment['score'] === null): ?>
                                        <div class="form-group">
                                            <label for="score_<?php echo $assignment['assignment_id']; ?>">
                                                <?php echo $assignment['title']; ?> 
                                                (Max: <?php echo $assignment['total_points']; ?>)
                                            </label>
                                            <input type="number" class="form-control" 
                                                   id="score_<?php echo $assignment['assignment_id']; ?>"
                                                   name="assumed_scores[<?php echo $assignment['assignment_id']; ?>]"
                                                   min="0" max="<?php echo $assignment['total_points']; ?>" step="0.5">
                                        </div>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">Simulate Grades</button>
                        </form>
                        
                        <?php if (isset($projected_grade)): ?>
                            <div class="mt-4">
                                <h5>Simulation Results</h5>
                                <div class="alert alert-info">
                                    <p><strong>Current Grade:</strong> <?php echo number_format($current_grade, 1); ?>%</p>
                                    <p><strong>Projected Grade:</strong> <?php echo number_format($projected_grade, 1); ?>%</p>
                                    <?php if (!empty($recommendations)): ?>
                                        <h6 class="mt-3">Recommendations:</h6>
                                        <ul>
                                            <?php foreach ($recommendations as $recommendation): ?>
                                                <li><?php echo $recommendation; ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script>
    $(document).ready(function() {
        $('input[name="simulation_type"]').change(function() {
            if ($(this).val() === 'target') {
                $('#target_grade_input').show();
                $('#custom_scores_input').hide();
            } else {
                $('#target_grade_input').hide();
                $('#custom_scores_input').show();
            }
        });
    });
    </script>
</body>
</html> 