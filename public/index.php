<?php
declare(strict_types=1);

session_start();

// 1. Native Autoloader (PSR-4 compliant structure)
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = __DIR__ . '/../src/';
    
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    
    if (file_exists($file)) {
        require $file;
    }
});

// 2. Configuration & Bootstrapping
$configFile = __DIR__ . '/../config/database.php';

if (!file_exists($configFile)) {
    if (file_exists(__DIR__ . '/setup.php')) {
        header('Location: /setup.php');
        exit;
    }
    die('Configuration missing. Please deploy setup.php.');
}

require_once $configFile;

try {
    $db = getDatabaseConnection();
} catch (Throwable $e) {
    http_response_code(500);
    error_log('Boot failure: ' . $e->getMessage());
    die('Database connection failed.');
}

// 3. Dependency Injection & Instantiation
$router = new \App\Router();
$authController = new \App\Controllers\AuthController($db);
$dashboardController = new \App\Controllers\DashboardController($db);

// Existing dependency injection...
$groceryController = new \App\Controllers\GroceryController($db);

// Register New Routes
$router->add('GET', '/grocery/new', [$groceryController, 'create'], true);
$router->add('POST', '/grocery/new', [$groceryController, 'store'], true);

// Existing dependency injection...
$userController = new \App\Controllers\UserController($db);
$calendarController = new \App\Controllers\CalendarController($db);

// New Routes
$router->add('GET', '/users/new', [$userController, 'create'], true);
$router->add('POST', '/users/new', [$userController, 'store'], true);

$router->add('GET', '/events/new', [$calendarController, 'create'], true);
$router->add('POST', '/events/new', [$calendarController, 'store'], true);

// 4. Route Wiring
$router->add('GET', '/login', [$authController, 'showLogin'], false);
$router->add('POST', '/login', [$authController, 'processLogin'], false);
$router->add('GET', '/logout', [$authController, 'logout'], false);
$router->add('GET', '/dashboard', [$dashboardController, 'index'], true);

// Root redirect
$router->add('GET', '/', function() {
    header('Location: /dashboard');
    exit;
}, false);

// 5. Dispatch
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];
$router->dispatch($uri, $method);