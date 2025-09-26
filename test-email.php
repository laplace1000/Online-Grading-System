<?php
require_once 'config/config.php';
require_once 'includes/EmailNotification.php';

try {
    $email = new EmailNotification($conn);
    
    // Test email template
    $template = [
        'subject' => 'Test Email from Grade Management System',
        'body' => '
            <h2>Test Email</h2>
            <p>This is a test email to verify the email notification system is working correctly.</p>
            <p>If you receive this email, it means the system is properly configured.</p>
            <p>Time sent: ' . date('Y-m-d H:i:s') . '</p>
        '
    ];
    
    // Sample data for testing
    $sample_data = [
        'test_var' => 'Test Value'
    ];
    
    // Send test email
    $result = $email->sendTestEmail('your-email@example.com', $template, $sample_data);
    
    if ($result) {
        echo "Test email sent successfully!";
    } else {
        echo "Failed to send test email. Check the error log for details.";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
} 