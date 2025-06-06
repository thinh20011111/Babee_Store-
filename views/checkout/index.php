<?php 
$page_title = "Thanh toán";
include 'views/layouts/header.php'; 
?>

<style>
/* General styles */
* {
    box-sizing: border-box;
}
.container {
    padding-left: 15px;
    padding-right: 15px;
}

/* Checkout steps */
.checkout-steps {
    display: flex;
    justify-content: center;
    align-items: center;
    margin-bottom: 30px;
}
.checkout-steps .step {
    width: 150px;
    height: 100px;
    padding: 10px;
    border-radius: 8px;
    background: #f8f9fa;
    transition: background 0.3s;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    margin: 0 20px;
}
.checkout-steps .step.active {
    background: #0d6efd;
    color: white;
}
.checkout-steps .step-icon {
    font-size: 1.5rem;
    margin-bottom: 5px;
}
.checkout-steps .step-label {
    font-size: 0.9rem;
    text-align: center;
}
.checkout-steps .arrow {
    font-size: 1.2rem;
    color: #6c757d;
    line-height: 100px;
}

/* Card styles */
.card {
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}
.card-header {
    padding: 15px;
    border-radius: 10px 10px 0 0;
    z-index: 10;
}
.card-body {
    padding: 20px;
}

/* Form styles */
.form-control, .form-check-input {
    border-radius: 5px;
}
.form-control:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 5px rgba(13,110,253,0.3);
}
.invalid-feedback {
    font-size: 0.9rem;
    color: #dc3545;
    margin-top: 5px;
}
.form-check-label {
    font-size: 1rem;
}

/* Button styles */
.btn-primary {
    background: #0d6efd;
    border: none;
    padding: 10px 20px;
    border-radius: 5px;
    transition: background 0.3s;
}
.btn-primary:hover {
    background: #0a58ca;
}
.btn-outline-secondary {
    border-radius: 5px;
    padding: 10px 20px;
}

/* Alert and other styles */
.alert {
    border-radius: 5px;
    padding: 15px;
    margin-bottom: 20px;
}
.order-item img, .order-item .bg-light {
    border-radius: 5px;
}
.secure-checkout {
    background: #e9ecef;
    padding: 15px;
    border-radius: 5px;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    /* General container */
    .container {
        padding-left: 10px;
        padding-right: 10px;
    }

    /* Checkout steps */
    .checkout-steps {
        flex-direction: column;
        align-items: center;
        margin-bottom: 20px;
    }
    .checkout-steps .step {
        width: 120px;
        height: 80px;
        margin: 8px 0;
        padding: 8px;
    }
    .checkout-steps .arrow {
        display: none;
    }
    .checkout-steps .step-icon {
        font-size: 1.3rem;
    }
    .checkout-steps .step-label {
        font-size: 0.85rem;
    }

    /* Card and header */
    .card-header h5 {
        font-size: 1.2rem;
    }
    .card-body {
        padding: 15px;
    }

    /* Form */
    .form-control, .btn {
        font-size: 0.95rem;
        padding: 8px;
    }
    .form-check-label {
        font-size: 0.95rem;
    }
    .form-label {
        font-size: 0.95rem;
    }
    .mb-3 {
        margin-bottom: 0.75rem !important;
    }
    .mb-4 {
        margin-bottom: 1rem !important;
    }
    .row > div {
        margin-bottom: 0.5rem;
    }

    /* Order summary */
    .order-item img, .order-item .bg-light {
        width: 35px;
        height: 35px;
    }
    .order-item .small {
        font-size: 0.9rem;
    }
    .order-item .x-small {
        font-size: 0.8rem;
    }
    .order-items .text-end {
        font-size: 0.9rem;
    }
    .secure-checkout .small {
        font-size: 0.85rem;
    }
    .shipping-info .small {
        font-size: 0.85rem;
    }
}

