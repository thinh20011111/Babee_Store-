<?php
// Orders management page
// Check direct script access
if (!defined('ADMIN_INCLUDED')) {
    define('ADMIN_INCLUDED', true);
}

// Include database connection
require_once '../config/database.php';
try {
    $db = new Database();
    $conn = $db->getConnection();
} catch (Exception $e) {
    error_log("Database connection error: " . $e->getMessage());
    die("Internal Server Error - Check logs for details.");
}

// Load required models
require_once '../models/Order.php';

// Initialize objects
$order = new Order($conn);

// Process actions
$action = isset($_GET['action']) ? $_GET['action'] : '';
$success_message = '';
$error_message = '';

// View single order
$view_order = null;
$order_items = null;
if ($action == 'view' && isset($_GET['id'])) {
    $order->id = $_GET['id'];
    if ($order->readOne()) {
        $view_order = $order;
        $order_items = $order->getOrderDetails();
    } else {
        $error_message = "Order not found.";
    }
}

// Update order status
if ($action == 'update_status' && isset($_POST['order_id']) && isset($_POST['status'])) {
    $order->id = $_POST['order_id'];
    if ($order->readOne()) {
        $order->status = $_POST['status'];
        if ($order->updateStatus()) {
            $success_message = "Order status updated successfully.";
            // Refresh order data
            $order->readOne();
            $view_order = $order;
            $order_items = $order->getOrderDetails();
        } else {
            $error_message = "Failed to update order status.";
        }
    } else {
        $error_message = "Order not found.";
    }
}

// Delete order
if ($action == 'delete' && isset($_GET['id'])) {
    $order->id = $_GET['id'];
    if ($order->delete()) {
        $success_message = "Order deleted successfully.";
    } else {
        $error_message = "Failed to delete order.";
    }
}

// Get search and filter parameters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$status = isset($_GET['status']) ? trim($_GET['status']) : '';
$page = isset($_GET['pg']) ? intval($_GET['pg']) : 1; // Changed from 'page' to 'pg'
if ($page < 1) $page = 1;
$items_per_page = 10;

// Get orders
$orders = [];
if (!empty($search)) {
    $stmt = $order->search($search, $page, $items_per_page);
    $total_rows = $order->countSearch($search);
} elseif (!empty($status)) {
    $stmt = $order->readByStatus($status, $page, $items_per_page);
    $total_rows = $order->countByStatus($status);
} else {
    $stmt = $order->read($items_per_page, $page);
    $total_rows = $order->countAll();
}

// Fetch orders
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $orders[] = $row;
}

// Calculate total pages and pagination range
$total_pages = ceil($total_rows / $items_per_page);
$start_item = ($page - 1) * $items_per_page + 1;
$end_item = min($page * $items_per_page, $total_rows);

// Pagination display logic
$max_visible_pages = 5; // Max page numbers to show (excluding First, Last, ellipsis)
$half_visible = floor($max_visible_pages / 2);
$start_page = max(1, $page - $half_visible);
$end_page = min($total_pages, $start_page + $max_visible_pages - 1);
if ($end_page - $start_page < $max_visible_pages - 1) {
    $start_page = max(1, $end_page - $max_visible_pages + 1);
}

// Define currency constant if not defined
if (!defined('CURRENCY')) {
    define('CURRENCY', 'đ');
}

