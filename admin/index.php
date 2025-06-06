<?php
// Admin main controller
session_start();
define('ADMIN_INCLUDED', true); // Define for included files

// Check if user is logged in and has admin/staff role
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['admin', 'staff'])) {
    header("Location: ../index.php?controller=user&action=login");
    exit;
}

// Define debug mode
define('DEBUG_MODE', true); // Set to false in production
$debug_logs = [];
$error_occurred = false;

// Track sidebar inclusion
if (!defined('SIDEBAR_INCLUDED')) {
    define('SIDEBAR_INCLUDED', true);
    $debug_logs[] = "Sidebar included in index.php";
} else {
    $debug_logs[] = "Warning: Sidebar inclusion attempted multiple times in index.php";
}

// Include database connection
require_once '../config/database.php';
try {
    $db = new Database();
    $conn = $db->getConnection();
    $conn->exec("SET NAMES utf8mb4");
    $debug_logs[] = "Database connection established successfully.";
} catch (Exception $e) {
    $error_occurred = true;
    $debug_logs[] = "Lỗi kết nối cơ sở dữ liệu: " . $e->getMessage();
    error_log("Lỗi kết nối cơ sở dữ liệu: " . $e->getMessage());
    die("Lỗi máy chủ nội bộ - Vui lòng kiểm tra nhật ký để biết chi tiết.");
}

// Get page parameter (default to dashboard)
$page = filter_input(INPUT_GET, 'page', FILTER_SANITIZE_STRING) ?: 'dashboard';

// Define allowed pages for all roles
$allowed_pages = [
    'dashboard', 'orders', 'products', 'product-edit', 'users', 'traffic',
    'banners', 'settings', 'promotions', 'reports', 'categories' // Added categories
];

// Restrict staff access
if ($_SESSION['user_role'] === 'staff') {
    $allowed_pages = array_intersect($allowed_pages, ['dashboard', 'orders', 'products', 'product-edit', 'traffic']);
}

// Validate the requested page
if (!in_array($page, $allowed_pages)) {
    $debug_logs[] = "Trang không hợp lệ được yêu cầu: $page, chuyển hướng về trang chủ";
    $page = 'dashboard';
}

// Log page access
$debug_logs[] = "Trang được truy cập: $page bởi user_role: {$_SESSION['user_role']}";

// Users page debug
if ($page === 'users' && $_SESSION['user_role'] !== 'admin') {
    $debug_logs[] = "Truy cập bị từ chối vào trang người dùng đối với user_role không phải admin: {$_SESSION['user_role']}";
    $_SESSION['error_message'] = "Bạn không có quyền truy cập trang này.";
    header("Location: index.php?page=dashboard");
    exit;
}

// Khởi tạo biến cho product-edit
$product = null;
if ($page == 'product-edit') {
    require_once '../models/Product.php';
    require_once '../models/Category.php';
    try {
        $product_model = new Product($conn);
        $product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

        if ($product_id > 0) {
            $product_model->id = $product_id;
            if (!$product_model->readOne()) {
                $_SESSION['error_message'] = "Không tìm thấy sản phẩm.";
                header("Location: index.php?page=products");
                exit;
            }
            $product = $product_model;
        } else {
            // Thêm mới sản phẩm
            $product = $product_model;
            $product->id = 0;
            $product->name = '';
            $product->price = 0;
            $product->sale_price = 0;
            $product->category_id = 0;
            $product->image = '';
        }

        // Lấy danh sách danh mục
        $category_model = new Category($conn);
        $categories = $category_model->read();
        $debug_logs[] = "Khởi tạo product-edit: ID=$product_id";
    } catch (Exception $e) {
        $error_occurred = true;
        $debug_logs[] = "Lỗi khởi tạo product-edit: " . $e->getMessage();
        error_log("Lỗi product-edit: " . $e->getMessage());
        $_SESSION['error_message'] = "Lỗi khi tải trang chỉnh sửa sản phẩm.";
        header("Location: index.php?page=products");
        exit;
    }
}

