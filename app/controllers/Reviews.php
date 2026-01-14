<?php

class Reviews extends Controller
{
    public function add()
    {
        // Must be logged in
        if (!is_logged_in()) {
            redirect('users/login');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'user_id'   => $_SESSION['user_id'],
                'course_id' => $_POST['course_id'],
                'rating'    => $_POST['rating'],
                'comment'   => $_POST['comment'] ?? null
            ];

            $reviewModel = $this->model('Review');

            $reviewModel->addReview($data);

            redirect('courses/show/' . $data['course_id']);
        }
    }
}
