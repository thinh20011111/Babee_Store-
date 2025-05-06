<?php 
$page_title = "Checkout";
include 'views/layouts/header.php'; 
?>

<h1 class="mb-4">Checkout</h1>

<!-- Checkout Steps -->
<div class="checkout-steps mb-4">
    <div class="row text-center">
        <div class="col-4">
            <div class="step active">
                <div class="step-icon">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <div class="step-label">Cart</div>
            </div>
        </div>
        <div class="col-4">
            <div class="step active">
                <div class="step-icon">
                    <i class="fas fa-address-card"></i>
                </div>
                <div class="step-label">Information</div>
            </div>
        </div>
        <div class="col-4">
            <div class="step">
                <div class="step-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="step-label">Confirmation</div>
            </div>
        </div>
    </div>
</div>

<!-- Success Message -->
<?php if(!empty($success)): ?>
<div class="alert alert-success mb-4">
    <?php echo $success; ?>
</div>

<div class="text-center mb-5">
    <a href="index.php" class="btn btn-primary me-2">Continue Shopping</a>
    <a href="index.php?controller=user&action=orders" class="btn btn-outline-secondary">View Your Orders</a>
</div>
<?php else: ?>

<!-- Error Message -->
<?php if(!empty($error)): ?>
<div class="alert alert-danger mb-4">
    <?php echo $error; ?>
</div>
<?php endif; ?>

<div class="row">
    <!-- Checkout Form -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Shipping Information</h5>
            </div>
            <div class="card-body">
                <form action="index.php?controller=cart&action=checkout" method="POST" id="checkout-form">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="full_name" class="form-label">Full Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="full_name" name="full_name" required value="<?php echo isset($user_data['full_name']) ? htmlspecialchars($user_data['full_name']) : ''; ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="email" name="email" required value="<?php echo isset($user_data['email']) ? htmlspecialchars($user_data['email']) : ''; ?>">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label">Phone <span class="text-danger">*</span></label>
                            <input type="tel" class="form-control" id="phone" name="phone" required value="<?php echo isset($user_data['phone']) ? htmlspecialchars($user_data['phone']) : ''; ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="city" class="form-label">City <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="city" name="city" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="address" class="form-label">Address <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="address" name="address" rows="3" required><?php echo isset($user_data['address']) ? htmlspecialchars($user_data['address']) : ''; ?></textarea>
                    </div>
                    
                    <div class="mb-4">
                        <label for="notes" class="form-label">Order Notes</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="Special instructions for delivery or other notes"></textarea>
                    </div>
                    
                    <div class="card-header bg-primary text-white mb-3">
                        <h5 class="mb-0">Payment Method</h5>
                    </div>
                    
                    <div class="payment-methods mb-4">
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="radio" name="payment_method" id="payment_cod" value="cod" checked>
                            <label class="form-check-label" for="payment_cod">
                                <i class="fas fa-money-bill-wave me-2"></i> Cash on Delivery
                            </label>
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="radio" name="payment_method" id="payment_bank" value="bank">
                            <label class="form-check-label" for="payment_bank">
                                <i class="fas fa-university me-2"></i> Bank Transfer
                            </label>
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="radio" name="payment_method" id="payment_paypal" value="paypal">
                            <label class="form-check-label" for="payment_paypal">
                                <i class="fab fa-paypal me-2"></i> PayPal
                            </label>
                        </div>
                    </div>
                    
                    <div class="text-end">
                        <a href="index.php?controller=cart&action=index" class="btn btn-outline-secondary me-2">Back to Cart</a>
                        <button type="submit" class="btn btn-primary">Place Order</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Order Summary -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Order Summary</h5>
            </div>
            <div class="card-body">
                <div class="order-items mb-3">
                    <?php foreach($cart_items as $item): ?>
                    <?php 
                    $price = (!empty($item['sale_price']) && $item['sale_price'] > 0) ? $item['sale_price'] : $item['price'];
                    $total = $price * $item['quantity'];
                    ?>
                    <div class="order-item d-flex justify-content-between align-items-center mb-2">
                        <div class="d-flex align-items-center">
                            <?php if(!empty($item['image'])): ?>
                            <img src="<?php echo htmlspecialchars($item['image']); ?>" class="img-thumbnail me-2" alt="<?php echo htmlspecialchars($item['name']); ?>" style="width: 40px;">
                            <?php else: ?>
                            <div class="bg-light d-flex align-items-center justify-content-center me-2" style="width: 40px; height: 40px;">
                                <i class="fas fa-tshirt text-secondary"></i>
                            </div>
                            <?php endif; ?>
                            <div>
                                <div class="small"><?php echo htmlspecialchars($item['name']); ?></div>
                                <div class="text-muted x-small">Qty: <?php echo $item['quantity']; ?></div>
                            </div>
                        </div>
                        <div class="text-end">
                            <?php echo CURRENCY . number_format($total); ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <hr>
                
                <div class="d-flex justify-content-between mb-2">
                    <span>Subtotal:</span>
                    <span><?php echo CURRENCY . number_format($cart_subtotal); ?></span>
                </div>
                
                <?php if(isset($_SESSION['promotion'])): ?>
                <div class="d-flex justify-content-between mb-2">
                    <span>Discount:</span>
                    <span class="text-danger">- <?php echo CURRENCY . number_format($promotion_discount); ?></span>
                </div>
                <?php endif; ?>
                
                <div class="d-flex justify-content-between mb-2">
                    <span>Shipping:</span>
                    <span>Free</span>
                </div>
                
                <hr>
                
                <div class="d-flex justify-content-between mb-3">
                    <strong>Total:</strong>
                    <strong class="text-primary"><?php echo CURRENCY . number_format($cart_total); ?></strong>
                </div>
            </div>
        </div>
        
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="secure-checkout text-center mb-3">
                    <i class="fas fa-lock text-success mb-2 fa-2x"></i>
                    <p class="mb-0 small">Your payment information is processed securely. We do not store credit card details nor have access to your credit card information.</p>
                </div>
                
                <hr>
                
                <div class="shipping-info small text-muted">
                    <p class="mb-1"><i class="fas fa-truck me-2"></i> Free shipping on all orders over <?php echo CURRENCY; ?>500,000</p>
                    <p class="mb-1"><i class="fas fa-undo me-2"></i> 30 days return policy</p>
                    <p class="mb-0"><i class="fas fa-shield-alt me-2"></i> Secure shopping</p>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Form validation
    const form = document.getElementById('checkout-form');
    
    if (form) {
        form.addEventListener('submit', function(event) {
            const requiredFields = form.querySelectorAll('[required]');
            let isValid = true;
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    field.classList.add('is-invalid');
                } else {
                    field.classList.remove('is-invalid');
                }
            });
            
            if (!isValid) {
                event.preventDefault();
                alert('Please fill all required fields.');
            }
        });
    }
});
</script>

<?php include 'views/layouts/footer.php'; ?>
