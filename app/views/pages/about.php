<?php
/**
 * About Page View
 */
?>

<style>
    body {
        background: url('/e-learning-platform/assets/sources/hero-bg.jpg') no-repeat center center fixed;
        background-size: cover;
        color: #f8f9fa; /* Light text for overall readability */
    }

    .transparent-section {
        background-color: rgba(0, 0, 0, 0.65);
        backdrop-filter: blur(5px); /* Subtle glassy feel */
        border-radius: 12px;
        padding: 2.5rem;
    }

    .transparent-card {
        background-color: rgba(0, 0, 0, 0.13);
        backdrop-filter: blur(5px);
        border: none;
    }

    .card-title, h1, h3, p, li {
        color: #ffffff;
    }

    .lead {
        color: #dddddd;
    }

    .text-muted {
        color: #bbbbbb !important;
    }

    ul li {
        margin-bottom: 0.5rem;
    }

    ul li strong {
        color: #ffffff;
    }
</style>

<div class="row my-5">
    <div class="col-md-8 mx-auto">
        <div class="card transparent-card shadow">
            <div class="card-body transparent-section">
                <h1 class="card-title mb-4"><?php echo $title ?? 'About Us'; ?></h1>
                
                <p class="lead">
                    Welcome to our E-Learning Platform, where knowledge meets opportunity.
                </p>
                
                <h3 class="mt-4">Our Mission</h3>
                <p>
                    We are dedicated to providing high-quality online education that is accessible to everyone. 
                    Our platform connects students with expert instructors, offering a wide range of courses 
                    designed to help you achieve your learning goals.
                </p>
                
                <h3 class="mt-4">What We Offer</h3>
                <ul>
                    <li><strong>Comprehensive Courses:</strong> Learn from industry experts with real-world experience</li>
                    <li><strong>Flexible Learning:</strong> Study at your own pace, anytime, anywhere</li>
                    <li><strong>Interactive Content:</strong> Engaging lessons with videos, quizzes, and assignments</li>
                    <li><strong>Certificates:</strong> Earn certificates upon course completion</li>
                    <li><strong>Community Support:</strong> Connect with fellow learners and instructors</li>
                </ul>
                
                <h3 class="mt-4">For Instructors</h3>
                <p>
                    Are you an expert in your field? Join our platform as an instructor and share your knowledge 
                    with thousands of students. Create and sell courses, build your reputation, and earn income 
                    doing what you love.
                </p>
                
                <div class="mt-4">
                    <a href="<?php echo BASE_URL; ?>" class="btn btn-primary btn-lg me-3">Back to Home</a>
                    <?php if (!is_logged_in()): ?>
                        <a href="<?php echo BASE_URL; ?>auth/register" class="btn btn-outline-light btn-lg">Get Started</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>