<?php
/**
 * Track Visits - File để thêm vào index.php để theo dõi lượt truy cập
 * File này nên được include sau khi $conn được định nghĩa
 */

// Đảm bảo session đã bắt đầu
if (session_status() == PHP_SESSION_NONE) {
    session_start();
    session_regenerate_id(true); // Đảm bảo session_id mới cho mỗi truy cập
}

// Sử dụng biến $conn đã được định nghĩa trong index.php
global $conn;
if (!$conn) {
    error_log("Database connection not available in track_visits.php at " . date('Y-m-d H:i:s'));
    return;
}

// Đảm bảo class TrafficLog được load
if (!class_exists('TrafficLog')) {
    if (file_exists('models/TrafficLog.php')) {
        include_once 'models/TrafficLog.php';
    } else {
        error_log("TrafficLog model not found at " . date('Y-m-d H:i:s'));
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
    error_log("Attempting to log access: URI=$request_uri, session_id=" . session_id());
    $traffic = new TrafficLog($conn);
    $result = $traffic->logAccess();
    if ($result) {
        error_log("Successfully logged access for session_id: " . session_id() . " at " . date('Y-m-d H:i:s'));
    } else {
        error_log("Failed to log access for session_id: " . session_id() . " at " . date('Y-m-d H:i:s'));
    }
} catch (Exception $e) {
    error_log("Error tracking visit: " . $e->getMessage() . " at " . date('Y-m-d H:i:s'));
}
?>