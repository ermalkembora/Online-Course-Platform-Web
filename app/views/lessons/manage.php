<?php
/**
 * Manage Lessons View
 */
$course = $data['course'];
$lessons = $data['lessons'] ?? [];
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

    h2, .card-title {
        color: #ffffff;
    }

    .text-muted, .form-text, small {
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

    .badge {
        opacity: 0.95;
    }

    .table th, .table td {
        color: #ffffff;
    }

    .table-hover tbody tr:hover {
        background-color: rgba(255, 255, 255, 0.1);
    }

    .alert-info {
        background-color: rgba(34, 87, 143, 0.2);
        color: #ffffff;
        border: none;
    }

    .alert-info h5 {
        color: #ffffff;
    }

    .table thead th {
        border-bottom: 2px solid rgba(255,255,255,0.3);
    }

    .btn-danger {
        background-color: #dc3545;
        border-color: #dc3545;
    }

    .btn-danger:hover {
        background-color: #bb2d3b;
    }

    .btn-sm {
        font-size: 0.85rem;
    }
</style>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 style="color: #ffffff;">Manage Lessons</h2>
                    <p class="mb-0">Course: <strong><?php echo htmlspecialchars($course['title']); ?></strong></p>
                </div>
                <div>
                    <a href="<?php echo BASE_URL; ?>courses/createLesson/<?php echo $course['id']; ?>" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Add Lesson
                    </a>
                    <a href="<?php echo BASE_URL; ?>courses/manage" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Back to Courses
                    </a>
                </div>
            </div>

            <?php if (empty($lessons)): ?>
                <div class="alert alert-info">
                    <h5><i class="bi bi-info-circle"></i> No Lessons Yet</h5>
                    <p class="mb-3">This course doesn't have any lessons yet. Create your first lesson to get started!</p>
                    <a href="<?php echo BASE_URL; ?>courses/createLesson/<?php echo $course['id']; ?>" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Create First Lesson
                    </a>
                </div>
            <?php else: ?>
                <div class="card transparent-card">
                    <div class="card-body" >
                        <div class="table-responsive" style="color: white;">
                            <table class="table-1 table-hover">
                                <thead>
                                    <tr>
                                        <th width="5%">Order</th>
                                        <th width="25%">Title</th>
                                        <th width="30%">Description</th>
                                        <th width="10%">Type</th>
                                        <th width="10%">Duration</th>
                                        <th width="10%">Free</th>
                                        <th width="10%">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($lessons as $lesson): ?>
                                        <tr>
                                            <td>
                                                <span class="badge bg-secondary"><?php echo $lesson['order_index']; ?></span>
                                            </td>
                                            <td>
                                                <strong><?php echo htmlspecialchars($lesson['title']); ?></strong>
                                            </td>
                                            <td>
                                                <small class="text-white">
                                                    <?php 
                                                    $desc = strip_tags($lesson['description'] ?? '');
                                                    echo htmlspecialchars(mb_substr($desc, 0, 60));
                                                    if (strlen($desc) > 60) echo '...';
                                                    ?>
                                                </small>
                                            </td>
                                            <td>
                                                <?php if ($lesson['video_url']): ?>
                                                    <span class="badge bg-danger">
                                                        <i class="bi bi-play-circle"></i> Video
                                                    </span>
                                                <?php elseif ($lesson['content']): ?>
                                                    <span class="badge bg-primary">
                                                        <i class="bi bi-file-text"></i> Text
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($lesson['duration']): ?>
                                                    <?php echo $lesson['duration']; ?> min
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($lesson['is_free']): ?>
                                                    <span class="badge bg-success">Free</span>
                                                <?php else: ?>
                                                    <span class="text-white">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="<?php echo BASE_URL; ?>courses/editLesson/<?php echo $lesson['id']; ?>" 
                                                       class="btn btn-sm btn-primary" 
                                                       title="Edit">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                    <form method="POST" 
                                                          action="<?php echo BASE_URL; ?>courses/deleteLesson/<?php echo $lesson['id']; ?>" 
                                                          class="d-inline"
                                                          onsubmit="return confirm('Are you sure you want to delete this lesson?');">
                                                        <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </form>
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
