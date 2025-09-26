<?php
require_once 'config/config.php';

// Create parent_student table if it doesn't exist
$create_table_sql = "
CREATE TABLE IF NOT EXISTS parent_student (
    parent_student_id INT PRIMARY KEY AUTO_INCREMENT,
    parent_id INT NOT NULL,
    student_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (parent_id) REFERENCES users(user_id),
    FOREIGN KEY (student_id) REFERENCES users(user_id),
    UNIQUE KEY unique_parent_student (parent_id, student_id)
)";

if ($conn->query($create_table_sql)) {
    echo "Parent-student table verified/created successfully.<br>";
} else {
    echo "Error creating parent_student table: " . $conn->error . "<br>";
}

// Create a test parent user if none exists
$create_parent_sql = "
INSERT INTO users (username, password, email, role, first_name, last_name, is_active)
SELECT 'testparent', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'parent@test.com', 'parent', 'Test', 'Parent', 1
WHERE NOT EXISTS (
    SELECT 1 FROM users WHERE role = 'parent'
)";

if ($conn->query($create_parent_sql)) {
    echo "Parent user verified/created successfully.<br>";
} else {
    echo "Error creating parent user: " . $conn->error . "<br>";
}

// Create a test student user if none exists
$create_student_sql = "
INSERT INTO users (username, password, email, role, first_name, last_name, is_active)
SELECT 'teststudent', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student@test.com', 'student', 'Test', 'Student', 1
WHERE NOT EXISTS (
    SELECT 1 FROM users WHERE role = 'student'
)";

if ($conn->query($create_student_sql)) {
    echo "Student user verified/created successfully.<br>";
} else {
    echo "Error creating student user: " . $conn->error . "<br>";
}

// Link parent and student if not already linked
$link_users_sql = "
INSERT INTO parent_student (parent_id, student_id)
SELECT p.user_id, s.user_id
FROM users p
JOIN users s ON s.role = 'student'
WHERE p.role = 'parent'
AND NOT EXISTS (
    SELECT 1 
    FROM parent_student ps 
    WHERE ps.parent_id = p.user_id 
    AND ps.student_id = s.user_id
)
LIMIT 1";

if ($conn->query($link_users_sql)) {
    echo "Parent-student relationship verified/created successfully.<br>";
} else {
    echo "Error creating parent-student relationship: " . $conn->error . "<br>";
}

// Display test credentials
echo "<br>Test Credentials:<br>";
echo "Parent Login: testparent / password<br>";
echo "Student Login: teststudent / password<br>";

// Display current parent-student relationships
$relationships_sql = "
SELECT 
    p.username as parent_username,
    p.first_name as parent_first_name,
    p.last_name as parent_last_name,
    s.username as student_username,
    s.first_name as student_first_name,
    s.last_name as student_last_name
FROM parent_student ps
JOIN users p ON p.user_id = ps.parent_id
JOIN users s ON s.user_id = ps.student_id";

$result = $conn->query($relationships_sql);
if ($result->num_rows > 0) {
    echo "<br>Current Parent-Student Relationships:<br>";
    while ($row = $result->fetch_assoc()) {
        echo "Parent: {$row['parent_username']} ({$row['parent_first_name']} {$row['parent_last_name']}) -> ";
        echo "Student: {$row['student_username']} ({$row['student_first_name']} {$row['student_last_name']})<br>";
    }
} else {
    echo "<br>No parent-student relationships found.<br>";
}
?> 