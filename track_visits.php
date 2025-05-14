<?php
/**
 * Track Visits - File để thêm vào index.php để theo dõi lượt truy cập
 * File này nên được include ở đầu index.php
 */

// Đảm bảo session đã bắt đầu
if (!isset($_SESSION)) {
    session_start();
}

// Include autoload và database connection nếu chưa được include
if (!isset($conn)) {
    include_once 'config/database.php';
    
    // Khởi tạo kết nối database
    try {
        $db = new Database();
        $conn = $db->getConnection();
    } catch (Exception $e) {
        // Nếu không thể kết nối, ghi log lỗi nhưng không làm crash trang
        error_log("Database connection error in track_visits.php: " . $e->getMessage());
        return;
    }
}

// Đảm bảo class TrafficLog được load
if (!class_exists('TrafficLog')) {
    if (file_exists('models/TrafficLog.php')) {
        include_once 'models/TrafficLog.php';
    } else {
        error_log("TrafficLog model not found");
        return;
    }
}

// Bỏ qua các request đến khu vực admin
if (isset($_GET['controller']) && $_GET['controller'] == 'admin') {
    return;
}

// Bỏ qua các request đến các file tài nguyên (js, css, images)
$request_uri = $_SERVER['REQUEST_URI'] ?? '';
if (preg_match('/\.(js|css|png|jpg|jpeg|gif|svg|ico|woff|woff2|ttf|otf)$/i', $request_uri)) {
    return;
}

// Khởi tạo đối tượng TrafficLog và ghi log
try {
    $traffic = new TrafficLog($conn);
    $traffic->logAccess();
} catch (Exception $e) {
    error_log("Error tracking visit: " . $e->getMessage());
}
?>