// Dashboard-specific logic
if ($page === 'dashboard') {
    try {
        require_once '../models/Order.php';
        require_once '../models/Product.php';
        require_once '../models/User.php';
        require_once '../models/TrafficLog.php';

        $order = new Order($conn);
        $product = new Product($conn);
        $user = new User($conn);
        $traffic = new TrafficLog($conn);

        // Get statistics
        $total_revenue = $order->getTotalRevenue() ?? 0;
        $order_count = $order->countAll() ?? 0;
        $product_count = $product->countAll() ?? 0;
        $user_count = $user->countAll() ?? 0;
        $total_visits = $traffic->getTotalVisits() ?? 0;

        // Get recent orders
        $recent_orders_stmt = $order->getRecentOrders(5);
        $recent_orders = [];
        while ($row = $recent_orders_stmt->fetch(PDO::FETCH_ASSOC)) {
            $recent_orders[] = $row;
        }

        // Get bestsellers
        $bestsellers_stmt = $product->getBestsellers(5);
        $bestsellers = [];
        while ($row = $bestsellers_stmt->fetch(PDO::FETCH_ASSOC)) {
            if (!empty($row['image']) && !file_exists($_SERVER['DOCUMENT_ROOT'] . $row['image'])) {
                $row['image'] = null;
            }
            $bestsellers[] = $row;
        }

        // Get traffic data (7 days)
        $traffic_stats = [];
        $traffic_labels = [];
        $traffic_data = [];
        $end_date = date('Y-m-d');
        $start_date = date('Y-m-d', strtotime('-7 days'));
        $traffic_stats = $traffic->getStatsRange($start_date, $end_date);
        if (empty($traffic_stats) && file_exists('../models/sample/traffic_data.php')) {
            require_once '../models/sample/traffic_data.php';
            $traffic_stats = array_slice(getSampleDailyTraffic(), -7);
        }
        foreach ($traffic_stats as $stat) {
            if (isset($stat['period']) && isset($stat['count'])) {
                $traffic_labels[] = date('d/m', strtotime($stat['period']));
                $traffic_data[] = (int)$stat['count'];
            }
        }
        if (empty($traffic_data)) {
            $traffic_labels = ['Không có dữ liệu'];
            $traffic_data = [0];
        }

        $traffic_labels_json = json_encode($traffic_labels, JSON_INVALID_UTF8_SUBSTITUTE);
        $traffic_data_json = json_encode($traffic_data, JSON_INVALID_UTF8_SUBSTITUTE);
        $debug_logs[] = "Dữ liệu trang chủ được tải thành công.";
    } catch (Exception $e) {
        $error_occurred = true;
        $debug_logs[] = "Lỗi tải dữ liệu trang chủ: " . $e->getMessage();
        error_log("Lỗi dữ liệu trang chủ: " . $e->getMessage());
    }
}

