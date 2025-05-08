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
        // Create month_year using DateTime for consistent format
        $date = DateTime::createFromFormat('Y-m-d', $row['year'] . '-' . str_pad($row['month'], 2, '0', STR_PAD_LEFT) . '-01');
        if ($date === false) {
            // Handle format error
            error_log("Invalid date format for year: {$row['year']}, month: {$row['month']}");
            continue;
        }
        $month_year = $date->format('M Y');
        $index = array_search($month_year, $monthly_revenue_labels);
        if ($index !== false) {
            $monthly_revenue_data[$index] = (float)($row['revenue'] ?? 0);
        } else {
            error_log("No match for month_year: $month_year in labels: " . implode(", ", $monthly_revenue_labels));
        }
    } catch (Exception $e) {
        error_log("Error processing revenue data: " . $e->getMessage());
    }
}

// Debug: Prepare raw data message
$debug_raw_revenue = empty($raw_revenue_data) 
    ? "No data returned from getMonthlyRevenue. Check orders table for non-cancelled orders."
    : $raw_revenue_data;

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
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
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
                                <div class="chart-container">
                                    <canvas id="monthlyRevenueChart" style="width: 100%; height: 100%;"></canvas>
                                </div>
                                <div id="monthlyRevenueError" class="error-message"></div>
                                <?php if ($monthly_revenue_labels[0] === 'No Data'): ?>
                                    <p class="text-center text-muted mt-3">No revenue data available for the last 12 months.</p>
                                <?php endif; ?>
                                <!-- Debug Info -->
                                
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="card shadow-sm">
                            <div class="card-header">
                                <h6 class="m-0 fw-bold text-primary"><i class="fas fa-chart-pie me-2"></i> Order Status</h6>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="orderStatusChart" style="width: 100%; height: 100%;"></canvas>
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
        console.log('Dashboard script started.');

        function refreshDashboard() {
            location.reload();
        }

        // Function to initialize charts
        function initializeCharts() {
            console.log('Attempting to initialize charts.');
            try {
                // Log data
                console.log('Raw Revenue Data:', <?php echo json_encode($debug_raw_revenue); ?>);
                console.log('Mapped Revenue Data:', <?php echo json_encode($debug_mapped_data); ?>);

                // Check Chart.js availability
                if (typeof Chart === 'undefined') {
                    console.error('Chart.js is not loaded!');
                    document.getElementById('monthlyRevenueError').textContent = 'Error: Chart.js library is not loaded.';
                    return;
                }
                console.log('Chart.js is loaded.');

                // Check Monthly Revenue Chart canvas
                const monthlyRevenueCanvas = document.getElementById('monthlyRevenueChart');
                if (!monthlyRevenueCanvas) {
                    console.error('Monthly Revenue Chart canvas not found!');
                    document.getElementById('monthlyRevenueError').textContent = 'Error: Monthly Revenue Chart canvas not found.';
                    return;
                }
                console.log('Monthly Revenue Chart canvas found.');
                const monthlyCanvasRect = monthlyRevenueCanvas.getBoundingClientRect();
                console.log('Monthly Revenue Canvas size:', {
                    width: monthlyCanvasRect.width,
                    height: monthlyCanvasRect.height
                });

                if (monthlyCanvasRect.width === 0 || monthlyCanvasRect.height === 0) {
                    console.error('Monthly Revenue Canvas has zero size!');
                    document.getElementById('monthlyRevenueError').textContent = 'Error: Monthly Revenue Chart canvas has zero size.';
                    return;
                }

                // Parse the data safely
                let monthlyLabels = <?php echo $monthly_revenue_labels_json; ?>;
                let monthlyData = <?php echo $monthly_revenue_data_json; ?>;
                
                console.log('Monthly labels:', monthlyLabels);
                console.log('Monthly data:', monthlyData);
                
                // Safety check for data
                if (!Array.isArray(monthlyLabels) || !Array.isArray(monthlyData) || 
                    monthlyLabels.length === 0 || monthlyData.length === 0) {
                    console.error('Invalid monthly revenue data format');
                    monthlyLabels = ['No Data'];
                    monthlyData = [0];
                }

                // Initialize Monthly Revenue Chart
                const monthlyRevenueChart = new Chart(monthlyRevenueCanvas, {
                    type: 'bar',
                    data: {
                        labels: monthlyLabels,
                        datasets: [{
                            label: 'Revenue',
                            data: monthlyData,
                            backgroundColor: 'rgba(0, 123, 255, 0.5)',
                            borderColor: 'rgba(0, 123, 255, 1)',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: true,
                                position: 'top'
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        let value = context.raw || 0;
                                        return 'Revenue: ' + value.toLocaleString() + ' <?php echo CURRENCY; ?>';
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value) {
                                        return value.toLocaleString() + ' <?php echo CURRENCY; ?>';
                                    }
                                }
                            },
                            x: {
                                ticks: {
                                    autoSkip: false,
                                    maxRotation: 45,
                                    minRotation: 45
                                },
                                title: {
                                    display: true,
                                    text: 'Month'
                                }
                            }
                        }
                    }
                });
                console.log('Monthly Revenue Chart initialized successfully.');

                // Check Order Status Chart canvas
                const orderStatusCanvas = document.getElementById('orderStatusChart');
                if (!orderStatusCanvas) {
                    console.error('Order Status Chart canvas not found!');
                    document.getElementById('orderStatusError').textContent = 'Error: Order Status Chart canvas not found.';
                    return;
                }
                console.log('Order Status Chart canvas found.');
                const orderCanvasRect = orderStatusCanvas.getBoundingClientRect();
                console.log('Order Status Canvas size:', {
                    width: orderCanvasRect.width,
                    height: orderCanvasRect.height
                });

                if (orderCanvasRect.width === 0 || orderCanvasRect.height === 0) {
                    console.error('Order Status Canvas has zero size!');
                    document.getElementById('orderStatusError').textContent = 'Error: Order Status Chart canvas has zero size.';
                    return;
                }

                // Order Status Chart
                const orderStatusData = {
                    pending: <?php echo $order->countByStatus('pending') ?? 0; ?>,
                    processing: <?php echo $order->countByStatus('processing') ?? 0; ?>,
                    shipped: <?php echo $order->countByStatus('shipped') ?? 0; ?>,
                    delivered: <?php echo $order->countByStatus('delivered') ?? 0; ?>,
                    cancelled: <?php echo $order->countByStatus('cancelled') ?? 0; ?>
                };

                console.log('Order Status Data:', orderStatusData);

                // Check if we have any order data
                const hasOrderData = Object.values(orderStatusData).some(value => value > 0);
                
                // Setup the order status data
                let statusLabels = ['Pending', 'Processing', 'Shipped', 'Delivered', 'Cancelled'];
                let statusData = [
                    orderStatusData.pending,
                    orderStatusData.processing,
                    orderStatusData.shipped,
                    orderStatusData.delivered,
                    orderStatusData.cancelled
                ];
                
                // If no data, show a message
                if (!hasOrderData) {
                    // Add a note under the chart
                    const noDataMsg = document.createElement('p');
                    noDataMsg.className = 'text-center text-muted mt-3';
                    noDataMsg.innerText = 'No order status data available.';
                    orderStatusCanvas.parentNode.appendChild(noDataMsg);
                }
                
                const orderStatusChart = new Chart(orderStatusCanvas, {
                    type: 'pie',
                    data: {
                        labels: statusLabels,
                        datasets: [{
                            data: statusData,
                            backgroundColor: [
                                'rgba(255, 193, 7, 0.8)',
                                'rgba(23, 162, 184, 0.8)',
                                'rgba(0, 123, 255, 0.8)',
                                'rgba(40, 167, 69, 0.8)',
                                'rgba(220, 53, 69, 0.8)'
                            ],
                            borderWidth: 1,
                            borderColor: [
                                'rgba(255, 193, 7, 1)',
                                'rgba(23, 162, 184, 1)',
                                'rgba(0, 123, 255, 1)',
                                'rgba(40, 167, 69, 1)',
                                'rgba(220, 53, 69, 1)'
                            ]
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'top',
                                labels: {
                                    padding: 15,
                                    usePointStyle: true,
                                    pointStyle: 'circle'
                                }
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        const label = context.label || '';
                                        const value = context.raw || 0;
                                        const total = context.chart.data.datasets[0].data.reduce((a, b) => a + b, 0);
                                        const percentage = total > 0 ? Math.round((value / total) * 100) : 0;
                                        return label + ': ' + value + ' đơn hàng (' + percentage + '%)';
                                    }
                                }
                            }
                        }
                    }
                });
                console.log('Order Status Chart initialized successfully.');
            } catch (error) {
                console.error('Error initializing charts:', error);
                document.getElementById('monthlyRevenueError').textContent = 'Error initializing charts: ' + error.message;
            }
        }

        // Run chart initialization after DOM is loaded
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM fully loaded.');
            // Delay chart initialization to avoid early errors
            setTimeout(initializeCharts, 100);
        });
    </script>
</body>
</html>