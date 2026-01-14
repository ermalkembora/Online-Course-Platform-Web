<?php

class Review
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function addReview($data)
    {
        // Insert or update review
        $this->db->query("
            INSERT INTO reviews (user_id, course_id, rating, comment)
            VALUES (:user_id, :course_id, :rating, :comment)
            ON DUPLICATE KEY UPDATE
                rating = :update_rating,
                comment = :update_comment
        ");

        $this->db->bind(':user_id', $data['user_id']);
        $this->db->bind(':course_id', $data['course_id']);
        $this->db->bind(':rating', $data['rating']);
        $this->db->bind(':comment', $data['comment']);
        $this->db->bind(':update_rating', $data['rating']);
        $this->db->bind(':update_comment', $data['comment']);

        $this->db->execute();

        // Recalculate course rating (UNIQUE placeholders!)
        $this->db->query("
            UPDATE courses
            SET
                rating = (
                    SELECT ROUND(AVG(rating), 2)
                    FROM reviews
                    WHERE course_id = :cid_avg
                ),
                review_count = (
                    SELECT COUNT(*)
                    FROM reviews
                    WHERE course_id = :cid_count
                )
            WHERE id = :cid_update
        ");

        $this->db->bind(':cid_avg', $data['course_id']);
        $this->db->bind(':cid_count', $data['course_id']);
        $this->db->bind(':cid_update', $data['course_id']);

        return $this->db->execute();
    }
   public function getByCourse($courseId)
{
    $this->db->query("
        SELECT 
            r.rating,
            r.comment,
            r.created_at,
            CONCAT(u.first_name, ' ', u.last_name) AS reviewer_name
        FROM reviews r
        JOIN users u ON u.id = r.user_id
        WHERE r.course_id = :course_id
        ORDER BY r.created_at DESC
    ");

    $this->db->bind(':course_id', $courseId);

    return $this->db->resultSet();
}



}
