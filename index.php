<?php

ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');
error_reporting(E_ALL);

// Cấu hình ứng dụng
define('SITE_NAME', 'Babee - Thời trang cho bé');
define('SITE_DESCRIPTION', 'Thời trang đường phố dành cho giới trẻ - Bold & Colorful');
define('CURRENCY', '₫');
define('ADMIN_EMAIL', 'babeemoonstore@gmail.com');

// Load cấu hình
require_once 'config/config.php';
require_once 'config/database.php';

// Kiểm tra kết nối
try {
    $db = new Database();
    $conn = $db->getConnection();
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

// Ghi log truy cập
include_once 'track_visits.php';

// Xử lý routing
$controller = isset($_GET['controller']) ? $_GET['controller'] : 'home';
$action = isset($_GET['action']) ? $_GET['action'] : 'index';

// Danh sách các controller/action trả về JSON (AJAX)
$jsonActions = [
    'feedback' => ['submit', 'update', 'delete'],
    'user' => ['updateCart', 'submitOrder'] // Thêm các action khác nếu cần
];

// Ánh xạ action đặc biệt
$actionMap = [
    'feedback' => [
        'submit' => 'submitFeedback',
        'update' => 'updateFeedback',
        'delete' => 'deleteFeedback'
    ]
];

$controller_name = ucfirst($controller) . 'Controller';
$controller_file = "controllers/{$controller_name}.php";

if (file_exists($controller_file)) {
    require_once $controller_file;
    $controller_instance = new $controller_name($conn);

    $method = $action;
    if (isset($actionMap[$controller]) && isset($actionMap[$controller][$action])) {
        $method = $actionMap[$controller][$action];
    }

    if (method_exists($controller_instance, $method)) {
        // Kiểm tra xem action có trả về JSON không
        $isJsonResponse = isset($jsonActions[$controller]) && in_array($action, $jsonActions[$controller]);

        ob_start();
        if ($isJsonResponse) {
            header('Content-Type: application/json; charset=UTF-8');
            $result = $controller_instance->$method($_POST, $_FILES);
            echo is_string($result) ? $result : json_encode($result);
        } else {
            // Trả về HTML (giao diện)
            $controller_instance->$method();
        }
        $output = ob_get_clean();
        if (!empty($output)) {
            echo $output;
        }
    } else {
        if (isset($jsonActions[$controller]) && in_array($action, $jsonActions[$controller])) {
            http_response_code(404);
            header('Content-Type: application/json; charset=UTF-8');
            echo json_encode(['success' => false, 'message' => 'Action không tồn tại.']);
        } else {
            include 'views/404.php';
        }
    }
} else {
    if (isset($jsonActions[$controller]) && in_array($action, $jsonActions[$controller])) {
        http_response_code(404);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(['success' => false, 'message' => 'Controller không tồn tại.']);
    } else {
        include 'views/404.php';
    }
}
exit();
?>