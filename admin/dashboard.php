<?php
// Dashboard page
if (!defined('ADMIN_INCLUDED')) {
    define('ADMIN_INCLUDED', true);
}

// Define debug mode
define('DEBUG_MODE', true); // Set to false in production

// Initialize debug log
$debug_logs = [];
$error_occurred = false;

// Include database connection with error handling
require_once '../config/database.php';
try {
    $db = new Database();
    $conn = $db->getConnection();
    $conn->exec("SET NAMES utf8mb4");
    $debug_logs[] = "Database connection established successfully.";
} catch (Exception $e) {
    $error_occurred = true;
    $debug_logs[] = "Database connection error: " . $e->getMessage();
    error_log("Database connection error: " . $e->getMessage());
    die("Internal Server Error - Check logs for details.");
}

// Include models with error handling
$required_files = [
    '../models/Order.php',
    '../models/Product.php',
    '../models/User.php',
    '../models/Promotion.php',
    '../models/TrafficLog.php'
];
foreach ($required_files as $file) {
    if (!file_exists($file)) {
        $error_occurred = true;
        $debug_logs[] = "Required file not found: $file";
        error_log("Required file not found: $file");
        die("Internal Server Error - Missing file: $file");
    }
    try {
        require_once $file;
        $debug_logs[] = "Successfully included: $file";
    } catch (Exception $e) {
        $error_occurred = true;
        $debug_logs[] = "Error including $file: " . $e->getMessage();
        error_log("Error including $file: " . $e->getMessage());
        die("Internal Server Error - Check logs for details.");
    }
}

// Initialize objects
try {
    $order = new Order($conn);
    $product = new Product($conn);
    $user = new User($conn);
    $promotion = new Promotion($conn);
    $traffic = new TrafficLog($conn);
    $debug_logs[] = "Objects initialized successfully.";
} catch (Exception $e) {
    $error_occurred = true;
    $debug_logs[] = "Error initializing objects: " . $e->getMessage();
    error_log("Error initializing objects: " . $e->getMessage());
    die("Internal Server Error - Check logs for details.");
}

// Get statistics with debug
try {
    $total_revenue = $order->getTotalRevenue() ?? 0;
    $order_count = $order->countAll() ?? 0;
    $product_count = $product->countAll() ?? 0;
    $user_count = $user->countAll() ?? 0;
    
    // Lấy thống kê truy cập
    $total_visits = $traffic->getTotalVisits() ?? 0;
    $today_visits = $traffic->getTodayVisits() ?? 0;
    
    $debug_logs[] = "Statistics fetched: Revenue=$total_revenue, Orders=$order_count, Products=$product_count, Users=$user_count, Total Visits=$total_visits, Today Visits=$today_visits";
} catch (Exception $e) {
    $error_occurred = true;
    $debug_logs[] = "Error fetching statistics: " . $e->getMessage();
    error_log("Dashboard statistics error: " . $e->getMessage());
}

// Get recent orders
try {
    $recent_orders_stmt = $order->getRecentOrders(5);
    $recent_orders = [];
    while ($row = $recent_orders_stmt->fetch(PDO::FETCH_ASSOC)) {
        $recent_orders[] = $row;
    }
    $debug_logs[] = "Recent orders fetched: " . count($recent_orders) . " records";
} catch (Exception $e) {
    $error_occurred = true;
    $debug_logs[] = "Error fetching recent orders: " . $e->getMessage();
    error_log("Recent orders error: " . $e->getMessage());
}

// Get bestsellers with image validation
try {
    $bestsellers_stmt = $product->getBestsellers(5);
    $bestsellers = [];
    while ($row = $bestsellers_stmt->fetch(PDO::FETCH_ASSOC)) {
        if (!empty($row['image']) && !file_exists($_SERVER['DOCUMENT_ROOT'] . $row['image'])) {
            $debug_logs[] = "Invalid image for product ID {$row['id']}: {$row['image']}";
            $row['image'] = null;
        }
        $bestsellers[] = $row;
    }
    $debug_logs[] = "Bestsellers fetched: " . count($bestsellers) . " records";
} catch (Exception $e) {
    $error_occurred = true;
    $debug_logs[] = "Error fetching bestsellers: " . $e->getMessage();
    error_log("Bestsellers error: " . $e->getMessage());
}

