<?php
/**
 * Users Controller
 * 
 * Handles user-related actions: registration, email verification, login, etc.
 */

class Users extends Controller {
    private $userModel;

    public function __construct() {
        $this->userModel = $this->model('User');
    }

    /**
     * Default index method - redirect to register
     * 
     * @return void
     */
    public function index() {
        $this->redirect('users/register');
    }

    /**
     * User registration
     * 
     * @return void
     */
    public function register() {
        // Redirect if already logged in
        if (is_logged_in()) {
            $this->redirect('');
        }

        $data = [
            'title' => 'Register',
            'full_name' => '',
            'email' => '',
            'password' => '',
            'confirm_password' => '',
            'errors' => []
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Sanitize input
            $full_name = trim($_POST['full_name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';

            // Validation
            $errors = [];

            // Full name validation
            if (empty($full_name)) {
                $errors['full_name'] = 'Full name is required';
            } elseif (strlen($full_name) < 2) {
                $errors['full_name'] = 'Full name must be at least 2 characters';
            } elseif (strlen($full_name) > 100) {
                $errors['full_name'] = 'Full name must be less than 100 characters';
            } else {
                // Split full name into first and last name
                $nameParts = explode(' ', $full_name, 2);
                $first_name = $nameParts[0];
                $last_name = isset($nameParts[1]) ? $nameParts[1] : '';
                
                if (empty($last_name)) {
                    $errors['full_name'] = 'Please provide both first and last name';
                }
            }

            // Email validation
            if (empty($email)) {
                $errors['email'] = 'Email is required';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = 'Please enter a valid email address';
            } elseif ($this->userModel->emailExists($email)) {
                $errors['email'] = 'Email is already registered';
            }

            // Password validation
            if (empty($password)) {
                $errors['password'] = 'Password is required';
            } elseif (strlen($password) < 8) {
                $errors['password'] = 'Password must be at least 8 characters';
            } elseif (!preg_match('/[A-Z]/', $password)) {
                $errors['password'] = 'Password must contain at least one uppercase letter';
            } elseif (!preg_match('/[a-z]/', $password)) {
                $errors['password'] = 'Password must contain at least one lowercase letter';
            } elseif (!preg_match('/[0-9]/', $password)) {
                $errors['password'] = 'Password must contain at least one number';
            }

            // Confirm password validation
            if (empty($confirm_password)) {
                $errors['confirm_password'] = 'Please confirm your password';
            } elseif ($password !== $confirm_password) {
                $errors['confirm_password'] = 'Passwords do not match';
            }

            // If no errors, create user
            if (empty($errors)) {
                // Hash password
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

                // Create user
                $newUserId = $this->userModel->create([
                    'email' => $email,
                    'password' => $hashedPassword,
                    'first_name' => $first_name,
                    'last_name' => $last_name
                ]);

                if ($newUserId) {
                    // Generate 6-digit verification code
                    $code = random_int(100000, 999999);
                    // Ensure it's a string and exactly 6 digits
                    $code = (string) $code;

                    // Set expiry time using DATE_ADD in the database to avoid timezone issues
                    // We'll use a special marker and let the Model handle it
                    $expiresAt = 'DATE_ADD(NOW(), INTERVAL 30 MINUTE)';

                    // Insert verification code into database
                    $created = $this->userModel->createVerificationCode($newUserId, $code, $expiresAt);

                    // Send verification email
                    require_once __DIR__ . '/../helpers/mail_helper.php';
                    $userName = $first_name . ' ' . $last_name;
                    send_verification_email($email, $code, $userName);

                    // Store pending user id in session
                    $_SESSION['pending_verification_user_id'] = $newUserId;

                    // Set success message
                    set_flash('success', 'Registration successful! Please check your email for the verification code.');

                    // Redirect to verify email page
                    $this->redirect('users/verifyEmail');
                    return;
                } else {
                    $errors['general'] = 'Registration failed. Please try again.';
                }
            }

            // Store errors and form data
            $data['errors'] = $errors;
            $data['full_name'] = htmlspecialchars($full_name);
            $data['email'] = htmlspecialchars($email);
        }

        $this->render('register', $data);
    }

    /**
     * Email verification page
     * Handles both GET (show form) and POST (process code)
     * 
     * @return void
     */
    public function verifyEmail() {
        // Handle POST request (code submission)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Read and trim the code from POST
            $inputCode = isset($_POST['code']) ? trim($_POST['code']) : '';
            
            // Clean the code - remove spaces and non-numeric characters
            $inputCode = preg_replace('/[^0-9]/', '', $inputCode);

            // Get user id from session
            if (empty($_SESSION['pending_verification_user_id'])) {
                set_flash('error', 'No verification is currently pending.');
                $this->redirect('users/login');
                return;
            }

            $userId = (int) $_SESSION['pending_verification_user_id'];

            // Validate code format
            if (empty($inputCode)) {
                $user = $this->userModel->findById($userId);
                $email = $user ? $user['email'] : '';
                
                $this->render('verify_email', [
                    'title' => 'Verify Email',
                    'email' => $email,
                    'error' => 'Verification code is required.',
                    'userId' => $userId
                ]);
                return;
            }

            if (strlen($inputCode) !== 6) {
                $user = $this->userModel->findById($userId);
                $email = $user ? $user['email'] : '';
                
                $this->render('verify_email', [
                    'title' => 'Verify Email',
                    'email' => $email,
                    'error' => 'Verification code must be 6 digits.',
                    'userId' => $userId
                ]);
                return;
            }

            // Find valid verification code
            $verificationRow = $this->userModel->findValidVerificationCode($userId, $inputCode);

            // If code is invalid or expired
            if (!$verificationRow) {
                $user = $this->userModel->findById($userId);
                $email = $user ? $user['email'] : '';
                
                $this->render('verify_email', [
                    'title' => 'Verify Email',
                    'email' => $email,
                    'error' => 'Invalid or expired verification code.',
                    'userId' => $userId
                ]);
                return;
            }

            // Code is valid - mark as used and verify user
            $this->userModel->markVerificationCodeUsed($verificationRow['id']);
            $this->userModel->markEmailVerified($userId);

            // Get the updated user row
            $user = $this->userModel->getUserById($userId);

            if ($user) {
                // Auto-login the user
                require_once __DIR__ . '/../helpers/auth_helper.php';
                login_user($user, false);

                // Unset pending verification session
                unset($_SESSION['pending_verification_user_id']);

                // Set success message
                set_flash('success', 'Email verified successfully. You are now logged in.');

                // Redirect to home page
                $this->redirect('');
                return;
            } else {
                set_flash('error', 'Email verified, but user not found. Please log in.');
                $this->redirect('users/login');
                return;
            }
        }

        // Handle GET request (show form)
        // Check if pending verification exists
        if (empty($_SESSION['pending_verification_user_id'])) {
            set_flash('error', 'No verification is currently pending.');
            $this->redirect('users/login');
            return;
        }

        $userId = (int) $_SESSION['pending_verification_user_id'];
        $user = $this->userModel->findById($userId);
        $email = $user ? $user['email'] : '';

        $data = [
            'title' => 'Verify Email',
            'email' => $email,
            'error' => '',
            'userId' => $userId
        ];

        $this->render('verify_email', $data);
    }

    /**
     * Resend verification code
     * Generates a new verification code and sends it to the user's email
     * 
     * @return void
     */
    public function resendVerificationCode() {
        // Get user id from session
        if (empty($_SESSION['pending_verification_user_id'])) {
            set_flash('error', 'No verification is currently pending.');
            $this->redirect('users/login');
            return;
        }

        $userId = (int) $_SESSION['pending_verification_user_id'];
        $user = $this->userModel->findById($userId);

        if (!$user) {
            set_flash('error', 'User not found.');
            $this->redirect('users/login');
            return;
        }

        // Generate new 6-digit verification code
        $code = random_int(100000, 999999);
        $code = (string) $code;

        // Set expiry time using DATE_ADD in the database to avoid timezone issues
        $expiresAt = 'DATE_ADD(NOW(), INTERVAL 30 MINUTE)';

        // Insert verification code into database
        $created = $this->userModel->createVerificationCode($userId, $code, $expiresAt);

        // Send verification email
        require_once __DIR__ . '/../helpers/mail_helper.php';
        $userName = $user['first_name'] . ' ' . $user['last_name'];
        send_verification_email($user['email'], $code, $userName);

        // Set success message
        set_flash('success', 'A new verification code has been sent to your email.');

        // Redirect back to verify email page
        $this->redirect('users/verifyEmail');
    }

    /**
     * Login page
     * Handles both GET (show form) and POST (process login)
     * 
     * @return void
     */
    public function login() {
        // Redirect if already logged in
        if (is_logged_in()) {
            $this->redirect('');
        }

        $data = [
            'title' => 'Login',
            'email' => '',
            'password' => '',
            'remember_me' => false,
            'errors' => []
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $rememberMe = isset($_POST['remember_me']);

            // Get client info
            require_once __DIR__ . '/../helpers/auth_helper.php';
            $ipAddress = get_client_ip();
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;

            // Validation
            $errors = [];

            if (empty($email)) {
                $errors['email'] = 'Email is required';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = 'Please enter a valid email address';
            }

            if (empty($password)) {
                $errors['password'] = 'Password is required';
            }

            // Check lockout status
            if (empty($errors)) {
                $lockoutStatus = $this->userModel->isLockedOut($email, $ipAddress);
                
                if ($lockoutStatus) {
                    $minutes = $lockoutStatus['minutes_remaining'];
                    $errors['general'] = "Too many failed attempts. Please try again in {$minutes} minute(s).";
                    
                    // Log the attempt (even though it's blocked)
                    $this->userModel->logLoginAttempt(null, $email, $ipAddress, $userAgent, false);
                }
            }

            // Attempt login if no errors
            if (empty($errors)) {
                // Find user by email
                $user = $this->userModel->findByEmail($email);

                if ($user && password_verify($password, $user['password'])) {
                    // Password correct - check email verification
                    if (!$user['email_verified'] || $user['email_verified'] == 0) {
                        // Email not verified - redirect to verification page
                        $_SESSION['pending_verification_user_id'] = $user['id'];
                        set_flash('error', 'Please verify your email to continue.');
                        $this->redirect('users/verifyEmail');
                        return;
                    }

                    // Email is verified - proceed with login
                    // Log successful attempt
                    $this->userModel->logLoginAttempt($user['id'], $email, $ipAddress, $userAgent, true);

                    // Clear previous FAILED attempts for this email/IP
                    $this->userModel->clearLoginAttempts($email, $ipAddress);

                    // Create session
                    login_user($user, $rememberMe);

                    // Handle remember-me
                    if ($rememberMe) {
                        // Generate secure token
                        $token = bin2hex(random_bytes(32));
                        
                        // Store token in database
                        if ($this->userModel->createRememberToken($user['id'], $token, $ipAddress, $userAgent)) {
                            // Set cookie (30 days)
                            $cookieLifetime = time() + (defined('REMEMBER_ME_LIFETIME') ? REMEMBER_ME_LIFETIME : 2592000);
                            setcookie('remember_token', $token, $cookieLifetime, '/', '', false, true);
                            setcookie('remember_user_id', $user['id'], $cookieLifetime, '/', '', false, true);
                        }
                    }

                    // Redirect to intended page, or based on role
                    $redirectUrl = session_get('redirect_after_login', '');
                    if ($redirectUrl) {
                        // User was trying to access a protected page before logging in
                        session_unset_key('redirect_after_login');
                        set_flash('success', 'Welcome back, ' . htmlspecialchars($user['first_name']) . '!');
                        $this->redirect($redirectUrl);
                        return;
                    }

                    // No specific target → choose based on role
                    // Check if user has admin role using User model (more reliable right after login)
                    $userRoles = $this->userModel->getRoles($user['id']);
                    $isAdmin = in_array('admin', $userRoles);

                    if ($isAdmin) {
                        // ADMIN: go to the list of registered users
                        set_flash('success', 'Welcome back, ' . htmlspecialchars($user['first_name']) . '!');
                        $this->redirect('admin/users_index');
                    } else {
                        // NORMAL USER: go to their own profile page
                        set_flash('success', 'Welcome back, ' . htmlspecialchars($user['first_name']) . '!');
                        $this->redirect('profile');
                    }
                    return;
                } else {
                    // Invalid credentials
                    $userId = $user ? $user['id'] : null;
                    $this->userModel->logLoginAttempt($userId, $email, $ipAddress, $userAgent, false);
                    
                    // Don't reveal if email exists or not (security best practice)
                    $errors['general'] = 'Invalid email or password';
                }
            }

            // Store errors and form data
            $data['errors'] = $errors;
            $data['email'] = htmlspecialchars($email);
            $data['remember_me'] = $rememberMe;
        }

        $this->render('login', $data);
    }

    /**
     * Logout
     * 
     * @return void
     */
    public function logout() {
        require_once __DIR__ . '/../helpers/auth_helper.php';
        
        $user = current_user();
        
        if ($user) {
            // Delete remember-me tokens
            if (isset($_COOKIE['remember_token'])) {
                $this->userModel->deleteRememberToken($_COOKIE['remember_token']);
                setcookie('remember_token', '', time() - 3600, '/', '', false, true);
            }
            
            if (isset($_COOKIE['remember_user_id'])) {
                setcookie('remember_user_id', '', time() - 3600, '/', '', false, true);
            }
        }

        // Logout user (destroys session)
        logout_user();

        set_flash('success', 'You have been logged out successfully.');
        $this->redirect('');
    }

    /**
     * Forgot password page
     * Handles both GET (show form) and POST (process email)
     * 
     * @return void
     */
    public function forgotPassword() {
        // Redirect if already logged in
        if (is_logged_in()) {
            $this->redirect('');
        }

        $data = [
            'title' => 'Forgot Password',
            'email' => '',
            'errors' => []
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Read and trim email
            $email = trim($_POST['email'] ?? '');

            // Validation
            $errors = [];

            if (empty($email)) {
                $errors['email'] = 'Email is required';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = 'Please enter a valid email address';
            }

            // If valid, process the request
            if (empty($errors)) {
                // Look up user by email
                $user = $this->userModel->findByEmail($email);

                // For security, always show the same message regardless of whether email exists
                if ($user) {
                    // Generate secure random token
                    $token = bin2hex(random_bytes(32));
                    
                    // Set expiry time using DATE_ADD in the database to avoid timezone issues
                    // We'll use a special marker and let the Model handle it
                    $expiresAt = 'DATE_ADD(NOW(), INTERVAL 60 MINUTE)';

                    // Store token in database
                    if ($this->userModel->createPasswordResetToken($user['id'], $token, $expiresAt)) {
                        // Build reset URL
                        $resetUrl = BASE_URL . 'users/resetPassword?token=' . urlencode($token);

                        // Send password reset email
                        require_once __DIR__ . '/../helpers/mail_helper.php';
                        $userName = $user['first_name'] . ' ' . $user['last_name'];
                        send_password_reset_email($user['email'], $userName, $resetUrl);
                    }
                }

                // Always show generic success message (security best practice)
                set_flash('success', 'If that email is registered, a password reset link has been sent.');
                $this->redirect('users/login');
                return;
            }

            // Store errors and form data
            $data['errors'] = $errors;
            $data['email'] = htmlspecialchars($email);
        }

        $this->render('forgot_password', $data);
    }

    /**
     * Reset password page
     * Handles both GET (show form) and POST (update password)
     * 
     * @return void
     */
    public function resetPassword() {
        // Redirect if already logged in
        if (is_logged_in()) {
            $this->redirect('');
        }

        // Get token from GET or POST
        $token = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = isset($_POST['token']) ? trim($_POST['token']) : '';
        } else {
            // GET request - read from query string
            $token = isset($_GET['token']) ? trim($_GET['token']) : '';
        }


        // Validate token is not empty
        if (empty($token)) {
            set_flash('error', 'Invalid or expired password reset link.');
            $this->redirect('users/login');
            return;
        }

        // Validate token format (64 hex characters)
        if (!preg_match('/^[a-f0-9]{64}$/i', $token)) {
            set_flash('error', 'Invalid or expired password reset link.');
            $this->redirect('users/login');
            return;
        }

        // Find valid reset token
        $resetRecord = $this->userModel->findValidPasswordResetToken($token);

        if (!$resetRecord) {
            set_flash('error', 'Invalid or expired password reset link.');
            $this->redirect('users/login');
            return;
        }

        $data = [
            'title' => 'Reset Password',
            'token' => $token,
            'errors' => []
        ];

        // Handle POST request (password update)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $password = $_POST['password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';

            // Re-validate token (in case it was used or expired between GET and POST)
            $resetRecord = $this->userModel->findValidPasswordResetToken($token);
            if (!$resetRecord) {
                set_flash('error', 'Invalid or expired password reset link.');
                $this->redirect('users/login');
                return;
            }

            // Validation
            $errors = [];

            // Password validation (same rules as register)
            if (empty($password)) {
                $errors['password'] = 'Password is required';
            } elseif (strlen($password) < 8) {
                $errors['password'] = 'Password must be at least 8 characters';
            } elseif (!preg_match('/[A-Z]/', $password)) {
                $errors['password'] = 'Password must contain at least one uppercase letter';
            } elseif (!preg_match('/[a-z]/', $password)) {
                $errors['password'] = 'Password must contain at least one lowercase letter';
            } elseif (!preg_match('/[0-9]/', $password)) {
                $errors['password'] = 'Password must contain at least one number';
            }

            // Confirm password validation
            if (empty($confirm_password)) {
                $errors['confirm_password'] = 'Please confirm your password';
            } elseif ($password !== $confirm_password) {
                $errors['confirm_password'] = 'Passwords do not match';
            }

            // If no errors, update password
            if (empty($errors)) {
                // Hash the new password
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

                // Update user's password
                if ($this->userModel->updateUserPassword($resetRecord['user_id'], $hashedPassword)) {
                    // Mark token as used
                    $this->userModel->markPasswordResetTokenUsed($resetRecord['id']);

                    // Get user data
                    $user = $this->userModel->findById($resetRecord['user_id']);

                    if ($user) {
                        // Auto-login the user
                        require_once __DIR__ . '/../helpers/auth_helper.php';
                        login_user($user, false);

                        set_flash('success', 'Your password has been reset and you are now logged in.');
                        $this->redirect('');
                        return;
                    } else {
                        set_flash('success', 'Your password has been reset. Please log in.');
                        $this->redirect('users/login');
                        return;
                    }
                } else {
                    $errors['general'] = 'Failed to update password. Please try again.';
                }
            }

            // Store errors
            $data['errors'] = $errors;
        }

