<?php
/**
 * Base Controller Class
 * 
 * All controllers extend this class.
 * Provides methods for loading models and rendering views.
 */

require_once __DIR__ . '/../helpers/session_helper.php';
require_once __DIR__ . '/../helpers/auth_helper.php';

class Controller {
    /**
     * Constructor - applies global authentication guard
     */
    public function __construct() {
        $this->applyAuthGuard();
    }

    /**
     * Apply authentication guard to protect all routes except public ones
     * 
     * PUBLIC ROUTES (no login required):
     * - Homepage (empty route, pages/index)
     * - About page (pages/about)
     * - User authentication pages (login, register, verify, password reset)
     * 
     * PROTECTED ROUTES (login required):
     * - All courses/* routes (browse, search, details, lessons, etc.)
     * - User profile and management
     * - Admin pages
     * - Checkout and payment pages
     * - All other routes (default to protected for security)
     * 
     * @return void
     */
    protected function applyAuthGuard() {
        // Get the route from $_GET['url'] (set by .htaccess rewrite)
        $route = $_GET['url'] ?? '';
        $route = trim($route, '/');
        
        // Normalize: handle empty route as homepage
        // Empty route means homepage (Pages::index)
        if ($route === '') {
            $route = 'pages/index'; // Normalize empty route to pages/index
        }
        
        // Define exact PUBLIC routes (no login required)
        // These are the only routes accessible without authentication
        $publicRoutesExact = [
            '',                    // homepage (empty route)
            'pages/index',         // homepage (Pages controller, index method)
            'pages/about',         // about page
            'users/login',         // login page
            'users/register',      // registration page
            'users/verifyEmail',   // email verification
            'users/forgotPassword', // forgot password
            'users/resetPassword',  // reset password
        ];
        
        // Check if current route is in the exact public list
        if (in_array($route, $publicRoutesExact, true)) {
            return; // Allow access - this is a public route
        }
        
        // Define protected route prefixes (require login if route starts with any of these)
        // All routes starting with these prefixes require authentication
        $protectedPrefixes = [
            'courses',      // all courses routes (courses, courses/index, courses/show, courses/search, etc.)
            'profile',      // profile controller
            'users/profile', // user profile pages
            'users/manageUsers', // user management (admin)
            'users/editUser',   // edit user (admin)
            'users/deleteUser', // delete user (admin)
            'users/createUser', // create user (admin)
            'admin',         // all admin routes
            'checkout',      // checkout and payment pages
            'enrollments',  // enrollment management
        ];
        
        // Check if this route should be PROTECTED (requires login)
        $requiresLogin = false;
        
        // Check if route matches any protected prefix
        foreach ($protectedPrefixes as $prefix) {
            // Exact match or route starts with prefix followed by /
            if ($route === $prefix || strpos($route, $prefix . '/') === 0) {
                $requiresLogin = true;
                break;
            }
        }
        
        // If route doesn't match any protected prefix, treat as PUBLIC for now
        // (Can be tightened later for security if needed)
        if (!$requiresLogin) {
            return; // Allow access - unknown routes are public for now
        }
        
        // If this route requires login and user is not logged in → redirect to login
        if ($requiresLogin && !is_logged_in()) {
            // Save the intended URL to redirect after login
            // This allows Users::login() to redirect back after successful login
            $requestUri = $_SERVER['REQUEST_URI'] ?? BASE_URL;
            $_SESSION['redirect_after_login'] = $requestUri;
            
            // Redirect to login page
            header('Location: ' . BASE_URL . 'users/login');
            exit;
        }
        
        // User is logged in or route is public → allow access
    }

