<?php
/**
 * Authentication Helper Functions
 * 
 * Provides user authentication, authorization, and user management functions.
 */

require_once __DIR__ . '/session_helper.php';
require_once __DIR__ . '/../../config/database.php';

/**
 * Login a user and set session data
 * 
 * @param array $user_row User data from database
 * @param bool $remember_me Whether to set remember-me cookie
 * @return void
 */
function login_user($user_row, $remember_me = false) {
    // Regenerate session ID for security
    regenerate_session_id();
    
    // Set session data
    $_SESSION['user_id'] = $user_row['id'];
    $_SESSION['user_email'] = $user_row['email'];
    $_SESSION['last_activity'] = time();
    
    // Update last activity in database
    $db = getDB();
    $db->query("UPDATE users SET last_activity = NOW() WHERE id = :id")
       ->bind(':id', $user_row['id'])
       ->execute();
    
    // Handle remember-me functionality
    if ($remember_me) {
        create_remember_token($user_row['id']);
    }
    
    // Create session record in database
    create_session_record($user_row['id']);
    
    // Clear redirect URL if exists
    if (isset($_SESSION['redirect_after_login'])) {
        unset($_SESSION['redirect_after_login']);
    }
}

/**
 * Logout current user
 * 
 * @return void
 */
function logout_user() {
    $user_id = $_SESSION['user_id'] ?? null;
    
    // Delete remember-me token if exists
    if (isset($_COOKIE['remember_token'])) {
        delete_remember_token($_COOKIE['remember_token']);
        setcookie('remember_token', '', time() - 3600, '/', '', false, true);
    }
    
    // Delete session from database
    if (isset($_SESSION['session_id'])) {
        $db = getDB();
        $db->query("DELETE FROM sessions WHERE session_id = :session_id")
           ->bind(':session_id', $_SESSION['session_id'])
           ->execute();
    }
    
    // Destroy session
    destroy_session();
}

/**
 * Get current logged-in user data
 * 
 * @return array|null User data or null if not logged in
 */
function current_user() {
    if (!is_logged_in()) {
        return null;
    }
    
    $user_id = $_SESSION['user_id'];
    $db = getDB();
    
    $user = $db->query("SELECT * FROM users WHERE id = :id")
               ->bind(':id', $user_id)
               ->single();
    
    return $user;
}

/**
 * Check if current user has a specific role
 * 
 * @param string $roleName Role name to check (e.g., 'admin', 'instructor', 'user')
 * @return bool
 */
