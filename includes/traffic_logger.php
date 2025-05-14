<?php
/**
 * Traffic Logger - Ghi log truy cập website
 * File này được include trong index.php để ghi lại mỗi lượt truy cập
 */

// Kiểm tra nếu đã tồn tại session
if (!isset($_SESSION)) {
    session_start();
}

// Bỏ qua các request tới file tài nguyên (CSS, JS, hình ảnh)
$request_uri = $_SERVER['REQUEST_URI'] ?? '';
if (preg_match('/\.(js|css|png|jpg|jpeg|gif|svg|ico|woff|woff2|ttf|otf)$/i', $request_uri)) {
    return;
}

// Bỏ qua các request đến khu vực admin
if (isset($_GET['controller']) && $_GET['controller'] == 'admin') {
    return;
}

// Kiểm tra kết nối cơ sở dữ liệu và khởi tạo nếu cần thiết
if (!isset($conn)) {
    try {
        include_once dirname(__FILE__) . '/../config/database.php';
        $db = new Database();
        $conn = $db->getConnection();
    } catch (Exception $e) {
        // Log lỗi nhưng không làm crash trang
        error_log("Database connection error in traffic_logger.php: " . $e->getMessage());
        return;
    }
}

// Kiểm tra class TrafficLog
if (!class_exists('TrafficLog')) {
    try {
        include_once dirname(__FILE__) . '/../models/TrafficLog.php';
    } catch (Exception $e) {
        error_log("Error loading TrafficLog class: " . $e->getMessage());
        return;
    }
}

// Log truy cập
try {
    // Tạo đối tượng TrafficLog
    $traffic = new TrafficLog($conn);
    
    // Chỉ ghi log cho các lượt truy cập thực (không phải bot)
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    if (!preg_match('/(bot|crawl|spider|slurp|mediapartners|facebookexternalhit)/i', $user_agent)) {
        $traffic->logAccess();
    }
} catch (Exception $e) {
    error_log("Error in traffic_logger.php: " . $e->getMessage());
}
?>