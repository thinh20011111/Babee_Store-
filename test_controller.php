<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error_log.txt');

// Thiết lập autoloader
spl_autoload_register(function ($class_name) {
    $filename = '';
    if (strpos($class_name, 'Controller') !== false) {
        $filename = 'controllers/' . $class_name . '.php';
    } else {
        $filename = 'models/' . $class_name . '.php';
    }
    error_log("Autoloader: Trying to load $filename");
    if (file_exists($filename)) {
        error_log("Autoloader: Loading $filename");
        require_once $filename;
        return true;
    }
    error_log("Autoloader: File not found $filename");
    return false;
});

try {
    // Test class existence
    $required_classes = ['Product', 'Banner', 'Category', 'Settings'];
    foreach ($required_classes as $class) {
        if (!class_exists($class)) {
            error_log("Class not found: $class");
            throw new Exception("Class $class not found.");
        }
        error_log("Class found: $class");
    }

    // Load database
    require_once 'config/database.php';
    error_log("Loaded config/database.php");

    $db = new Database();
    $conn = $db->getConnection();
    error_log("Database connected");

    // Tạo HomeController
    $controller = new HomeController($conn);
    error_log("HomeController instantiated");

    // Gọi index()
    $controller->index();
    error_log("Called HomeController->index()");
    echo "Test completed!";
} catch (Exception $e) {
    error_log("Error in test_controller.php: " . $e->getMessage());
    echo "Error: " . $e->getMessage() . "<br>";
    echo "Test completed!";
}
?>