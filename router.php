<?php
// First, handle static files directly with proper MIME types
$requestUri = $_SERVER['REQUEST_URI'];
$filePath = __DIR__ . parse_url($requestUri, PHP_URL_PATH);

// Define MIME types for common file extensions
$mimeTypes = [
    'css' => 'text/css',
    'js' => 'application/javascript',
    'jpg' => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'png' => 'image/png',
    'gif' => 'image/gif',
    'svg' => 'image/svg+xml',
    'ico' => 'image/x-icon',
    'woff' => 'font/woff',
    'woff2' => 'font/woff2',
    'ttf' => 'font/ttf'
];

// Check if file exists and has a recognized extension
$extension = pathinfo($filePath, PATHINFO_EXTENSION);
if (file_exists($filePath) && isset($mimeTypes[$extension])) {
    header("Content-Type: $mimeTypes[$extension]");
    readfile($filePath);
    exit;
}

/**
 * Router for Corpus Digitale
 * Handles all incoming requests and routes them to the appropriate controllers
 */
class Router {
    private array $routes = [];
    /** @var callable|null */
    private $notFoundHandler;

    /**
     * Register a new route
     */
    public function addRoute(string $method, string $path, callable|array|string|null $handler): static {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'handler' => $handler
        ];
        return $this;
    }

    /**
     * Set 404 handler
     */
    public function setNotFoundHandler(callable $handler): static {
        $this->notFoundHandler = $handler;
        return $this;
    }

    /**
     * Match current request against registered routes
     */
    public function handleRequest(string $requestMethod, string $requestPath): bool {
        try {
            // Remove query string
            $requestPath = parse_url($requestPath, PHP_URL_PATH);
            $requestPath = trim($requestPath, '/');

            foreach ($this->routes as $route) {
                // Skip routes that don't match the request method
                if ($route['method'] !== 'ANY' && $route['method'] !== $requestMethod) {
                    continue;
                }

                // Handle exact matches
                if ($route['path'] === $requestPath) {
                    return $this->executeHandler($route['handler']);
                }

                // Handle pattern matching with parameters
                $pattern = $this->convertRouteToPattern($route['path']);
                if (preg_match($pattern, $requestPath, $matches)) {
                    array_shift($matches); // Remove full match
                    return $this->executeHandler($route['handler'], $matches);
                }
            }

            // No matching route found, execute 404 handler
            if ($this->notFoundHandler) {
                return $this->executeHandler($this->notFoundHandler);
            }

            // Default 404 response if no handler defined
            header("HTTP/1.0 404 Not Found");
            echo "404 - Page not found";
            return false;
        } catch (\Throwable $e) {
            header("HTTP/1.0 500 Internal Server Error");
            ob_clean();
            include "500.php";
            return false;
        }
    }

    /**
     * Convert route with placeholders to regex pattern
     */
    private function convertRouteToPattern(string $route): string {
        $route = preg_replace('~\{([^/]+)}~', '([^/]+)', $route);
        return '~^' . $route . '$~';
    }

    /**
     * Execute the route handler
     */
    private function executeHandler(string|array|callable $handler, array $params = []): bool {
        if (is_callable($handler)) {
            return $handler(...$params) !== false;
        } elseif (is_string($handler) && file_exists($handler)) {
            extract($params, EXTR_SKIP);
            include $handler;
            return true;
        } elseif (is_array($handler) && count($handler) === 2) {
            // Format: ['file.php', ['param1' => 'value1']]
            $file = $handler[0];
            $variables = $handler[1];

            // Replace placeholder values with actual parameters
            foreach ($variables as $key => $value) {
                if (is_string($value) && str_starts_with($value, '$')) {
                    $index = (int)substr($value, 1) - 1;
                    if (isset($params[$index])) {
                        $variables[$key] = $params[$index];
                    }
                }
            }

            extract($variables, EXTR_SKIP);
            require $file;
            return true;
        }
        return false;
    }
}

ob_start();

// Initialize router
$router = new Router();
// Register routes
// ----- Pages -----
$router->addRoute('GET', '', 'index.php')
    ->addRoute('GET', 'index', 'index.php')
    ->addRoute('GET', 'index.php', 'index.php')
    ->addRoute('GET', 'search', 'search.php')
    ->addRoute('GET', 'authors', 'authors.php')
    ->addRoute('GET', 'authors/{author}', ['author.php', ['author' => '$1']])
    ->addRoute('GET', 'authors/{author}/books', ['author_books.php', ['author' => '$1']])
    ->addRoute('GET', 'authors/{author}/books/{book}', ['book.php', ['author' => '$1', 'book' => '$2']]);

// ----- Auth & Account -----
$router->addRoute('GET', 'auth/login', 'auth/login.php')
    ->addRoute('POST', 'auth/login', 'auth/login.php')
    ->addRoute('GET', 'auth/register', 'auth/register.php')
    ->addRoute('POST', 'auth/register', 'auth/register.php')
    ->addRoute('GET', 'auth/logout', 'auth/logout.php')
    ->addRoute('ANY', 'account', 'auth/account.php');

// ----- API Routes -----
$router->addRoute('ANY', 'api/authors', function() {
    header('Content-Type: application/json');
    include 'api/authors.php';
})
    ->addRoute('ANY', 'api/books', function() {
        header('Content-Type: application/json');
        include 'api/books.php';
    });

// Set 404 handler
$router->setNotFoundHandler(function() {
    header("HTTP/1.0 404 Not Found");
    include '404.php';
});

// Handle the request
$router->handleRequest($_SERVER['REQUEST_METHOD'], $requestUri);