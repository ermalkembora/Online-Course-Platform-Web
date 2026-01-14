<?php
/**
 * Admin Controller
 * 
 * Handles admin-only operations like user management.
 */

class Admin extends Controller {
    private $userModel;

    public function __construct() {
        require_admin(); // Protect all admin routes
        $this->userModel = $this->model('User');
    }

    /**
     * List all users
     * 
     * @return void
     */
    public function users_index() {
        $users = $this->userModel->getAllUsers();

        $data = [
            'title' => 'User Management',
            'users' => $users
        ];

        $this->render('users_index', $data);
    }

    /**
     * Edit user (admin)
     * 
     * @param int $id User ID
     * @return void
     */
    public function user_edit($id = null) {
        if (!$id) {
            set_flash('error', 'User ID is required.');
            $this->redirect('admin/users_index');
        }

        $userId = (int)$id;
        $user = $this->userModel->findById($userId);

        if (!$user) {
            set_flash('error', 'User not found.');
            $this->redirect('admin/users_index');
        }

        // Prevent admin from editing themselves (optional - remove if you want to allow)
        $currentUser = current_user();
        if ($currentUser && $currentUser['id'] == $userId) {
            set_flash('info', 'To edit your own profile, please use the Profile page.');
            $this->redirect('profile');
        }

        // Get all roles
        $roles = $this->userModel->getAllRoles();
        $userRoles = $this->userModel->getRoles($userId);

        $data = [
            'title' => 'Edit User',
            'user' => $user,
            'roles' => $roles,
            'userRoles' => $userRoles,
            'errors' => []
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';

            if ($action === 'update') {
                // Update user details
                $first_name = trim($_POST['first_name'] ?? '');
                $last_name = trim($_POST['last_name'] ?? '');
                $email = trim($_POST['email'] ?? '');
                $errors = [];

                // Validation
                if (empty($first_name) || strlen($first_name) < 2) {
                    $errors['first_name'] = 'First name must be at least 2 characters';
                }

                if (empty($last_name) || strlen($last_name) < 2) {
                    $errors['last_name'] = 'Last name must be at least 2 characters';
                }

                if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $errors['email'] = 'Valid email is required';
                } elseif ($email !== $user['email'] && $this->userModel->emailExists($email)) {
                    $errors['email'] = 'Email is already taken';
                }

                if (empty($errors)) {
                    $updateData = [
                        'first_name' => $first_name,
                        'last_name' => $last_name,
                        'email' => $email
                    ];

                    if ($this->userModel->update($userId, $updateData)) {
                        set_flash('success', 'User updated successfully!');
                        $this->redirect('admin/users_index');
                    } else {
                        $errors['general'] = 'Failed to update user.';
                    }
                }

                $data['errors'] = $errors;
                $data['user']['first_name'] = htmlspecialchars($first_name);
                $data['user']['last_name'] = htmlspecialchars($last_name);
                $data['user']['email'] = htmlspecialchars($email);

            } elseif ($action === 'update_role') {
                // Update user role
                $roleId = (int)($_POST['role_id'] ?? 0);

                if ($this->userModel->updateUserRole($userId, $roleId)) {
                    set_flash('success', 'User role updated successfully!');
                    $this->redirect('admin/user_edit/' . $userId);
                } else {
                    $data['errors']['general'] = 'Failed to update user role.';
                }

            } elseif ($action === 'delete') {
                // Delete user
                if ($this->userModel->delete($userId)) {
                    set_flash('success', 'User deleted successfully!');
                    $this->redirect('admin/users_index');
                } else {
                    $data['errors']['general'] = 'Failed to delete user.';
                }
            }
        }

