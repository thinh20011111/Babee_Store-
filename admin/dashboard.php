<?php
// Dashboard page
// Check direct script access
if (!defined('ADMIN_INCLUDED')) {
    define('ADMIN_INCLUDED', true);
}

// Load required models
require_once '../models/Order.php';
require_once '../models/Product.php';
require_once '../models/User.php';
require_once '../models/Promotion.php';

// Initialize objects
$order = new Order($conn);
$product = new Product($conn);
$user = new User($conn);
$promotion = new Promotion($conn);

// Get statistics
$total_revenue = $order->getTotalRevenue();
$order_count = $order->countAll();
$product_count = $product->countAll();
$user_count = $user->countAll();

// Get monthly revenue data
$monthly_revenue_data = [];
$monthly_revenue_labels = [];
$monthly_revenue_stmt = $order->getMonthlyRevenue();
while ($row = $monthly_revenue_stmt->fetch(PDO::FETCH_ASSOC)) {
    $month_name = date('F', mktime(0, 0, 0, $row['month'], 1));
    $monthly_revenue_labels[] = $month_name;
    $monthly_revenue_data[] = (float)$row['revenue'];
}

// Get recent orders
$recent_orders = [];
$recent_orders_stmt = $order->getRecentOrders(5);
while ($row = $recent_orders_stmt->fetch(PDO::FETCH_ASSOC)) {
    $recent_orders[] = $row;
}

// Get best selling products
$bestsellers = [];
$bestsellers_stmt = $product->getBestsellers(5);
while ($row = $bestsellers_stmt->fetch(PDO::FETCH_ASSOC)) {
    $bestsellers[] = $row;
}
?>