    /**
     * Parse current route from REQUEST_URI (similar to App::parseUrl())
     * 
     * This method should match the logic in App::parseUrl() to ensure
     * consistent route identification for the auth guard.
     * 
     * @return array URL segments (empty array for homepage)
     */
    private function parseCurrentRoute() {
        // Try to get URL from $_GET['url'] first (if using .htaccess rewrite)
        // This is the primary method when using mod_rewrite
        if (isset($_GET['url'])) {
            $url = rtrim($_GET['url'], '/');
            $url = filter_var($url, FILTER_SANITIZE_URL);
            if (!empty($url)) {
                return explode('/', $url);
            }
            // Empty URL means homepage - return empty array
            return [];
        }
        
        // Fallback: parse from REQUEST_URI
        // This handles cases where $_GET['url'] is not set
        $requestUri = $_SERVER['REQUEST_URI'] ?? '/';
        
        // Get base path from BASE_URL
        $basePath = parse_url(BASE_URL, PHP_URL_PATH);
        if ($basePath && $basePath !== '/') {
            // Remove base path from request URI
            if (strpos($requestUri, $basePath) === 0) {
                $requestUri = substr($requestUri, strlen($basePath));
            }
        }
        
        // Remove query string
        $requestUri = strtok($requestUri, '?');
        
        // Remove leading/trailing slashes
        $requestUri = trim($requestUri, '/');
        
        // Remove /public if present (from root redirect)
        if (strpos($requestUri, 'public/') === 0) {
            $requestUri = substr($requestUri, 7);
        } elseif ($requestUri === 'public') {
            $requestUri = '';
        }
        $requestUri = trim($requestUri, '/');
        
        // Empty request URI means homepage
        if (empty($requestUri) || $requestUri === '/') {
            return [];
        }
        
        return explode('/', $requestUri);
    }

    /**
     * Load a model
     * 
     * @param string $model Model name
     * @return object Model instance
     */
    protected function model($model) {
        require_once ROOT . '/app/models/' . $model . '.php';
        return new $model();
    }

    /**
     * Render a view
     * 
     * @param string $view View file name (without .php extension)
     * @param array $data Data to pass to the view
     * @return void
     */
    protected function renderView($view, $data = []) {
        // Extract data array to variables
        extract($data);
        
        // Determine view path based on controller name
        $controllerName = str_replace('Controller', '', get_class($this));
        $controllerName = strtolower($controllerName);
        
        // Check if view exists in controller-specific folder
        $viewPath = ROOT . '/app/views/' . $controllerName . '/' . $view . '.php';
        
        // If not found, check in views root
        if (!file_exists($viewPath)) {
            $viewPath = ROOT . '/app/views/' . $view . '.php';
        }
        
        // If still not found, throw error
        if (!file_exists($viewPath)) {
            die("View file not found: {$view}");
        }
        
        // Load view
        require_once $viewPath;
    }

    /**
     * Render view with layout (header and footer)
     * 
     * @param string $view View file name
     * @param array $data Data to pass to the view
     * @param string $layout Layout name (default: 'default')
     * @return void
     */
    protected function render($view, $data = [], $layout = 'default') {
        // Extract data array to variables
        extract($data);
        
        // Determine view path
        $controllerName = str_replace('Controller', '', get_class($this));
        $controllerName = strtolower($controllerName);
        
        $viewPath = ROOT . '/app/views/' . $controllerName . '/' . $view . '.php';
        
        if (!file_exists($viewPath)) {
            $viewPath = ROOT . '/app/views/' . $view . '.php';
        }
        
        if (!file_exists($viewPath)) {
            die("View file not found: {$view}");
        }
        
        // Load layout header
        $headerPath = ROOT . '/app/views/inc/header.php';
        if (file_exists($headerPath)) {
            require_once $headerPath;
        }
        
        // Load view content
        require_once $viewPath;
        
        // Load layout footer
        $footerPath = ROOT . '/app/views/inc/footer.php';
        if (file_exists($footerPath)) {
            require_once $footerPath;
        }
    }

    /**
     * Redirect to a URL
     * 
     * @param string $url URL to redirect to
     * @return void
     */
    protected function redirect($url) {
        if (strpos($url, 'http') !== 0) {
            $url = BASE_URL . ltrim($url, '/');
        }
        header('Location: ' . $url);
        exit;
    }

    /**
     * Return JSON response
     * 
     * @param mixed $data Data to encode as JSON
     * @param int $statusCode HTTP status code
     * @return void
     */
    protected function json($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}

