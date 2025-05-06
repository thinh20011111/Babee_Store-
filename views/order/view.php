<?php 
$page_title = "Order Details";
include 'views/layouts/header.php'; 
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="mb-0">Order #<?php echo $this->order->order_number; ?></h1>
                <a href="index.php?controller=user&action=orders" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i> Back to Orders
                </a>
            </div>
            
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">Order Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Order Number:</strong> <?php echo htmlspecialchars($this->order->order_number); ?></p>
                            <p><strong>Date Placed:</strong> <?php echo date('F j, Y', strtotime($this->order->created_at)); ?></p>
                            <p><strong>Total Amount:</strong> <?php echo CURRENCY . number_format($this->order->total_amount); ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Status:</strong> 
                                <span class="badge <?php 
                                    switch($this->order->status) {
                                        case 'pending': echo 'bg-warning'; break;
                                        case 'processing': echo 'bg-info'; break;
                                        case 'shipped': echo 'bg-primary'; break;
                                        case 'delivered': echo 'bg-success'; break;
                                        case 'cancelled': echo 'bg-danger'; break;
                                        default: echo 'bg-secondary';
                                    }
                                ?>">
                                    <?php echo ucfirst($this->order->status); ?>
                                </span>
                            </p>
                            <p><strong>Payment Method:</strong> <?php echo htmlspecialchars($this->order->payment_method); ?></p>
                            <p><strong>Shipping Address:</strong> <?php echo htmlspecialchars($this->order->shipping_address); ?>, <?php echo htmlspecialchars($this->order->shipping_city); ?></p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">Order Items</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Product</th>
                                    <th class="text-center">Quantity</th>
                                    <th class="text-end">Price</th>
                                    <th class="text-end">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($item = $order_items->fetch(PDO::FETCH_ASSOC)): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <?php if(!empty($item['image'])): ?>
                                                    <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="me-3" style="width: 50px; height: 50px; object-fit: cover;">
                                                <?php else: ?>
                                                    <div class="bg-light d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                                                        <i class="fas fa-tshirt text-secondary"></i>
                                                    </div>
                                                <?php endif; ?>
                                                <div>
                                                    <h6 class="mb-0"><?php echo htmlspecialchars($item['name']); ?></h6>
                                                    <?php if(!empty($item['category_name'])): ?>
                                                        <small class="text-muted"><?php echo htmlspecialchars($item['category_name']); ?></small>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center"><?php echo $item['quantity']; ?></td>
                                        <td class="text-end"><?php echo CURRENCY . number_format($item['price']); ?></td>
                                        <td class="text-end"><?php echo CURRENCY . number_format($item['price'] * $item['quantity']); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <th colspan="3" class="text-end">Total:</th>
                                    <th class="text-end"><?php echo CURRENCY . number_format($this->order->total_amount); ?></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
            
            <?php if(!empty($this->order->notes)): ?>
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">Order Notes</h5>
                </div>
                <div class="card-body">
                    <p class="mb-0"><?php echo nl2br(htmlspecialchars($this->order->notes)); ?></p>
                </div>
            </div>
            <?php endif; ?>
            
            <div class="text-center">
                <a href="index.php" class="btn btn-outline-secondary">Continue Shopping</a>
                <?php if($this->order->status === 'pending'): ?>
                    <a href="index.php?controller=order&action=cancel&id=<?php echo $this->order->id; ?>" class="btn btn-danger ms-2" onclick="return confirm('Are you sure you want to cancel this order?');">Cancel Order</a>
                <?php endif; ?>
                
                <?php if(in_array($this->order->status, ['shipped', 'delivered'])): ?>
                    <a href="#" class="btn btn-primary ms-2">Track Shipment</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include 'views/layouts/footer.php'; ?>