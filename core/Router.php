<?php
class Router {
    private $routes = [];
    private $params = [];
    
    // OPTIMIZATION: Separate routes by method for faster lookup
    private $routesByMethod = [
        'GET' => [],
        'POST' => [],
        'PUT' => [],
        'PATCH' => [],
        'DELETE' => []
    ];
    
    // OPTIMIZATION: Separate exact matches from parameterized routes
    private $exactRoutes = [
        'GET' => [],
        'POST' => [],
        'PUT' => [],
        'PATCH' => [],
        'DELETE' => []
    ];
    
    public function add($method, $path, $controller, $action) {
        $method = strtoupper($method);
        
        // OPTIMIZATION: Pre-compile regex pattern during registration
        $pattern = $this->compilePattern($path);
        
        // OPTIMIZATION: Check if route is exact match (no parameters)
        $isExactMatch = strpos($path, '{') === false;
        
        $route = [
            'method' => $method,
            'path' => $path,
            'pattern' => $pattern, // Pre-compiled pattern
            'controller' => $controller,
            'action' => $action,
            'isExactMatch' => $isExactMatch
        ];
        
        // Store in all routes (backward compatibility)
        $this->routes[] = $route;
        
        // OPTIMIZATION: Store in method-specific array
        if (!isset($this->routesByMethod[$method])) {
            $this->routesByMethod[$method] = [];
        }
        $this->routesByMethod[$method][] = $route;
        
        // OPTIMIZATION: Store exact matches separately for O(1) lookup
        if ($isExactMatch) {
            if (!isset($this->exactRoutes[$method])) {
                $this->exactRoutes[$method] = [];
            }
            $this->exactRoutes[$method][$path] = $route;
        }
    }
    
    /**
     * OPTIMIZATION: Compile regex pattern once during registration
     * Instead of compiling every request
     */
    private function compilePattern($path) {
        // Replace {param} placeholders with regex groups
        $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '([a-zA-Z0-9\-]+)', $path);
        // Escape forward slashes
        $pattern = str_replace('/', '\/', $pattern);
        return '#^' . $pattern . '$#';
    }
    
    public function get($path, $controller, $action) {
        $this->add('GET', $path, $controller, $action);
    }
    
    public function post($path, $controller, $action) {
        $this->add('POST', $path, $controller, $action);
    }
    
    public function put($path, $controller, $action) {
        $this->add('PUT', $path, $controller, $action);
    }

    public function patch($path, $controller, $action) {
        $this->add('PATCH', $path, $controller, $action);
    }
    
    public function delete($path, $controller, $action) {
        $this->add('DELETE', $path, $controller, $action);
    }
    
    public function dispatch() {
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $method = $_SERVER['REQUEST_METHOD'];
        
        // Skip routing for static files (assets, uploads, images, etc.)
        $staticExtensions = ['.css', '.js', '.jpg', '.jpeg', '.png', '.gif', '.svg', '.ico', '.woff', '.woff2', '.ttf', '.eot', '.pdf', '.doc', '.docx', '.xls', '.xlsx'];
        $staticPaths = ['/assets/', '/uploads/', '/favicon.ico'];
        
        foreach ($staticPaths as $staticPath) {
            if (strpos($uri, $staticPath) === 0) {
                // Let web server handle static files
                return;
            }
        }
        
        foreach ($staticExtensions as $ext) {
            if (substr($uri, -strlen($ext)) === $ext) {
                // Let web server handle static files
                return;
            }
        }
        
        // Handle method override for PUT/PATCH/DELETE (browsers don't support these natively)
        // Store raw input for later use (php://input can only be read once)
        $rawInput = null;
        if ($method === 'POST') {
            // Check form data first
            if (isset($_POST['_method'])) {
                $method = strtoupper($_POST['_method']);
            }
            // Also check JSON body for method override
            elseif (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
                $rawInput = file_get_contents('php://input');
                $jsonInput = json_decode($rawInput, true);
                if (isset($jsonInput['_method'])) {
                    $method = strtoupper($jsonInput['_method']);
                }
                // Store raw input for controllers to use
                $GLOBALS['_RAW_INPUT'] = $rawInput;
            }
        } elseif (in_array($method, ['PUT', 'PATCH', 'DELETE'])) {
            // Store raw input for PUT/PATCH/DELETE requests
            $rawInput = file_get_contents('php://input');
            $GLOBALS['_RAW_INPUT'] = $rawInput;
        }
        
        // Normalize URI - remove trailing slash except for root
        $uri = rtrim($uri, '/');
        if (empty($uri)) {
            $uri = '/';
        }
        
        // Handle root route
        if ($uri === '/' && $method === 'GET') {
            if (Auth::check()) {
                header('Location: /dashboard');
            } else {
                header('Location: /login');
            }
            exit;
        }
        
        // OPTIMIZATION: Check exact matches first (O(1) lookup)
        if (isset($this->exactRoutes[$method][$uri])) {
            $route = $this->exactRoutes[$method][$uri];
            $this->params = [];
            $this->executeRoute($route, []);
            return;
        }
        
        // OPTIMIZATION: Only check routes for current HTTP method
        $routesToCheck = $this->routesByMethod[$method] ?? [];
        
        // OPTIMIZATION: Early exit - check parameterized routes
        foreach ($routesToCheck as $route) {
            // Skip exact matches (already checked above)
            if ($route['isExactMatch']) {
                continue;
            }
            
            // Use pre-compiled pattern
            if (preg_match($route['pattern'], $uri, $matches)) {
                array_shift($matches);
                $this->params = $matches;
                
                // OPTIMIZATION: Early exit - execute immediately when match found
                $this->executeRoute($route, $matches);
                return;
            }
        }
        
        // 404 Not Found
        http_response_code(404);
        echo "404 - Page Not Found<br>";
        echo "URI: " . htmlspecialchars($uri) . "<br>";
        echo "Method: " . htmlspecialchars($method) . "<br>";
        echo "<br>Available routes:<br>";
        foreach ($this->routes as $route) {
            // Use pre-compiled pattern if available, otherwise compile on the fly
            $pattern = $route['pattern'] ?? $this->compilePattern($route['path']);
            $matched = preg_match($pattern, $uri, $testMatches) ? '✓ MATCH' : '';
            echo htmlspecialchars($route['method'] . ' ' . $route['path']) . " " . $matched . "<br>";
        }
    }
    
    /**
     * OPTIMIZATION: Execute route with error handling
     * Separated for code reusability
     */
    private function executeRoute($route, $params) {
        $controllerName = $route['controller'];
        $actionName = $route['action'];
        
        if (!class_exists($controllerName)) {
            http_response_code(500);
            die("Error: Controller class '{$controllerName}' not found.");
        }
        
        $controller = new $controllerName();
        
        if (!method_exists($controller, $actionName)) {
            http_response_code(500);
            die("Error: Method '{$actionName}' not found in controller '{$controllerName}'.");
        }
        
        call_user_func_array([$controller, $actionName], $params);
    }
    
    /**
     * Legacy method for backward compatibility
     * @deprecated Use compilePattern() instead
     */
    private function convertToRegex($path) {
        return $this->compilePattern($path);
    }
}