        $this->render('user_edit', $data);
    }

    /**
     * View login attempts logs
     * 
     * @return void
     */
    public function loginLogs() {
        $page = (int)($_GET['page'] ?? 1);
        $perPage = 50;

        if ($page < 1) {
            $page = 1;
        }

        $offset = ($page - 1) * $perPage;

        // Get total count
        require_once __DIR__ . '/../../config/database.php';
        $db = getDB();
        $db->query("SELECT COUNT(*) as total FROM login_attempts");
        $totalResult = $db->single();
        $total = (int)$totalResult['total'];

        // Get login attempts
        $db->query("
            SELECT la.*, 
                   CONCAT(u.first_name, ' ', u.last_name) as user_name,
                   u.email as user_email
            FROM login_attempts la
            LEFT JOIN users u ON la.user_id = u.id
            ORDER BY la.attempted_at DESC
            LIMIT :limit OFFSET :offset
        ")
        ->bind(':limit', $perPage, PDO::PARAM_INT)
        ->bind(':offset', $offset, PDO::PARAM_INT);

        $logs = $db->resultSet();

        $data = [
            'title' => 'Login Attempts Logs',
            'logs' => $logs,
            'pagination' => [
                'current_page' => $page,
                'total_pages' => ceil($total / $perPage),
                'total' => $total,
                'per_page' => $perPage
            ]
        ];

        $this->render('login_logs', $data);
    }

    /**
     * View payment logs
     * 
     * @return void
     */
    public function paymentLogs() {
        $page = (int)($_GET['page'] ?? 1);
        $perPage = 50;

        if ($page < 1) {
            $page = 1;
        }

        $offset = ($page - 1) * $perPage;

        // Get total count
        require_once __DIR__ . '/../../config/database.php';
        $db = getDB();
        $db->query("SELECT COUNT(*) as total FROM payments");
        $totalResult = $db->single();
        $total = (int)$totalResult['total'];

        // Get payments
        $db->query("
            SELECT p.*, 
                   CONCAT(u.first_name, ' ', u.last_name) as user_name,
                   u.email as user_email,
                   c.title as course_title
            FROM payments p
            INNER JOIN users u ON p.user_id = u.id
            INNER JOIN courses c ON p.course_id = c.id
            ORDER BY p.created_at DESC
            LIMIT :limit OFFSET :offset
        ")
        ->bind(':limit', $perPage, PDO::PARAM_INT)
        ->bind(':offset', $offset, PDO::PARAM_INT);

        $logs = $db->resultSet();

        $data = [
            'title' => 'Payment Logs',
            'logs' => $logs,
            'pagination' => [
                'current_page' => $page,
                'total_pages' => ceil($total / $perPage),
                'total' => $total,
                'per_page' => $perPage
            ]
        ];

        $this->render('payment_logs', $data);
    }

    /**
     * View third-party logs
     * 
     * @return void
     */
    public function thirdPartyLogs() {
        $page = (int)($_GET['page'] ?? 1);
        $perPage = 50;

        if ($page < 1) {
            $page = 1;
        }

        $offset = ($page - 1) * $perPage;

        // Get total count
        require_once __DIR__ . '/../../config/database.php';
        $db = getDB();
        $db->query("SELECT COUNT(*) as total FROM third_party_logs");
        $totalResult = $db->single();
        $total = (int)$totalResult['total'];

        // Get third-party logs
        $db->query("
            SELECT tpl.*, 
                   CONCAT(u.first_name, ' ', u.last_name) as user_name,
                   u.email as user_email,
                   c.title as course_title
            FROM third_party_logs tpl
            LEFT JOIN users u ON tpl.user_id = u.id
            LEFT JOIN courses c ON tpl.course_id = c.id
            ORDER BY tpl.created_at DESC
            LIMIT :limit OFFSET :offset
        ")
        ->bind(':limit', $perPage, PDO::PARAM_INT)
        ->bind(':offset', $offset, PDO::PARAM_INT);

        $logs = $db->resultSet();

        // Format log messages for display
        foreach ($logs as &$log) {
            $log['short_message'] = $this->getShortMessage($log);
        }

        $data = [
            'title' => 'Third-Party API Logs',
            'logs' => $logs,
            'pagination' => [
                'current_page' => $page,
                'total_pages' => ceil($total / $perPage),
                'total' => $total,
                'per_page' => $perPage
            ]
        ];

        $this->render('third_party_logs', $data);
    }

    /**
     * Get short message from log entry
     * 
     * @param array $log Log entry
     * @return string Short message
     */
    private function getShortMessage($log) {
        $requestType = $log['request_type'] ?? '';
        $status = $log['status'] ?? '';
        $errorMessage = $log['error_message'] ?? '';

        if ($errorMessage) {
            return $errorMessage;
        }

        $message = ucfirst(str_replace('_', ' ', $requestType));
        if ($status) {
            $message .= ' - ' . ucfirst($status);
        }

        return $message;
    }
}

