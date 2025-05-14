<?php 
// Bật error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Khởi tạo logging
$log_file = '/tmp/debug.log';
if (!file_exists(dirname($log_file))) {
    mkdir(dirname($log_file), 0755, true);
}
file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] Bắt đầu render views/cart/index.php\n", FILE_APPEND);

// Kiểm tra các biến cần thiết
$page_title = "Giỏ hàng";
if (!isset($cart_items)) {
    file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] Cảnh báo: Biến \$cart_items không được định nghĩa\n", FILE_APPEND);
    $cart_items = [];
}
if (!isset($cart_total)) {
    file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] Cảnh báo: Biến \$cart_total không được định nghĩa\n", FILE_APPEND);
    $cart_total = 0;
}
file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] Cart items count: " . count($cart_items) . ", Cart total: $cart_total\n", FILE_APPEND);

// Kiểm tra và log stock của từng item
foreach ($cart_items as $item) {
    $stock = isset($item['stock']) ? $item['stock'] : 'N/A';
    file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] Item ID: {$item['id']}, Name: {$item['name']}, Quantity: {$item['quantity']}, Stock: $stock\n", FILE_APPEND);
}

// Include header
try {
    $header_path = __DIR__ . '/layouts/header.php';
    if (!file_exists($header_path)) {
        file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] Lỗi: File $header_path không tồn tại\n", FILE_APPEND);
        die("Lỗi: File header.php không tồn tại tại " . htmlspecialchars($header_path));
    }
    include $header_path;
    file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] Đã include header.php\n", FILE_APPEND);
} catch (Exception $e) {
    file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] Lỗi khi include header.php: " . $e->getMessage() . "\n", FILE_APPEND);
    die("Lỗi khi load header: " . htmlspecialchars($e->getMessage()));
}
?>

