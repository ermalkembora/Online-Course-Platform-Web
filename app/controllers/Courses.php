<?php
/**
 * Courses Controller
 * 
 * Handles course-related actions: listing, details, enrolled courses.
 */

class Courses extends Controller {
    private $courseModel;

    public function __construct() {
        // Require login for all course-related routes
        // This protects: /courses, /courses/index, /courses/show, /courses/search, etc.
        require_login(BASE_URL . 'users/login');
        
        $this->courseModel = $this->model('Course');
    }

    /**
     * List all published courses
     * 
     * @return void
     */
    public function index() {
        $page = (int)($_GET['page'] ?? 1);
        $search = trim($_GET['search'] ?? '');

        if ($page < 1) {
            $page = 1;
        }

        if (!empty($search)) {
            $result = $this->courseModel->searchCourses($search, $page);
        } else {
            $result = $this->courseModel->getPublishedCourses($page);
        }

        $data = [
            'title' => 'Courses',
            'courses' => $result['courses'],
            'pagination' => [
                'current_page' => $result['page'],
                'total_pages' => $result['total_pages'],
                'total' => $result['total'],
                'per_page' => $result['per_page']
            ],
            'search_term' => $search
        ];

        $this->render('index', $data);
    }

    /**
     * Show course details
     * 
     * @param int $id Course ID
     * @return void
     */
    public function show($id = null) {
        if (!$id) {
            set_flash('error', 'Course ID is required.');
            $this->redirect('courses');
        }

        $courseId = (int)$id;
        $course = $this->courseModel->findWithLessons($courseId);
        $reviewModel = $this->model('Review');
$reviews = $reviewModel->getByCourse($courseId);


        if (!$course) {
            set_flash('error', 'Course not found.');
            $this->redirect('courses');
        }

        // Check if user is enrolled (if logged in)
        $isEnrolled = false;
        if (is_logged_in()) {
            $user = current_user();
            $isEnrolled = $this->courseModel->isEnrolled($user['id'], $courseId);
        }
$data = [
    'title' => $course['title'],
    'course' => $course,
    'is_enrolled' => $isEnrolled,
    'reviews' => $reviews
];


        $this->render('show', $data);
    }

    /**
     * Show enrolled courses (My Courses)
     * 
     * @return void
     */
    public function my_courses() {
        require_login();

        $user = current_user();
        if (!$user) {
            set_flash('error', 'User not found.');
            $this->redirect('');
        }

        $enrolledCourses = $this->courseModel->getEnrolledCourses($user['id']);

        $data = [
            'title' => 'My Courses',
            'courses' => $enrolledCourses
        ];

        $this->render('my_courses', $data);
    }

    /**
     * Enroll in free course
     * 
     * @return void
     */
    public function enroll() {
        require_login();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('courses');
        }

        $courseId = (int)($_POST['course_id'] ?? 0);
        $user = current_user();

        if (!$courseId) {
            set_flash('error', 'Course ID is required.');
            $this->redirect('courses');
        }

        $course = $this->courseModel->findById($courseId);
        if (!$course) {
            set_flash('error', 'Course not found.');
            $this->redirect('courses');
        }

        // Check if course is free
        if ($course['price'] > 0) {
            set_flash('error', 'This course requires payment. Please use the checkout.');
            $this->redirect('checkout/confirm/' . $courseId);
        }

        // Check if already enrolled
        if ($this->courseModel->isEnrolled($user['id'], $courseId)) {
            set_flash('info', 'You are already enrolled in this course.');
            $this->redirect('courses/show/' . $courseId);
        }

        // Create enrollment
        require_once __DIR__ . '/../models/Payment.php';
        $paymentModel = $this->model('Payment');
        