// Get monthly revenue data
$monthly_revenue_data = array_fill(0, 12, 0);
$monthly_revenue_labels = [];
$today = new DateTime();
$today->modify('first day of this month');
for ($i = 11; $i >= 0; $i--) {
    $date = (clone $today)->modify("-$i months");
    $monthly_revenue_labels[] = $date->format('M Y');
}

try {
    $monthly_revenue_stmt = $order->getMonthlyRevenue();
    $raw_revenue_data = [];
    while ($row = $monthly_revenue_stmt->fetch(PDO::FETCH_ASSOC)) {
        $raw_revenue_data[] = $row;
        try {
            $year = intval($row['year']);
            $month = intval($row['month']);
            $date_string = $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT) . '-01';
            $date = DateTime::createFromFormat('Y-m-d', $date_string);
            
            if ($date === false) {
                $errors = DateTime::getLastErrors();
                $error_msg = "Invalid date format for year: $year, month: $month, date string: $date_string";
                if ($errors) {
                    $error_msg .= " - DateTime errors: " . json_encode($errors);
                }
                $debug_logs[] = $error_msg;
                error_log($error_msg);
                continue;
            }
            
            $month_year = $date->format('M Y');
            $index = array_search($month_year, $monthly_revenue_labels);
            if ($index !== false) {
                $monthly_revenue_data[$index] = (float)($row['revenue'] ?? 0);
                $debug_logs[] = "Mapped revenue $month_year to index $index: {$monthly_revenue_data[$index]}";
            } else {
                $debug_logs[] = "No match for month_year: $month_year in labels";
            }
        } catch (Exception $e) {
            $debug_logs[] = "Error processing revenue data: " . $e->getMessage();
            error_log("Revenue processing error: " . $e->getMessage());
        }
    }
} catch (Exception $e) {
    $error_occurred = true;
    $debug_logs[] = "Error fetching monthly revenue: " . $e->getMessage();
    error_log("Monthly revenue error: " . $e->getMessage());
}

// Lấy dữ liệu truy cập theo ngày (7 ngày gần nhất)
$traffic_stats = [];
$traffic_labels = [];
$traffic_data = [];

try {
    $end_date = date('Y-m-d');
    $start_date = date('Y-m-d', strtotime('-7 days'));
    
    $traffic_stats = $traffic->getStatsRange($start_date, $end_date);
    
    $debug_logs[] = "Raw traffic stats from DB: " . json_encode($traffic_stats);
    
    if (empty($traffic_stats)) {
        $debug_logs[] = "No traffic data found in DB for range $start_date to $end_date";
        if (file_exists('../models/sample/traffic_data.php')) {
            require_once '../models/sample/traffic_data.php';
            $sample_data = getSampleDailyTraffic();
            $traffic_stats = array_slice($sample_data, -7);
            $debug_logs[] = "Using sample traffic data: " . json_encode($traffic_stats);
        } else {
            $debug_logs[] = "Sample traffic data file not found at ../models/sample/traffic_data.php";
            $current_date = new DateTime($start_date);
            $end = new DateTime($end_date);
            $interval_obj = new DateInterval('P1D');
            $date_range = new DatePeriod($current_date, $interval_obj, $end->modify('+1 day'));
            foreach ($date_range as $date) {
                $traffic_stats[] = ['period' => $date->format('Y-m-d'), 'count' => 0];
            }
            $debug_logs[] = "Generated default traffic data: " . json_encode($traffic_stats);
        }
    }
    
    foreach ($traffic_stats as $stat) {
        if (isset($stat['period']) && isset($stat['count']) && strtotime($stat['period']) !== false) {
            $traffic_labels[] = date('d/m', strtotime($stat['period']));
            $traffic_data[] = (int)$stat['count'];
            $debug_logs[] = "Processed traffic stat: Period={$stat['period']}, Count={$stat['count']}";
        } else {
            $debug_logs[] = "Invalid traffic stat entry: " . json_encode($stat);
        }
    }
    
    $debug_logs[] = "Traffic labels: " . json_encode($traffic_labels);
    $debug_logs[] = "Traffic data: " . json_encode($traffic_data);
} catch (Exception $e) {
    $debug_logs[] = "Error fetching traffic data: " . $e->getMessage();
    error_log("Traffic data error: " . $e->getMessage());
    $traffic_labels = ['No Data'];
    $traffic_data = [0];
}

