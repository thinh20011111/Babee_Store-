<?php
// Dashboard page
if (!defined('ADMIN_INCLUDED')) {
    define('ADMIN_INCLUDED', true);
}

require_once '../models/Order.php';
require_once '../models/Product.php';
require_once '../models/User.php';
require_once '../models/Promotion.php';

// Include database connection
require_once '../config/database.php';
$db = new Database();
$conn = $db->getConnection();

// Initialize objects
$order = new Order($conn);
$product = new Product($conn);
$user = new User($conn);
$promotion = new Promotion($conn);

// Get statistics
$total_revenue = $order->getTotalRevenue() ?? 0;
$order_count = $order->countAll() ?? 0;
$product_count = $product->countAll() ?? 0;
$user_count = $user->countAll() ?? 0;

// Get recent orders and bestsellers
$recent_orders_stmt = $order->getRecentOrders(5);
$recent_orders = [];
while ($row = $recent_orders_stmt->fetch(PDO::FETCH_ASSOC)) {
    $recent_orders[] = $row;
}

$bestsellers_stmt = $product->getBestsellers(5);
$bestsellers = [];
while ($row = $bestsellers_stmt->fetch(PDO::FETCH_ASSOC)) {
    $bestsellers[] = $row;
}

// Get monthly revenue data (last 12 months)
$monthly_revenue_data = array_fill(0, 12, 0); // Initialize with zeros
$monthly_revenue_labels = [];
$today = new DateTime();
$today->modify('first day of this month'); // Start from first day of current month
for ($i = 11; $i >= 0; $i--) {
    $date = (clone $today)->modify("-$i months");
    $monthly_revenue_labels[] = $date->format('M Y');
}

// Fetch revenue data
$monthly_revenue_stmt = $order->getMonthlyRevenue();
$raw_revenue_data = [];
while ($row = $monthly_revenue_stmt->fetch(PDO::FETCH_ASSOC)) {
    $raw_revenue_data[] = $row;
    try {
        // In PostgreSQL, EXTRACT returns decimal/float values, so we need to convert them to integers
        $year = intval($row['year']);
        $month = intval($row['month']);
        
        // Debug the values
        error_log("Processing revenue data - Year: $year, Month: $month, Revenue: {$row['revenue']}");
        
        // Create month_year using DateTime for consistent format
        $date_string = $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT) . '-01';
        $date = DateTime::createFromFormat('Y-m-d', $date_string);
        
        if ($date === false) {
            // Handle format error and provide detailed diagnostics
            $errors = DateTime::getLastErrors();
            $error_msg = "Invalid date format for year: $year, month: $month, date string: $date_string";
            if ($errors) {
                $error_msg .= " - DateTime errors: " . json_encode($errors);
            }
            error_log($error_msg);
            continue;
        }
        
        $month_year = $date->format('M Y');
        error_log("Formatted date: $month_year");
        
        $index = array_search($month_year, $monthly_revenue_labels);
        if ($index !== false) {
            $monthly_revenue_data[$index] = (float)($row['revenue'] ?? 0);
            error_log("Mapped revenue $month_year to index $index: {$monthly_revenue_data[$index]}");
        } else {
            error_log("No match for month_year: $month_year in labels: " . implode(", ", $monthly_revenue_labels));
        }
    } catch (Exception $e) {
        error_log("Error processing revenue data: " . $e->getMessage());
    }
}

// Debug: Prepare raw data message
if (empty($raw_revenue_data)) {
    $debug_raw_revenue = "No data returned from getMonthlyRevenue. Check orders table for non-cancelled orders.";
    // Additional debug query to check if we have any valid orders
    $debug_orders_query = "SELECT id, status, total_amount, created_at FROM orders WHERE status != 'cancelled' LIMIT 5";
    $debug_orders_stmt = $conn->prepare($debug_orders_query);
    $debug_orders_stmt->execute();
    $debug_orders = [];
    while ($row = $debug_orders_stmt->fetch(PDO::FETCH_ASSOC)) {
        $debug_orders[] = $row;
    }
    if (!empty($debug_orders)) {
        $debug_raw_revenue = "Found orders but no monthly revenue data. Sample orders: " . json_encode($debug_orders);
    }
} else {
    $debug_raw_revenue = $raw_revenue_data;
}