        if ($paymentModel->createEnrollment($user['id'], $courseId)) {
            set_flash('success', 'Successfully enrolled in the course!');
            $this->redirect('courses/my_courses');
        } else {
            set_flash('error', 'Failed to enroll in course.');
            $this->redirect('courses/show/' . $courseId);
        }
    }

    /**
     * Create a new course
     * 
     * @return void
     */
    public function create() {
        require_login();

        $user = current_user();
        if (!$user) {
            set_flash('error', 'User not found.');
            $this->redirect('');
        }

        $data = [
            'title' => 'Create Course',
            'errors' => [],
            'course' => [
                'title' => '',
                'description' => '',
                'price' => '0.00',
                'status' => 'draft'
            ]
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $title = trim($_POST['title'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $price = (float)($_POST['price'] ?? 0);
            $status = trim($_POST['status'] ?? 'draft');
            $errors = [];

            // Validation
            if (empty($title)) {
                $errors['title'] = 'Course title is required';
            } elseif (strlen($title) < 3) {
                $errors['title'] = 'Title must be at least 3 characters';
            } elseif (strlen($title) > 255) {
                $errors['title'] = 'Title must be less than 255 characters';
            }

            if (empty($description)) {
                $errors['description'] = 'Description is required';
            } elseif (strlen($description) < 10) {
                $errors['description'] = 'Description must be at least 10 characters';
            }

            if ($price < 0) {
                $errors['price'] = 'Price cannot be negative';
            }

            if (!in_array($status, ['draft', 'published'])) {
                $status = 'draft';
            }

            // Handle thumbnail upload
            $thumbnail = null;
            if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
                $uploadResult = $this->uploadThumbnail($_FILES['thumbnail']);
                
                if ($uploadResult['success']) {
                    $thumbnail = $uploadResult['filename'];
                } else {
                    $errors['thumbnail'] = $uploadResult['message'];
                }
            }

            // Create course if no errors
            if (empty($errors)) {
                $courseData = [
                    'instructor_id' => $user['id'],
                    'title' => $title,
                    'description' => $description,
                    'price' => $price,
                    'thumbnail' => $thumbnail,
                    'status' => $status
                ];

                $courseId = $this->courseModel->create($courseData);

                if ($courseId) {
                    set_flash('success', 'Course created successfully!');
                    $this->redirect('courses/manage');
                } else {
                    $errors['general'] = 'Failed to create course. Please try again.';
                }
            }

            $data['errors'] = $errors;
            $data['course'] = [
                'title' => htmlspecialchars($title),
                'description' => htmlspecialchars($description),
                'price' => $price,
                'status' => $status
            ];
        }

        $this->render('create', $data);
    }

    /**
     * Edit course
     * 
     * @param int $id Course ID
     * @return void
     */
    public function edit($id = null) {
        require_login();

        if (!$id) {
            set_flash('error', 'Course ID is required.');
            $this->redirect('courses/manage');
        }

        $courseId = (int)$id;
        $user = current_user();
        
        // Check if user is owner or admin
        if (!$this->courseModel->isOwner($courseId, $user['id']) && !is_admin()) {
            set_flash('error', 'You do not have permission to edit this course.');
            $this->redirect('courses/manage');
        }

        $course = $this->courseModel->findById($courseId);
        if (!$course) {
            set_flash('error', 'Course not found.');
            $this->redirect('courses/manage');
        }

        $data = [
            'title' => 'Edit Course',
            'course' => $course,
            'errors' => []
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $title = trim($_POST['title'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $price = (float)($_POST['price'] ?? 0);
            $status = trim($_POST['status'] ?? 'draft');
            $errors = [];

            // Validation
            if (empty($title)) {
                $errors['title'] = 'Course title is required';
            } elseif (strlen($title) < 3) {
                $errors['title'] = 'Title must be at least 3 characters';
            }

            if (empty($description)) {
                $errors['description'] = 'Description is required';
            } elseif (strlen($description) < 10) {
                $errors['description'] = 'Description must be at least 10 characters';
            }

            if ($price < 0) {
                $errors['price'] = 'Price cannot be negative';
            }

            if (!in_array($status, ['draft', 'published'])) {
                $status = 'draft';
            }

            // Handle thumbnail upload
            $thumbnail = $course['thumbnail'];
            if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
                $uploadResult = $this->uploadThumbnail($_FILES['thumbnail']);
                
                if ($uploadResult['success']) {
                    // Delete old thumbnail if exists
                    if ($thumbnail) {
                        $oldPath = UPLOAD_DIR . 'courses/' . $thumbnail;
                        if (file_exists($oldPath)) {
                            unlink($oldPath);
                        }
                    }
                    $thumbnail = $uploadResult['filename'];
                } else {
                    $errors['thumbnail'] = $uploadResult['message'];
                }
            }

            // Update course if no errors
            if (empty($errors)) {
                $updateData = [
                    'title' => $title,
                    'description' => $description,
                    'price' => $price,
                    'thumbnail' => $thumbnail,
                    'status' => $status
                ];

                if ($this->courseModel->update($courseId, $updateData)) {
                    set_flash('success', 'Course updated successfully!');
                    $this->redirect('courses/manage');
                } else {
                    $errors['general'] = 'Failed to update course. Please try again.';
                }
            }

            $data['errors'] = $errors;
            $data['course']['title'] = htmlspecialchars($title);
            $data['course']['description'] = htmlspecialchars($description);
            $data['course']['price'] = $price;
            $data['course']['status'] = $status;
            $data['course']['thumbnail'] = $thumbnail;
        }

        $this->render('edit', $data);
    }

    /**
     * Manage courses (list instructor's courses)
     * 
     * @return void
     */
    public function manage() {
        require_login();

        $user = current_user();
        if (!$user) {
            set_flash('error', 'User not found.');
            $this->redirect('');
        }

        $courses = $this->courseModel->getInstructorCourses($user['id']);

        $data = [
            'title' => 'Manage My Courses',
            'courses' => $courses
        ];

        $this->render('manage', $data);
    }

    /**
     * Upload course thumbnail
     * 
     * @param array $file Uploaded file array
     * @return array Result with success status and filename/message
     */
    private function uploadThumbnail($file) {
        // Check file error
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'message' => 'File upload error'];
        }

        // Check file size
        if ($file['size'] > MAX_FILE_SIZE) {
            return ['success' => false, 'message' => 'File size exceeds maximum allowed size (5MB)'];
        }

        // Check file type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mimeType, ALLOWED_IMAGE_TYPES)) {
            return ['success' => false, 'message' => 'Invalid file type. Only JPG, PNG, GIF, and WebP are allowed'];
        }

        // Generate unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = time() . '_' . uniqid() . '.' . $extension;
        $uploadPath = UPLOAD_DIR . 'courses/' . $filename;

        // Create directory if it doesn't exist
        $uploadDir = dirname($uploadPath);
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
            return ['success' => true, 'filename' => $filename];
        }

        return ['success' => false, 'message' => 'Failed to upload file'];
    }

    /**
     * Manage lessons for a course
     * 
     * @param int $id Course ID
     * @return void
     */
    public function lessons($id = null) {
        require_login();

        if (!$id) {
            set_flash('error', 'Course ID is required.');
            $this->redirect('courses/manage');
        }

        $courseId = (int)$id;
        $user = current_user();

        // Check if user is owner or admin
        if (!$this->courseModel->isOwner($courseId, $user['id']) && !is_admin()) {
            set_flash('error', 'You do not have permission to manage lessons for this course.');
            $this->redirect('courses/manage');
        }

        $course = $this->courseModel->findById($courseId);
        if (!$course) {
            set_flash('error', 'Course not found.');
            $this->redirect('courses/manage');
        }

        $lessons = $this->courseModel->getLessons($courseId);

        $data = [
            'title' => 'Manage Lessons',
            'course' => $course,
            'lessons' => $lessons
        ];

        $this->render('lessons/manage', $data);
    }

    /**
     * Create a new lesson
     * 
     * @param int $id Course ID
     * @return void
     */
    public function createLesson($id = null) {
        require_login();

        if (!$id) {
            set_flash('error', 'Course ID is required.');
            $this->redirect('courses/manage');
        }

        $courseId = (int)$id;
        $user = current_user();

        // Check if user is owner or admin
        if (!$this->courseModel->isOwner($courseId, $user['id']) && !is_admin()) {
            set_flash('error', 'You do not have permission to create lessons for this course.');
            $this->redirect('courses/manage');
        }

        $course = $this->courseModel->findById($courseId);
        if (!$course) {
            set_flash('error', 'Course not found.');
            $this->redirect('courses/manage');
        }

        // Get max order index
        $lessons = $this->courseModel->getLessons($courseId);
        $maxOrder = count($lessons);

        $data = [
            'title' => 'Create Lesson',
            'course' => $course,
            'errors' => [],
            'lesson' => [
                'title' => '',
                'description' => '',
                'content' => '',
                'video_url' => '',
                'duration' => '',
                'order_index' => $maxOrder + 1,
                'is_free' => 0
            ]
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $title = trim($_POST['title'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $content = trim($_POST['content'] ?? '');
            $videoUrl = trim($_POST['video_url'] ?? '');
            $duration = (int)($_POST['duration'] ?? 0);
            $orderIndex = (int)($_POST['order_index'] ?? $maxOrder + 1);
            $isFree = isset($_POST['is_free']) ? 1 : 0;

            $errors = [];

            // Validation
            if (empty($title)) {
                $errors['title'] = 'Lesson title is required';
            } elseif (strlen($title) < 3) {
                $errors['title'] = 'Title must be at least 3 characters';
            }

            if (empty($content) && empty($videoUrl)) {
                $errors['content'] = 'Either content or video URL is required';
            }

            if (!empty($videoUrl) && !filter_var($videoUrl, FILTER_VALIDATE_URL)) {
                $errors['video_url'] = 'Please enter a valid URL';
            }

            if ($duration < 0) {
                $errors['duration'] = 'Duration cannot be negative';
            }

            // Create lesson if no errors
            if (empty($errors)) {
                $lessonData = [
                    'course_id' => $courseId,
                    'title' => $title,
                    'description' => $description ?: null,
                    'content' => $content ?: null,
                    'video_url' => $videoUrl ?: null,
                    'duration' => $duration ?: null,
                    'order_index' => $orderIndex,
                    'is_free' => $isFree
                ];

                $lessonId = $this->courseModel->createLesson($lessonData);

                if ($lessonId) {
                    set_flash('success', 'Lesson created successfully!');
                    $this->redirect('courses/lessons/' . $courseId);
                } else {
                    $errors['general'] = 'Failed to create lesson. Please try again.';
                }
            }

            $data['errors'] = $errors;
            $data['lesson'] = [
                'title' => htmlspecialchars($title),
                'description' => htmlspecialchars($description),
                'content' => htmlspecialchars($content),
                'video_url' => htmlspecialchars($videoUrl),
                'duration' => $duration,
                'order_index' => $orderIndex,
                'is_free' => $isFree
            ];
        }

        $this->render('lessons/create', $data);
    }

    /**
     * Edit lesson
     * 
     * @param int $id Lesson ID
     * @return void
     */
    public function editLesson($id = null) {
        require_login();

        if (!$id) {
            set_flash('error', 'Lesson ID is required.');
            $this->redirect('courses/manage');
        }

        $lessonId = (int)$id;
        $lesson = $this->courseModel->findLesson($lessonId);

        if (!$lesson) {
            set_flash('error', 'Lesson not found.');
            $this->redirect('courses/manage');
        }

        $user = current_user();
        $course = $this->courseModel->findById($lesson['course_id']);

        // Check if user is owner or admin
        if (!$this->courseModel->isOwner($lesson['course_id'], $user['id']) && !is_admin()) {
            set_flash('error', 'You do not have permission to edit this lesson.');
            $this->redirect('courses/manage');
        }

        $data = [
            'title' => 'Edit Lesson',
            'course' => $course,
            'lesson' => $lesson,
            'errors' => []
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $title = trim($_POST['title'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $content = trim($_POST['content'] ?? '');
            $videoUrl = trim($_POST['video_url'] ?? '');
            $duration = (int)($_POST['duration'] ?? 0);
            $orderIndex = (int)($_POST['order_index'] ?? $lesson['order_index']);
            $isFree = isset($_POST['is_free']) ? 1 : 0;

            $errors = [];

            // Validation
            if (empty($title)) {
                $errors['title'] = 'Lesson title is required';
            } elseif (strlen($title) < 3) {
                $errors['title'] = 'Title must be at least 3 characters';
            }

            if (empty($content) && empty($videoUrl)) {
                $errors['content'] = 'Either content or video URL is required';
            }

            if (!empty($videoUrl) && !filter_var($videoUrl, FILTER_VALIDATE_URL)) {
                $errors['video_url'] = 'Please enter a valid URL';
            }

            if ($duration < 0) {
                $errors['duration'] = 'Duration cannot be negative';
            }

            // Update lesson if no errors
            if (empty($errors)) {
                $updateData = [
                    'title' => $title,
                    'description' => $description ?: null,
                    'content' => $content ?: null,
                    'video_url' => $videoUrl ?: null,
                    'duration' => $duration ?: null,
                    'order_index' => $orderIndex,
                    'is_free' => $isFree
                ];

                if ($this->courseModel->updateLesson($lessonId, $updateData)) {
                    set_flash('success', 'Lesson updated successfully!');
                    $this->redirect('courses/lessons/' . $lesson['course_id']);
                } else {
                    $errors['general'] = 'Failed to update lesson. Please try again.';
                }
            }

            $data['errors'] = $errors;
            $data['lesson']['title'] = htmlspecialchars($title);
            $data['lesson']['description'] = htmlspecialchars($description);
            $data['lesson']['content'] = htmlspecialchars($content);
            $data['lesson']['video_url'] = htmlspecialchars($videoUrl);
            $data['lesson']['duration'] = $duration;
            $data['lesson']['order_index'] = $orderIndex;
            $data['lesson']['is_free'] = $isFree;
        }

        $this->render('lessons/edit', $data);
    }

    /**
     * Delete lesson
     * 
     * @param int $id Lesson ID
     * @return void
     */
    public function deleteLesson($id = null) {
        require_login();

        // Only allow POST requests
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            set_flash('error', 'Invalid request method.');
            $this->redirect('courses/manage');
        }

        if (!$id) {
            set_flash('error', 'Lesson ID is required.');
            $this->redirect('courses/manage');
        }

        $lessonId = (int)$id;
        $lesson = $this->courseModel->findLesson($lessonId);

        if (!$lesson) {
            set_flash('error', 'Lesson not found.');
            $this->redirect('courses/manage');
        }

        $user = current_user();

        // Check if user is owner or admin
        if (!$this->courseModel->isOwner($lesson['course_id'], $user['id']) && !is_admin()) {
            set_flash('error', 'You do not have permission to delete this lesson.');
            $this->redirect('courses/manage');
        }

        if ($this->courseModel->deleteLesson($lessonId)) {
            set_flash('success', 'Lesson deleted successfully!');
        } else {
            set_flash('error', 'Failed to delete lesson.');
        }

        $this->redirect('courses/lessons/' . $lesson['course_id']);
    }

    /**
     * Course player
     * 
     * @param int $courseId Course ID
     * @param int $lessonId Optional lesson ID
     * @return void
     */
    public function player($courseId = null, $lessonId = null) {
        require_login();

        if (!$courseId) {
            set_flash('error', 'Course ID is required.');
            $this->redirect('courses');
        }

        $courseId = (int)$courseId;
        $user = current_user();

        // Check if user is enrolled, owner, or admin
        $isEnrolled = $this->courseModel->isEnrolled($user['id'], $courseId);
        $isOwner = $this->courseModel->isOwner($courseId, $user['id']);
        $isAdmin = is_admin();

        if (!$isEnrolled && !$isOwner && !$isAdmin) {
            set_flash('error', 'You must be enrolled in this course to access the player.');
            $this->redirect('courses/show/' . $courseId);
        }

        $course = $this->courseModel->findById($courseId);
        if (!$course) {
            set_flash('error', 'Course not found.');
            $this->redirect('courses');
        }

        $lessons = $this->courseModel->getLessons($courseId);

        if (empty($lessons)) {
            set_flash('info', 'This course has no lessons yet.');
            $this->redirect('courses/show/' . $courseId);
        }

        // Get current lesson
        $currentLesson = null;
        if ($lessonId) {
            $currentLesson = $this->courseModel->findLesson((int)$lessonId);
            // Verify lesson belongs to course
            if (!$currentLesson || $currentLesson['course_id'] != $courseId) {
                $currentLesson = null;
            }
        }

        // If no lesson specified or invalid, use first lesson
        if (!$currentLesson) {
            $currentLesson = $lessons[0];
        }

        // Get next and previous lessons
        $nextLesson = $this->courseModel->getNextLesson($courseId, $currentLesson['order_index']);
        $previousLesson = $this->courseModel->getPreviousLesson($courseId, $currentLesson['order_index']);

        $data = [
            'title' => $currentLesson['title'] . ' - ' . $course['title'],
            'course' => $course,
            'lessons' => $lessons,
            'current_lesson' => $currentLesson,
            'next_lesson' => $nextLesson,
            'previous_lesson' => $previousLesson
        ];

        $this->render('player', $data);
    }
}