@media (max-width: 576px) {
    /* General container */
    .container {
        padding-left: 8px;
        padding-right: 8px;
    }

    /* Checkout steps */
    .checkout-steps {
        margin-bottom: 15px;
    }
    .checkout-steps .step {
        width: 100px;
        height: 70px;
        margin: 6px 0;
        padding: 6px;
    }
    .checkout-steps .step-icon {
        font-size: 1.2rem;
    }
    .checkout-steps .step-label {
        font-size: 0.8rem;
    }

    /* Card and header */
    .card-header h5 {
        font-size: 1.1rem;
    }
    .card-body {
        padding: 12px;
    }

    /* Form */
    .form-control, .btn {
        font-size: 0.9rem;
        padding: 6px;
    }
    .form-check-label {
        font-size: 0.9rem;
    }
    .form-label {
        font-size: 0.9rem;
    }
    .invalid-feedback {
        font-size: 0.85rem;
    }
    .row > div {
        margin-bottom: 0.4rem;
    }

    /* Order summary */
    .order-item img, .order-item .bg-light {
        width: 30px;
        height: 30px;
    }
    .order-item .small {
        font-size: 0.85rem;
    }
    .order-item .x-small {
        font-size: 0.75rem;
    }
    .order-items .text-end {
        font-size: 0.85rem;
    }
    .secure-checkout .fa-2x {
        font-size: 1.5rem;
    }
    .secure-checkout .small {
        font-size: 0.8rem;
    }
    .shipping-info .small {
        font-size: 0.8rem;
    }

    /* Buttons */
    .text-end .btn {
        display: block;
        width: 100%;
        margin-bottom: 0.5rem;
    }
    .text-end .me-2 {
        margin-right: 0 !important;
    }
}
</style>

<h1 class="mb-4">Thanh toán</h1>

<!-- Checkout Steps -->
<div class="checkout-steps">
    <div class="step active">
        <div class="step-icon">
            <i class="fas fa-shopping-cart"></i>
        </div>
        <div class="step-label">Giỏ hàng</div>
    </div>
    <div class="arrow">
        <i class="fas fa-arrow-right"></i>
    </div>
    <div class="step active">
        <div class="step-icon">
            <i class="fas fa-address-card"></i>
        </div>
        <div class="step-label">Thông tin</div>
    </div>
    <div class="arrow">
        <i class="fas fa-arrow-right"></i>
    </div>
    <div class="step">
        <div class="step-icon">
            <i class="fas fa-check-circle"></i>
        </div>
        <div class="step-label">Xác nhận</div>
    </div>
</div>

<!-- Success Message -->
<?php if(!empty($success)): ?>
<div class="alert alert-success mb-4">
    <?php echo htmlspecialchars($success); ?>
</div>

<div class="text-center mb-5">
    <a href="index.php" class="btn btn-primary me-2">Tiếp tục mua sắm</a>
    <a href="index.php?controller=user&action=orders" class="btn btn-outline-secondary">Xem đơn hàng</a>
</div>
<?php else: ?>

<!-- Error Message -->
<?php 
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
if (!empty($_SESSION['order_message'])): ?>
<div class="alert alert-danger mb-4" id="message">
    <strong>Lỗi:</strong> <?php 
    echo htmlspecialchars($_SESSION['order_message']); 
    unset($_SESSION['order_message']);
    ?>
</div>
<?php endif; ?>

