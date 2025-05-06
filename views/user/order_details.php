<?php 
$page_title = "Order Details";
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
    
    <!-- Order Details Content -->
    <div class="col-md-9">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Order #<?php echo htmlspecialchars($order->order_number); ?></h5>
                <a href="index.php?controller=user&action=orders" class="btn btn-sm btn-light">
                    <i class="fas fa-arrow-left me-1"></i> Back to Orders
                </a>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h6 class="text-muted">Order Information</h6>
                        <p class="mb-1"><strong>Order Number:</strong> <?php echo htmlspecialchars($order->order_number); ?></p>
                        <p class="mb-1"><strong>Date:</strong> <?php echo date('F d, Y H:i', strtotime($order->created_at)); ?></p>
                        <p class="mb-1">
                            <strong>Status:</strong>
                            <?php
                            $status_class = '';
                            switch($order->status) {
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
                            <span class="badge <?php echo $status_class; ?>"><?php echo ucfirst($order->status); ?></span>
                        </p>
                        <p class="mb-1"><strong>Payment Method:</strong> <?php echo ucfirst($order->payment_method); ?></p>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted">Shipping Information</h6>
                        <p class="mb-1"><strong>Address:</strong> <?php echo htmlspecialchars($order->shipping_address); ?></p>
                        <p class="mb-1"><strong>City:</strong> <?php echo htmlspecialchars($order->shipping_city); ?></p>
                        <p class="mb-1"><strong>Phone:</strong> <?php echo htmlspecialchars($order->shipping_phone); ?></p>
                        <?php if(!empty($order->notes)): ?>
                        <p class="mb-1"><strong>Notes:</strong> <?php echo htmlspecialchars($order->notes); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <h6 class="text-muted mb-3">Order Items</h6>
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
                            while($item = $order_items->fetch(PDO::FETCH_ASSOC)): 
                                $item_total = $item['price'] * $item['quantity'];
                                $subtotal += $item_total;
                            ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <?php if(!empty($item['image'])): ?>
                                        <img src="<?php echo htmlspecialchars($item['image']); ?>" class="img-thumbnail me-2" alt="<?php echo htmlspecialchars($item['product_name']); ?>" style="width: 50px;">
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
                                <td><strong><?php echo CURRENCY . number_format($order->total_amount); ?></strong></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                
                <?php if($order->status == 'pending'): ?>
                <div class="text-end mt-3">
                    <a href="index.php?controller=order&action=cancel&id=<?php echo $order->id; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to cancel this order?')">
                        <i class="fas fa-times me-1"></i> Cancel Order
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Order Timeline -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-light">
                <h5 class="mb-0">Order Status Timeline</h5>
            </div>
            <div class="card-body">
                <div class="order-timeline">
                    <div class="timeline-item">
                        <div class="timeline-icon <?php echo in_array($order->status, ['pending', 'processing', 'shipped', 'delivered']) ? 'active' : ''; ?>">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <div class="timeline-content">
                            <h6>Order Placed</h6>
                            <p class="text-muted"><?php echo date('F d, Y H:i', strtotime($order->created_at)); ?></p>
                        </div>
                    </div>
                    <div class="timeline-item">
                        <div class="timeline-icon <?php echo in_array($order->status, ['processing', 'shipped', 'delivered']) ? 'active' : ''; ?>">
                            <i class="fas fa-cogs"></i>
                        </div>
                        <div class="timeline-content">
                            <h6>Processing</h6>
                            <p class="text-muted">Order confirmed and being processed</p>
                        </div>
                    </div>
                    <div class="timeline-item">
                        <div class="timeline-icon <?php echo in_array($order->status, ['shipped', 'delivered']) ? 'active' : ''; ?>">
                            <i class="fas fa-truck"></i>
                        </div>
                        <div class="timeline-content">
                            <h6>Shipped</h6>
                            <p class="text-muted">Order has been shipped</p>
                        </div>
                    </div>
                    <div class="timeline-item">
                        <div class="timeline-icon <?php echo $order->status == 'delivered' ? 'active' : ''; ?>">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="timeline-content">
                            <h6>Delivered</h6>
                            <p class="text-muted">Order has been delivered</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'views/layouts/footer.php'; ?>
