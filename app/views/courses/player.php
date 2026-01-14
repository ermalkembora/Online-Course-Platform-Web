<?php
/**
 * Course Player View
 */
$course = $data['course'];
$lessons = $data['lessons'];
$currentLesson = $data['current_lesson'];
$nextLesson = $data['next_lesson'] ?? null;
$previousLesson = $data['previous_lesson'] ?? null;

// Convert YouTube URL to embed format if needed
function getEmbedUrl($url) {
    if (empty($url)) return null;
    
    if (strpos($url, 'youtube.com/embed/') !== false) return $url;
    
    if (preg_match('/youtube.com\/watch\?v=([a-zA-Z0-9_-]+)/', $url, $matches)) return 'https://www.youtube.com/embed/' . $matches[1];
    
    if (preg_match('/youtu.be\/([a-zA-Z0-9_-]+)/', $url, $matches)) return 'https://www.youtube.com/embed/' . $matches[1];
    
    return $url;
}

$embedUrl = getEmbedUrl($currentLesson['video_url']);
?>

<style>
body {
    background: url('/e-learning-platform/assets/sources/home.jpg') no-repeat center center fixed;
    background-size: cover;
    color: #f8f9fa;
}

.card {
    background-color: rgba(34,87,143,0.13);
    backdrop-filter: blur(3px);
    border: none;
    border-radius: 15px;
    box-shadow: 0 8px 32px rgba(0,0,0,0.4);
    color: #ffffff;
}

h2, h4, h5, strong {
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

.btn-success {
    background-color: #198754;
    border-color: #198754;
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

.breadcrumb a {
    color: #0d6efd;
}

.breadcrumb a:hover {
    color: #0b5ed7;
    text-decoration: underline;
}

.list-group-item {
    background-color: rgba(0,0,0,0.45);
    color: #ffffff;
    border: 1px solid rgba(255,255,255,0.2);
    border-radius: 8px;
    margin-bottom: 8px;
}

.list-group-item.active {
    background-color: #0d6efd;
    border-color: #0d6efd;
}

.list-group-item.active strong {
    color: white;
}

.lesson-content {
    line-height: 1.8;
    color: #ffffff;
}

.lesson-content h4 {
    margin-bottom: 1rem;
}
.ratio iframe {
    border-radius: 12px;
}
</style>

<div class="row">
    <div class="col-md-9">
        <div class="card shadow mb-4">
            <div class="card-body">
                <nav aria-label="breadcrumb" class="mb-3">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>courses">Courses</a></li>
                        <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>courses/show/<?php echo $course['id']; ?>"><?php echo htmlspecialchars($course['title']); ?></a></li>
                        <li class="breadcrumb-item active"><?php echo htmlspecialchars($currentLesson['title']); ?></li>
                    </ol>
                </nav>

                <h2 class="mb-3"><?php echo htmlspecialchars($currentLesson['title']); ?></h2>
                <?php if ($currentLesson['description']): ?>
                    <p class="text-muted mb-4"><?php echo htmlspecialchars($currentLesson['description']); ?></p>
                <?php endif; ?>

                <!-- Video Player -->
                <?php if ($embedUrl): ?>
                    <div class="ratio ratio-16x9 mb-4">
                        <iframe src="<?php echo htmlspecialchars($embedUrl); ?>" 
                                frameborder="0" 
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                                allowfullscreen>
                        </iframe>
                    </div>
                <?php elseif ($currentLesson['video_url']): ?>
                    <div class="alert alert-info mb-4">
                        <p><strong>Video URL:</strong> <a href="<?php echo htmlspecialchars($currentLesson['video_url']); ?>" target="_blank"><?php echo htmlspecialchars($currentLesson['video_url']); ?></a></p>
                    </div>
                <?php endif; ?>

                <!-- Lesson Content -->
                <?php if ($currentLesson['content']): ?>
                    <div class="lesson-content">
                        <h4>Lesson Content</h4>
                        <div class="border rounded p-4 bg-secondary">
                            <?php echo nl2br(htmlspecialchars($currentLesson['content'])); ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Navigation -->
                <div class="d-flex justify-content-between mt-4 pt-4 border-top">
                    <?php if ($previousLesson): ?>
                        <a href="<?php echo BASE_URL; ?>courses/player/<?php echo $course['id']; ?>/<?php echo $previousLesson['id']; ?>" 
                           class="btn btn-outline-primary">
                            <i class="bi bi-arrow-left"></i> Previous Lesson
                        </a>
                    <?php else: ?><span></span><?php endif; ?>

                    <?php if ($nextLesson): ?>
                        <a href="<?php echo BASE_URL; ?>courses/player/<?php echo $course['id']; ?>/<?php echo $nextLesson['id']; ?>" 
                           class="btn btn-primary">
                            Next Lesson <i class="bi bi-arrow-right"></i>
                        </a>
                    <?php else: ?>
                        <a href="<?php echo BASE_URL; ?>courses/show/<?php echo $course['id']; ?>" 
                           class="btn btn-success">
                            <i class="bi bi-check-circle"></i> Course Complete
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Sidebar: Lessons List -->
    <div class="col-md-3">
        <div class="card shadow sticky-top" style="top: 20px; background-color: rgba(34,87,143,0.13);">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <i class="bi bi-list-ul"></i> Course Lessons
                </h5>
            </div>
            <div class="list-group list-group-flush" style="max-height: 600px; overflow-y: auto;">
                <?php foreach ($lessons as $index => $lesson): ?>
                    <a href="<?php echo BASE_URL; ?>courses/player/<?php echo $course['id']; ?>/<?php echo $lesson['id']; ?>" 
                       class="list-group-item list-group-item-action <?php echo $lesson['id'] == $currentLesson['id'] ? 'active' : ''; ?>">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <div class="d-flex align-items-center mb-1">
                                    <span class="badge bg-secondary me-2"><?php echo $index + 1; ?></span>
                                    <strong><?php echo htmlspecialchars($lesson['title']); ?></strong>
                                </div>
                                <?php if ($lesson['description']): ?>
                                    <small class="text-muted d-block">
                                        <?php echo htmlspecialchars(mb_substr($lesson['description'], 0, 50)); ?>
                                        <?php if (strlen($lesson['description']) > 50) echo '...'; ?>
                                    </small>
                                <?php endif; ?>
                                <div class="mt-1">
                                    <?php if ($lesson['duration']): ?>
                                        <small class="text-muted">
                                            <i class="bi bi-clock"></i> <?php echo $lesson['duration']; ?> min
                                        </small>
                                    <?php endif; ?>
                                    <?php if ($lesson['is_free']): ?>
                                        <span class="badge bg-success ms-2">Free</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php if ($lesson['id'] == $currentLesson['id']): ?>
                                <i class="bi bi-play-circle-fill text-white"></i>
                            <?php endif; ?>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
            <div class="card-footer">
                <a href="<?php echo BASE_URL; ?>courses/show/<?php echo $course['id']; ?>" 
                   class="btn btn-outline-secondary btn-sm w-100">
                    <i class="bi bi-arrow-left"></i> Back to Course
                </a>
            </div>
        </div>
    </div>
</div>
