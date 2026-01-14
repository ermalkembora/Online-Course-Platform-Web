<?php
/**
 * Courses Index View
 */

$courses = $data['courses'] ?? [];
$pagination = $data['pagination'] ?? [];
$searchTerm = $data['search_term'] ?? '';
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
        backdrop-filter: blur(15px);
        border: none;
        border-radius: 15px;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4);
        color: #ffffff;
    }

    h2, .card-title {
        color: #ffffff;
    }

    .text-muted, .small {
        color: #e0e0e0 !important;
    }

    .form-control, .form-select {
        background-color: rgba(0, 0, 0, 0.5);
        color: #ffffff;
        border: 1px solid rgba(255, 255, 255, 0.3);
    }

    .form-control:focus, .form-select:focus {
        background-color: rgba(0, 0, 0, 0.65);
        color: #ffffff;
        border-color: #0d6efd;
        box-shadow: none;
    }

    .btn-primary {
        background-color: #0d6efd;
        border-color: #0d6efd;
    }

    .btn-primary:hover {
        background-color: #0b5ed7;
    }

    .btn-outline-secondary {
        color: #bbbbbb;
        border-color: #bbbbbb;
    }

    .btn-outline-secondary:hover {
        background-color: rgba(255, 255, 255, 0.1);
        color: #ffffff;
    }

    .alert-info {
        background-color: rgba(0, 123, 255, 0.2);
        color: #ffffff;
        border: none;
    }

    .pagination .page-link {
        background-color: rgba(0, 0, 0, 0.5);
        color: #ffffff;
        border: 1px solid rgba(255, 255, 255, 0.3);
    }

    .pagination .page-item.active .page-link {
        background-color: #0d6efd;
        border-color: #0d6efd;
        color: #ffffff;
    }

    .pagination .page-link:hover {
        background-color: rgba(13, 110, 253, 0.8);
        color: #ffffff;
    }

    .card-img-top {
        border-radius: 15px 15px 0 0;
    }

    .bg-secondary {
        background-color: rgba(100, 100, 100, 0.6) !important;
    }
</style>

<div class="row mb-4">
    <div class="col-12">
        <h2 class="mb-4">All Courses</h2>
        
        <!-- Search Form -->
        <form method="GET" action="<?php echo BASE_URL; ?>courses" class="mb-4">
            <div class="input-group">
                <input type="text" 
                       class="form-control" 
                       name="search" 
                       placeholder="Search courses..." 
                       value="<?php echo htmlspecialchars($searchTerm); ?>">
                <button class="btn btn-primary" type="submit">
                    <i class="bi bi-search"></i> Search
                </button>
                <?php if (!empty($searchTerm)): ?>
                    <a href="<?php echo BASE_URL; ?>courses" class="btn btn-outline-secondary">
                        Clear
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<?php if (empty($courses)): ?>
    <div class="alert alert-info">
        <?php if (!empty($searchTerm)): ?>
            No courses found matching "<?php echo htmlspecialchars($searchTerm); ?>".
        <?php else: ?>
            No courses available yet. Check back soon!
        <?php endif; ?>
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
                             style="height: 200px; object-fit: cover;">
                    <?php else: ?>
                        <div class="card-img-top bg-secondary d-flex align-items-center justify-content-center" 
                             style="height: 200px;">
                            <i class="bi bi-book text-white" style="font-size: 3rem;"></i>
                        </div>
                    <?php endif; ?>
                    
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title"><?php echo htmlspecialchars($course['title']); ?></h5>
                        <p class="card-text small mb-2">
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
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <span class="h5 text-primary mb-0"><?php echo '$' . number_format($course['price'], 2); ?></span>
                                </div>
                                <div class="text-muted small">
                                    <i class="bi bi-people"></i> <?php echo $course['enrollment_count'] ?? 0; ?> students
                                </div>
                            </div>
                            
                           <div class="mb-2">
    <?php if (!empty($course['review_count']) && $course['review_count'] > 0): ?>
        <span class="badge bg-warning text-dark">
            <i class="bi bi-star-fill"></i>
            <?php echo number_format($course['rating'], 1); ?>
        </span>
        <span class="text-muted small ms-2">
            (<?php echo $course['review_count']; ?> reviews)
        </span>
    <?php else: ?>
        <span class="badge bg-secondary">
            No ratings yet
        </span>
    <?php endif; ?>
</div>

                            
                            <a href="<?php echo BASE_URL; ?>courses/show/<?php echo $course['id']; ?>" 
                               class="btn btn-primary w-100">
                                View Details
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Pagination -->
    <?php if ($pagination['total_pages'] > 1): ?>
        <nav aria-label="Course pagination" class="mt-4">
            <ul class="pagination justify-content-center">
                <?php if ($pagination['current_page'] > 1): ?>
                    <li class="page-item">
                        <a class="page-link" 
                           href="<?php echo BASE_URL; ?>courses?page=<?php echo $pagination['current_page'] - 1; ?><?php echo !empty($searchTerm) ? '&search=' . urlencode($searchTerm) : ''; ?>">
                            Previous
                        </a>
                    </li>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                    <?php if ($i == $pagination['current_page']): ?>
                        <li class="page-item active">
                            <span class="page-link"><?php echo $i; ?></span>
                        </li>
                    <?php else: ?>
                        <li class="page-item">
                            <a class="page-link" 
                               href="<?php echo BASE_URL; ?>courses?page=<?php echo $i; ?><?php echo !empty($searchTerm) ? '&search=' . urlencode($searchTerm) : ''; ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                    <?php endif; ?>
                <?php endfor; ?>

                <?php if ($pagination['current_page'] < $pagination['total_pages']): ?>
                    <li class="page-item">
                        <a class="page-link" 
                           href="<?php echo BASE_URL; ?>courses?page=<?php echo $pagination['current_page'] + 1; ?><?php echo !empty($searchTerm) ? '&search=' . urlencode($searchTerm) : ''; ?>">
                            Next
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
        
        <p class="text-center text-muted mt-2">
            Showing page <?php echo $pagination['current_page']; ?> of <?php echo $pagination['total_pages']; ?> 
            (<?php echo $pagination['total']; ?> total courses)
        </p>
    <?php endif; ?>
<?php endif; ?>
