<?php 
$page_title = "Giỏ hàng";
include 'views/layouts/header.php'; 
?>

<div class="container mt-5 mb-5">
    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom-0 py-3">
                    <h5 class="mb-0">Giỏ hàng của bạn</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($cart_items)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-shopping-cart fa-4x text-muted mb-3"></i>
                            <h4>Giỏ hàng của bạn đang trống</h4>
                            <p class="text-muted">Khám phá các sản phẩm và thêm vào giỏ hàng</p>
                            <a href="index.php?controller=product&action=list" class="btn btn-primary mt-3">Tiếp tục mua sắm</a>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table align-middle">
                                <thead>
                                    <tr>
                                        <th colspan="2">Sản phẩm</th>
                                        <th>Giá</th>
                                        <th>Số lượng</th>
                                        <th class="text-end">Tổng tiền</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($cart_items as $item): ?>
                                        <tr data-product-id="<?php echo $item['id']; ?>">
                                            <td width="80">
                                                <?php if (!empty($item['image'])): ?>
                                                    <img src="<?php echo htmlspecialchars($item['image']); ?>" class="img-fluid rounded" alt="<?php echo htmlspecialchars($item['name']); ?>" style="max-width: 70px;">
                                                <?php else: ?>
                                                    <div class="bg-light rounded d-flex align-items-center justify-content-center" style="width: 70px; height: 70px;">
                                                        <i class="fas fa-tshirt fa-2x text-secondary"></i>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <h6 class="mb-1"><?php echo htmlspecialchars($item['name']); ?></h6>
                                            </td>
                                            <td>
                                                <?php 
                                                $price = (!empty($item['sale_price']) && $item['sale_price'] > 0) ? $item['sale_price'] : $item['price'];
                                                echo CURRENCY . number_format($price);
                                                ?>
                                            </td>
                                            <td>
                                                <div class="input-group input-group-sm" style="width: 100px;">
                                                    <button type="button" class="btn btn-outline-secondary decrease-qty-btn"><i class="fas fa-minus"></i></button>
                                                    <input type="number" class="form-control text-center item-qty" value="<?php echo $item['quantity']; ?>" min="1" max="<?php echo isset($item['stock']) ? $item['stock'] : 10; ?>">
                                                    <button type="button" class="btn btn-outline-secondary increase-qty-btn"><i class="fas fa-plus"></i></button>
                                                </div>
                                            </td>
                                            <td class="text-end item-total">
                                                <?php echo CURRENCY . number_format($price * $item['quantity']); ?>
                                            </td>
                                            <td class="text-end">
                                                <a href="index.php?controller=cart&action=remove&id=<?php echo $item['id']; ?>" class="btn btn-sm btn-outline-danger remove-item-btn">
                                                    <i class="fas fa-trash-alt"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3 d-flex justify-content-between">
                            <a href="index.php?controller=product&action=list" class="btn btn-outline-primary">
                                <i class="fas fa-arrow-left me-2"></i> Tiếp tục mua sắm
                            </a>
                            <a href="index.php?controller=cart&action=clear" class="btn btn-outline-danger">
                                <i class="fas fa-trash-alt me-2"></i> Xóa giỏ hàng
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <?php if (!empty($cart_items)): ?>
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom-0 py-3">
                        <h5 class="mb-0">Thông tin thanh toán</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-3">
                            <span>Tạm tính:</span>
                            <span class="fw-bold cart-subtotal"><?php echo CURRENCY . number_format($cart_total); ?></span>
                        </div>
                        
                        <!-- Promotion code input -->
                        <div class="mb-3">
                            <label for="promotion-code" class="form-label">Mã giảm giá</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="promotion-code" placeholder="Nhập mã giảm giá">
                                <button class="btn btn-outline-primary" type="button" id="apply-promotion-btn">Áp dụng</button>
                            </div>
                            <div id="promotion-message" class="form-text"></div>
                        </div>
                        
                        <!-- Discount amount (shown only when promotion is applied) -->
                        <div id="discount-row" class="d-flex justify-content-between mb-3" style="display: none !important;">
                            <span>Giảm giá:</span>
                            <span class="fw-bold text-danger" id="discount-amount">- <?php echo CURRENCY; ?>0</span>
                        </div>
                        
                        <!-- Total with horizontal line above -->
                        <hr>
                        <div class="d-flex justify-content-between mb-3">
                            <span>Tổng cộng:</span>
                            <span class="fw-bold fs-5 cart-total"><?php echo CURRENCY . number_format($cart_total); ?></span>
                        </div>
                        
                        <!-- Checkout button -->
                        <a href="index.php?controller=cart&action=checkout" class="btn btn-primary w-100">
                            Thanh toán <i class="fas fa-arrow-right ms-2"></i>
                        </a>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Help card -->
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6><i class="fas fa-info-circle me-2"></i> Cần hỗ trợ?</h6>
                    <p class="small text-muted mb-0">Nếu bạn có bất kỳ câu hỏi nào về sản phẩm hoặc đơn hàng, vui lòng liên hệ:</p>
                    <ul class="list-unstyled small text-muted mt-2 mb-0">
                        <li><i class="fas fa-phone-alt me-2"></i> <?php echo ADMIN_PHONE ?? '+84 123 456 789'; ?></li>
                        <li><i class="fas fa-envelope me-2"></i> <?php echo ADMIN_EMAIL; ?></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Cart JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Quantity adjustment
    const decreaseBtns = document.querySelectorAll('.decrease-qty-btn');
    const increaseBtns = document.querySelectorAll('.increase-qty-btn');
    const qtyInputs = document.querySelectorAll('.item-qty');
    
    // Decrease quantity
    decreaseBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const input = this.parentElement.querySelector('.item-qty');
            let value = parseInt(input.value);
            if (value > 1) {
                input.value = value - 1;
                updateCartItem(input);
            }
        });
    });
    
    // Increase quantity
    increaseBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const input = this.parentElement.querySelector('.item-qty');
            let value = parseInt(input.value);
            let max = parseInt(input.getAttribute('max'));
            if (value < max) {
                input.value = value + 1;
                updateCartItem(input);
            }
        });
    });
    
    // Manual quantity input
    qtyInputs.forEach(input => {
        input.addEventListener('change', function() {
            let value = parseInt(this.value);
            let min = parseInt(this.getAttribute('min'));
            let max = parseInt(this.getAttribute('max'));
            
            if (isNaN(value) || value < min) {
                this.value = min;
                value = min;
            } else if (value > max) {
                this.value = max;
                value = max;
            }
            
            updateCartItem(this);
        });
    });
    
    // Update cart item via AJAX
    function updateCartItem(input) {
        const productRow = input.closest('tr');
        const productId = productRow.dataset.productId;
        const quantity = input.value;
        
        // Send AJAX request
        fetch('index.php?controller=cart&action=update', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: `product_id=${productId}&quantity=${quantity}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update item total
                const itemTotal = productRow.querySelector('.item-total');
                itemTotal.textContent = new Intl.NumberFormat('vi-VN', { 
                    style: 'currency', 
                    currency: 'VND',
                    maximumFractionDigits: 0
                }).format(data.item_total);
                
                // Update cart subtotal and total
                const cartSubtotal = document.querySelector('.cart-subtotal');
                const cartTotal = document.querySelector('.cart-total');
                
                if (cartSubtotal) {
                    cartSubtotal.textContent = new Intl.NumberFormat('vi-VN', { 
                        style: 'currency', 
                        currency: 'VND',
                        maximumFractionDigits: 0
                    }).format(data.cart_total);
                }
                
                if (cartTotal) {
                    cartTotal.textContent = new Intl.NumberFormat('vi-VN', { 
                        style: 'currency', 
                        currency: 'VND',
                        maximumFractionDigits: 0
                    }).format(data.cart_total);
                }
                
                // Update cart count in header
                const cartCount = document.querySelector('.cart-count');
                if (cartCount) {
                    cartCount.textContent = data.cart_count;
                }
            }
        })
        .catch(error => {
            console.error('Error updating cart:', error);
        });
    }
    
    // Remove items via AJAX
    const removeButtons = document.querySelectorAll('.remove-item-btn');
    removeButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            if (confirm('Bạn có chắc chắn muốn xóa sản phẩm này khỏi giỏ hàng?')) {
                const url = this.getAttribute('href');
                
                // Send AJAX request
                fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Remove row from table
                        const row = this.closest('tr');
                        row.remove();
                        
                        // Update cart count in header
                        const cartCount = document.querySelector('.cart-count');
                        if (cartCount) {
                            cartCount.textContent = data.cart_count;
                        }
                        
                        // Update cart subtotal and total
                        const cartSubtotal = document.querySelector('.cart-subtotal');
                        const cartTotal = document.querySelector('.cart-total');
                        
                        if (cartSubtotal) {
                            cartSubtotal.textContent = new Intl.NumberFormat('vi-VN', { 
                                style: 'currency', 
                                currency: 'VND',
                                maximumFractionDigits: 0
                            }).format(data.cart_total);
                        }
                        
                        if (cartTotal) {
                            cartTotal.textContent = new Intl.NumberFormat('vi-VN', { 
                                style: 'currency', 
                                currency: 'VND',
                                maximumFractionDigits: 0
                            }).format(data.cart_total);
                        }
                        
                        // If cart is empty, reload the page to show empty cart message
                        if (data.cart_count === 0) {
                            window.location.reload();
                        }
                    }
                })
                .catch(error => {
                    console.error('Error removing item:', error);
                });
            }
        });
    });
    
    // Apply promotion code
    const applyPromotionBtn = document.getElementById('apply-promotion-btn');
    if (applyPromotionBtn) {
        applyPromotionBtn.addEventListener('click', function() {
            const codeInput = document.getElementById('promotion-code');
            const code = codeInput.value.trim();
            const messageElement = document.getElementById('promotion-message');
            
            if (!code) {
                messageElement.textContent = 'Vui lòng nhập mã giảm giá';
                messageElement.className = 'form-text text-danger';
                return;
            }
            
            // Send AJAX request
            fetch('index.php?controller=cart&action=applyPromotion', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: `code=${code}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success message
                    messageElement.textContent = data.message;
                    messageElement.className = 'form-text text-success';
                    
                    // Show discount row
                    const discountRow = document.getElementById('discount-row');
                    const discountAmount = document.getElementById('discount-amount');
                    
                    discountRow.style.display = 'flex';
                    discountAmount.textContent = '- ' + new Intl.NumberFormat('vi-VN', { 
                        style: 'currency', 
                        currency: 'VND',
                        maximumFractionDigits: 0
                    }).format(data.discount_amount);
                    
                    // Update cart total
                    const cartTotal = document.querySelector('.cart-total');
                    if (cartTotal) {
                        cartTotal.textContent = new Intl.NumberFormat('vi-VN', { 
                            style: 'currency', 
                            currency: 'VND',
                            maximumFractionDigits: 0
                        }).format(data.new_total);
                    }
                } else {
                    // Show error message
                    messageElement.textContent = data.message;
                    messageElement.className = 'form-text text-danger';
                }
            })
            .catch(error => {
                console.error('Error applying promotion:', error);
            });
        });
    }
});
</script>

<?php include 'views/layouts/footer.php'; ?>