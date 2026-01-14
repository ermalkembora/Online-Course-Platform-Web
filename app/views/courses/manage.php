<?php
/**
 * Manage Courses View
 */
$courses = $data['courses'] ?? [];
?>

<style>
    body {
        background: url('/e-learning-platform/assets/sources/home.jpg') no-repeat center center fixed;
        background-size: cover;
        color: #f8f9fa;
        min-height: 100vh;
    }

    .transparent-card {
        background-color: rgba(34, 87, 143, 0.13);
        backdrop-filter: blur(3px);
        border: none;
        border-radius: 15px;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4);
    }

    h2, .card-title, th, label {
        color: #ffffff;
    }

    .text-muted, small {
        color: #e0e0e0;
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

    .table th, .table td {
        vertical-align: middle;
        color: #ffffff;
    }

    .table-hover tbody tr:hover {
        background-color: rgba(255, 255, 255, 0.05);
    }

    .badge {
        opacity: 0.95;
    }

    .img-thumbnail {
        border: 1px solid rgba(255,255,255,0.3);
    }

    .alert-info {
        background-color: rgba(13, 110, 253, 0.2);
        color: #ffffff;
        border: none;
    }

    .text-primary {
        color: #66b2ff !important;
    }

    .btn-sm {
        font-size: 0.85rem;
    }
</style>

<div class="container py-5">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Manage My Courses</h2>
                <a href="<?php echo BASE_URL; ?>courses/create" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Create New Course
                </a>
            </div>

            <?php if (empty($courses)): ?>
                <div class="alert alert-info">
                    <h5><i class="bi bi-info-circle"></i> No Courses Yet</h5>
                    <p class="mb-3">You haven't created any courses yet. Start by creating your first course!</p>
                    <a href="<?php echo BASE_URL; ?>courses/create" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Create Your First Course
                    </a>
                </div>
            <?php else: ?>
                <div class="card transparent-card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table-1 table-hover">
                                <thead>
                                    <tr>
                                        <th>Thumbnail</th>
                                        <th>Title</th>
                                        <th>Status</th>
                                        <th>Price</th>
                                        <th>Students</th>
                                        <th>Lessons</th>
                                        <th>Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($courses as $course): ?>
                                        <tr>
                                            <td>
                                                <?php if ($course['thumbnail']): ?>
                                                    <img src="<?php echo BASE_URL; ?>uploads/courses/<?php echo htmlspecialchars($course['thumbnail']); ?>" 
                                                         alt="Thumbnail" 
                                                         class="img-thumbnail" 
                                                         style="width: 80px; height: 60px; object-fit: cover;">
                                                <?php else: ?>
                                                    <div class="bg-secondary d-flex align-items-center justify-content-center " 
                                                         style="width: 80px; height: 60px;">
                                                        <i class="bi bi-book text-white"></i>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <strong class="text-white"><?php echo htmlspecialchars($course['title']); ?></strong>
                                                <br>
                                                <small class="text-white">
                                                    <?php echo htmlspecialchars(mb_substr(strip_tags($course['description'] ?? ''), 0, 50)); ?>
                                                    <?php if (strlen($course['description'] ?? '') > 50) echo '...'; ?>
                                                </small>
                                            </td>
                                            <td>
                                                <?php if ($course['status'] === 'published'): ?>
                                                    <span class="badge bg-success">Published</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Draft</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <strong class="text-primary">
                                                    $<?php echo number_format($course['price'], 2); ?>
                                                </strong>
                                            </td>
                                            <td class="text-white">
                                                <i class="bi bi-people text-white"></i> <?php echo $course['enrollment_count'] ?? 0; ?>
                                            </td>
                                            <td class="text-white">
                                                <i class="bi bi-list-ul text-white"></i> <?php echo $course['lesson_count'] ?? 0; ?>
                                            </td>
                                            <td>
                                                <small><?php echo date('M d, Y', strtotime($course['created_at'])); ?></small>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="<?php echo BASE_URL; ?>courses/edit/<?php echo $course['id']; ?>" 
                                                       class="btn btn-sm btn-primary" 
                                                       title="Edit Course">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                    <a href="<?php echo BASE_URL; ?>courses/show/<?php echo $course['id']; ?>" 
                                                       class="btn btn-sm btn-info" 
                                                       title="View Course"
                                                       target="_blank">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                    <a href="<?php echo BASE_URL; ?>courses/lessons/<?php echo $course['id']; ?>" 
                                                       class="btn btn-sm btn-warning" 
                                                       title="Manage Lessons">
                                                        <i class="bi bi-list-ul"></i> Manage Lessons
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