if (empty($traffic_data) || empty($traffic_labels)) {
    $traffic_labels = ['No Data'];
    $traffic_data = [0];
    $debug_logs[] = "No valid traffic data, setting default to 'No Data'";
}

try {
    $traffic_labels_json = json_encode($traffic_labels, JSON_INVALID_UTF8_SUBSTITUTE);
    $traffic_data_json = json_encode($traffic_data, JSON_INVALID_UTF8_SUBSTITUTE);
    $monthly_revenue_labels_json = json_encode($monthly_revenue_labels, JSON_INVALID_UTF8_SUBSTITUTE);
    $monthly_revenue_data_json = json_encode($monthly_revenue_data, JSON_INVALID_UTF8_SUBSTITUTE);
    $debug_logs[] = "JSON encoding successful.";
} catch (Exception $e) {
    $error_occurred = true;
    $debug_logs[] = "Error encoding JSON: " . $e->getMessage();
    error_log("JSON encoding error: " . $e->getMessage());
    $traffic_labels_json = json_encode(['Error']);
    $traffic_data_json = json_encode([0]);
    $monthly_revenue_labels_json = json_encode(['Error']);
    $monthly_revenue_data_json = json_encode([0]);
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
    <title>Admin Dashboard</title>
    <link rel="icon" type="image/png" href="data:image/png;base64,iVBORw0KGgo=">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            background: linear-gradient(135deg, #f4f6f9 0%, #e9ecef 100%);
            font-family: 'Segoe UI', Arial, sans-serif;
        }
        .content-wrapper {
            margin-left: 250px;
            padding: 20px;
            min-height: 100vh;
        }
        .card {
            border: none;
            border-radius: 12px;
            background: #fff;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .card:hover {
            transform: translateY(-8px);
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
        }
        .card-header {
            background: linear-gradient(90deg, #007bff, #0056b3);
            color: #fff;
            border-radius: 12px 12px 0 0;
            padding: 15px 20px;
        }
        .card-header h6 {
            margin: 0;
            font-weight: 600;
        }
        .card-body {
            padding: 20px;
        }
        .stat-card {
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            border-left: 4px solid #007bff;
        }
        .stat-card i {
            font-size: 2.5rem;
            color: #007bff;
        }
        .stat-card h6 {
            font-size: 0.9rem;
            color: #6c757d;
        }
        .stat-card h4 {
            font-weight: 700;
            color: #343a40;
        }
        .chart-container {
            position: relative;
            height: 350px;
            background: #fff;
            border-radius: 10px;
            padding: 15px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            transition: box-shadow 0.3s ease;
        }
        .chart-container:hover {
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.15);
        }
        .chart-header {
            background: #007bff;
            color: #fff;
            border-radius: 10px 10px 0 0;
            padding: 10px 15px;
            margin: -15px -15px 15px -15px;
        }
        .chart-footer {
            text-align: center;
            font-size: 0.9rem;
            color: #6c757d;
            margin-top: 10px;
        }
        .table {
            background: #fff;
            border-radius: 10px;
            overflow: hidden;
        }
        .table thead {
            background: #343a40;
            color: #fff;
        }
        .table tbody tr {
            transition: background 0.2s ease;
        }
        .table tbody tr:hover {
            background: #f1f3f5;
        }
        .list-group-item {
            border: none;
            border-radius: 8px;
            margin-bottom: 10px;
            background: #f8f9fa;
            transition: background 0.2s ease;
        }
        .list-group-item:hover {
            background: #e9ecef;
        }
        .bestseller-img {
            width: 60px;
            height: 60px;
            border-radius: 8px;
            border: 1px solid #dee2e6;
            object-fit: cover;
        }
        .bestseller-img-placeholder {
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f1f3f5;
            border-radius: 8px;
            border: 1px solid #dee2e6;
        }
        .badge {
            padding: 8px 12px;
            font-size: 0.85rem;
        }
        .progress {
            height: 15px;
            border-radius: 8px;
        }
        .progress-bar {
            border-radius: 8px;
        }
        .debug-info {
            background: #fff;
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            margin-top: 20px;
            font-size: 0.9rem;
            display: <?php echo DEBUG_MODE ? 'block' : 'none'; ?>;
        }
        .error-message {
            color: #dc3545;
            font-weight: 600;
            margin-top: 10px;
        }
        @media (max-width: 992px) {
            .content-wrapper {
                margin-left: 0;
            }
            .stat-card {
                text-align: center;
            }
            .stat-card i {
                margin-bottom: 10px;
            }
            .chart-container {
                height: 300px;
            }
        }
    </style>
</head>
<body>
    <div class="content-wrapper">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0 fw-bold text-primary">Dashboard</h1>
                <button class="btn btn-primary" onclick="refreshDashboard()">
                    <i class="fas fa-sync-alt me-2"></i> Cập nhật Dữ liệu
                </button>
            </div>

            <!-- Statistics Cards -->
            <div class="row g-4 mb-4">
                <div class="col-md-6 col-lg-3">
                    <div class="card stat-card shadow-sm">
                        <div class="card-body d-flex align-items-center">
                            <i class="fas fa-money-bill-wave me-3"></i>
                            <div>
                                <h6 class="text-uppercase">Tổng Doanh thu</h6>
                                <h4 class="mb-0"><?php echo CURRENCY . number_format($total_revenue); ?></h4>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="card stat-card shadow-sm">
                        <div class="card-body d-flex align-items-center">
                            <i class="fas fa-shopping-cart me-3"></i>
                            <div>
                                <h6 class="text-uppercase">Tổng Đơn hàng</h6>
                                <h4 class="mb-0"><?php echo number_format($order_count); ?></h4>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="card stat-card shadow-sm">
                        <div class="card-body d-flex align-items-center">
                            <i class="fas fa-box me-3"></i>
                            <div>
                                <h6 class="text-uppercase">Tổng Sản phẩm</h6>
                                <h4 class="mb-0"><?php echo number_format($product_count); ?></h4>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="card stat-card shadow-sm">
                        <div class="card-body d-flex align-items-center">
                            <i class="fas fa-users me-3"></i>
                            <div>
                                <h6 class="text-uppercase">Tổng Người dùng</h6>
                                <h4 class="mb-0"><?php echo number_format($user_count); ?></h4>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="card stat-card shadow-sm">
                        <div class="card-body d-flex align-items-center">
                            <i class="fas fa-chart-line me-3"></i>
                            <div>
                                <h6 class="text-uppercase">Lượt truy cập</h6>
                                <h4 class="mb-0"><?php echo number_format($total_visits); ?></h4>
                                <small class="text-muted">Hôm nay: <?php echo number_format($today_visits); ?></small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts -->
            <div class="row mb-4">
                <!-- Website Traffic Chart -->
                <div class="col-lg-6">
                    <div class="chart-container">
                        <div class="chart-header">
                            <h6><i class="fas fa-chart-line me-2"></i> Lượt truy cập 7 ngày gần đây</h6>
                        </div>
                        <canvas id="trafficChart"></canvas>
                        <div class="chart-footer">
                            Dữ liệu cập nhật đến ngày <?php echo date('d/m/Y'); ?>
                        </div>
                        <div id="trafficChartError" class="error-message"></div>
                    </div>
                </div>
                
                <!-- Monthly Revenue Chart -->
                <div class="col-lg-6">
                    <div class="chart-container">
                        <div class="chart-header">
                            <h6><i class="fas fa-chart-bar me-2"></i> Doanh thu Hàng tháng</h6>
                        </div>
                        <canvas id="revenueChart"></canvas>
                        <div class="chart-footer">
                            Dữ liệu doanh thu 12 tháng gần nhất
                        </div>
                        <div id="revenueChartError" class="error-message"></div>
                    </div>
                </div>

                <!-- Order Status Chart -->
                <div class="col-lg-6">
                    <div class="chart-container">
                        <div class="chart-header">
                            <h6><i class="fas fa-chart-pie me-2"></i> Trạng thái Đơn hàng</h6>
                        </div>
                        <?php
                        $status_labels = ['Chờ xử lý', 'Đang xử lý', 'Đã giao', 'Hoàn thành', 'Đã hủy'];
                        $status_counts = [
                            $order->countByStatus('pending'),
                            $order->countByStatus('processing'),
                            $order->countByStatus('shipped'),
                            $order->countByStatus('delivered'),
                            $order->countByStatus('cancelled')
                        ];
                        $status_colors = ['warning', 'info', 'primary', 'success', 'danger'];
                        $total_orders = array_sum($status_counts);
                        $debug_logs[] = "Order status counts: " . json_encode(array_combine($status_labels, $status_counts));
                        ?>
                        <?php if ($total_orders == 0): ?>
                            <div class="text-center text-muted py-4">Không có dữ liệu trạng thái đơn hàng.</div>
                        <?php else: ?>
                            <div class="row mb-4">
                                <?php foreach ($status_labels as $index => $label): 
                                    $count = $status_counts[$index];
                                    $percentage = ($total_orders > 0) ? round(($count / $total_orders) * 100) : 0;
                                    $color = $status_colors[$index];
                                ?>
                                <div class="col">
                                    <div class="card border-<?php echo $color; ?> h-100 shadow-sm rounded">
                                        <div class="card-body p-2 text-center">
                                            <h6 class="text-<?php echo $color; ?>"><?php echo $label; ?></h6>
                                            <h3 class="mb-0"><?php echo $count; ?></h3>
                                            <div class="progress mt-2">
                                                <div class="progress-bar bg-<?php echo $color; ?>" role="progressbar" 
                                                    style="width: <?php echo $percentage; ?>%" 
                                                    aria-valuenow="<?php echo $percentage; ?>" 
                                                    aria-valuemin="0" 
                                                    aria-valuemax="100">
                                                </div>
                                            </div>
                                            <small class="text-muted"><?php echo $percentage; ?>%</small>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="row justify-content-center">
                                <div class="col-md-10">
                                    <div class="progress">
                                        <?php foreach ($status_labels as $index => $label): 
                                            $count = $status_counts[$index];
                                            $percentage = ($total_orders > 0) ? round(($count / $total_orders) * 100) : 0;
                                            $color = $status_colors[$index];
                                            if ($percentage > 0):
                                        ?>
                                        <div class="progress-bar bg-<?php echo $color; ?>" role="progressbar" 
                                            style="width: <?php echo $percentage; ?>%" 
                                            aria-valuenow="<?php echo $percentage; ?>" 
                                            aria-valuemin="0" 
                                            aria-valuemax="100" 
                                            title="<?php echo $label; ?>: <?php echo $count; ?> (<?php echo $percentage; ?>%)">
                                            <?php if ($percentage >= 10): ?>
                                                <?php echo $label; ?> <?php echo $percentage; ?>%
                                            <?php endif; ?>
                                        </div>
                                        <?php endif; ?>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                        <div id="orderStatusError" class="error-message"></div>
                    </div>
                </div>
            </div>

            <!-- Recent Orders & Best Sellers -->
            <div class="row">
                <div class="col-lg-8">
                    <div class="card shadow-sm">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6><i class="fas fa-shopping-cart me-2"></i> Đơn hàng Gần đây</h6>
                            <a href="index.php?page=orders" class="btn btn-sm btn-primary">Xem Tất cả</a>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead>
                                        <tr>
                                            <th>Mã Đơn</th>
                                            <th>Khách hàng</th>
                                            <th>Số tiền</th>
                                            <th>Trạng thái</th>
                                            <th>Ngày</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($recent_orders)): ?>
                                        <tr>
                                            <td colspan="5" class="text-center">Không tìm thấy đơn hàng gần đây</td>
                                        </tr>
                                        <?php else: ?>
                                        <?php foreach ($recent_orders as $order): ?>
                                        <tr>
                                            <td>
                                                <a href="index.php?page=orders&action=view&id=<?php echo $order['id']; ?>" class="text-decoration-none">
                                                    #<?php echo htmlspecialchars($order['order_number']); ?>
                                                </a>
                                            </td>
                                            <td>
                                                <?php echo htmlspecialchars($order['full_name'] ?? ($order['username'] ?? 'Khách')); ?>
                                            </td>
                                            <td><?php echo CURRENCY . number_format($order['total_amount']); ?></td>
                                            <td>
                                                <?php
                                                $status_class = '';
                                                $status_text = '';
                                                switch($order['status']) {
                                                    case 'pending': 
                                                        $status_class = 'bg-warning text-dark'; 
                                                        $status_text = 'Chờ xử lý'; 
                                                        break;
                                                    case 'processing': 
                                                        $status_class = 'bg-info text-dark'; 
                                                        $status_text = 'Đang xử lý'; 
                                                        break;
                                                    case 'shipped': 
                                                        $status_class = 'bg-primary text-white'; 
                                                        $status_text = 'Đã giao'; 
                                                        break;
                                                    case 'delivered': 
                                                        $status_class = 'bg-success text-white'; 
                                                        $status_text = 'Hoàn thành'; 
                                                        break;
                                                    case 'cancelled': 
                                                        $status_class = 'bg-danger text-white'; 
                                                        $status_text = 'Đã hủy'; 
                                                        break;
                                                    default: 
                                                        $status_class = 'bg-secondary text-white';
                                                        $status_text = 'Không xác định';
                                                }
                                                ?>
                                                <span class="badge <?php echo $status_class; ?> rounded-pill"><?php echo $status_text; ?></span>
                                            </td>
                                            <td><?php echo date('d/m/Y', strtotime($order['created_at'])); ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card shadow-sm">
                        <div class="card-header">
                            <h6><i class="fas fa-star me-2"></i> Sản phẩm Bán chạy</h6>
                        </div>
                        <div class="card-body">
                            <div class="list-group list-group-flush">
                                <?php if (empty($bestsellers)): ?>
                                <div class="list-group-item text-center">Không tìm thấy sản phẩm bán chạy</div>
                                <?php else: ?>
                                <?php foreach ($bestsellers as $product): ?>
                                <div class="list-group-item d-flex align-items-center">
                                    <div class="flex-shrink-0 me-3">
                                        <?php if (!empty($product['image'])): ?>
                                        <img src="<?php echo htmlspecialchars($product['image']); ?>" class="bestseller-img img-fluid" alt="<?php echo htmlspecialchars($product['name']); ?>">
                                        <?php else: ?>
                                        <div class="bestseller-img-placeholder">
                                            <i class="fas fa-tshirt fa-2x text-secondary"></i>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1"><?php echo htmlspecialchars($product['name']); ?></h6>
                                        <small class="text-muted"><?php echo number_format($product['total_sold']); ?> đơn vị bán</small>
                                    </div>
                                    <div class="text-end">
                                        <div class="fw-bold text-success"><?php echo CURRENCY . number_format($product['total_revenue']); ?></div>
                                        <small class="text-muted"><?php echo CURRENCY . number_format($product['price']); ?>/đơn vị</small>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Debug Info -->
            <?php if (defined('DEBUG_MODE') && DEBUG_MODE && !empty($debug_logs)): ?>
            <div class="debug-info">
                <h6 class="fw-bold text-muted"><i class="fas fa-bug me-2"></i>Thông tin Debug</h6>
                <ul class="mb-0">
                    <?php foreach ($debug_logs as $log): ?>
                    <li><?php echo htmlspecialchars($log); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script>
        console.log("Dashboard script started at <?php echo date('Y-m-d H:i:s'); ?>");

        function refreshDashboard() {
            console.log("Refreshing dashboard...");
            location.reload();
        }

        document.addEventListener("DOMContentLoaded", function() {
            console.log("DOM fully loaded. Initializing charts...");

            // Traffic Chart
            const trafficCtx = document.getElementById('trafficChart');
            if (!trafficCtx) {
                console.error("Traffic chart canvas not found!");
                document.getElementById('trafficChartError').innerText = "Lỗi: Không tìm thấy canvas biểu đồ truy cập.";
                return;
            }
            const trafficContext = trafficCtx.getContext('2d');
            
            const trafficLabels = <?php echo $traffic_labels_json; ?>;
            const trafficData = <?php echo $traffic_data_json; ?>;
            
            if (!Array.isArray(trafficLabels) || !Array.isArray(trafficData)) {
                console.error("Invalid traffic data format:", { trafficLabels, trafficData });
                document.getElementById('trafficChartError').innerText = "Lỗi: Định dạng dữ liệu không hợp lệ.";
                return;
            }
            
            if (trafficLabels.length !== trafficData.length) {
                console.error("Mismatch between traffic labels and data:", trafficLabels.length, trafficData.length);
                document.getElementById('trafficChartError').innerText = "Lỗi: Số lượng nhãn và dữ liệu không khớp.";
                return;
            }
            
            const trafficGradient = trafficContext.createLinearGradient(0, 0, 0, 300);
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
                            lineTension: 0.3,
                            fill: true
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: false,
                                ticks: {
                                    precision: 0,
                                    stepSize: 1
                                },
                                title: {
                                    display: true,
                                    text: 'Số lượt truy cập',
                                    font: { size: 14 }
                                }
                            },
                            x: {
                                ticks: {
                                    font: { size: 12 }
                                },
                                title: {
                                    display: true,
                                    text: 'Ngày',
                                    font: { size: 14 }
                                }
                            }
                        },
                        plugins: {
                            tooltip: {
                                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                titleFont: { size: 14 },
                                bodyFont: { size: 12 },
                                callbacks: {
                                    label: function(context) {
                                        return `Lượt truy cập: ${context.parsed.y.toLocaleString()}`;
                                    }
                                }
                            },
                            legend: {
                                display: true,
                                position: 'top',
                                labels: {
                                    font: { size: 14 }
                                }
                            }
                        },
                        interaction: {
                            intersect: false,
                            mode: 'index'
                        }
                    }
                });
                console.log("Traffic chart initialized successfully.");
            } catch (error) {
                console.error("Error initializing traffic chart:", error);
                document.getElementById('trafficChartError').innerText = "Lỗi khởi tạo biểu đồ: " + (error.message || 'Không xác định');
            }

            // Revenue Chart
            const revenueCtx = document.getElementById('revenueChart');
            if (!revenueCtx) {
                console.error("Revenue chart canvas not found!");
                document.getElementById('revenueChartError').innerText = "Lỗi: Không tìm thấy canvas biểu đồ doanh thu.";
                return;
            }
            const revenueContext = revenueCtx.getContext('2d');
            
            const revenueLabels = <?php echo $monthly_revenue_labels_json; ?>;
            const revenueData = <?php echo $monthly_revenue_data_json; ?>;
            
            if (!Array.isArray(revenueLabels) || !Array.isArray(revenueData)) {
                console.error("Invalid revenue data format:", { revenueLabels, revenueData });
                document.getElementById('revenueChartError').innerText = "Lỗi: Định dạng dữ liệu không hợp lệ.";
                return;
            }
            
            if (revenueLabels.length !== revenueData.length) {
                console.error("Mismatch between revenue labels and data:", revenueLabels.length, revenueData.length);
                document.getElementById('revenueChartError').innerText = "Lỗi: Số lượng nhãn và dữ liệu không khớp.";
                return;
            }
            
            const revenueGradient = revenueContext.createLinearGradient(0, 0, 0, 300);
            revenueGradient.addColorStop(0, 'rgba(78, 115, 223, 0.8)');
            revenueGradient.addColorStop(1, 'rgba(78, 115, 223, 0.1)');
            
            try {
                new Chart(revenueContext, {
                    type: 'bar', // Use bar chart
                    data: {
                        labels: revenueLabels,
                        datasets: [{
                            label: 'Doanh thu',
                            data: revenueData,
                            backgroundColor: revenueGradient,
                            borderColor: 'rgba(78, 115, 223, 1)',
                            borderWidth: 1,
                            borderRadius: 8
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value) {
                                        return '<?php echo CURRENCY; ?>' + value.toLocaleString();
                                    }
                                },
                                title: {
                                    display: true,
                                    text: 'Doanh thu',
                                    font: { size: 14 }
                                }
                            },
                            x: {
                                ticks: {
                                    font: { size: 12 }
                                },
                                title: {
                                    display: true,
                                    text: 'Tháng',
                                    font: { size: 14 }
                                }
                            }
                        },
                        plugins: {
                            tooltip: {
                                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                titleFont: { size: 14 },
                                bodyFont: { size: 12 },
                                callbacks: {
                                    label: function(context) {
                                        return `Doanh thu: <?php echo CURRENCY; ?>${context.parsed.y.toLocaleString()}`;
                                    }
                                }
                            },
                            legend: {
                                display: true,
                                position: 'top',
                                labels: {
                                    font: { size: 14 }
                                }
                            }
                        }
                    }
                });
                console.log("Revenue chart initialized successfully.");
            } catch (error) {
                console.error("Error initializing revenue chart:", error);
                document.getElementById('revenueChartError').innerText = "Lỗi khởi tạo biểu đồ: " + (error.message || 'Không xác định');
            }
        });
    </script>
</body>
</html>