        $this->render('reset_password', $data);
    }

    /**
     * View user profile
     * Normal users can only view their own profile
     * Admins can view any user's profile
     * 
     * @param int|null $id User ID (optional, defaults to current user)
     * @return void
     */
    public function profile($id = null) {

        require_once __DIR__ . '/../helpers/session_helper.php';
        require_once __DIR__ . '/../helpers/auth_helper.php';

        // Must be logged in
        if (!is_logged_in()) {
            $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'] ?? BASE_URL;
            $this->redirect('users/login');
            return;
        }

        // Get current logged-in user ID from session
        $currentUserId = $_SESSION['user_id'] ?? null;

        // Determine if current user is admin
        $isAdmin = has_role('admin');

        // If no ID is provided, default to current user's profile
        if ($id === null) {
            $id = $currentUserId;
        }

        // If not admin, ensure user can ONLY access their own profile
        if (!$isAdmin && (int)$id !== (int)$currentUserId) {
            set_flash('error', 'You are not allowed to view another user\'s profile.');
            $this->redirect('profile');
            return;
        }

        // Fetch the user by ID
        $user = $this->userModel->findById($id);
        if (!$user) {
            set_flash('error', 'User not found.');
            // For normal user, go back to own profile; for admin, go to user list
            if ($isAdmin) {
                $this->redirect('users/manageUsers');
            } else {
                $this->redirect('profile');
            }
            return;
        }

        // Get user roles for display
        $userRoles = $this->userModel->getRoles($id);

        $data = [
            'title' => 'User Profile',
            'user' => $user,
            'userRoles' => $userRoles,
            'isAdmin' => $isAdmin,
            'isOwnProfile' => (int)$id === (int)$currentUserId
        ];

        // Render profile view
        $this->render('users/profile', $data);
    }

    /**
     * List all users (admin only)
     * 
     * @return void
     */
    public function manageUsers() {
        require_once __DIR__ . '/../helpers/session_helper.php';
        require_once __DIR__ . '/../helpers/auth_helper.php';

        // Only admin can access
        require_admin();

        // Get all users using existing model method
        $users = $this->userModel->getAllUsers();

        $data = [
            'title' => 'User Management',
            'users' => $users
        ];

        $this->render('admin/users_index', $data);
    }

    /**
     * Edit a user (admin only)
     * 
     * @param int $id User ID
     * @return void
     */
    public function editUser($id) {
        require_once __DIR__ . '/../helpers/session_helper.php';
        require_once __DIR__ . '/../helpers/auth_helper.php';

        require_admin();

        $user = $this->userModel->findById($id);
        if (!$user) {
            set_flash('error', 'User not found.');
            $this->redirect('users/manageUsers');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');
            $firstName = trim($_POST['first_name'] ?? '');
            $lastName = trim($_POST['last_name'] ?? '');
            $emailVerified = isset($_POST['email_verified']) ? 1 : 0;

            $errors = [];

            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = 'Please provide a valid email address.';
            } elseif ($email !== $user['email'] && $this->userModel->emailExists($email)) {
                $errors['email'] = 'Email is already registered.';
            }

            if (empty($firstName) || strlen($firstName) < 2) {
                $errors['first_name'] = 'First name must be at least 2 characters.';
            }

            if (empty($lastName) || strlen($lastName) < 2) {
                $errors['last_name'] = 'Last name must be at least 2 characters.';
            }

            if (empty($errors)) {
                $updateData = [
                    'email' => $email,
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'email_verified' => $emailVerified
                ];

                if ($this->userModel->update($id, $updateData)) {
                    set_flash('success', 'User updated successfully.');
                    $this->redirect('users/manageUsers');
                    return;
                } else {
                    $errors['general'] = 'Failed to update user. Please try again.';
                }
            }

            $data = [
                'title' => 'Edit User',
                'user' => array_merge($user, [
                    'email' => $email,
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'email_verified' => $emailVerified
                ]),
                'errors' => $errors
            ];

            $this->render('admin/edit_user', $data);
            return;
        }

        // GET: show form
        $data = [
            'title' => 'Edit User',
            'user' => $user,
            'errors' => []
        ];

        $this->render('admin/edit_user', $data);
    }

    /**
     * Delete a user (admin only)
     * 
     * @param int $id User ID
     * @return void
     */
    public function deleteUser($id) {
        require_once __DIR__ . '/../helpers/session_helper.php';
        require_once __DIR__ . '/../helpers/auth_helper.php';

        require_admin();

        // Prevent admin from deleting themselves
        $currentUserId = $_SESSION['user_id'] ?? null;
        if ((int)$id === (int)$currentUserId) {
            set_flash('error', 'You cannot delete your own account.');
            $this->redirect('users/manageUsers');
            return;
        }

        if ($this->userModel->delete($id)) {
            set_flash('success', 'User deleted successfully.');
        } else {
            set_flash('error', 'Failed to delete user.');
        }

        $this->redirect('users/manageUsers');
    }

    /**
     * Create a new user (admin only)
     * 
     * @return void
     */
    public function createUser() {
        require_once __DIR__ . '/../helpers/session_helper.php';
        require_once __DIR__ . '/../helpers/auth_helper.php';

        require_admin();

        $data = [
            'title' => 'Add New User',
            'email' => '',
            'first_name' => '',
            'last_name' => '',
            'password' => '',
            'errors' => []
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');
            $firstName = trim($_POST['first_name'] ?? '');
            $lastName = trim($_POST['last_name'] ?? '');
            $password = $_POST['password'] ?? '';

            $errors = [];

            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = 'Please provide a valid email address.';
            } elseif ($this->userModel->emailExists($email)) {
                $errors['email'] = 'Email is already registered.';
            }

            if (empty($firstName) || strlen($firstName) < 2) {
                $errors['first_name'] = 'First name must be at least 2 characters.';
            }

            if (empty($lastName) || strlen($lastName) < 2) {
                $errors['last_name'] = 'Last name must be at least 2 characters.';
            }

            if (empty($password)) {
                $errors['password'] = 'Password is required.';
            } elseif (strlen($password) < 8) {
                $errors['password'] = 'Password must be at least 8 characters.';
            } elseif (!preg_match('/[A-Z]/', $password)) {
                $errors['password'] = 'Password must contain at least one uppercase letter.';
            } elseif (!preg_match('/[a-z]/', $password)) {
                $errors['password'] = 'Password must contain at least one lowercase letter.';
            } elseif (!preg_match('/[0-9]/', $password)) {
                $errors['password'] = 'Password must contain at least one number.';
            }

            if (empty($errors)) {
                $hashed = password_hash($password, PASSWORD_DEFAULT);

                $newUserId = $this->userModel->create([
                    'email' => $email,
                    'password' => $hashed,
                    'first_name' => $firstName,
                    'last_name' => $lastName
                ]);

                if ($newUserId) {
                    set_flash('success', 'New user created successfully.');
                    $this->redirect('users/manageUsers');
                    return;
                } else {
                    $errors['general'] = 'Failed to create user. Please try again.';
                }
            }

            $data = [
                'title' => 'Add New User',
                'email' => $email,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'password' => '',
                'errors' => $errors
            ];

            $this->render('admin/create_user', $data);
            return;
        }

        $this->render('admin/create_user', $data);
    }
    /**
 * Edit own profile (normal user)
 */
