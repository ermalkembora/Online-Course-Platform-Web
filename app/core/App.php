<?php
/**
 * Simple Router Class
 * 
 * Handles URL routing and dispatches requests to appropriate controllers.
 */

class App {
    private $controller = 'Pages';
    private $method = 'index';
    private $params = [];

    public function __construct() {
        $url = $this->parseUrl();
        
        // Set controller
        if (isset($url[0]) && !empty($url[0])) {
            $controllerName = ucfirst($url[0]);
            if (file_exists(ROOT . '/app/controllers/' . $controllerName . '.php')) {
                $this->controller = $controllerName;
                unset($url[0]);
            }
        }
        
        // Require and instantiate controller
        require_once ROOT . '/app/controllers/' . $this->controller . '.php';
        $this->controller = new $this->controller();
        
        // Set method
        if (isset($url[1])) {
            // Convert dash-separated method names to camelCase
            // e.g., "paypal-success" -> "paypalSuccess"
            $methodName = $this->dashToCamelCase($url[1]);
            
            if (method_exists($this->controller, $methodName)) {
                $this->method = $methodName;
                unset($url[1]);
            }
            // If method doesn't exist, keep default 'index' - will be caught in run()
        }
        
        // Set parameters
        $this->params = $url ? array_values($url) : [];
    }

    /**
     * Convert dash-separated string to camelCase
     * 
     * @param string $string Dash-separated string (e.g., "paypal-success")
     * @return string CamelCase string (e.g., "paypalSuccess")
     */
    private function dashToCamelCase($string) {
        // Split by dash
        $parts = explode('-', $string);
        
        // First part stays lowercase, rest are capitalized
        $result = $parts[0];
        for ($i = 1; $i < count($parts); $i++) {
            $result .= ucfirst($parts[$i]);
        }
        
        return $result;
    }

    /**
     * Parse URL from $_GET['url'] or REQUEST_URI
     * 
     * @return array URL segments
     */
    private function parseUrl() {
        if (isset($_GET['url'])) {
            $url = rtrim($_GET['url'], '/');
            $url = filter_var($url, FILTER_SANITIZE_URL);
            $url = explode('/', $url);
            return $url;
        }
        
        // Fallback: parse from REQUEST_URI
        $requestUri = $_SERVER['REQUEST_URI'] ?? '/';
        $basePath = str_replace('/public', '', parse_url(BASE_URL, PHP_URL_PATH));
        
        // Remove base path from request URI
        if ($basePath !== '/' && strpos($requestUri, $basePath) === 0) {
            $requestUri = substr($requestUri, strlen($basePath));
        }
        
        // Remove query string
        $requestUri = strtok($requestUri, '?');
        
        // Remove leading/trailing slashes
        $requestUri = trim($requestUri, '/');
        
        // Remove /public if present
        if (strpos($requestUri, 'public/') === 0) {
            $requestUri = substr($requestUri, 7);
        }
        $requestUri = trim($requestUri, '/');
        
        if (empty($requestUri)) {
            return [];
        }
        
        return explode('/', $requestUri);
    }

    /**
     * Run the application
     * 
     * @return void
     */
    public function run() {
        // Check if method exists before calling
        if (!method_exists($this->controller, $this->method)) {
            die("Error: Method '{$this->method}' not found in " . get_class($this->controller) . " controller.");
        }
        
        // Call the controller method with parameters
        call_user_func_array([$this->controller, $this->method], $this->params);
    }
}

