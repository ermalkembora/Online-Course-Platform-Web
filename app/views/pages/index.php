<?php
/**
 * Homepage View
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
        backdrop-filter: blur(5px); /* Optional subtle blur for glassy feel */
        border-radius: 12px;
        padding: 2rem;
    }

    .transparent-card {
        background-color: rgba(0, 0, 0, 0.65);
        backdrop-filter: blur(5px);
        border: none;
    }

    .card-title, .card-text {
        color: #ffffff;
    }

    .text-muted {
        color: #bbbbbb !important;
    }
</style>

<!-- Hero Section -->
<div class="row mb-5">
    <div class="col-12">
        <div class="transparent-section text-center shadow position-relative text-white" 
             style="min-height: 400px; display: flex; align-items: center; justify-content: center;">
            
            <!-- No need for separate overlay anymore -->
            
            <div>
                <h1 class="display-4 fw-bold mb-3">Welcome to <?php echo SITE_NAME; ?></h1>
                <p class="lead mb-4"><strong>Buy and sell courses easily.</strong></p>
                <a href="<?php echo BASE_URL; ?>courses" class="btn btn-light btn-lg">
                    <i class="bi bi-search"></i> Browse Courses
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Features Section -->
<div class="row mb-5">
    <div class="col-12">
        <h2 class="text-center mb-4 text-white">Why Choose Our Platform?</h2>
    </div>
    <?php 
    $features = [
        ['icon' => 'bi-book', 'title' => 'Learn Anytime', 'text' => 'Access courses from anywhere, at any time. Learn at your own pace with our flexible learning platform.'],
        ['icon' => 'bi-people', 'title' => 'Expert Instructors', 'text' => 'Learn from industry experts with years of real-world experience. Quality courses from trusted instructors.'],
        ['icon' => 'bi-award', 'title' => 'Create & Sell', 'text' => 'Share your knowledge! Create courses and earn money by teaching others what you know.']
    ];
    foreach ($features as $feature): ?>
    <div class="col-md-4 mb-4">
        <div class="card transparent-card h-100 shadow-sm">
            <div class="card-body text-center p-4">
                <div class="mb-3">
                    <i class="bi <?php echo $feature['icon']; ?> text-primary" style="font-size: 3rem;"></i>
                </div>
                <h5 class="card-title"><?php echo $feature['title']; ?></h5>
                <p class="card-text text-muted">
                    <?php echo $feature['text']; ?>
                </p>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Call to Action -->
<?php if (!is_logged_in()): ?>
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card transparent-card shadow">
                <div class="card-body text-center p-5">
                    <h3 class="mb-3 text-white">Ready to Get Started?</h3>
                    <p class="text-muted mb-4">
                        Join thousands of students learning new skills and instructors sharing their knowledge.
                    </p>
                    <div class="d-flex gap-2 justify-content-center">
                        <a href="<?php echo BASE_URL; ?>users/register" class="btn btn-primary btn-lg">
                            <i class="bi bi-person-plus"></i> Create Account
                        </a>
                        <a href="<?php echo BASE_URL; ?>courses" class="btn btn-outline-light btn-lg">
                            <i class="bi bi-search"></i> Browse Courses
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php else: ?>
    <!-- Quick Actions for Logged-in Users -->
    <div class="row">
        <div class="col-md-6 mb-3">
            <div class="card transparent-card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title text-white">
                        <i class="bi bi-collection text-primary"></i> Continue Learning
                    </h5>
                    <p class="card-text text-muted">Access your enrolled courses and continue where you left off.</p>
                    <a href="<?php echo BASE_URL; ?>courses/my_courses" class="btn btn-primary">
                        Go to My Courses
                    </a>
                </div>
            </div>
        </div>
        <div class="col-md-6 mb-3">
            <div class="card transparent-card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title text-white">
                        <i class="bi bi-plus-circle text-primary"></i> Create Course
                    </h5>
                    <p class="card-text text-muted">Share your expertise by creating and selling your own courses.</p>
                    <a href="<?php echo BASE_URL; ?>courses/create" class="btn btn-outline-light">
                        Create New Course
                    </a>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>