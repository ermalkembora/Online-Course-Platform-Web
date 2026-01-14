<?php
/**
 * Create Lesson View
 */
$course = $data['course'];
$lesson = $data['lesson'] ?? [];
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

    .form-control {
        background-color: rgba(255, 255, 255, 0.15);
        color: #ffffff;
        border: 1px solid rgba(255,255,255,0.3);
    }

    .form-control:focus {
        background-color: rgba(255, 255, 255, 0.25);
        color: #ffffff;
        border-color: #0d6efd;
        box-shadow: none;
    }

    .invalid-feedback {
        color: #ffb3b3;
    }

    .alert-danger {
        background-color: rgba(220, 53, 69, 0.2);
        color: #ffffff;
        border: none;
    }
</style>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card transparent-card">
                <div class="card-body p-5">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h2>Create New Lesson</h2>
                            <p class="text-white mb-0">Course: <strong><?php echo htmlspecialchars($course['title']); ?></strong></p>
                        </div>
                        <a href="<?php echo BASE_URL; ?>courses/lessons/<?php echo $course['id']; ?>" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left"></i> Back to Lessons
                        </a>
                    </div>

                    <?php if (isset($errors['general'])): ?>
                        <div class="alert alert-danger">
                            <?php echo htmlspecialchars($errors['general']); ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" id="createLessonForm" class="needs-validation" novalidate>
                        <div class="mb-3">
                            <label for="title" class="form-label text-white">Lesson Title <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control <?php echo isset($errors['title']) ? 'is-invalid' : ''; ?>" 
                                   id="title" 
                                   name="title" 
                                   value="<?php echo htmlspecialchars($lesson['title'] ?? ''); ?>" 
                                   required 
                                   minlength="3"
                                   placeholder="Enter lesson title">
                            <?php if (isset($errors['title'])): ?>
                                <div class="invalid-feedback">
                                    <?php echo htmlspecialchars($errors['title']); ?>
                                </div>
                            <?php else: ?>
                                <div class="invalid-feedback">Please provide a lesson title (at least 3 characters).</div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label text-white">Short Description</label>
                            <textarea class="form-control" 
                                      id="description" 
                                      name="description" 
                                      rows="2"
                                      placeholder="Brief description of the lesson"><?php echo htmlspecialchars($lesson['description'] ?? ''); ?></textarea>
                            <div class="form-text">Optional: A brief description of what this lesson covers.</div>
                        </div>

                        <div class="mb-3">
                            <label for="content" class="form-label text-white">Lesson Content</label>
                            <textarea class="form-control <?php echo isset($errors['content']) ? 'is-invalid' : ''; ?>" 
                                      id="content" 
                                      name="content" 
                                      rows="10"
                                      placeholder="Enter lesson content (text, HTML, etc.)"><?php echo htmlspecialchars($lesson['content'] ?? ''); ?></textarea>
                            <?php if (isset($errors['content'])): ?>
                                <div class="invalid-feedback">
                                    <?php echo htmlspecialchars($errors['content']); ?>
                                </div>
                            <?php endif; ?>
                            <div class="form-text">Enter the lesson content. Either content or video URL is required.</div>
                        </div>

                        <div class="mb-3">
                            <label for="video_url" class="form-label text-white">Video URL</label>
                            <input type="url" 
                                   class="form-control <?php echo isset($errors['video_url']) ? 'is-invalid' : ''; ?>" 
                                   id="video_url" 
                                   name="video_url" 
                                   value="<?php echo htmlspecialchars($lesson['video_url'] ?? ''); ?>" 
                                   placeholder="https://www.youtube.com/watch?v=... or video URL">
                            <?php if (isset($errors['video_url'])): ?>
                                <div class="invalid-feedback">
                                    <?php echo htmlspecialchars($errors['video_url']); ?>
                                </div>
                            <?php endif; ?>
                            <div class="form-text text-white">Optional: YouTube URL or other video URL. Either content or video URL is required.</div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="duration" class="form-label text-white">Duration (minutes)</label>
                                <input type="number" 
                                       class="form-control <?php echo isset($errors['duration']) ? 'is-invalid' : ''; ?>" 
                                       id="duration" 
                                       name="duration" 
                                       value="<?php echo htmlspecialchars($lesson['duration'] ?? ''); ?>" 
                                       min="0"
                                       placeholder="0">
                                <?php if (isset($errors['duration'])): ?>
                                    <div class="invalid-feedback">
                                        <?php echo htmlspecialchars($errors['duration']); ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="col-md-4">
                                <label for="order_index" class="form-label text-white">Order</label>
                                <input type="number" 
                                       class="form-control" 
                                       id="order_index" 
                                       name="order_index" 
                                       value="<?php echo htmlspecialchars($lesson['order_index'] ?? 1); ?>" 
                                       min="1"
                                       required>
                                <div class="form-text text-white">Display order in course</div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label text-white">Options</label>
                                <div class="form-check mt-2">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           id="is_free" 
                                           name="is_free" 
                                           value="1"
                                           <?php echo ($lesson['is_free'] ?? 0) ? 'checked' : ''; ?>>
                                    <label class="form-check-label text-white" for="is_free">
                                        Free Preview
                                    </label>
                                </div>
                                <div class="form-text text-white">Allow non-enrolled users to view</div>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> Create Lesson
                            </button>
                            <a href="<?php echo BASE_URL; ?>courses/lessons/<?php echo $course['id']; ?>" class="btn btn-outline-secondary">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('createLessonForm');
    const content = document.getElementById('content');
    const videoUrl = document.getElementById('video_url');
    
    // Validate that at least one of content or video_url is provided
    function validateContent() {
        if (content.value.trim() === '' && videoUrl.value.trim() === '') {
            content.setCustomValidity('Either content or video URL is required');
            videoUrl.setCustomValidity('Either content or video URL is required');
        } else {
            content.setCustomValidity('');
            videoUrl.setCustomValidity('');
        }
    }
    
    content.addEventListener('input', validateContent);
    videoUrl.addEventListener('input', validateContent);
    
    form.addEventListener('submit', function(event) {
        validateContent();
        if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
        }
        form.classList.add('was-validated');
    });
});
</script>
