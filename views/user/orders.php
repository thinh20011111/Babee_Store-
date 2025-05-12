<?php 
$page_title = "My Orders";
include 'views/layouts/header.php'; 
?>

<div class="row">
    <!-- Sidebar Menu -->
    <div class="col-md-3 mb-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">My Account</h5>
            </div>
            <div class="list-group list-group-flush">
                <a href="index.php?controller=user&action=profile" class="list-group-item list-group-item-action">
                    <i class="fas fa-user me-2"></i> My Profile
                </a>
                <a href="index.php?controller=user&action=orders" class="list-group-item list-group-item-action active">
                    <i class="fas fa-shopping-bag me-2"></i> My Orders
                </a>
                <a href="index.php?controller=user&action=changePassword" class="list-group-item list-group-item-action">
                    <i class="fas fa-key me-2"></i> Change Password
                </a>
                <a href="index.php?controller=user&action=logout" class="list-group-item list-group-item-action text-danger">
                    <i class="fas fa-sign-out-alt me-2"></i> Logout
                </a>
            </div>
        </div>
    </div>
    
    <!-- Orders Content -->
    <div class="col-md-9">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">My Orders</h5>
            </div>
            <div class="card-body p-0">
                <?php if(isset($_SESSION['order_message'])): ?>
                <div class="alert alert-info m-3">
                    <?php
                    echo $_SESSION['order_message'];
                    unset($_SESSION['order_message']);
                    ?>
                </div>
                <?php endif; ?>
                
                <?php if(!$stmt): ?>
                <div class="alert alert-danger m-3">
                    Error: $stmt is null
                </div>
                <?php elseif($stmt->rowCount() == 0): ?>
                <div class="alert alert-info m-3">
                    <p class="mb-0">You haven't placed any orders yet. <a href="index.php?controller=product&action=list" class="alert-link">Start shopping</a></p>
                </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Order #</th>
                                <th>Date</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($order = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($order['order_number']); ?></td>
                                <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                                <td><?php echo (defined('CURRENCY') ? CURRENCY : '$') . number_format($order['total_amount'], 2); ?></td>
                                <td>
                                    <?php
                                    $status_class = '';
                                    switch($order['status']) {
                                        case 'pending':
                                            $status_class = 'bg-warning text-dark';
                                            break;
                                        case 'processing':
                                            $status_class = 'bg-info text-dark';
                                            break;
                                        case 'shipped':
                                            $status_class = 'bg-primary';
                                            break;
                                        case 'delivered':
                                            $status_class = 'bg-success';
                                            break;
                                        case 'cancelled':
                                            $status_class = 'bg-danger';
                                            break;
                                        default:
                                            $status_class = 'bg-secondary';
                                    }
                                    ?>
                                    <span class="badge <?php echo $status_class; ?>"><?php echo ucfirst($order['status']); ?></span>
                                </td>
                                <td>
                                    <a href="index.php?controller=user&action=orderDetails&id=<?php echo $order['id']; ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                    <?php if($order['status'] == 'pending'): ?>
                                    <a href="index.php?controller=order&action=cancel&id=<?php echo $order['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to cancel this order?')">
                                        <i class="fas fa-times"></i> Cancel
                                    </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include 'views/layouts/footer.php'; ?>