if (!defined('CURRENCY')) {
    define('CURRENCY', 'đ');
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bảng điều khiển quản trị</title>
    <link rel="icon" type="image/png" href="data:image/png;base64,iVBORw0KGgo=">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <?php if (in_array($page, ['dashboard', 'traffic'])): ?>
    <script defer src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
    <?php endif; ?>
</head>
<body class="d-flex">
    <!-- Sidebar -->
    <?php 
    $sidebar_included = include_once 'sidebar.php';
    if (!$sidebar_included) {
        $debug_logs[] = "Lỗi: Không thể bao gồm sidebar.php";
        error_log("Không thể bao gồm sidebar.php");
    }
    ?>

    <!-- Main Content -->
    <div class="flex-grow-1 p-4 bg-light">
        <div class="container-fluid">
            <?php
            // Display error message if set
            if (isset($_SESSION['error_message'])) {
                echo "<div class='alert alert-danger alert-dismissible fade show' role='alert'>";
                echo htmlspecialchars($_SESSION['error_message']);
                echo "<button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>";
                echo "</div>";
                unset($_SESSION['error_message']);
            }

            // Include the requested page
            $page_file = $page . '.php';
            if (file_exists($page_file)) {
                try {
                    ob_start();
                    include_once $page_file;
                    $page_content = ob_get_clean();
                    echo $page_content;
                    $debug_logs[] = "Đã tải trang: $page_file";
                } catch (Exception $e) {
                    $error_occurred = true;
                    $debug_logs[] = "Lỗi tải trang $page_file: " . $e->getMessage();
                    error_log("Lỗi tải trang $page_file: " . $e->getMessage());
                    echo "<div class='alert alert-danger'>Lỗi tải trang: " . htmlspecialchars($e->getMessage()) . "</div>";
                }
            } else {
                $debug_logs[] = "Không tìm thấy trang: $page_file";
                echo "<div class='alert alert-warning'>Trang <strong>" . htmlspecialchars($page) . "</strong> đang được phát triển.</div>";
            }
            ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <?php if ($page === 'dashboard'): ?>
    <script>
        window.addEventListener("load", function() {
            console.log("Kịch bản trang chủ bắt đầu tại <?php echo date('Y-m-d H:i:s'); ?>");
            if (!window.Chart) {
                console.error("Chart.js không được tải!");
                document.getElementById('trafficChartError').innerText = "Lỗi: Chart.js không tải được. Kiểm tra CDN hoặc mạng.";
                return;
            }
            const trafficCtx = document.getElementById('trafficChart');
            if (!trafficCtx) {
                console.error("Không tìm thấy canvas biểu đồ lượt truy cập!");
                document.getElementById('trafficChartError').innerText = "Lỗi: Không tìm thấy canvas biểu đồ lượt truy cập.";
                return;
            }
            const trafficContext = trafficCtx.getContext('2d');
            const trafficLabels = <?php echo $traffic_labels_json; ?>;
            const trafficData = <?php echo $traffic_data_json; ?>;
            if (!Array.isArray(trafficLabels) || !Array.isArray(trafficData)) {
                console.error("Định dạng dữ liệu không hợp lệ:", { trafficLabels, trafficData });
                document.getElementById('trafficChartError').innerText = "Lỗi: Định dạng dữ liệu không hợp lệ cho biểu đồ.";
                return;
            }
            if (trafficLabels.length !== trafficData.length) {
                console.error("Không khớp giữa độ dài nhãn và dữ liệu:", trafficLabels.length, trafficData.length);
                document.getElementById('trafficChartError').innerText = "Lỗi: Không khớp giữa độ dài nhãn và dữ liệu.";
                return;
            }
            const trafficGradient = trafficContext.createLinearGradient(0, 0, 0, 400);
            trafficGradient.addColorStop(0, 'rgba(78, 115, 223, 0.8)');
            trafficGradient.addColorStop(1, 'rgba(78, 115, 223, 0.1)');
            try {
                new Chart(trafficContext, {
                    type: 'line',
                    data: {
                        labels: trafficLabels,
                        datasets: [{
                            label: 'Lượt truy cập',
                            data: trafficData,
                            backgroundColor: trafficGradient,
                            borderColor: 'rgba(78, 115, 223, 1)',
                            borderWidth: 3,
                            pointBackgroundColor: 'rgba(78, 115, 223, 1)',
                            pointBorderColor: '#fff',
                            pointHoverRadius: 6,
                            pointHoverBackgroundColor: 'rgba(78, 115, 223, 1)',
                            pointHoverBorderColor: '#fff',
                            pointHitRadius: 10,
                            tension: 0.3,
                            fill: true
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: { beginAtZero: false, ticks: { precision: 0, stepSize: 1 }, title: { display: true, text: 'Số lượt truy cập', font: { size: 14 } } },
                            x: { ticks: { font: { size: 12 } }, title: { display: true, text: 'Ngày', font: { size: 14 } } }
                        },
                        plugins: {
                            tooltip: {
                                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                titleFont: { size: 14 },
                                bodyFont: { size: 12 },
                                callbacks: {
                                    title: function(tooltipItems) { return tooltipItems[0].label; },
                                    label: function(context) { return `Lượt truy cập: ${context.parsed.y.toLocaleString()}`; }
                                }
                            },
                            legend: { display: true, position: 'top', labels: { font: { size: 14 } } }
                        },
                        interaction: { intersect: false, mode: 'index' }
                    }
                });
                console.log("Biểu đồ lượt truy cập được khởi tạo thành công.");
            } catch (error) {
                console.error("Lỗi khởi tạo biểu đồ lượt truy cập:", error.message);
                document.getElementById('trafficChartError').innerText = "Lỗi khởi tạo biểu đồ: " + error.message;
            }
        });
    </script>
    <?php endif; ?>

    <style>
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e7eb 100%);
            color: #333;
        }
        .container-fluid {
            padding: 30px;
            max-width: 1400px;
        }
        .card {
            border-radius: 12px;
            background: #fff;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .card:hover {
            transform: translateY(-8px);
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
        }
        .debug-info {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            font-size: 0.9rem;
            border: 1px solid #e9ecef;
            display: <?php echo DEBUG_MODE ? 'block' : 'none'; ?>;
        }
        .debug-info ul {
            list-style: none;
            padding: 0;
        }
        .debug-info li {
            margin-bottom: 5px;
            color: #495057;
        }
        @media (max-width: 992px) {
            .sidebar {
                width: 100%;
                position: relative;
            }
        }
        @media (max-width: 576px) {
            .container-fluid {
                padding: 20px;
            }
        }
    </style>
</body>
</html>