// Debug: Prepare mapped data
$debug_mapped_data = [
    'labels' => $monthly_revenue_labels,
    'data' => $monthly_revenue_data
];

// Ensure data is valid
if (empty($monthly_revenue_data) || array_sum($monthly_revenue_data) == 0) {
    $monthly_revenue_labels = ['No Data'];
    $monthly_revenue_data = [0];
}

// Convert to JSON
$monthly_revenue_labels_json = json_encode($monthly_revenue_labels);
$monthly_revenue_data_json = json_encode($monthly_revenue_data);

// Define currency constant if not defined
if (!defined('CURRENCY')) {
    define('CURRENCY', 'đ');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <!-- Favicon (empty data URI to avoid CORS) -->
    <link rel="icon" type="image/png" href="data:image/png;base64,iVBORw0KGgo=">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <!-- No Chart.js needed anymore as we use Bootstrap 5 components -->
    <style>
        .sidebar {
            min-height: 100vh;
            position: sticky;
            top: 0;
        }
        .card {
            transition: transform 0.3s;
        }
        .card:hover {
            transform: translateY(-5px);
        }
        .chart-container {
            position: relative;
            height: 400px;
            background-color: #f8f9fa;
            border: 2px solid #007bff;
        }
        .badge {
            padding: 8px 12px;
        }
        .list-group-item {
            border: none;
        }
        .table img {
            object-fit: cover;
        }
        .debug-info {
            background-color: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
            margin-top: 10px;
            font-size: 0.9em;
        }
        .error-message {
            color: red;
            margin-top: 10px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="d-flex">
        <!-- Sidebar -->
        <div class="bg-dark sidebar p-3 text-white" style="width: 250px;">
            <h4 class="text-center mb-4">Admin Panel</h4>
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link text-white active" href="index.php?page=dashboard"><i class="fas fa-home me-2"></i> Dashboard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white" href="index.php?page=orders"><i class="fas fa-shopping-cart me-2"></i> Orders</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white" href="index.php?page=products"><i class="fas fa-box me-2"></i> Products</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white" href="index.php?page=users"><i class="fas fa-users me-2"></i> Users</a>
                </li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="flex-grow-1 p-4">
            <div class="container-fluid">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="h3 mb-0">Dashboard</h1>
                    <button class="btn btn-primary" onclick="refreshDashboard()">
                        <i class="fas fa-sync-alt me-2"></i> Refresh Data
                    </button>
                </div>

                <!-- Statistics Cards -->
                <div class="row g-4 mb-4">
                    <div class="col-md-6 col-lg-3">
                        <div class="card shadow-sm border-0">
                            <div class="card-body d-flex align-items-center">
                                <i class="fas fa-money-bill-wave fa-3x text-primary me-3"></i>
                                <div>
                                    <h6 class="text-uppercase text-muted mb-1">Total Revenue</h6>
                                    <h4 class="mb-0"><?php echo CURRENCY . number_format($total_revenue); ?></h4>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <div class="card shadow-sm border-0">
                            <div class="card-body d-flex align-items-center">
                                <i class="fas fa-shopping-cart fa-3x text-success me-3"></i>
                                <div>
                                    <h6 class="text-uppercase text-muted mb-1">Total Orders</h6>
                                    <h4 class="mb-0"><?php echo number_format($order_count); ?></h4>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <div class="card shadow-sm border-0">
                            <div class="card-body d-flex align-items-center">
                                <i class="fas fa-box fa-3x text-warning me-3"></i>
                                <div>
                                    <h6 class="text-uppercase text-muted mb-1">Total Products</h6>
                                    <h4 class="mb-0"><?php echo number_format($product_count); ?></h4>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <div class="card shadow-sm border-0">
                            <div class="card-body d-flex align-items-center">
                                <i class="fas fa-users fa-3x text-info me-3"></i>
                                <div>
                                    <h6 class="text-uppercase text-muted mb-1">Total Users</h6>
                                    <h4 class="mb-0"><?php echo number_format($user_count); ?></h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts -->
                <div class="row mb-4">
                    <div class="col-lg-6">
                        <div class="card shadow-sm">
                            <div class="card-header">
                                <h6 class="m-0 fw-bold text-primary"><i class="fas fa-chart-bar me-2"></i> Monthly Revenue</h6>
                            </div>
                            <div class="card-body">
                                <div class="monthly-revenue-chart">
                                    <?php if ($monthly_revenue_labels[0] === 'No Data'): ?>
                                        <p class="text-center text-muted mt-3">No revenue data available for the last 12 months.</p>
                                    <?php else: ?>
                                        <!-- Bootstrap 5 based bar chart -->
                                        <div class="row align-items-end mb-4" style="height: 300px;">
                                            <?php foreach ($monthly_revenue_data as $index => $value): ?>
                                                <?php 
                                                    $month = $monthly_revenue_labels[$index] ?? 'Unknown';
                                                    $height_percentage = 0;
                                                    $max_value = max($monthly_revenue_data);
                                                    if ($max_value > 0) {
                                                        $height_percentage = ($value / $max_value) * 100;
                                                    }
                                                ?>
                                                <div class="col px-1 text-center">
                                                    <div class="d-flex flex-column align-items-center">
                                                        <div class="text-primary fw-bold small mb-1"><?php echo CURRENCY . number_format($value); ?></div>
                                                        <div class="bg-primary rounded-top" style="height: <?php echo $height_percentage; ?>%; width: 100%; min-height: 5px;"></div>
                                                        <div class="small text-muted mt-2" style="writing-mode: vertical-rl; transform: rotate(180deg); height: 80px;"><?php echo $month; ?></div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div id="monthlyRevenueError" class="error-message"></div>
                                <!-- Debug Info -->
                                <div class="debug-info">
                                    <strong>Raw Revenue Data:</strong><br>
                                    <?php
                                    if (is_string($debug_raw_revenue)) {
                                        echo htmlspecialchars($debug_raw_revenue);
                                    } else {
                                        echo '<pre>' . htmlspecialchars(json_encode($debug_raw_revenue, JSON_PRETTY_PRINT)) . '</pre>';
                                    }
                                    ?>
                                    <strong>Mapped Revenue Data:</strong><br>
                                    <pre><?php echo htmlspecialchars(json_encode($debug_mapped_data, JSON_PRETTY_PRINT)); ?></pre>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="card shadow-sm">
                            <div class="card-header">
                                <h6 class="m-0 fw-bold text-primary"><i class="fas fa-chart-pie me-2"></i> Order Status</h6>
                            </div>
                            <div class="card-body">
                                <!-- Bootstrap 5 Order Status Chart -->
                                <div class="order-status-chart">
                                    <?php
                                    $status_labels = ['Pending', 'Processing', 'Shipped', 'Delivered', 'Cancelled'];
                                    $status_counts = [
                                        $order->countByStatus('pending'),
                                        $order->countByStatus('processing'),
                                        $order->countByStatus('shipped'),
                                        $order->countByStatus('delivered'),
                                        $order->countByStatus('cancelled')
                                    ];
                                    $status_colors = [
                                        'warning',  // Pending - yellow
                                        'info',     // Processing - blue
                                        'primary',  // Shipped - dark blue
                                        'success',  // Delivered - green
                                        'danger'    // Cancelled - red
                                    ];
                                    $total_orders = array_sum($status_counts);
                                    ?>
                                    
                                    <?php if ($total_orders == 0): ?>
                                        <div class="text-center text-muted py-4">No order status data available.</div>
                                    <?php else: ?>
                                        <!-- Visual representation using colored cards -->
                                        <div class="row mb-4">
                                            <?php foreach ($status_labels as $index => $label): 
                                                $count = $status_counts[$index];
                                                $percentage = ($total_orders > 0) ? round(($count / $total_orders) * 100) : 0;
                                                $color = $status_colors[$index];
                                            ?>
                                            <div class="col">
                                                <div class="card border-<?php echo $color; ?> h-100">
                                                    <div class="card-body p-2 text-center">
                                                        <h6 class="text-<?php echo $color; ?>"><?php echo $label; ?></h6>
                                                        <h3 class="mb-0"><?php echo $count; ?></h3>
                                                        <div class="progress mt-2" style="height: 10px;">
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
                                        
                                        <!-- Visual representation as circular progress -->
                                        <div class="row justify-content-center">
                                            <div class="col-md-10">
                                                <div class="progress" style="height: 25px;">
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
                                </div>
                                <div id="orderStatusError" class="error-message"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Orders & Best Sellers -->
                <div class="row">
                    <!-- Recent Orders -->
                    <div class="col-lg-8">
                        <div class="card shadow-sm">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h6 class="m-0 fw-bold text-primary"><i class="fas fa-shopping-cart me-2"></i> Recent Orders</h6>
                                <a href="index.php?page=orders" class="btn btn-sm btn-primary">View All</a>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle">
                                        <thead class="table-dark">
                                            <tr>
                                                <th>Order #</th>
                                                <th>Customer</th>
                                                <th>Amount</th>
                                                <th>Status</th>
                                                <th>Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($recent_orders)): ?>
                                            <tr>
                                                <td colspan="5" class="text-center">No recent orders found</td>
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
                                                    <?php echo htmlspecialchars($order['full_name'] ?? ($order['username'] ?? 'Guest')); ?>
                                                </td>
                                                <td><?php echo CURRENCY . number_format($order['total_amount']); ?></td>
                                                <td>
                                                    <?php
                                                    $status_class = '';
                                                    switch($order['status']) {
                                                        case 'pending': $status_class = 'bg-warning text-dark'; break;
                                                        case 'processing': $status_class = 'bg-info text-dark'; break;
                                                        case 'shipped': $status_class = 'bg-primary text-white'; break;
                                                        case 'delivered': $status_class = 'bg-success text-white'; break;
                                                        case 'cancelled': $status_class = 'bg-danger text-white'; break;
                                                        default: $status_class = 'bg-secondary text-white';
                                                    }
                                                    ?>
                                                    <span class="badge <?php echo $status_class; ?>"><?php echo ucfirst($order['status']); ?></span>
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

                    <!-- Best Selling Products -->
                    <div class="col-lg-4">
                        <div class="card shadow-sm">
                            <div class="card-header">
                                <h6 class="m-0 fw-bold text-primary"><i class="fas fa-star me-2"></i> Best Sellers</h6>
                            </div>
                            <div class="card-body">
                                <div class="list-group list-group-flush">
                                    <?php if (empty($bestsellers)): ?>
                                    <div class="list-group-item text-center">No best sellers found</div>
                                    <?php else: ?>
                                    <?php foreach ($bestsellers as $product): ?>
                                    <div class="list-group-item">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="mb-1"><?php echo htmlspecialchars($product['name']); ?></h6>
                                                <small class="text-muted"><?php echo number_format($product['total_sold']); ?> units sold</small>
                                            </div>
                                            <div class="text-end">
                                                <div class="fw-bold text-success"><?php echo CURRENCY . number_format($product['total_revenue']); ?></div>
                                                <small class="text-muted"><?php echo CURRENCY . number_format($product['price']); ?>/unit</small>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS and Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        console.log("Dashboard script started with Bootstrap 5 visualizations.");

        function refreshDashboard() {
            location.reload();
        }

        // We are now using pure Bootstrap 5 for visualizations, no chart.js initialization needed
        document.addEventListener("DOMContentLoaded", function() {
            console.log("DOM fully loaded. Bootstrap 5 visualizations are static and dont require JavaScript initialization.");
            
            // Just log data for debugging purposes
            console.log("Raw Revenue Data:", <?php echo json_encode($debug_raw_revenue); ?>);
            console.log("Mapped Revenue Data:", <?php echo json_encode($debug_mapped_data); ?>);
            
            // Status counts for debugging
            const statusCounts = {
                "Pending": <?php echo $order->countByStatus("pending"); ?>,
                "Processing": <?php echo $order->countByStatus("processing"); ?>,
                "Shipped": <?php echo $order->countByStatus("shipped"); ?>,
                "Delivered": <?php echo $order->countByStatus("delivered"); ?>,
                "Cancelled": <?php echo $order->countByStatus("cancelled"); ?>
            };
            console.log("Order Status Data:", statusCounts);
        });
    </script>
</body>
</html>