// Debug output (for development)
$debug_info = [
    'total_rows' => $total_rows,
    'total_pages' => $total_pages,
    'current_page' => $page,
    'items_per_page' => $items_per_page,
    'orders_count' => count($orders)
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Management</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" integrity="sha512-z3gLpd7yknf1YoNbCzqRKc4qyor8gaKU1qmn+CShxbuBusANI9QpRohGBreCFkKxLhei6S9CQXFEbbKuqLg0DA==" crossorigin="anonymous">
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
        .badge {
            padding: 8px 12px;
        }
        .table img {
            object-fit: cover;
        }
        .pagination .page-link {
            color: #007bff;
            transition: background-color 0.2s, color 0.2s;
        }
        .pagination .page-link:hover {
            background-color: #e9ecef;
            color: #0056b3;
        }
        .pagination .page-item.active .page-link {
            background-color: #007bff;
            border-color: #007bff;
            color: white;
        }
        .pagination .page-item.disabled .page-link {
            color: #6c757d;
            pointer-events: none;
            background-color: #f8f9fa;
        }
        .pagination-info {
            font-size: 0.9rem;
            color: #6c757d;
            margin-bottom: 1rem;
        }
        .debug-info {
            display: none; /* Enable in development */
            position: fixed;
            bottom: 10px;
            right: 10px;
            background: rgba(0, 0, 0, 0.8);
            color: white;
            padding: 10px;
            border-radius: 4px;
            font-size: 12px;
            max-width: 300px;
        }
    </style>
</head>
<body>
    <div class="d-flex">
        <!-- Sidebar -->
        <?php include 'sidebar.php'; ?>

        <!-- Main Content -->
        <div class="flex-grow-1 p-4">
            <div class="container-fluid">
                <h1 class="mt-4 mb-3">Order Management</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.php?page=dashboard">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Orders</li>
                    </ol>
                </nav>

                <?php if (!empty($success_message)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo $success_message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo $error_message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>
                
                <?php if ($view_order): ?>
                <!-- Order Details View -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5><i class="fas fa-file-invoice me-2"></i> Order #<?php echo htmlspecialchars($view_order->order_number); ?> Details</h5>
                        <a href="index.php?page=orders" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left me-1"></i> Back to Orders
                        </a>
                    </div>
                    <div class="card-body">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h6 class="mb-3 fw-bold">Order Information</h6>
                                <table class="table table-bordered">
                                    <tr>
                                        <th style="width: 40%;">Order Number</th>
                                        <td><?php echo htmlspecialchars($view_order->order_number); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Date</th>
                                        <td><?php echo date('F d, Y H:i', strtotime($view_order->created_at)); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Status</th>
                                        <td>
                                            <?php
                                            $status_class = '';
                                            switch($view_order->status) {
                                                case 'pending': $status_class = 'bg-warning text-dark'; break;
                                                case 'processing': $status_class = 'bg-info text-dark'; break;
                                                case 'shipped': $status_class = 'bg-primary text-white'; break;
                                                case 'delivered': $status_class = 'bg-success text-white'; break;
                                                case 'cancelled': $status_class = 'bg-danger text-white'; break;
                                                default: $status_class = 'bg-secondary text-white';
                                            }
                                            ?>
                                            <span class="badge <?php echo $status_class; ?>"><?php echo ucfirst($view_order->status); ?></span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Payment Method</th>
                                        <td><?php echo ucfirst($view_order->payment_method); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Total Amount</th>
                                        <td><?php echo CURRENCY . number_format($view_order->total_amount); ?></td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h6 class="mb-3 fw-bold">Customer Information</h6>
                                <table class="table table-bordered">
                                    <tr>
                                        <th style="width: 40%;">Recipient Name</th>
                                        <td><?php echo htmlspecialchars($view_order->shipping_name); ?></td>
                                    </tr>
                                    <tr>
                                        <th style="width: 40%;">Customer</th>
                                        <td><?php echo $view_order->user_id ? "Registered User (ID: " . $view_order->user_id . ")" : "Guest"; ?></td>
                                    </tr>
                                    <tr>
                                        <th>Shipping Address</th>
                                        <td><?php echo htmlspecialchars($view_order->shipping_address); ?></td>
                                    </tr>
                                    <tr>
                                        <th>City</th>
                                        <td><?php echo htmlspecialchars($view_order->shipping_city); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Phone</th>
                                        <td><?php echo htmlspecialchars($view_order->shipping_phone); ?></td>
                                    </tr>
                                    <?php if(!empty($view_order->notes)): ?>
                                    <tr>
                                        <th>Notes</th>
                                        <td><?php echo htmlspecialchars($view_order->notes); ?></td>
                                    </tr>
                                    <?php endif; ?>
                                </table>
                            </div>
                        </div>
                        
                        <h6 class="mb-3 fw-bold">Ordered Items</h6>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Product</th>
                                        <th>Price</th>
                                        <th>Quantity</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $subtotal = 0;
                                    while ($item = $order_items->fetch(PDO::FETCH_ASSOC)): 
                                        $item_total = $item['price'] * $item['quantity'];
                                        $subtotal += $item_total;
                                    ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <?php if(!empty($item['image'])): ?>
                                                <img src="<?php echo htmlspecialchars($item['image']); ?>" class="img-thumbnail me-2" alt="<?php echo htmlspecialchars($item['product_name']); ?>" style="width: 50px; height: 50px;">
                                                <?php else: ?>
                                                <div class="bg-light d-flex align-items-center justify-content-center me-2" style="width: 50px; height: 50px;">
                                                    <i class="fas fa-tshirt text-secondary"></i>
                                                </div>
                                                <?php endif; ?>
                                                <?php echo htmlspecialchars($item['product_name']); ?>
                                            </div>
                                        </td>
                                        <td><?php echo CURRENCY . number_format($item['price']); ?></td>
                                        <td><?php echo $item['quantity']; ?></td>
                                        <td><?php echo CURRENCY . number_format($item_total); ?></td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="3" class="text-end"><strong>Subtotal:</strong></td>
                                        <td><?php echo CURRENCY . number_format($subtotal); ?></td>
                                    </tr>
                                    <tr>
                                        <td colspan="3" class="text-end"><strong>Shipping:</strong></td>
                                        <td>Free</td>
                                    </tr>
                                    <tr>
                                        <td colspan="3" class="text-end"><strong>Total:</strong></td>
                                        <td><strong><?php echo CURRENCY . number_format($view_order->total_amount); ?></strong></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        
                        <div class="row mt-4">
                            <div class="col-md-6">
                                <h6 class="mb-3 fw-bold">Update Order Status</h6>
                                <form action="index.php?page=orders&action=update_status" method="POST" class="d-flex">
                                    <input type="hidden" name="order_id" value="<?php echo $view_order->id; ?>">
                                    <select name="status" class="form-select me-2">
                                        <option value="pending" <?php echo ($view_order->status == 'pending') ? 'selected' : ''; ?>>Pending</option>
                                        <option value="processing" <?php echo ($view_order->status == 'processing') ? 'selected' : ''; ?>>Processing</option>
                                        <option value="shipped" <?php echo ($view_order->status == 'shipped') ? 'selected' : ''; ?>>Shipped</option>
                                        <option value="delivered" <?php echo ($view_order->status == 'delivered') ? 'selected' : ''; ?>>Delivered</option>
                                        <option value="cancelled" <?php echo ($view_order->status == 'cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                                    </select>
                                    <button type="submit" class="btn btn-primary">Update Status</button>
                                </form>
                            </div>
                            <div class="col-md-6 text-end">
                                <a href="index.php?page=orders&action=delete&id=<?php echo $view_order->id; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this order? This action cannot be undone.')">
                                    <i class="fas fa-trash me-1"></i> Delete Order
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php else: ?>
                <!-- Orders List View -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h6 class="m-0 fw-bold text-primary"><i class="fas fa-table me-2"></i> Orders List</h6>
                    </div>
                    <div class="card-body">
                        <!-- Search and Filter Form -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <form action="index.php" method="GET" class="d-flex">
                                    <input type="hidden" name="page" value="orders">
                                    <input type="text" name="search" class="form-control me-2" placeholder="Search orders..." value="<?php echo htmlspecialchars($search); ?>">
                                    <button type="submit" class="btn btn-primary">Search</button>
                                </form>
                            </div>
                            <div class="col-md-6">
                                <form action="index.php" method="GET" class="d-flex justify-content-end">
                                    <input type="hidden" name="page" value="orders">
                                    <select name="status" class="form-select me-2" style="max-width: 200px;">
                                        <option value="">All Statuses</option>
                                        <option value="pending" <?php echo ($status == 'pending') ? 'selected' : ''; ?>>Pending</option>
                                        <option value="processing" <?php echo ($status == 'processing') ? 'selected' : ''; ?>>Processing</option>
                                        <option value="shipped" <?php echo ($status == 'shipped') ? 'selected' : ''; ?>>Shipped</option>
                                        <option value="delivered" <?php echo ($status == 'delivered') ? 'selected' : ''; ?>>Delivered</option>
                                        <option value="cancelled" <?php echo ($status == 'cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                                    </select>
                                    <button type="submit" class="btn btn-secondary">Filter</button>
                                </form>
                            </div>
                        </div>
                        
                        <!-- Orders Table -->
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Order #</th>
                                        <th>Date</th>
                                        <th>Customer</th>
                                        <th>Total</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($orders)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center">No orders found</td>
                                    </tr>
                                    <?php else: ?>
                                    <?php foreach ($orders as $order_item): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($order_item['order_number']); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($order_item['created_at'])); ?></td>
                                        <td>
                                            <?php if (isset($order_item['full_name']) && !empty($order_item['full_name'])): ?>
                                            <?php echo htmlspecialchars($order_item['full_name']); ?>
                                            <?php elseif (isset($order_item['username']) && !empty($order_item['username'])): ?>
                                            <?php echo htmlspecialchars($order_item['username']); ?>
                                            <?php elseif (isset($order_item['email']) && !empty($order_item['email'])): ?>
                                            <?php echo htmlspecialchars($order_item['email']); ?>
                                            <?php else: ?>
                                            Guest
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo CURRENCY . number_format($order_item['total_amount']); ?></td>
                                        <td>
                                            <?php
                                            $status_class = '';
                                            switch($order_item['status']) {
                                                case 'pending': $status_class = 'bg-warning text-dark'; break;
                                                case 'processing': $status_class = 'bg-info text-dark'; break;
                                                case 'shipped': $status_class = 'bg-primary text-white'; break;
                                                case 'delivered': $status_class = 'bg-success text-white'; break;
                                                case 'cancelled': $status_class = 'bg-danger text-white'; break;
                                                default: $status_class = 'bg-secondary text-white';
                                            }
                                            ?>
                                            <span class="badge <?php echo $status_class; ?>"><?php echo ucfirst($order_item['status']); ?></span>
                                        </td>
                                        <td>
                                            <a href="index.php?page=orders&action=view&id=<?php echo $order_item['id']; ?>" class="btn btn-primary btn-sm me-1">
                                                <i class="fas fa-eye"></i> View
                                            </a>
                                            <a href="index.php?page=orders&action=delete&id=<?php echo $order_item['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this order? This action cannot be undone.')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        <?php if ($total_rows > 0): ?>
                        <div class="pagination-info text-center">
                            Showing <?php echo $start_item; ?> to <?php echo $end_item; ?> of <?php echo $total_rows; ?> orders
                        </div>
                        <nav aria-label="Page navigation">
                            <ul class="pagination justify-content-center mt-3">
                                <!-- First Page -->
                                <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="index.php?page=orders&<?php 
                                        echo (!empty($search)) ? 'search=' . urlencode($search) . '&' : '';
                                        echo (!empty($status)) ? 'status=' . urlencode($status) . '&' : '';
                                        echo 'pg=1';
                                    ?>" aria-label="First">
                                        <i class="fas fa-angle-double-left"></i>
                                    </a>
                                </li>
                                <!-- Previous Page -->
                                <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="index.php?page=orders&<?php 
                                        echo (!empty($search)) ? 'search=' . urlencode($search) . '&' : '';
                                        echo (!empty($status)) ? 'status=' . urlencode($status) . '&' : '';
                                        echo 'pg=' . ($page - 1);
                                    ?>" aria-label="Previous">
                                        <i class="fas fa-angle-left"></i>
                                    </a>
                                </li>
                                <!-- Page Numbers -->
                                <?php if ($start_page > 1): ?>
                                <li class="page-item disabled">
                                    <span class="page-link">...</span>
                                </li>
                                <?php endif; ?>
                                <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                                <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                                    <a class="page-link" href="index.php?page=orders&<?php 
                                        echo (!empty($search)) ? 'search=' . urlencode($search) . '&' : '';
                                        echo (!empty($status)) ? 'status=' . urlencode($status) . '&' : '';
                                        echo 'pg=' . $i;
                                    ?>"><?php echo $i; ?></a>
                                </li>
                                <?php endfor; ?>
                                <?php if ($end_page < $total_pages): ?>
                                <li class="page-item disabled">
                                    <span class="page-link">...</span>
                                </li>
                                <?php endif; ?>
                                <!-- Next Page -->
                                <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="index.php?page=orders&<?php 
                                        echo (!empty($search)) ? 'search=' . urlencode($search) . '&' : '';
                                        echo (!empty($status)) ? 'status=' . urlencode($status) . '&' : '';
                                        echo 'pg=' . ($page + 1);
                                    ?>" aria-label="Next">
                                        <i class="fas fa-angle-right"></i>
                                    </a>
                                </li>
                                <!-- Last Page -->
                                <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="index.php?page=orders&<?php 
                                        echo (!empty($search)) ? 'search=' . urlencode($search) . '&' : '';
                                        echo (!empty($status)) ? 'status=' . urlencode($status) . '&' : '';
                                        echo 'pg=' . $total_pages;
                                    ?>" aria-label="Last">
                                        <i class="fas fa-angle-double-right"></i>
                                    </a>
                                </li>
                            </ul>
                        </nav>
                        <?php endif; ?>
                    </div>
                </div>
                
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS and Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>