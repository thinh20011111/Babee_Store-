<?php
// Cấu hình ứng dụng
define('SITE_NAME', 'StreetStyle');
define('SITE_DESCRIPTION', 'Thời trang đường phố dành cho giới trẻ - Bold & Colorful');
define('CURRENCY', '₫');
define('ADMIN_EMAIL', 'contact@streetstyle.com');

// Load cấu hình
require_once 'config/config.php';
require_once 'config/database.php';

// Kiểm tra kết nối
try {
    $db = new Database(); // Create an instance of the Database class
    $conn = $db->getConnection(); // Call the getConnection method on the instance
} catch (PDOException $e) {
    die("Database Connection failed: " . $e->getMessage());
}

// Khởi tạo session
session_start();

// Tự động load các class
spl_autoload_register(function ($class_name) {
    if (file_exists('models/' . $class_name . '.php')) {
        require_once 'models/' . $class_name . '.php';
    }
});

// Xử lý routing
$controller = isset($_GET['controller']) ? $_GET['controller'] : 'home';
$action = isset($_GET['action']) ? $_GET['action'] : 'index';

// Load controller tương ứng
$controller_name = ucfirst($controller) . 'Controller';
$controller_file = "controllers/{$controller_name}.php";

if (file_exists($controller_file)) {
    require_once $controller_file;
    $controller_instance = new $controller_name($conn);
    
    // Gọi action tương ứng
    if (method_exists($controller_instance, $action)) {
        $controller_instance->$action();
    } else {
        // Action không tồn tại -> 404
        include 'views/404.php';
    }
} else {
    // Controller không tồn tại -> 404
    include 'views/404.php';
}