<?php
ob_start();
/**
 * Help Desk LAN - Front Controller Entry Point
 */

// Bypass PHP built-in server routing for static files
if (php_sapi_name() === 'cli-server') {
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    if (file_exists(__DIR__ . $path) && is_file(__DIR__ . $path)) {
        return false;
    }
}

// 1. Core PSR-4 Autoloader Block
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = __DIR__ . '/../app/';

    // Check if class uses prefix
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

// 2. Installation Check
$configFile = __DIR__ . '/../config/config.php';
if (file_exists($configFile)) {
    require_once $configFile;
} else if (getenv('DB_HOST') !== false) {
    // Load database settings securely from environment variables (Cloud production)
    define('DB_HOST', getenv('DB_HOST'));
    define('DB_PORT', getenv('DB_PORT') ?: '3306');
    define('DB_NAME', getenv('DB_NAME') ?: 'defaultdb');
    define('DB_USER', getenv('DB_USER'));
    define('DB_PASS', getenv('DB_PASS'));
    define('APP_KEY', getenv('APP_KEY') ?: 'bd183705ddb697b843bbc70a85f871314c4d8e7c7423a3d860e6db6a7a05e5d7');
    define('UPLOAD_DIR', __DIR__ . '/../public/uploads');
    define('LOG_DIR', __DIR__ . '/../logs');
    define('DEBUG_MODE', false);
} else {
    // If not installed, redirect to install wizard
    if (basename($_SERVER['PHP_SELF']) !== 'install.php') {
        header('Location: install.php');
        exit;
    }
}

// 3. Initialize Router App
use App\Core\App;
use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\TicketController;
use App\Controllers\UserController;
use App\Controllers\DepartmentController;
use App\Controllers\SettingsController;
use App\Controllers\ReportController;

$app = new App();

// 4. Register Authentication Routes
$app->get('/login', [AuthController::class, 'showLogin']);
$app->post('/login', [AuthController::class, 'login']);
$app->get('/logout', [AuthController::class, 'logout']);
$app->get('/recover', [AuthController::class, 'showRecover']);
$app->post('/recover', [AuthController::class, 'recover']);

// 5. Register Dashboard Routing
$app->get('/', [DashboardController::class, 'index']);
$app->get('/dashboard', [DashboardController::class, 'index']);

// 6. Register Ticket Management Routes
$app->get('/tickets', [TicketController::class, 'index']);
$app->get('/my-tickets', [TicketController::class, 'myTickets']);
$app->get('/tickets/create', [TicketController::class, 'create']);
$app->post('/tickets/create', [TicketController::class, 'store']);
$app->get('/tickets/view/{id}', [TicketController::class, 'view']);
$app->post('/tickets/comment/{id}', [TicketController::class, 'addComment']);
$app->post('/tickets/assign/{id}', [TicketController::class, 'assign']);
$app->post('/tickets/status/{id}', [TicketController::class, 'changeStatus']);
$app->post('/tickets/priority/{id}', [TicketController::class, 'changePriority']);
$app->post('/tickets/category/{id}', [TicketController::class, 'changeCategory']);
$app->post('/tickets/update-properties/{id}', [TicketController::class, 'updateProperties']);
$app->post('/tickets/time/{id}', [TicketController::class, 'updateTimeSpent']);
$app->post('/tickets/delete/{id}', [TicketController::class, 'delete']);
$app->post('/comments/edit/{id}', [TicketController::class, 'editComment']);
$app->post('/comments/delete/{id}', [TicketController::class, 'deleteComment']);

// 7. Register User and Technician Management Routes
$app->get('/users', [UserController::class, 'index']);
$app->get('/users/create', [UserController::class, 'create']);
$app->post('/users/create', [UserController::class, 'store']);
$app->get('/users/edit/{id}', [UserController::class, 'edit']);
$app->post('/users/edit/{id}', [UserController::class, 'update']);
$app->post('/users/toggle/{id}', [UserController::class, 'toggleStatus']);
$app->get('/technicians', [UserController::class, 'techniciansList']);
$app->post('/technicians/specialty/{id}', [UserController::class, 'updateSpecialty']);

// 8. Register Department / Category / Priority Management Routes
$app->get('/departments', [DepartmentController::class, 'index']);
$app->post('/departments/create', [DepartmentController::class, 'store']);
$app->post('/departments/edit/{id}', [DepartmentController::class, 'update']);
$app->post('/departments/delete/{id}', [DepartmentController::class, 'delete']);
$app->get('/categories', [DepartmentController::class, 'categories']);
$app->post('/categories/create', [DepartmentController::class, 'storeCategory']);
$app->post('/categories/edit/{id}', [DepartmentController::class, 'updateCategory']);
$app->post('/categories/delete/{id}', [DepartmentController::class, 'deleteCategory']);

// 9. Register System Custom Settings
$app->get('/settings', [SettingsController::class, 'index']);
$app->post('/settings/save', [SettingsController::class, 'save']);
$app->post('/settings/logo', [SettingsController::class, 'uploadLogo']);
$app->post('/notifications/read/{id}', [DashboardController::class, 'markNotificationRead']);

// 10. Register Reports and Audit Trail Routes
$app->get('/reports', [ReportController::class, 'index']);
$app->post('/reports/export', [ReportController::class, 'export']);
$app->get('/audit', [ReportController::class, 'auditLogs']);

// Run the application dispatcher
$app->run();