<div class="container mt-5 mb-5">
    <!-- Debug information (chỉ hiển thị nếu DEBUG_MODE bật) -->
    <?php if (defined('DEBUG_MODE') && DEBUG_MODE): ?>
    <div class="debug-info alert alert-info">
        <strong>Debug Info:</strong><br>
        Cart Items Count: <?php echo count($cart_items); ?><br>
        Cart Total: <?php echo CURRENCY . number_format($cart_total); ?><br>
        <?php if (!empty($cart_items)): ?>
        Cart Items Details:<br>
        <ul>
            <?php foreach ($cart_items as $item): ?>
            <li>
                ID: <?php echo htmlspecialchars($item['id'] ?? 'N/A'); ?>,
                Name: <?php echo htmlspecialchars($item['name'] ?? 'N/A'); ?>,
                Quantity: <?php echo htmlspecialchars($item['quantity'] ?? 'N/A'); ?>,
                Stock: <?php echo isset($item['stock']) ? htmlspecialchars($item['stock']) : 'N/A'; ?>,
                Price: <?php echo CURRENCY . number_format((!empty($item['sale_price']) && $item['sale_price'] > 0) ? $item['sale_price'] : $item['price']); ?>
            </li>
            <?php endforeach; ?>
        </ul>
        <?php endif; ?>
    </div>
    <?php
    file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] Đã hiển thị debug info\n", FILE_APPEND);
    endif; ?>

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
                                                    <input type="number" class="form-control text-center item-qty" data-product-id="<?php echo $item['id']; ?>" value="<?php echo $item['quantity']; ?>" min="1" max="<?php echo isset($item['stock']) ? $item['stock'] : 10; ?>">
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
                        
                        <!-- Discount amount -->
                        <div id="discount-row" class="d-flex justify-content-between mb-3" style="display: none;">
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
                        <li><i class="fas fa-envelope me-2"></i> <?php echo ADMIN_EMAIL ?? 'support@example.com'; ?></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Cart JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded for cart page');
    console.log('Cart items count:', <?php echo count($cart_items); ?>);
    console.log('Cart total:', <?php echo json_encode($cart_total); ?>);

    // Quantity adjustment
    const decreaseBtns = document.querySelectorAll('.decrease-qty-btn');
    const increaseBtns = document.querySelectorAll('.increase-qty-btn');
    const qtyInputs = document.querySelectorAll('.item-qty');
    
    if (!decreaseBtns.length) console.warn('Không tìm thấy decrease-qty-btn');
    if (!increaseBtns.length) console.warn('Không tìm thấy increase-qty-btn');
    if (!qtyInputs.length) console.warn('Không tìm thấy item-qty');

    // Decrease quantity
    decreaseBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const input = this.parentElement.querySelector('.item-qty');
            if (!input) {
                console.error('Không tìm thấy item-qty trong input-group');
                return;
            }
            let value = parseInt(input.value) || 1;
            const min = parseInt(input.getAttribute('min')) || 1;
            if (value > min) {
                input.value = value - 1;
                console.log('Quantity decreased:', { productId: input.dataset.productId, quantity: input.value });
                updateCartItem(input);
            } else {
                input.value = min;
                console.log('Quantity at minimum:', { productId: input.dataset.productId, quantity: input.value });
            }
        });
    });
    
    // Increase quantity
    increaseBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const input = this.parentElement.querySelector('.item-qty');
            if (!input) {
                console.error('Không tìm thấy item-qty trong input-group');
                return;
            }
            let value = parseInt(input.value) || 1;
            const max = parseInt(input.getAttribute('max')) || 10;
            if (value < max) {
                input.value = value + 1;
                console.log('Quantity increased:', { productId: input.dataset.productId, quantity: input.value });
                updateCartItem(input);
            } else {
                input.value = max;
                console.log('Quantity at maximum:', { productId: input.dataset.productId, quantity: input.value });
            }
        });
    });
    
    // Manual quantity input
    qtyInputs.forEach(input => {
        input.addEventListener('input', function() {
            let value = parseInt(this.value);
            const min = parseInt(this.getAttribute('min')) || 1;
            const max = parseInt(this.getAttribute('max')) || 10;
            
            console.log('Quantity input changed:', { productId: this.dataset.productId, value, min, max });
            if (isNaN(value) || value < min) {
                this.value = min;
                console.log('Quantity set to minimum:', { productId: this.dataset.productId, quantity: this.value });
            } else if (value > max) {
                this.value = max;
                console.log('Quantity set to maximum:', { productId: this.dataset.productId, quantity: this.value });
            } else {
                console.log('Quantity validated:', { productId: this.dataset.productId, quantity: this.value });
            }
            
            updateCartItem(this);
        });
    });
    
    // Update cart item via AJAX
    function updateCartItem(input) {
        if (!input.dataset.productId) {
            console.error('Không tìm thấy data-product-id trên item-qty');
            return;
        }
        const productId = input.dataset.productId;
        const quantity = parseInt(input.value);
        
        console.log('Updating cart item:', { productId, quantity });
        
        fetch('index.php?controller=cart&action=update', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: `product_id=${encodeURIComponent(productId)}&quantity=${encodeURIComponent(quantity)}`
        })
        .then(response => {
            console.log('AJAX response received:', { status: response.status, ok: response.ok });
            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }
            return response.text();
        })
        .then(text => {
            console.log('Raw response:', text);
            try {
                const data = JSON.parse(text);
                console.log('AJAX data:', data);
                if (data.success) {
                    // Update item total
                    const productRow = input.closest('tr');
                    if (!productRow) {
                        console.error('Không tìm thấy product row');
                        return;
                    }
                    const itemTotal = productRow.querySelector('.item-total');
                    if (itemTotal) {
                        itemTotal.textContent = new Intl.NumberFormat('vi-VN', { 
                            style: 'currency', 
                            currency: 'VND',
                            maximumFractionDigits: 0
                        }).format(data.item_total);
                    } else {
                        console.warn('Không tìm thấy item-total');
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
                    } else {
                        console.warn('Không tìm thấy cart-subtotal');
                    }
                    
                    if (cartTotal) {
                        cartTotal.textContent = new Intl.NumberFormat('vi-VN', { 
                            style: 'currency', 
                            currency: 'VND',
                            maximumFractionDigits: 0
                        }).format(data.cart_total);
                    } else {
                        console.warn('Không tìm thấy cart-total');
                    }
                    
                    // Update cart count in header
                    const cartCount = document.querySelector('.cart-count');
                    if (cartCount) {
                        cartCount.textContent = data.cart_count;
                    } else {
                        console.warn('Không tìm thấy cart-count');
                    }
                } else {
                    console.error('Lỗi từ server:', data.message);
                    alert(data.message || 'Không thể cập nhật giỏ hàng. Vui lòng thử lại.');
                }
            } catch (e) {
                console.error('Lỗi phân tích JSON:', e, 'Response text:', text);
                alert('Lỗi server: Không nhận được dữ liệu hợp lệ. Vui lòng thử lại.');
            }
        })
        .catch(error => {
            console.error('Lỗi AJAX:', error);
            if (error.message.includes('timeout')) {
                alert('Yêu cầu quá lâu, vui lòng kiểm tra kết nối và thử lại.');
            } else {
                alert('Đã xảy ra lỗi khi cập nhật giỏ hàng: ' + error.message);
            }
        });
    }
    
    // Remove items via AJAX
    const removeButtons = document.querySelectorAll('.remove-item-btn');
    if (!removeButtons.length) console.warn('Không tìm thấy remove-item-btn');
    
    removeButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            if (confirm('Bạn có chắc chắn muốn xóa sản phẩm này khỏi giỏ hàng?')) {
                const url = this.getAttribute('href');
                console.log('Removing item:', url);
                
                fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => {
                    console.log('AJAX response received:', { status: response.status, ok: response.ok });
                    if (!response.ok) {
                        throw new Error(`HTTP error! Status: ${response.status}`);
                    }
                    return response.text();
                })
                .then(text => {
                    console.log('Raw response:', text);
                    try {
                        const data = JSON.parse(text);
                        console.log('AJAX data:', data);
                        if (data.success) {
                            // Remove row from table
                            const row = this.closest('tr');
                            if (row) {
                                row.remove();
                            } else {
                                console.error('Không tìm thấy product row để xóa');
                            }
                            
                            // Update cart count in header
                            const cartCount = document.querySelector('.cart-count');
                            if (cartCount) {
                                cartCount.textContent = data.cart_count;
                            } else {
                                console.warn('Không tìm thấy cart-count');
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
                            } else {
                                console.warn('Không tìm thấy cart-subtotal');
                            }
                            
                            if (cartTotal) {
                                cartTotal.textContent = new Intl.NumberFormat('vi-VN', { 
                                    style: 'currency', 
                                    currency: 'VND',
                                    maximumFractionDigits: 0
                                }).format(data.cart_total);
                            } else {
                                console.warn('Không tìm thấy cart-total');
                            }
                            
                            // If cart is empty, reload the page
                            if (data.cart_count === 0) {
                                console.log('Cart is empty, reloading page');
                                window.location.reload();
                            }
                        } else {
                            console.error('Lỗi từ server:', data.message);
                            alert(data.message || 'Không thể xóa sản phẩm. Vui lòng thử lại.');
                        }
                    } catch (e) {
                        console.error('Lỗi phân tích JSON:', e, 'Response text:', text);
                        alert('Lỗi server: Không nhận được dữ liệu hợp lệ. Vui lòng thử lại.');
                    }
                })
                .catch(error => {
                    console.error('Lỗi AJAX:', error);
                    alert('Đã xảy ra lỗi khi xóa sản phẩm: ' + error.message);
                });
            }
        });
    });
    
    // Apply promotion code
    const applyPromotionBtn = document.getElementById('apply-promotion-btn');
    if (applyPromotionBtn) {
        applyPromotionBtn.addEventListener('click', function() {
            const codeInput = document.getElementById('promotion-code');
            const messageElement = document.getElementById('promotion-message');
            if (!codeInput || !messageElement) {
                console.error('Không tìm thấy promotion-code hoặc promotion-message');
                return;
            }
            
            const code = codeInput.value.trim();
            console.log('Applying promotion code:', code);
            if (!code) {
                messageElement.textContent = 'Vui lòng nhập mã giảm giá';
                messageElement.className = 'form-text text-danger';
                console.log('Empty promotion code');
                return;
            }
            
            fetch('index.php?controller=cart&action=applyPromotion', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: `code=${encodeURIComponent(code)}`
            })
            .then(response => {
                console.log('AJAX response received:', { status: response.status, ok: response.ok });
                if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }
                return response.text();
            })
            .then(text => {
                console.log('Raw response:', text);
                try {
                    const data = JSON.parse(text);
                    console.log('AJAX data:', data);
                    if (data.success) {
                        messageElement.textContent = data.message;
                        messageElement.className = 'form-text text-success';
                        
                        // Show discount row
                        const discountRow = document.getElementById('discount-row');
                        const discountAmount = document.getElementById('discount-amount');
                        if (discountRow && discountAmount) {
                            discountRow.style.display = 'flex';
                            discountAmount.textContent = '- ' + new Intl.NumberFormat('vi-VN', { 
                                style: 'currency', 
                                currency: 'VND',
                                maximumFractionDigits: 0
                            }).format(data.discount_amount);
                        } else {
                            console.warn('Không tìm thấy discount-row hoặc discount-amount');
                        }
                        
                        // Update cart total
                        const cartTotal = document.querySelector('.cart-total');
                        if (cartTotal) {
                            cartTotal.textContent = new Intl.NumberFormat('vi-VN', { 
                                style: 'currency', 
                                currency: 'VND',
                                maximumFractionDigits: 0
                            }).format(data.new_total);
                        } else {
                            console.warn('Không tìm thấy cart-total');
                        }
                    } else {
                        messageElement.textContent = data.message;
                        messageElement.className = 'form-text text-danger';
                        console.error('Lỗi từ server:', data.message);
                    }
                } catch (e) {
                    console.error('Lỗi phân tích JSON:', e, 'Response text:', text);
                    messageElement.textContent = 'Lỗi server: Không nhận được dữ liệu hợp lệ.';
                    messageElement.className = 'form-text text-danger';
                }
            })
            .catch(error => {
                console.error('Lỗi AJAX:', error);
                messageElement.textContent = 'Đã xảy ra lỗi khi áp dụng mã giảm giá.';
                messageElement.className = 'form-text text-danger';
            });
        });
    } else {
        console.log('Không tìm thấy apply-promotion-btn');
    }
});
</script>

<?php
// Include footer
try {
    $footer_path = __DIR__ . '/layouts/footer.php';
    if (!file_exists($footer_path)) {
        file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] Lỗi: File $footer_path không tồn tại\n", FILE_APPEND);
        die("Lỗi: File footer.php không tồn tại tại " . htmlspecialchars($footer_path));
    }
    include $footer_path;
    file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] Đã include footer.php\n", FILE_APPEND);
    file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] Hoàn thành render views/cart/index.php\n", FILE_APPEND);
} catch (Exception $e) {
    file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] Lỗi khi include footer.php: " . $e->getMessage() . "\n", FILE_APPEND);
    die("Lỗi khi load footer: " . htmlspecialchars($e->getMessage()));
}
?>