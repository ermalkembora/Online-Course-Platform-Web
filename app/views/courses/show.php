<?php
/**
 * Course Details View
 */
$course = $data['course'];
$isEnrolled = $data['is_enrolled'] ?? false;
$lessons = $course['lessons'] ?? [];
?>

<style>
    body {
        background: url('/e-learning-platform/assets/sources/home.jpg') no-repeat center center fixed;
        background-size: cover;
        color: #f8f9fa;
        min-height: 100vh;
    }

    .card {
        background-color: rgba(34, 87, 143, 0.13);
        backdrop-filter: blur(3px);
        border: none;
        border-radius: 15px;
        box-shadow: 0 8px 32px rgba(0,0,0,0.4);
        color: #ffffff;
    }

    h1, h3, h4, h5, h6, .card-title {
        color: #ffffff;
    }

    .text-muted, .small {
        color: #e0e0e0 !important;
    }

    .btn-primary {
        background-color: #0d6efd;
        border-color: #0d6efd;
    }

    .btn-primary:hover {
        background-color: #0b5ed7;
    }

    .btn-success {
        background-color: #198754;
        border-color: #198754;
    }

    .btn-outline-primary {
        color: #0d6efd;
        border-color: #0d6efd;
    }

    .btn-outline-primary:hover {
        background-color: rgba(13, 110, 253, 0.1);
        color: #ffffff;
    }

    .alert-success {
        background-color: rgba(25, 135, 84, 0.2);
        color: #ffffff;
        border: none;
    }

    .list-group-item {
        background-color: rgba(0,0,0,0.45);
        color: #ffffff;
        border: 1px solid rgba(255,255,255,0.2);
        border-radius: 8px;
        margin-bottom: 8px;
    }

    .list-group-item .badge {
        background-color: rgba(255,255,255,0.2);
        color: #ffffff;
    }

    hr {
        border-color: rgba(255, 255, 255, 0.2);
    }

    a {
        color: #0d6efd;
        text-decoration: none;
    }

    a:hover {
        color: #0b5ed7;
        text-decoration: underline;
    }

    .bg-secondary {
        background-color: rgba(100, 100, 100, 0.6) !important;
    }

    .card-img-top {
        border-radius: 15px 15px 0 0;
    }
</style>

<div class="row">
    <div class="col-md-8">
        <div class="card shadow mb-4">
            <?php if ($course['thumbnail']): ?>
                <img src="<?php echo BASE_URL; ?>uploads/courses/<?php echo htmlspecialchars($course['thumbnail']); ?>" 
                     class="card-img-top" 
                     alt="<?php echo htmlspecialchars($course['title']); ?>"
                     style="max-height: 400px; object-fit: cover;">
            <?php else: ?>
                <div class="card-img-top bg-secondary d-flex align-items-center justify-content-center" 
                     style="height: 300px;">
                    <i class="bi bi-book text-white" style="font-size: 5rem;"></i>
                </div>
            <?php endif; ?>
            
            <div class="card-body">
                <h1 class="card-title mb-3"><?php echo htmlspecialchars($course['title']); ?></h1>
                
                <div class="mb-3">
                    <p class="text-muted mb-2">
                        <i class="bi bi-person"></i> 
                        <strong>Instructor:</strong> <?php echo htmlspecialchars($course['instructor_name']); ?>
                    </p>
                    <p class="text-muted mb-2">
                        <i class="bi bi-people"></i> 
                        <strong>Students:</strong> <?php echo $course['enrollment_count'] ?? 0; ?> enrolled
                    </p>
                   <p class="text-muted mb-2">
    <i class="bi bi-star-fill text-warning"></i> 
    <strong>Rating:</strong>
    <?php if ($course['review_count'] > 0): ?>
        <?php echo number_format($course['rating'], 1); ?> / 5
        <small>(<?php echo $course['review_count']; ?> reviews)</small>
    <?php else: ?>
        No ratings yet
    <?php endif; ?>
