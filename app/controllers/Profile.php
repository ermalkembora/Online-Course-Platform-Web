<?php
/**
 * Profile Controller
 * 
 * Handles user profile viewing and editing.
 */

class Profile extends Controller {
    private $userModel;

    public function __construct() {
        $this->userModel = $this->model('User');
    }

    /**
     * View own profile
     * 
     * @return void
     */
   public function index() {
    require_login();

    $user = current_user();
    if (!$user) {
        set_flash('error', 'User not found.');
        $this->redirect('');
    }

    $data = [
        'title' => 'My Profile',
        'user' => $user,
        'isOwnProfile' => true,   // ✅ THIS IS THE KEY FIX
        'isAdmin' => false        // optional but safe
    ];

    $this->render('index', $data);
}


    /**
     * Edit own profile
     * 
     * @return void
     */
    public function edit() {
        require_login();

        $user = current_user();
        if (!$user) {
            set_flash('error', 'User not found.');
            $this->redirect('');
        }

        $data = [
            'title' => 'Edit Profile',
            'user' => $user,
            'errors' => []
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $first_name = trim($_POST['first_name'] ?? '');
            $last_name = trim($_POST['last_name'] ?? '');
            $password = $_POST['password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';
            $errors = [];

            // Validate first name
            if (empty($first_name)) {
                $errors['first_name'] = 'First name is required';
            } elseif (strlen($first_name) < 2) {
                $errors['first_name'] = 'First name must be at least 2 characters';
            } elseif (strlen($first_name) > 50) {
                $errors['first_name'] = 'First name must be less than 50 characters';
            }

            // Validate last name
            if (empty($last_name)) {
                $errors['last_name'] = 'Last name is required';
            } elseif (strlen($last_name) < 2) {
                $errors['last_name'] = 'Last name must be at least 2 characters';
            } elseif (strlen($last_name) > 50) {
                $errors['last_name'] = 'Last name must be less than 50 characters';
            }

            // Validate password if provided
            if (!empty($password)) {
                if (strlen($password) < 8) {
                    $errors['password'] = 'Password must be at least 8 characters';
                } elseif (!preg_match('/[A-Z]/', $password)) {
                    $errors['password'] = 'Password must contain at least one uppercase letter';
                } elseif (!preg_match('/[a-z]/', $password)) {
                    $errors['password'] = 'Password must contain at least one lowercase letter';
                } elseif (!preg_match('/[0-9]/', $password)) {
                    $errors['password'] = 'Password must contain at least one number';
                }

                if ($password !== $confirm_password) {
                    $errors['confirm_password'] = 'Passwords do not match';
                }
            }

            // Handle profile picture upload
            $profilePicture = $user['profile_picture'];
            if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
                $uploadResult = $this->uploadProfilePicture($_FILES['profile_picture'], $user['id']);
                
                if ($uploadResult['success']) {
                    // Delete old profile picture if exists
                    if ($profilePicture) {
                        $oldPath = UPLOAD_DIR . 'profiles/' . $profilePicture;
                        if (file_exists($oldPath)) {
                            unlink($oldPath);
                        }
                    }
                    $profilePicture = $uploadResult['filename'];
                } else {
                    $errors['profile_picture'] = $uploadResult['message'];
                }
            }

            // Update user if no errors
            if (empty($errors)) {
                $updateData = [
                    'first_name' => $first_name,
                    'last_name' => $last_name,
                    'profile_picture' => $profilePicture
                ];

                // Update password if provided
                if (!empty($password)) {
                    $updateData['password'] = password_hash($password, PASSWORD_DEFAULT);
                }

                if ($this->userModel->update($user['id'], $updateData)) {
                    set_flash('success', 'Profile updated successfully!');
                    $this->redirect('profile');
                } else {
                    $errors['general'] = 'Failed to update profile. Please try again.';
                }
            }

            $data['errors'] = $errors;
            $data['user']['first_name'] = htmlspecialchars($first_name);
            $data['user']['last_name'] = htmlspecialchars($last_name);
        }

        $this->render('edit', $data);
    }

    /**
     * Upload profile picture
     * 
     * @param array $file Uploaded file array
     * @param int $userId User ID
     * @return array Result with success status and filename/message
     */
    private function uploadProfilePicture($file, $userId) {
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
        $filename = $userId . '_' . time() . '_' . uniqid() . '.' . $extension;
        $uploadPath = UPLOAD_DIR . 'profiles/' . $filename;

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
}

