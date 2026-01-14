<?php
/**
 * My Courses View
 */
$courses = $data['courses'] ?? [];
?>

<style>
body {
    background: url('/e-learning-platform/assets/sources/home.jpg') no-repeat center center fixed;
    background-size: cover;
    color: #f8f9fa;
}

.card {
    background-color: rgba(34,87,143,0.13);
    backdrop-filter: blur(15px);
    border: none;
    border-radius: 15px;
    box-shadow: 0 8px 32px rgba(0,0,0,0.4);
    color: #ffffff;
}

.card-title, h2, strong {
    color: #ffffff;
}

.text-muted, small {
    color: #e0e0e0 !important;
}

.btn-primary {
    background-color: #0d6efd;
    border-color: #0d6efd;
}

.btn-primary:hover {
    background-color: #0b5ed7;
}

.btn-outline-primary {
    color: #0d6efd;
    border-color: #0d6efd;
}

.btn-outline-primary:hover {
    background-color: rgba(13,110,253,0.1);
    color: #ffffff;
}

.bg-secondary {
    background-color: rgba(100,100,100,0.6) !important;
}

.progress-bar {
    background-color: #0d6efd;
}
</style>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>My Courses</h2>
            <a href="<?php echo BASE_URL; ?>courses" class="btn btn-outline-primary">
                <i class="bi bi-search"></i> Browse All Courses
            </a>
        </div>

        <?php if (empty($courses)): ?>
            <div class="alert alert-info">
                <h5><i class="bi bi-info-circle"></i> No Enrolled Courses</h5>
                <p class="mb-0">
                    You haven't enrolled in any courses yet. 
                    <a href="<?php echo BASE_URL; ?>courses">Browse courses</a> to get started!
                </p>
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($courses as $course): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100 shadow-sm">
                            <?php if ($course['thumbnail']): ?>
                                <img src="<?php echo BASE_URL; ?>uploads/courses/<?php echo htmlspecialchars($course['thumbnail']); ?>" 
                                     class="card-img-top" 
                                     alt="<?php echo htmlspecialchars($course['title']); ?>"
                                     style="height: 200px; object-fit: cover; border-radius: 15px 15px 0 0;">
                            <?php else: ?>
                                <div class="card-img-top bg-secondary d-flex align-items-center justify-content-center" 
                                     style="height: 200px; border-radius: 15px 15px 0 0;">
                                    <i class="bi bi-book text-white" style="font-size: 3rem;"></i>
                                </div>
                            <?php endif; ?>
                            
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title"><?php echo htmlspecialchars($course['title']); ?></h5>
                                <p class="card-text text-muted small mb-2">
                                    <i class="bi bi-person"></i> <?php echo htmlspecialchars($course['instructor_name']); ?>
                                </p>
                                <p class="card-text flex-grow-1">
                                    <?php 
                                    $description = strip_tags($course['description'] ?? '');
                                    echo htmlspecialchars(mb_substr($description, 0, 100));
                                    if (strlen($description) > 100) echo '...';
                                    ?>
                                </p>
                                
                                <div class="mt-auto">
                                    <?php if ($course['progress_percentage'] > 0): ?>
                                        <div class="mb-2">
                                            <small class="text-muted">Progress: <?php echo number_format($course['progress_percentage'], 1); ?>%</small>
                                            <div class="progress" style="height: 6px;">
                                                <div class="progress-bar" 
                                                     role="progressbar" 
                                                     style="width: <?php echo $course['progress_percentage']; ?>%"
                                                     aria-valuenow="<?php echo $course['progress_percentage']; ?>" 
                                                     aria-valuemin="0" 
                                                     aria-valuemax="100">
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($course['completed_at']): ?>
                                        <div class="alert alert-success py-2 mb-2">
                                            <i class="bi bi-check-circle"></i> Completed on <?php echo date('M d, Y', strtotime($course['completed_at'])); ?>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="mb-2">
                                        <small class="text-muted">
                                            <i class="bi bi-calendar"></i> Enrolled: <?php echo date('M d, Y', strtotime($course['enrolled_at'])); ?>
                                        </small>
                                    </div>
                                    
                                    <a href="<?php echo BASE_URL; ?>courses/show/<?php echo $course['id']; ?>" 
                                       class="btn btn-primary w-100">
                                        <i class="bi bi-play-circle"></i> Continue Learning
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
