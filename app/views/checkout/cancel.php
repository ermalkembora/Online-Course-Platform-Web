<?php
/**
 * Payment Cancelled View
 */
$courseId = $data['course_id'] ?? 0;
?>

<div class="row">
    <div class="col-md-6 mx-auto">
        <div class="card shadow border-warning">
            <div class="card-body p-5 text-center">
                <div class="mb-4">
                    <i class="bi bi-x-circle-fill text-warning" style="font-size: 5rem;"></i>
                </div>
                
                <h2 class="text-warning mb-3">Payment Cancelled</h2>
                
                <p class="lead mb-4">
                    Your payment was cancelled. No money has been charged.
                </p>
                
                <div class="d-grid gap-2">
                    <a href="<?php echo BASE_URL; ?>courses" class="btn btn-primary btn-lg">
                        <i class="bi bi-search"></i> Back to Courses
                    </a>
                    <?php if ($courseId): ?>
                        <a href="<?php echo BASE_URL; ?>courses/show/<?php echo $courseId; ?>" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left"></i> Back to Course
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