function has_role($roleName) {
    if (!is_logged_in()) {
        return false;
    }
    
    $user_id = $_SESSION['user_id'];
    $db = getDB();
    
    $result = $db->query("
        SELECT ur.id 
        FROM user_roles ur
        INNER JOIN roles r ON ur.role_id = r.id
        WHERE ur.user_id = :user_id AND r.name = :role_name
        LIMIT 1
    ")
    ->bind(':user_id', $user_id)
    ->bind(':role_name', $roleName)
    ->single();
    
    return $result !== false;
}

/**
 * Check if current user is admin
 * 
 * @return bool
 */
function is_admin() {
    return has_role('admin');
}

/**
 * Check if current user is instructor
 * 
 * @return bool
 */
function is_instructor() {
    return has_role('instructor') || is_admin();
}

/**
 * Require user to have a specific role
 * 
 * @param string $roleName Required role name
 * @return void
 */
function require_role($roleName) {
    require_login();
    
    if (!has_role($roleName)) {
        set_flash('error', "Access denied. {$roleName} role required.");
        header('Location: ' . BASE_URL);
        exit;
    }
}

/**
 * Create remember-me token
 * 
 * @param int $user_id User ID
 * @return string Token
 */
function create_remember_token($user_id) {
    $token = bin2hex(random_bytes(32));
    $expires_at = date('Y-m-d H:i:s', time() + REMEMBER_ME_LIFETIME);
    
    $db = getDB();
    $db->query("
        INSERT INTO remember_tokens (user_id, token, expires_at) 
        VALUES (:user_id, :token, :expires_at)
    ")
    ->bind(':user_id', $user_id)
    ->bind(':token', $token)
    ->bind(':expires_at', $expires_at)
    ->execute();
    
    // Set secure cookie
    setcookie('remember_token', $token, time() + REMEMBER_ME_LIFETIME, '/', '', false, true);
    
    return $token;
}

/**
 * Delete remember-me token
 * 
 * @param string $token Token to delete
 * @return void
 */
function delete_remember_token($token) {
    $db = getDB();
    $db->query("DELETE FROM remember_tokens WHERE token = :token")
       ->bind(':token', $token)
       ->execute();
}

/**
 * Check remember-me token and auto-login if valid
 * 
 * @return bool True if auto-logged in, false otherwise
 */
function check_remember_me() {
    if (is_logged_in() || !isset($_COOKIE['remember_token'])) {
        return false;
    }
    
    $token = $_COOKIE['remember_token'];
    $db = getDB();
    
    $result = $db->query("
        SELECT u.* 
        FROM users u
        INNER JOIN remember_tokens rt ON u.id = rt.user_id
        WHERE rt.token = :token AND rt.expires_at > NOW()
        LIMIT 1
    ")
    ->bind(':token', $token)
    ->single();
    
    if ($result) {
        login_user($result, false); // Don't create new token, reuse existing
        return true;
    } else {
        // Invalid token, delete cookie
        setcookie('remember_token', '', time() - 3600, '/');
        return false;
    }
}

/**
 * Create session record in database
 * 
 * @param int $user_id User ID
 * @return void
 */
function create_session_record($user_id) {
    $session_id = session_id();
    $ip_address = get_client_ip();
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $expires_at = date('Y-m-d H:i:s', time() + SESSION_LIFETIME);
    
    $db = getDB();
    $db->query("
        INSERT INTO sessions (user_id, session_id, ip_address, user_agent, expires_at) 
        VALUES (:user_id, :session_id, :ip_address, :user_agent, :expires_at)
        ON DUPLICATE KEY UPDATE expires_at = VALUES(expires_at)
    ")
    ->bind(':user_id', $user_id)
    ->bind(':session_id', $session_id)
    ->bind(':ip_address', $ip_address)
    ->bind(':user_agent', $user_agent)
    ->bind(':expires_at', $expires_at)
    ->execute();
    
    $_SESSION['session_id'] = $session_id;
}

/**
 * Get client IP address
 * 
 * @return string IP address
 */
function get_client_ip() {
    $ip = $_SERVER['HTTP_CLIENT_IP'] ?? 
          $_SERVER['HTTP_X_FORWARDED_FOR'] ?? 
          $_SERVER['REMOTE_ADDR'] ?? 
          '0.0.0.0';
    
    // Handle comma-separated IPs from proxies
    if (strpos($ip, ',') !== false) {
        $ip = trim(explode(',', $ip)[0]);
    }
    
    return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : '0.0.0.0';
}

/**
 * Auto-login via remember-me cookie
 * This runs on every page load if user is not logged in
 */
function auto_login_remember_me() {
    if (is_logged_in()) {
        return false;
    }

    if (!isset($_COOKIE['remember_token']) || !isset($_COOKIE['remember_user_id'])) {
        return false;
    }

    $token = $_COOKIE['remember_token'];
    $userId = (int)$_COOKIE['remember_user_id'];

    // Load User model
    require_once ROOT . '/app/models/User.php';
    $userModel = new User();

    // Find token in database
    $userData = $userModel->findRememberToken($token);

    if ($userData && $userData['id'] == $userId) {
        // Valid token - log user in
        login_user($userData, false); // Don't create new token, reuse existing
        return true;
    } else {
        // Invalid token - delete cookies
        setcookie('remember_token', '', time() - 3600, '/', '', false, true);
        setcookie('remember_user_id', '', time() - 3600, '/', '', false, true);
        return false;
    }
}

// Auto-check remember-me on page load (if not already logged in)
if (!is_logged_in()) {
    auto_login_remember_me();
}

