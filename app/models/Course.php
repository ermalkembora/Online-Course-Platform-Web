<?php
/**
 * Course Model
 * 
 * Handles all database operations related to courses.
 */

require_once __DIR__ . '/../../config/database.php';

class Course {
    private $db;

    public function __construct() {
        $this->db = getDB();
    }

    /**
     * Get all published courses with pagination
     * 
     * @param int $page Page number (1-based)
     * @param int $perPage Items per page
     * @return array Courses with pagination info
     */
   public function getPublishedCourses($page = 1, $perPage = 12){
    $offset = ($page - 1) * $perPage;

    // Total count
    $this->db->query("
        SELECT COUNT(*) as total 
        FROM courses 
        WHERE status = 'published'
    ");
    $total = (int)$this->db->single()['total'];

    // Courses WITH RATINGS
    $this->db->query("
        SELECT 
            c.*,
            CONCAT(u.first_name, ' ', u.last_name) AS instructor_name,
            (SELECT COUNT(*) FROM enrollments WHERE course_id = c.id) AS enrollment_count,
            COALESCE(AVG(r.rating), 0) AS rating,
            COUNT(r.id) AS review_count
        FROM courses c
        INNER JOIN users u ON c.instructor_id = u.id
        LEFT JOIN reviews r ON r.course_id = c.id
        WHERE c.status = 'published'
        GROUP BY c.id
        ORDER BY c.created_at DESC
        LIMIT :limit OFFSET :offset
    ")
    ->bind(':limit', $perPage, PDO::PARAM_INT)
    ->bind(':offset', $offset, PDO::PARAM_INT);

    $courses = $this->db->resultSet();

    return [
        'courses' => $courses,
        'total' => $total,
        'page' => $page,
        'per_page' => $perPage,
        'total_pages' => ceil($total / $perPage)
    ];
}


    /**
     * Get course by ID
     * 
     * @param int $id Course ID
     * @return array|false Course data or false if not found
     */
    public function findById($id) {
        $this->db->query("
            SELECT c.*, 
                   CONCAT(u.first_name, ' ', u.last_name) as instructor_name,
                   u.email as instructor_email,
                   (SELECT COUNT(*) FROM enrollments WHERE course_id = c.id) as enrollment_count
            FROM courses c
            INNER JOIN users u ON c.instructor_id = u.id
            WHERE c.id = :id
        ")
        ->bind(':id', $id);

        return $this->db->single();
    }

    /**
     * Get course with lessons
     * 
     * @param int $id Course ID
     * @return array|false Course data with lessons or false
     */
    public function findWithLessons($id) {
        $course = $this->findById($id);
        
        if (!$course) {
            return false;
        }

        // Get lessons
        $this->db->query("
            SELECT * FROM lessons 
            WHERE course_id = :course_id 
            ORDER BY order_index ASC, created_at ASC
        ")
        ->bind(':course_id', $id);

        $course['lessons'] = $this->db->resultSet();

        return $course;
    }

    /**
     * Get enrolled courses for a user
     * 
     * @param int $userId User ID
     * @return array Enrolled courses
     */
    public function getEnrolledCourses($userId) {
        $this->db->query("
            SELECT c.*, 
                   CONCAT(u.first_name, ' ', u.last_name) as instructor_name,
                   e.enrolled_at,
                   e.progress_percentage,
                   e.completed_at
            FROM enrollments e
            INNER JOIN courses c ON e.course_id = c.id
            INNER JOIN users u ON c.instructor_id = u.id
            WHERE e.user_id = :user_id
            ORDER BY e.enrolled_at DESC
        ")
        ->bind(':user_id', $userId);

        return $this->db->resultSet();
    }

    /**
     * Check if user is enrolled in course
     * 
     * @param int $userId User ID
     * @param int $courseId Course ID
     * @return bool True if enrolled
     */
    public function isEnrolled($userId, $courseId) {
        $this->db->query("
            SELECT id FROM enrollments 
            WHERE user_id = :user_id AND course_id = :course_id
        ")
        ->bind(':user_id', $userId)
        ->bind(':course_id', $courseId);

        return $this->db->single() !== false;
    }

    /**
     * Search courses
     * 
     * @param string $searchTerm Search term
     * @param int $page Page number
     * @param int $perPage Items per page
     * @return array Courses with pagination info
     */
    public function searchCourses($searchTerm, $page = 1, $perPage = 12) {
        $offset = ($page - 1) * $perPage;
        $searchPattern = "%{$searchTerm}%";

        // BUG FIX: The :search placeholder was used twice in the SQL (once for title, once for description)
        // but was only bound once, causing SQLSTATE[HY093]: Invalid parameter number error.
        // Solution: Use two different placeholder names (:keyword1 and :keyword2) and bind both with the same value.
        // This ensures all placeholders in the SQL have matching bind() calls.

        // Get total count
        $this->db->query("
            SELECT COUNT(*) as total 
            FROM courses c
            INNER JOIN users u ON c.instructor_id = u.id
            WHERE c.status = 'published'
            AND (c.title LIKE :keyword1 OR c.description LIKE :keyword2)
        ")
        ->bind(':keyword1', $searchPattern)
        ->bind(':keyword2', $searchPattern);
        $totalResult = $this->db->single();
        $total = (int)$totalResult['total'];

        // Get courses
        $this->db->query("
            SELECT c.*, 
                   CONCAT(u.first_name, ' ', u.last_name) as instructor_name,
                   (SELECT COUNT(*) FROM enrollments WHERE course_id = c.id) as enrollment_count
            FROM courses c
            INNER JOIN users u ON c.instructor_id = u.id
            WHERE c.status = 'published'
            AND (c.title LIKE :keyword1 OR c.description LIKE :keyword2)
            ORDER BY c.created_at DESC
            LIMIT :limit OFFSET :offset
        ")
        ->bind(':keyword1', $searchPattern)
        ->bind(':keyword2', $searchPattern)
        ->bind(':limit', $perPage, PDO::PARAM_INT)
        ->bind(':offset', $offset, PDO::PARAM_INT);

        $courses = $this->db->resultSet();

        return [
            'courses' => $courses,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => ceil($total / $perPage),
            'search_term' => $searchTerm
        ];
    }

    /**
     * Create a new course
     * 
     * @param array $data Course data
     * @return int|false Course ID on success, false on failure
     */
    public function create($data) {
        try {
            $this->db->query("
                INSERT INTO courses (instructor_id, title, description, price, thumbnail, status) 
                VALUES (:instructor_id, :title, :description, :price, :thumbnail, :status)
            ")
            ->bind(':instructor_id', $data['instructor_id'])
            ->bind(':title', $data['title'])
            ->bind(':description', $data['description'])
            ->bind(':price', $data['price'])
            ->bind(':thumbnail', $data['thumbnail'] ?? null)
            ->bind(':status', $data['status'] ?? 'draft');

            if ($this->db->execute()) {
                return $this->db->lastInsertId();
            }

            return false;
        } catch (Exception $e) {
            error_log("Course creation error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update course
     * 
     * @param int $id Course ID
     * @param array $data Course data to update
     * @return bool Success status
     */
    public function update($id, $data) {
        $fields = [];
        $allowedFields = ['title', 'description', 'price', 'thumbnail', 'status'];

        foreach ($data as $key => $value) {
            if (in_array($key, $allowedFields)) {
                $fields[] = "{$key} = :{$key}";
            }
        }

        if (empty($fields)) {
            return false;
        }

        $fields[] = "updated_at = NOW()";
        $sql = "UPDATE courses SET " . implode(', ', $fields) . " WHERE id = :id";

        $this->db->query($sql)->bind(':id', $id);

        foreach ($data as $key => $value) {
            if (in_array($key, $allowedFields)) {
                $this->db->bind(":{$key}", $value);
            }
        }

        return $this->db->execute();
    }

    /**
     * Get courses by instructor
     * 
     * @param int $instructorId Instructor ID
     * @return array Courses
     */
    public function getInstructorCourses($instructorId) {
        $this->db->query("
            SELECT c.*, 
                   (SELECT COUNT(*) FROM enrollments WHERE course_id = c.id) as enrollment_count,
                   (SELECT COUNT(*) FROM lessons WHERE course_id = c.id) as lesson_count
            FROM courses c
            WHERE c.instructor_id = :instructor_id
            ORDER BY c.created_at DESC
        ")
        ->bind(':instructor_id', $instructorId);

        return $this->db->resultSet();
    }

    /**
     * Check if user is course owner
     * 
     * @param int $courseId Course ID
     * @param int $userId User ID
     * @return bool True if user is owner
     */
    public function isOwner($courseId, $userId) {
        $this->db->query("
            SELECT id FROM courses 
            WHERE id = :course_id AND instructor_id = :user_id
        ")
        ->bind(':course_id', $courseId)
        ->bind(':user_id', $userId);

        return $this->db->single() !== false;
    }

    /**
     * Delete course
     * 
     * @param int $id Course ID
     * @return bool Success status
     */
    public function delete($id) {
        $this->db->query("DELETE FROM courses WHERE id = :id")
                 ->bind(':id', $id);
        
        return $this->db->execute();
    }

    /**
     * Get lessons for a course
     * 
     * @param int $courseId Course ID
     * @return array Lessons
     */
    public function getLessons($courseId) {
        $this->db->query("
            SELECT * FROM lessons 
            WHERE course_id = :course_id 
            ORDER BY order_index ASC, created_at ASC
        ")
        ->bind(':course_id', $courseId);

        return $this->db->resultSet();
    }

    /**
     * Find lesson by ID
     * 
     * @param int $id Lesson ID
     * @return array|false Lesson data or false
     */
    public function findLesson($id) {
        $this->db->query("SELECT * FROM lessons WHERE id = :id")
                 ->bind(':id', $id);

        return $this->db->single();
    }

    /**
     * Create a new lesson
     * 
     * @param array $data Lesson data
     * @return int|false Lesson ID on success, false on failure
     */
    public function createLesson($data) {
        try {
            $this->db->query("
                INSERT INTO lessons (course_id, title, description, content, video_url, duration, order_index, is_free) 
                VALUES (:course_id, :title, :description, :content, :video_url, :duration, :order_index, :is_free)
            ")
            ->bind(':course_id', $data['course_id'])
            ->bind(':title', $data['title'])
            ->bind(':description', $data['description'] ?? null)
            ->bind(':content', $data['content'] ?? null)
            ->bind(':video_url', $data['video_url'] ?? null)
            ->bind(':duration', $data['duration'] ?? null)
            ->bind(':order_index', $data['order_index'] ?? 0)
            ->bind(':is_free', $data['is_free'] ?? 0);

            if ($this->db->execute()) {
                return $this->db->lastInsertId();
            }

            return false;
        } catch (Exception $e) {
            error_log("Lesson creation error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update lesson
     * 
     * @param int $id Lesson ID
     * @param array $data Lesson data to update
     * @return bool Success status
     */
    public function updateLesson($id, $data) {
        $fields = [];
        $allowedFields = ['title', 'description', 'content', 'video_url', 'duration', 'order_index', 'is_free'];

        foreach ($data as $key => $value) {
            if (in_array($key, $allowedFields)) {
                $fields[] = "{$key} = :{$key}";
            }
        }

        if (empty($fields)) {
            return false;
        }

        $fields[] = "updated_at = NOW()";
        $sql = "UPDATE lessons SET " . implode(', ', $fields) . " WHERE id = :id";

        $this->db->query($sql)->bind(':id', $id);

        foreach ($data as $key => $value) {
            if (in_array($key, $allowedFields)) {
                $this->db->bind(":{$key}", $value);
            }
        }

        return $this->db->execute();
    }

    /**
     * Delete lesson
     * 
     * @param int $id Lesson ID
     * @return bool Success status
     */
    public function deleteLesson($id) {
        $this->db->query("DELETE FROM lessons WHERE id = :id")
                 ->bind(':id', $id);
        
        return $this->db->execute();
    }

    /**
     * Get next lesson in course
     * 
     * @param int $courseId Course ID
     * @param int $currentOrderIndex Current lesson order index
     * @return array|false Next lesson or false
     */
    public function getNextLesson($courseId, $currentOrderIndex) {
        $this->db->query("
            SELECT * FROM lessons 
            WHERE course_id = :course_id 
            AND order_index > :order_index
            ORDER BY order_index ASC
            LIMIT 1
        ")
        ->bind(':course_id', $courseId)
        ->bind(':order_index', $currentOrderIndex);

        return $this->db->single();
    }

    /**
     * Get previous lesson in course
     * 
     * @param int $courseId Course ID
     * @param int $currentOrderIndex Current lesson order index
     * @return array|false Previous lesson or false
     */
    public function getPreviousLesson($courseId, $currentOrderIndex) {
        $this->db->query("
            SELECT * FROM lessons 
            WHERE course_id = :course_id 
            AND order_index < :order_index
            ORDER BY order_index DESC
            LIMIT 1
        ")
        ->bind(':course_id', $courseId)
        ->bind(':order_index', $currentOrderIndex);

        return $this->db->single();
    }
}

