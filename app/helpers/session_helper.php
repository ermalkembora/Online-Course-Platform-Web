<?php
/**
 * Session Helper Functions
 * 
 * Provides session management, flash messages, and authentication checks.
 */

require_once __DIR__ . '/../../config/config.php';

/**
 * Start session if not already started
 */
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_secure', 0); // Set to 1 in production with HTTPS
    
    session_name(SESSION_NAME);
    session_start();
}

/**
 * Check if user is logged in
 * 
 * @return bool
 */
function is_logged_in() {
    // Check if user_id exists in session
    if (!isset($_SESSION['user_id'])) {
        return false;
    }
    
    // Check session timeout (15 minutes of inactivity)
    if (isset($_SESSION['last_activity'])) {
        $inactive_time = time() - $_SESSION['last_activity'];
        
        if ($inactive_time > SESSION_LIFETIME) {
            // Session expired due to inactivity
            logout_user();
            return false;
        }
    }
    
    // Update last activity timestamp
    $_SESSION['last_activity'] = time();
    
    return true;
}

/**
 * Require user to be logged in, redirect if not
 * 
 * @param string $redirect_url Optional redirect URL (defaults to login page)
 * @return void
 */
function require_login($redirect_url = null) {
    if (!is_logged_in()) {
        if ($redirect_url === null) {
            $redirect_url = BASE_URL . 'auth/login.php';
        }
        
        // Store intended destination for redirect after login
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'] ?? BASE_URL;
        
        header('Location: ' . $redirect_url);
        exit;
    }
}

/**
 * Require user to have admin role, redirect if not
 * 
 * @return void
 */
function require_admin() {
    require_login();
    
    if (!has_role('admin')) {
        set_flash('error', 'Access denied. Administrator privileges required.');
        header('Location: ' . BASE_URL);
        exit;
    }
}

/**
 * Set a flash message
 * 
 * @param string $key Flash message key (e.g., 'success', 'error', 'info')
 * @param string $message Message to display
 * @return void
 */
function set_flash($key, $message) {
    if (!isset($_SESSION['flash_messages'])) {
        $_SESSION['flash_messages'] = [];
    }
    
    $_SESSION['flash_messages'][$key] = $message;
}

/**
 * Display and clear a flash message
 * 
 * @param string $key Flash message key
 * @param bool $clear Whether to clear the message after displaying
 * @return string|null The message or null if not set
 */
function display_flash($key, $clear = true) {
    if (!isset($_SESSION['flash_messages'][$key])) {
        return null;
    }
    
    $message = $_SESSION['flash_messages'][$key];
    
    if ($clear) {
        unset($_SESSION['flash_messages'][$key]);
    }
    
    return $message;
}

/**
 * Get all flash messages and clear them
 * 
 * @param bool $clear Whether to clear messages after retrieving
 * @return array
 */
function get_all_flash($clear = true) {
    $messages = $_SESSION['flash_messages'] ?? [];
    
    if ($clear) {
        unset($_SESSION['flash_messages']);
    }
    
    return $messages;
}

/**
 * Check if a flash message exists
 * 
 * @param string $key Flash message key
 * @return bool
 */
function has_flash($key) {
    return isset($_SESSION['flash_messages'][$key]);
}

/**
 * Regenerate session ID (security best practice)
 * 
 * @return void
 */
function regenerate_session_id() {
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_regenerate_id(true);
    }
}

/**
 * Destroy session and clear all session data
 * 
 * @return void
 */
function destroy_session() {
    $_SESSION = [];
    
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/');
    }
    
    session_destroy();
}

/**
 * Get session value
 * 
 * @param string $key Session key
 * @param mixed $default Default value if key doesn't exist
 * @return mixed
 */
function session_get($key, $default = null) {
    return $_SESSION[$key] ?? $default;
}

/**
 * Set session value
 * 
 * @param string $key Session key
 * @param mixed $value Value to set
 * @return void
 */
function session_set($key, $value) {
    $_SESSION[$key] = $value;
}

/**
 * Unset session value
 * 
 * @param string $key Session key
 * @return void
 */
function session_unset_key($key) {
    if (isset($_SESSION[$key])) {
        unset($_SESSION[$key]);
    }
}