public function edit()
{
    require_once __DIR__ . '/../helpers/auth_helper.php';

    if (!is_logged_in()) {
        $this->redirect('users/login');
        return;
    }

    $user = current_user();

    $data = [
        'title' => 'Edit Profile',
        'user' => $user,
        'errors' => []
    ];

    $this->render('users/edit', $data);
}
/**
 * Update own profile (POST)
 */
public function update()
{
    require_once __DIR__ . '/../helpers/auth_helper.php';

    if (!is_logged_in() || $_SERVER['REQUEST_METHOD'] !== 'POST') {
        $this->redirect('users/profile');
        return;
    }

    $userId = $_SESSION['user_id'];

    $firstName = trim($_POST['first_name'] ?? '');
    $lastName  = trim($_POST['last_name'] ?? '');

    $errors = [];

    if (strlen($firstName) < 2) {
        $errors['first_name'] = 'First name too short';
    }

    if (strlen($lastName) < 2) {
        $errors['last_name'] = 'Last name too short';
    }

    if (!empty($errors)) {
        $this->render('users/edit', [
            'title' => 'Edit Profile',
            'user' => array_merge(current_user(), [
                'first_name' => $firstName,
                'last_name' => $lastName
            ]),
            'errors' => $errors
        ]);
        return;
    }

    $this->userModel->update($userId, [
        'first_name' => $firstName,
        'last_name' => $lastName
    ]);

    // update session
    $_SESSION['user_first_name'] = $firstName;
    $_SESSION['user_last_name'] = $lastName;

    set_flash('success', 'Profile updated successfully.');
    $this->redirect('profile');
}

}
