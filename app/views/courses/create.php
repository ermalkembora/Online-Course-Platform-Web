<?php
/**
 * Create Course View
 */
$course = $data['course'] ?? [];
$errors = $data['errors'] ?? [];
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

    h2, .card-title, label {
        color: #ffffff;
    }

    .text-muted, .form-text {
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

    .invalid-feedback {
        color: #ff6b6b;
    }

    .alert-danger {
        background-color: rgba(255, 0, 0, 0.2);
        color: #ffffff;
        border: none;
    }
</style>

<div class="row">
    <div class="col-md-10 mx-auto">
        <div class="card transparent-card">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Create New Course</h2>
                    <a href="<?php echo BASE_URL; ?>courses/manage" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Back to My Courses
                    </a>
                </div>

                <?php if (isset($errors['general'])): ?>
                    <div class="alert alert-danger">
                        <?php echo htmlspecialchars($errors['general']); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data" id="createCourseForm" class="needs-validation" novalidate>
                    <div class="mb-3">
                        <label for="title" class="form-label">Course Title <span class="text-danger">*</span></label>
                        <input type="text" 
                               class="form-control <?php echo isset($errors['title']) ? 'is-invalid' : ''; ?>" 
                               id="title" 
                               name="title" 
                               value="<?php echo htmlspecialchars($course['title'] ?? ''); ?>" 
                               required 
                               minlength="3"
                               maxlength="255"
                               placeholder="Enter course title">
                        <?php if (isset($errors['title'])): ?>
                            <div class="invalid-feedback">
                                <?php echo htmlspecialchars($errors['title']); ?>
                            </div>
                        <?php else: ?>
                            <div class="invalid-feedback">Please provide a course title (at least 3 characters).</div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description <span class="text-danger">*</span></label>
                        <textarea class="form-control <?php echo isset($errors['description']) ? 'is-invalid' : ''; ?>" 
                                  id="description" 
                                  name="description" 
                                  rows="8" 
                                  required 
                                  minlength="10"
                                  placeholder="Enter course description"><?php echo htmlspecialchars($course['description'] ?? ''); ?></textarea>
                        <?php if (isset($errors['description'])): ?>
                            <div class="invalid-feedback">
                                <?php echo htmlspecialchars($errors['description']); ?>
                            </div>
                        <?php else: ?>
                            <div class="invalid-feedback">Please provide a description (at least 10 characters).</div>
                        <?php endif; ?>
                        <div class="form-text">Describe what students will learn in this course.</div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="price" class="form-label">Price ($) <span class="text-danger">*</span></label>
                            <input type="number" 
                                   class="form-control <?php echo isset($errors['price']) ? 'is-invalid' : ''; ?>" 
                                   id="price" 
                                   name="price" 
                                   value="<?php echo htmlspecialchars($course['price'] ?? '0.00'); ?>" 
                                   step="0.01" 
                                   min="0" 
                                   required>
                            <?php if (isset($errors['price'])): ?>
                                <div class="invalid-feedback">
                                    <?php echo htmlspecialchars($errors['price']); ?>
                                </div>
                            <?php else: ?>
                                <div class="invalid-feedback">Please provide a valid price.</div>
                            <?php endif; ?>
                            <div class="form-text">Set price to 0 for a free course.</div>
                        </div>

                        <div class="col-md-6">
                            <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="draft" <?php echo ($course['status'] ?? 'draft') === 'draft' ? 'selected' : ''; ?>>Draft</option>
                                <option value="published" <?php echo ($course['status'] ?? '') === 'published' ? 'selected' : ''; ?>>Published</option>
                            </select>
                            <div class="form-text">Draft courses are not visible to students.</div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="thumbnail" class="form-label">Course Thumbnail</label>
                        <input type="file" 
                               class="form-control <?php echo isset($errors['thumbnail']) ? 'is-invalid' : ''; ?>" 
                               id="thumbnail" 
                               name="thumbnail" 
                               accept="image/jpeg,image/png,image/gif,image/webp">
                        <?php if (isset($errors['thumbnail'])): ?>
                            <div class="invalid-feedback">
                                <?php echo htmlspecialchars($errors['thumbnail']); ?>
                            </div>
                        <?php endif; ?>
                        <div class="form-text">Upload a thumbnail image (JPG, PNG, GIF, or WebP, max 5MB). Recommended size: 1280x720px.</div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> Create Course
                        </button>
                        <a href="<?php echo BASE_URL; ?>courses/manage" class="btn btn-outline-secondary">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('createCourseForm');
    
    form.addEventListener('submit', function(event) {
        if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
        }
        form.classList.add('was-validated');
    });
});
</script>