<div class="container-fluid px-4">
    <h1 class="mt-4">Dashboard</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item active">Dashboard</li>
    </ol>
    
    <!-- Dashboard Cards -->
    <div class="row">
        <div class="col-xl-3 col-md-6">
            <div class="card bg-primary text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-0"><?php echo CURRENCY . number_format($total_revenue); ?></h5>
                            <div class="small">Total Revenue</div>
                        </div>
                        <div>
                            <i class="fas fa-money-bill-wave fa-2x"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="index.php?page=reports">View Details</a>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-success text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-0"><?php echo number_format($order_count); ?></h5>
                            <div class="small">Total Orders</div>
                        </div>
                        <div>
                            <i class="fas fa-shopping-cart fa-2x"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="index.php?page=orders">View Details</a>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-warning text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-0"><?php echo number_format($product_count); ?></h5>
                            <div class="small">Total Products</div>
                        </div>
                        <div>
                            <i class="fas fa-tshirt fa-2x"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="index.php?page=products">View Details</a>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-danger text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-0"><?php echo number_format($user_count); ?></h5>
                            <div class="small">Total Users</div>
                        </div>
                        <div>
                            <i class="fas fa-users fa-2x"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="index.php?page=users">View Details</a>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Charts Row -->
    <div class="row">
        <div class="col-xl-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-chart-bar me-1"></i>
                    Monthly Revenue
                </div>
                <div class="card-body">
                    <canvas id="monthlyRevenueChart" height="300"></canvas>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-chart-pie me-1"></i>
                    Order Status Distribution
                </div>
                <div class="card-body">
                    <canvas id="orderStatusChart" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Recent Orders and Bestsellers -->
    <div class="row">
        <div class="col-xl-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-table me-1"></i>
                    Recent Orders
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-sm">
                            <thead>
                                <tr>
                                    <th>Order #</th>
                                    <th>Date</th>
                                    <th>Customer</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_orders as $order): ?>
                                <tr>
                                    <td><a href="index.php?page=orders&action=view&id=<?php echo $order['id']; ?>"><?php echo htmlspecialchars($order['order_number']); ?></a></td>
                                    <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                                    <td><?php echo htmlspecialchars($order['full_name'] ?? $order['username']); ?></td>
                                    <td><?php echo CURRENCY . number_format($order['total_amount']); ?></td>
                                    <td>
                                        <?php
                                        $status_class = '';
                                        switch($order['status']) {
                                            case 'pending': $status_class = 'bg-warning text-dark'; break;
                                            case 'processing': $status_class = 'bg-info text-dark'; break;
                                            case 'shipped': $status_class = 'bg-primary'; break;
                                            case 'delivered': $status_class = 'bg-success'; break;
                                            case 'cancelled': $status_class = 'bg-danger'; break;
                                            default: $status_class = 'bg-secondary';
                                        }
                                        ?>
                                        <span class="badge <?php echo $status_class; ?>"><?php echo ucfirst($order['status']); ?></span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($recent_orders)): ?>
                                <tr>
                                    <td colspan="5" class="text-center">No recent orders found</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="text-end mt-2">
                        <a href="index.php?page=orders" class="btn btn-sm btn-primary">View All Orders</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-star me-1"></i>
                    Bestselling Products
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-sm">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Category</th>
                                    <th>Price</th>
                                    <th>Sold</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($bestsellers as $product): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <?php if (!empty($product['image'])): ?>
                                            <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="img-thumbnail me-2" style="width: 40px; height: 40px;">
                                            <?php else: ?>
                                            <div class="bg-light d-flex align-items-center justify-content-center me-2" style="width: 40px; height: 40px;">
                                                <i class="fas fa-tshirt text-secondary"></i>
                                            </div>
                                            <?php endif; ?>
                                            <a href="index.php?page=product-edit&id=<?php echo $product['id']; ?>"><?php echo htmlspecialchars($product['name']); ?></a>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($product['category_name']); ?></td>
                                    <td>
                                        <?php if ($product['is_sale'] == 1 && !empty($product['sale_price']) && $product['sale_price'] < $product['price']): ?>
                                        <span class="text-danger"><?php echo CURRENCY . number_format($product['sale_price']); ?></span>
                                        <br><small class="text-muted text-decoration-line-through"><?php echo CURRENCY . number_format($product['price']); ?></small>
                                        <?php else: ?>
                                        <?php echo CURRENCY . number_format($product['price']); ?>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo $product['total_sold'] ?? 0; ?></td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($bestsellers)): ?>
                                <tr>
                                    <td colspan="4" class="text-center">No bestselling products found</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="text-end mt-2">
                        <a href="index.php?page=products" class="btn btn-sm btn-primary">View All Products</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Monthly Revenue Chart
    const monthlyRevenueChart = new Chart(
        document.getElementById('monthlyRevenueChart'),
        {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($monthly_revenue_labels); ?>,
                datasets: [{
                    label: 'Revenue',
                    data: <?php echo json_encode($monthly_revenue_data); ?>,
                    backgroundColor: 'rgba(0, 123, 255, 0.5)',
                    borderColor: 'rgba(0, 123, 255, 1)',
                    borderWidth: 1
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
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return '<?php echo CURRENCY; ?>' + context.raw.toLocaleString();
                            }
                        }
                    }
                }
            }
        }
    );

    // Order Status Chart
    const orderStatusData = {
        pending: <?php echo $order->countByStatus('pending'); ?>,
        processing: <?php echo $order->countByStatus('processing'); ?>,
        shipped: <?php echo $order->countByStatus('shipped'); ?>,
        delivered: <?php echo $order->countByStatus('delivered'); ?>,
        cancelled: <?php echo $order->countByStatus('cancelled'); ?>
    };

    const orderStatusChart = new Chart(
        document.getElementById('orderStatusChart'),
        {
            type: 'pie',
            data: {
                labels: ['Pending', 'Processing', 'Shipped', 'Delivered', 'Cancelled'],
                datasets: [{
                    data: [
                        orderStatusData.pending,
                        orderStatusData.processing,
                        orderStatusData.shipped,
                        orderStatusData.delivered,
                        orderStatusData.cancelled
                    ],
                    backgroundColor: [
                        'rgba(255, 193, 7, 0.8)',
                        'rgba(23, 162, 184, 0.8)',
                        'rgba(0, 123, 255, 0.8)',
                        'rgba(40, 167, 69, 0.8)',
                        'rgba(220, 53, 69, 0.8)'
                    ],
                    borderColor: [
                        'rgba(255, 193, 7, 1)',
                        'rgba(23, 162, 184, 1)',
                        'rgba(0, 123, 255, 1)',
                        'rgba(40, 167, 69, 1)',
                        'rgba(220, 53, 69, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right'
                    }
                }
            }
        }
    );
});
</script>
