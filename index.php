<?php
require_once 'config/config.php';
require_once 'includes/auth.php';

// If user is already logged in, redirect to their dashboard
if ($auth->isLoggedIn()) {
    switch ($_SESSION['role']) {
        case 'admin':
            header('Location: admin/dashboard.php');
            break;
        case 'teacher':
            header('Location: teacher/dashboard.php');
            break;
        case 'student':
            header('Location: student/dashboard.php');
            break;
        case 'parent':
            header('Location: parent/dashboard.php');
            break;
        default:
            // If role is not recognized, log them out
            $auth->logout();
            header('Location: login.php');
    }
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .hero {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 4rem 0;
            text-align: center;
        }

        .features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            padding: 4rem 0;
        }

        .feature-card {
            text-align: center;
            padding: 2rem;
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
        }

        .feature-icon {
            font-size: 2.5rem;
            color: var(--secondary-color);
            margin-bottom: 1rem;
        }

        .cta-section {
            background-color: var(--light-gray);
            padding: 4rem 0;
            text-align: center;
        }

        .testimonials {
            padding: 4rem 0;
            background: white;
        }

        .testimonial-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }

        .testimonial-card {
            padding: 2rem;
            background: var(--light-gray);
            border-radius: var(--border-radius);
            position: relative;
        }

        .testimonial-card::before {
            content: '"';
            position: absolute;
            top: 1rem;
            left: 1rem;
            font-size: 4rem;
            color: var(--secondary-color);
            opacity: 0.2;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <a href="<?php echo SITE_URL; ?>" class="navbar-brand"><?php echo SITE_NAME; ?></a>
                <div>
                    <a href="login.php" class="btn btn-primary">Login</a>
                </div>
            </div>
        </div>
    </nav>

    <section class="hero">
        <div class="container">
            <h1 style="font-size: 3rem; margin-bottom: 1rem;">Transform Education with Technology</h1>
            <p style="font-size: 1.2rem; margin-bottom: 2rem;">A comprehensive online grading system that empowers teachers, students, and parents</p>
            <a href="login.php" class="btn btn-primary" style="font-size: 1.2rem; padding: 1rem 2rem;">Get Started</a>
        </div>
    </section>

    <section class="features">
        <div class="container">
            <div class="feature-card">
                <i class="fas fa-graduation-cap feature-icon"></i>
                <h3>For Students</h3>
                <p>Track your progress, submit assignments, and view detailed feedback in real-time</p>
            </div>
            
            <div class="feature-card">
                <i class="fas fa-chalkboard-teacher feature-icon"></i>
                <h3>For Teachers</h3>
                <p>Streamline grading, create assignments, and communicate effectively with students and parents</p>
            </div>
            
            <div class="feature-card">
                <i class="fas fa-users feature-icon"></i>
                <h3>For Parents</h3>
                <p>Stay involved in your child's education with real-time access to grades and progress reports</p>
            </div>
        </div>
    </section>

    <section class="cta-section">
        <div class="container">
            <h2 style="font-size: 2.5rem; margin-bottom: 1rem;">Ready to Get Started?</h2>
            <p style="font-size: 1.2rem; margin-bottom: 2rem;">Join thousands of schools already using our platform</p>
            <a href="contact.php" class="btn btn-primary" style="font-size: 1.2rem; padding: 1rem 2rem;">Contact Us</a>
        </div>
    </section>

    <section class="testimonials">
        <div class="container">
            <h2 class="text-center" style="font-size: 2.5rem; margin-bottom: 1rem;">What People Say</h2>
            <div class="testimonial-grid">
                <div class="testimonial-card">
                    <p>"This system has revolutionized how we manage grades and communicate with parents. It's incredibly user-friendly!"</p>
                    <p style="margin-top: 1rem;"><strong>- Sarah Johnson</strong><br>High School Teacher</p>
                </div>
                
                <div class="testimonial-card">
                    <p>"As a parent, I love being able to track my child's progress in real-time. The notifications feature is especially helpful."</p>
                    <p style="margin-top: 1rem;"><strong>- Michael Chen</strong><br>Parent</p>
                </div>
                
                <div class="testimonial-card">
                    <p>"The analytics and reporting features have helped us make data-driven decisions to improve student outcomes."</p>
                    <p style="margin-top: 1rem;"><strong>- Dr. Emily Rodriguez</strong><br>School Administrator</p>
                </div>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?> 