</p>

                </div>
                
                <hr>
                
                <h4>Description</h4>
                <p class="card-text">
                    <?php echo nl2br(htmlspecialchars($course['description'] ?? 'No description available.')); ?>
                </p>
            </div>
        </div>

        <!-- Lessons Section -->
        <div class="card shadow">
            <div class="card-body">
                <h4 class="mb-4">Course Lessons</h4>
                
                <?php if (empty($lessons)): ?>
                    <p class="text-muted">No lessons available yet.</p>
                <?php else: ?>
                    <div class="list-group">
                        <?php foreach ($lessons as $index => $lesson): ?>
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1">
                                            <span class="badge bg-secondary me-2"><?php echo $index + 1; ?></span>
                                            <?php echo htmlspecialchars($lesson['title']); ?>
                                        </h6>
                                        <?php if ($lesson['description']): ?>
                                            <p class="mb-1 text-muted small">
                                                <?php echo htmlspecialchars(mb_substr($lesson['description'], 0, 100)); ?>
                                                <?php if (strlen($lesson['description']) > 100) echo '...'; ?>
                                            </p>
                                        <?php endif; ?>
                                        <small class="text-muted">
                                            <?php if ($lesson['duration']): ?>
                                                <i class="bi bi-clock"></i> <?php echo $lesson['duration']; ?> min
                                            <?php endif; ?>
                                            <?php if ($lesson['is_free']): ?>
                                                <span class="badge bg-success ms-2">Free Preview</span>
                                            <?php endif; ?>
                                        </small>
                                    </div>
                                    <?php if ($isEnrolled): ?>
                                        <a href="<?php echo BASE_URL; ?>courses/player/<?php echo $course['id']; ?>/<?php echo $lesson['id']; ?>" 
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-play-circle"></i> Watch
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Sidebar -->
    <div class="col-md-4">
        <div class="card shadow sticky-top" style="top: 20px;">
            <div class="card-body">
                <h3 class="text-primary mb-3">
                    <?php echo '$' . number_format($course['price'], 2); ?>
                </h3>
                
                <?php if (!is_logged_in()): ?>
                    <p class="text-muted">Please <a href="<?php echo BASE_URL; ?>users/login">login</a> to enroll in this course.</p>
                    <a href="<?php echo BASE_URL; ?>users/login" class="btn btn-primary w-100 mb-2">
                        Login to Enroll
                    </a>
                <?php elseif ($isEnrolled): ?>
                    <div class="alert alert-success">
                        <i class="bi bi-check-circle"></i> <strong>You are enrolled!</strong>
                    </div>
                    <a href="<?php echo BASE_URL; ?>courses/my_courses" class="btn btn-success w-100">
                        Go to My Courses
                    </a>
                <?php else: ?>
                    <?php if ($course['price'] > 0): ?>
                        <a href="<?php echo BASE_URL; ?>checkout/payWithPaypal/<?php echo $course['id']; ?>" 
                           class="btn btn-primary w-100 btn-lg">
                            <i class="bi bi-paypal"></i> Buy with PayPal - <?php echo '$' . number_format($course['price'], 2); ?>
                        </a>
                    <?php else: ?>
                        <form method="POST" action="<?php echo BASE_URL; ?>courses/enroll">
                            <input type="hidden" name="course_id" value="<?php echo $course['id']; ?>">
                            <button type="submit" class="btn btn-primary w-100 btn-lg">
                                <i class="bi bi-check-circle"></i> Enroll for Free
                            </button>
                        </form>
                    <?php endif; ?>
                <?php endif; ?>
                
                <hr>
                
                <h5>Course Details</h5>
                <ul class="list-unstyled">
                    <li class="mb-2">
                        <i class="bi bi-book"></i> 
                        <strong>Lessons:</strong> <?php echo count($lessons); ?>
                    </li>
                    <li class="mb-2">
                        <i class="bi bi-people"></i> 
                        <strong>Students:</strong> <?php echo $course['enrollment_count'] ?? 0; ?>
                    </li>
                    <li class="mb-2">
                        <i class="bi bi-calendar"></i> 
                        <strong>Created:</strong> <?php echo date('M d, Y', strtotime($course['created_at'])); ?>
                    </li>
                   <li class="mb-2">
    <i class="bi bi-star-fill text-warning"></i> 
    <strong>Rating:</strong>
    <?php if ($course['review_count'] > 0): ?>
        <?php echo number_format($course['rating'], 1); ?> / 5
    <?php else: ?>
        Not rated yet
    <?php endif; ?>
</li>

                </ul>
            </div>
        </div>
    </div>
</div>
<?php if ($isEnrolled): ?>
<hr>
<h4>Leave a Review</h4>

<form method="POST" action="<?php echo BASE_URL; ?>reviews/add">
    <input type="hidden" name="course_id" value="<?php echo $course['id']; ?>">

    <select name="rating" class="form-control mb-2" required>
        <option value="5">★★★★★</option>
        <option value="4">★★★★☆</option>
        <option value="3">★★★☆☆</option>
        <option value="2">★★☆☆☆</option>
        <option value="1">★☆☆☆☆</option>
    </select>

    <textarea name="comment" class="form-control mb-2"
              placeholder="Optional comment"></textarea>

    <button class="btn btn-primary">Submit Review</button>
</form>
<?php endif; ?>

<hr>
<h4>Student Reviews</h4>

<?php if (!empty($reviews)): ?>
    <?php foreach ($reviews as $review): ?>
        <div class="card mb-3">
            <div class="card-body">
                <strong><?php echo htmlspecialchars($review['reviewer_name'] ?? 'Anonymous'); ?>
</strong>
                <span class="text-warning ms-2">
                    <?php echo str_repeat('★', (int)$review['rating']); ?>
                </span>

                <p class="mt-2 mb-1">
                    <?php echo nl2br(htmlspecialchars($review['comment'])); ?>
                </p>

                <small class="text-muted">
                    <?php echo date('F j, Y', strtotime($review['created_at'])); ?>
                </small>
            </div>
        </div>
    <?php endforeach; ?>
<?php else: ?>
    <p class="text-muted">No reviews yet.</p>
<?php endif; ?>