<div class="row">
    <!-- Checkout Form -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Thông tin giao hàng</h5>
            </div>
            <div class="card-body">
                <form action="index.php?controller=order&action=create" method="POST" id="checkout-form">
                    <div class="row">
                        <div class="col-12 col-md-6 mb-3">
                            <label for="shipping_name" class="form-label">Người nhận <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="shipping_name" name="shipping_name" required value="<?php echo isset($user_data['full_name']) ? htmlspecialchars($user_data['full_name']) : ''; ?>">
                            <div id="shipping_name-error" class="invalid-feedback">Vui lòng nhập tên người nhận (không chỉ chứa khoảng trắng).</div>
                        </div>
                        <div class="col-12 col-md-6 mb-3">
                            <label for="customer_email" class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="customer_email" name="customer_email" required value="<?php echo isset($user_data['email']) ? htmlspecialchars($user_data['email']) : ''; ?>">
                            <div id="customer_email-error" class="invalid-feedback">Vui lòng nhập email hợp lệ.</div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-12 col-md-6 mb-3">
                            <label for="shipping_phone" class="form-label">Số điện thoại <span class="text-danger">*</span></label>
                            <input type="tel" class="form-control" id="shipping_phone" name="shipping_phone" required value="<?php echo isset($user_data['phone']) ? htmlspecialchars($user_data['phone']) : ''; ?>">
                            <div id="shipping_phone-error" class="invalid-feedback">Vui lòng nhập số điện thoại.</div>
                        </div>
                        <div class="col-12 col-md-6 mb-3">
                            <label for="shipping_city" class="form-label">Thành phố <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="shipping_city" name="shipping_city" required value="<?php echo isset($user_data['city']) ? htmlspecialchars($user_data['city']) : ''; ?>">
                            <div id="shipping_city-error" class="invalid-feedback">Vui lòng nhập thành phố.</div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="shipping_address" class="form-label">Địa chỉ <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="shipping_address" name="shipping_address" rows="3" required><?php echo isset($user_data['address']) ? htmlspecialchars($user_data['address']) : ''; ?></textarea>
                        <div id="shipping_address-error" class="invalid-feedback">Vui lòng nhập địa chỉ.</div>
                    </div>
                    
                    <div class="mb-4">
                        <label for="notes" class="form-label">Ghi chú đơn hàng</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="Hướng dẫn giao hàng hoặc ghi chú khác"></textarea>
                    </div>
                    
                    <div class="card-header bg-primary text-white mb-3">
                        <h5 class="mb-0">Phương thức thanh toán</h5>
                    </div>
                    
                    <div class="payment-methods mb-4">
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="radio" name="payment_method" id="payment_cod" value="cod" checked required>
                            <label class="form-check-label" for="payment_cod">
                                <i class="fas fa-money-bill-wave me-2"></i> Thanh toán khi nhận hàng
                            </label>
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="radio" name="payment_method" id="payment_bank" value="bank" required disabled>
                            <label class="form-check-label" for="payment_bank">
                                <i class="fas fa-university me-2"></i> Chuyển khoản ngân hàng
                            </label>
                        </div>
                    </div>
                    <div id="payment_method-error" class="invalid-feedback d-block" style="display: none;">Vui lòng chọn phương thức thanh toán.</div>
                    
                    <div class="text-end">
                        <a href="index.php?controller=cart&action=index" class="btn btn-outline-secondary me-2">Quay lại giỏ hàng</a>
                        <button type="submit" class="btn btn-primary">Đặt hàng</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Order Summary -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Tóm tắt đơn hàng</h5>
            </div>
            <div class="card-body">
                <div class="order-items mb-3">
                    <?php foreach($cart_items as $item): ?>
                    <?php 
                    $price = (!empty($item['data']['sale_price']) && $item['data']['sale_price'] > 0) ? $item['data']['sale_price'] : $item['data']['price'];
                    $total = $price * $item['quantity'];
                    ?>
                    <div class="order-item d-flex justify-content-between align-items-center mb-2">
                        <div class="d-flex align-items-center">
                            <?php if(!empty($item['data']['image'])): ?>
                            <img src="<?php echo htmlspecialchars($item['data']['image']); ?>" class="img-thumbnail me-2" alt="<?php echo htmlspecialchars($item['data']['name']); ?>" style="width: 40px;">
                            <?php else: ?>
                            <div class="bg-light d-flex align-items-center justify-content-center me-2" style="width: 40px; height: 40px;">
                                <i class="fas fa-tshirt text-secondary"></i>
                            </div>
                            <?php endif; ?>
                            <div>
                                <div class="small"><?php echo htmlspecialchars($item['data']['name']); ?></div>
                                <div class="text-muted x-small">Số lượng: <?php echo $item['quantity']; ?></div>
                            </div>
                        </div>
                        <div class="text-end">
                            <?php echo CURRENCY . number_format($total, 0, ',', '.'); ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <hr>
                
                <div class="d-flex justify-content-between mb-2">
                    <span>Tạm tính:</span>
                    <span><?php echo CURRENCY . number_format($cart_subtotal, 0, ',', '.'); ?></span>
                </div>
                
                <?php if(isset($_SESSION['promotion'])): ?>
                <div class="d-flex justify-content-between mb-2">
                    <span>Giảm giá:</span>
                    <span class="text-danger">- <?php echo CURRENCY . number_format($promotion_discount, 0, ',', '.'); ?></span>
                </div>
                <?php endif; ?>
                
                <div class="d-flex justify-content-between mb-2">
                    <span>Giao hàng:</span>
                    <span>Miễn phí</span>
                </div>
                
                <hr>
                
                <div class="d-flex justify-content-between mb-3">
                    <strong>Tổng cộng:</strong>
                    <strong class="text-primary"><?php echo CURRENCY . number_format($cart_total, 0, ',', '.'); ?></strong>
                </div>
            </div>
        </div>
        
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="secure-checkout text-center mb-3">
                    <i class="fas fa-lock text-success mb-2 fa-2x"></i>
                    <p class="mb-0 small">Thông tin thanh toán của bạn được xử lý an toàn. Chúng tôi không lưu trữ chi tiết thẻ tín dụng cũng như không có quyền truy cập vào thông tin thẻ tín dụng của bạn.</p>
                </div>
                
                <hr>
                
                <div class="shipping-info small text-muted">
                    <p class="mb-1"><i class="fas fa-truck me-2"></i> Miễn phí vận chuyển cho đơn hàng trên <?php echo CURRENCY; ?>500,000</p>
                    <p class="mb-1"><i class="fas fa-undo me-2"></i> Chính sách đổi trả trong 30 ngày</p>
                    <p class="mb-0"><i class="fas fa-shield-alt me-2"></i> Mua sắm an toàn</p>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('checkout-form');
    let messageDiv = document.getElementById('message');

    if (form) {
        form.addEventListener('submit', function(event) {
            let isValid = true;

            // Validate required text fields
            const requiredFields = form.querySelectorAll('input[required]:not([type="radio"]), textarea[required]');
            requiredFields.forEach(field => {
                const errorDiv = document.getElementById(`${field.id}-error`);
                const value = field.value.trim();
                if (value === '' || (field.id === 'shipping_name' && value.match(/^\s*$/))) {
                    isValid = false;
                    field.classList.add('is-invalid');
                    if (errorDiv) errorDiv.style.display = 'block';
                } else {
                    field.classList.remove('is-invalid');
                    if (errorDiv) errorDiv.style.display = 'none';
                }
            });

            // Validate email format
            const emailField = document.getElementById('customer_email');
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            const emailError = document.getElementById('customer_email-error');
            if (emailField && !emailRegex.test(emailField.value.trim())) {
                isValid = false;
                emailField.classList.add('is-invalid');
                if (emailError) emailError.style.display = 'block';
            } else if (emailField) {
                emailField.classList.remove('is-invalid');
                if (emailError) errorDiv.style.display = 'none';
            }

            // Validate payment method
            const paymentMethods = form.querySelectorAll('input[name="payment_method"]');
            const paymentError = document.getElementById('payment_method-error');
            const isPaymentSelected = Array.from(paymentMethods).some(radio => radio.checked);
            if (!isPaymentSelected) {
                isValid = false;
                paymentError.style.display = 'block';
            } else {
                paymentError.style.display = 'none';
            }

            if (!isValid) {
                event.preventDefault();
                // Create messageDiv if it doesn't exist
                if (!messageDiv) {
                    messageDiv = document.createElement('div');
                    messageDiv.id = 'message';
                    messageDiv.className = 'alert alert-danger mb-4';
                    form.parentNode.insertBefore(messageDiv, form.nextSibling);
                }
                messageDiv.textContent = 'Vui lòng kiểm tra và điền đầy đủ thông tin.';
                messageDiv.style.display = 'block';
                window.scrollTo({ top: 0, behavior: 'smooth' });
            } else if (messageDiv) {
                // Hide messageDiv if it exists and validation passes
                messageDiv.style.display = 'none';
            }
        });
    }
});
</script>

<?php include 'views/layouts/footer.php'; ?>