<?php
/**
 * Payment Success View
 */
$courseId = $data['course_id'] ?? 0;
?>

<div class="row">
    <div class="col-md-6 mx-auto">
        <div class="card shadow border-success">
            <div class="card-body p-5 text-center">
                <div class="mb-4">
                    <i class="bi bi-check-circle-fill text-success" style="font-size: 5rem;"></i>
                </div>
                
                <h2 class="text-success mb-3">Payment Successful!</h2>
                
                <p class="lead mb-4">
                    Thank you for your purchase. Your payment was successful and you have been enrolled in the course.
                </p>
                
                <div class="d-grid gap-2">
                    <a href="<?php echo BASE_URL; ?>courses/my_courses" class="btn btn-primary btn-lg">
                        <i class="bi bi-book"></i> Go to My Courses
                    </a>
                    <a href="<?php echo BASE_URL; ?>courses/show/<?php echo $courseId; ?>" class="btn btn-outline-primary">
                        <i class="bi bi-arrow-left"></i> Back to Course
                    </a>
                    <a href="<?php echo BASE_URL; ?>courses" class="btn btn-outline-secondary">
                        <i class="bi bi-search"></i> Browse More Courses
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

