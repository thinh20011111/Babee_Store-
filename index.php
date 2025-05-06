<?php
// Main entry point for the application
session_start();

// Include configuration files
require_once 'config/config.php';
require_once 'config/database.php';

// Autoload models and controllers
spl_autoload_register(function ($class_name) {
    // Check if it's a controller
    if (strpos($class_name, 'Controller') !== false) {
        $filename = 'controllers/' . $class_name . '.php';
        if (file_exists($filename)) {
            require_once $filename;
            return true;
        }
    }
    
    // Check if it's a model
    $filename = 'models/' . $class_name . '.php';
    if (file_exists($filename)) {
        require_once $filename;
        return true;
    }
    
    return false;
});

// Database connection
$db = new Database();
$conn = $db->getConnection();

// Routing
$controller = isset($_GET['controller']) ? $_GET['controller'] : 'home';
$action = isset($_GET['action']) ? $_GET['action'] : 'index';

// Sanitize input parameters
$controller = filter_var($controller, FILTER_SANITIZE_STRING);
$action = filter_var($action, FILTER_SANITIZE_STRING);

// Convert to PascalCase for controller class name
$controllerClass = ucfirst(strtolower($controller)) . 'Controller';

// Check if controller exists
if (class_exists($controllerClass)) {
    $controllerInstance = new $controllerClass($conn);
    
    // Check if action exists
    if (method_exists($controllerInstance, $action)) {
        // Call the action
        $controllerInstance->$action();
    } else {
        // Action not found - show 404
        header("HTTP/1.0 404 Not Found");
        include('views/404.php');
    }
} else {
    // Controller not found - show 404
    header("HTTP/1.0 404 Not Found");
    include('views/404.php');
}
?>
