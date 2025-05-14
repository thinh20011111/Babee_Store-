<?php
// Bật error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Kiểm tra các biến cần thiết
$page_title = "Giỏ hàng";
if (!isset($cart_items)) {
    $cart_items = [];
}
if (!isset($cart_total)) {
    $cart_total = 0;
}

// Kiểm tra mã giảm giá trong session
$promotion_discount = isset($_SESSION['promotion']['discount_amount']) ? $_SESSION['promotion']['discount_amount'] : 0;
$final_total = $cart_total - $promotion_discount;

// Kiểm tra constant CURRENCY
$currency = defined('CURRENCY') ? CURRENCY : '₫';

// Kiểm tra constants ADMIN_PHONE và ADMIN_EMAIL
$admin_phone = defined('ADMIN_PHONE') ? ADMIN_PHONE : '0359349545';
$admin_email = defined('ADMIN_EMAIL') ? ADMIN_EMAIL : 'contact@streetstyle.com';

// Include header
try {
    $header_path = __DIR__ . '/../layouts/header.php';
    if (!file_exists($header_path)) {
        echo "<p class='error'>Lỗi: File header.php không tồn tại. Vui lòng tạo file tại " . htmlspecialchars($header_path) . "</p>";
    } else {
        include $header_path;
    }
} catch (Exception $e) {
    echo "<p class='error'>Lỗi khi load header: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .cart-item img {
            max-width: 70px;
        }
        .input-group-sm {
            width: 100px;
        }
        .error {
            color: red;
        }
    </style>
</head>
<body>
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
                                        <?php foreach ($cart_items as $key => $item): ?>
                                            <tr data-product-id="<?php echo htmlspecialchars($item['product_id']); ?>" data-variant-id="<?php echo htmlspecialchars($item['variant_id']); ?>">
                                                <td width="80">
                                                    <?php if (!empty($item['data']['image'])): ?>
                                                        <img src="<?php echo htmlspecialchars($item['data']['image']); ?>" class="img-fluid rounded" alt="<?php echo htmlspecialchars($item['data']['name']); ?>" style="max-width: 70px;">
                                                    <?php else: ?>
                                                        <div class="bg-light rounded d-flex align-items-center justify-content-center" style="width: 70px; height: 70px;">
                                                            <i class="fas fa-tshirt fa-2x text-secondary"></i>
                                                        </div>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <h6 class="mb-1"><?php echo htmlspecialchars($item['data']['name'] ?? 'Không xác định'); ?></h6>
                                                </td>
                                                <td>
                                                    <?php 
                                                    $price = (!empty($item['data']['sale_price']) && $item['data']['sale_price'] > 0) ? $item['data']['sale_price'] : ($item['data']['price'] ?? 0);
                                                    echo $currency . number_format($price, 0, ',', '.');
                                                    ?>
                                                </td>
                                                <td>
                                                    <div class="input-group input-group-sm" style="width: 100px;">
                                                        <button type="button" class="btn btn-outline-secondary decrease-qty-btn"><i class="fas fa-minus"></i></button>
                                                        <input type="number" class="form-control text-center item-qty" 
                                                               data-product-id="<?php echo htmlspecialchars($item['product_id']); ?>" 
                                                               data-variant-id="<?php echo htmlspecialchars($item['variant_id']); ?>" 
                                                               value="<?php echo htmlspecialchars($item['quantity']); ?>" 
                                                               min="1" 
                                                               max="<?php echo isset($item['data']['stock']) ? htmlspecialchars($item['data']['stock']) : 10; ?>">
                                                        <button type="button" class="btn btn-outline-secondary increase-qty-btn"><i class="fas fa-plus"></i></button>
                                                    </div>
                                                </td>
                                                <td class="text-end item-total">
                                                    <?php echo $currency . number_format($price * $item['quantity'], 0, ',', '.'); ?>
                                                </td>
                                                <td class="text-end">
                                                    <a href="index.php?controller=cart&action=remove&product_id=<?php echo htmlspecialchars($item['product_id']); ?>&variant_id=<?php echo htmlspecialchars($item['variant_id']); ?>" 
                                                       class="btn btn-sm btn-outline-danger remove-item-btn">
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
                                <span class="fw-bold cart-subtotal"><?php echo $currency . number_format($cart_total, 0, ',', '.'); ?></span>
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
                            <div id="discount-row" class="d-flex justify-content-between mb-3" style="display: <?php echo $promotion_discount > 0 ? 'flex' : 'none'; ?>;">
                                <span>Giảm giá:</span>
                                <span class="fw-bold text-danger" id="discount-amount"><?php echo $promotion_discount > 0 ? '- ' . $currency . number_format($promotion_discount, 0, ',', '.') : '- ' . $currency . '0'; ?></span>
                            </div>
                            
                            <!-- Total with horizontal line above -->
                            <hr>
                            <div class="d-flex justify-content-between mb-3">
                                <span>Tổng cộng:</span>
                                <span class="fw-bold fs-5 cart-total"><?php echo $currency . number_format($final_total, 0, ',', '.'); ?></span>
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
                            <li><i class="fas fa-phone-alt me-2"></i> <?php echo htmlspecialchars($admin_phone); ?></li>
                            <li><i class="fas fa-envelope me-2"></i> <?php echo htmlspecialchars($admin_email); ?></li>
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
                if (!input) return;
                let value = parseInt(input.value) || 1;
                const min = parseInt(input.getAttribute('min')) || 1;
                if (value > min) {
                    input.value = value - 1;
                    updateCartItem(input);
                } else {
                    input.value = min;
                }
            });
        });
        
        // Increase quantity
        increaseBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const input = this.parentElement.querySelector('.item-qty');
                if (!input) return;
                let value = parseInt(input.value) || 1;
                const max = parseInt(input.getAttribute('max')) || 10;
                if (value < max) {
                    input.value = value + 1;
                    updateCartItem(input);
                } else {
                    input.value = max;
                }
            });
        });
        
        // Manual quantity input
        qtyInputs.forEach(input => {
            input.addEventListener('input', function() {
                let value = parseInt(this.value);
                const min = parseInt(this.getAttribute('min')) || 1;
                const max = parseInt(this.getAttribute('max')) || 10;
                
                if (isNaN(value) || value < min) {
                    this.value = min;
                } else if (value > max) {
                    this.value = max;
                }
                
                updateCartItem(this);
            });
        });
        
        // Update cart item via AJAX
        function updateCartItem(input) {
            if (!input.dataset.productId || !input.dataset.variantId) return;
            const productId = input.dataset.productId;
            const variantId = input.dataset.variantId;
            const quantity = parseInt(input.value);
            
            fetch('index.php?controller=cart&action=update', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: `product_id=${encodeURIComponent(productId)}&variant_id=${encodeURIComponent(variantId)}&quantity=${encodeURIComponent(quantity)}`
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    // Update item total
                    const productRow = input.closest('tr');
                    if (productRow) {
                        const itemTotal = productRow.querySelector('.item-total');
                        if (itemTotal) {
                            itemTotal.textContent = new Intl.NumberFormat('vi-VN', { 
                                style: 'currency', 
                                currency: 'VND',
                                maximumFractionDigits: 0
                            }).format(data.item_total);
                        }
                    }
                    
                    // Update cart subtotal and total
                    const cartSubtotal = document.querySelector('.cart-subtotal');
                    const cartTotal = document.querySelector('.cart-total');
                    const discountRow = document.getElementById('discount-row');
                    const discountAmount = document.getElementById('discount-amount');
                    
                    if (cartSubtotal) {
                        cartSubtotal.textContent = new Intl.NumberFormat('vi-VN', { 
                            style: 'currency', 
                            currency: 'VND',
                            maximumFractionDigits: 0
                        }).format(data.cart_total);
                    }
                    
                    if (data.discount_amount !== undefined && discountRow && discountAmount) {
                        if (data.discount_amount > 0) {
                            discountRow.style.display = 'flex';
                            discountAmount.textContent = '- ' + new Intl.NumberFormat('vi-VN', { 
                                style: 'currency', 
                                currency: 'VND',
                                maximumFractionDigits: 0
                            }).format(data.discount_amount);
                        } else {
                            discountRow.style.display = 'none';
                            discountAmount.textContent = '- ' + new Intl.NumberFormat('vi-VN', { 
                                style: 'currency', 
                                currency: 'VND',
                                maximumFractionDigits: 0
                            }).format(0);
                        }
                    }
                    
                    if (cartTotal) {
                        const newTotal = data.discount_amount !== undefined ? data.cart_total - data.discount_amount : data.cart_total;
                        cartTotal.textContent = new Intl.NumberFormat('vi-VN', { 
                            style: 'currency', 
                            currency: 'VND',
                            maximumFractionDigits: 0
                        }).format(newTotal);
                    }
                    
                    // Update cart count in header
                    const cartCount = document.querySelector('.cart-count');
                    if (cartCount) {
                        cartCount.textContent = data.cart_count;
                    }
                } else {
                    alert(data.message || 'Không thể cập nhật giỏ hàng. Vui lòng thử lại.');
                }
            })
            .catch(error => {
                if (error.message.includes('timeout')) {
                    alert('Yêu cầu quá lâu, vui lòng kiểm tra kết nối và thử lại.');
                } else {
                    alert('Đã xảy ra lỗi khi cập nhật giỏ hàng: ' + error.message);
                }
            });
        }
        
        // Remove items via AJAX
        const removeButtons = document.querySelectorAll('.remove-item-btn');
        
        removeButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                
                if (confirm('Bạn có chắc chắn muốn xóa sản phẩm này khỏi giỏ hàng?')) {
                    const url = this.getAttribute('href');
                    
                    fetch(url, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`HTTP error! Status: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            // Remove row from table
                            const row = this.closest('tr');
                            if (row) {
                                row.remove();
                            }
                            
                            // Update cart count in header
                            const cartCount = document.querySelector('.cart-count');
                            if (cartCount) {
                                cartCount.textContent = data.cart_count;
                            }
                            
                            // Update cart subtotal and total
                            const cartSubtotal = document.querySelector('.cart-subtotal');
                            const cartTotal = document.querySelector('.cart-total');
                            const discountRow = document.getElementById('discount-row');
                            const discountAmount = document.getElementById('discount-amount');
                            
                            if (cartSubtotal) {
                                cartSubtotal.textContent = new Intl.NumberFormat('vi-VN', { 
                                    style: 'currency', 
                                    currency: 'VND',
                                    maximumFractionDigits: 0
                                }).format(data.cart_total);
                            }
                            
                            if (data.discount_amount !== undefined && discountRow && discountAmount) {
                                if (data.discount_amount > 0) {
                                    discountRow.style.display = 'flex';
                                    discountAmount.textContent = '- ' + new Intl.NumberFormat('vi-VN', { 
                                        style: 'currency', 
                                        currency: 'VND',
                                        maximumFractionDigits: 0
                                    }).format(data.discount_amount);
                                } else {
                                    discountRow.style.display = 'none';
                                    discountAmount.textContent = '- ' + new Intl.NumberFormat('vi-VN', { 
                                        style: 'currency', 
                                        currency: 'VND',
                                        maximumFractionDigits: 0
                                    }).format(0);
                                }
                            }
                            
                            if (cartTotal) {
                                const newTotal = data.discount_amount !== undefined ? data.cart_total - data.discount_amount : data.cart_total;
                                cartTotal.textContent = new Intl.NumberFormat('vi-VN', { 
                                    style: 'currency', 
                                    currency: 'VND',
                                    maximumFractionDigits: 0
                                }).format(newTotal);
                            }
                            
                            // If cart is empty, reload the page
                            if (data.cart_count === 0) {
                                window.location.reload();
                            }
                        } else {
                            alert(data.message || 'Không thể xóa sản phẩm. Vui lòng thử lại.');
                        }
                    })
                    .catch(error => {
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
                if (!codeInput || !messageElement) return;
                
                const code = codeInput.value.trim();
                if (!code) {
                    messageElement.textContent = 'Vui lòng nhập mã giảm giá';
                    messageElement.className = 'form-text text-danger';
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
                    if (!response.ok) {
                        throw new Error(`HTTP error! Status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
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
                        }
                        
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
                        messageElement.textContent = data.message;
                        messageElement.className = 'form-text text-danger';
                    }
                })
                .catch(error => {
                    messageElement.textContent = 'Đã xảy ra lỗi khi áp dụng mã giảm giá.';
                    messageElement.className = 'form-text text-danger';
                });
            });
        }
    });
    </script>

    <?php 
    // Include footer
    try {
        $footer_path = __DIR__ . '/../layouts/footer.php';
        if (!file_exists($footer_path)) {
            echo "<p class='error'>Lỗi: File footer.php không tồn tại. Vui lòng tạo file tại " . htmlspecialchars($footer_path) . "</p>";
        } else {
            include $footer_path;
        }
    } catch (Exception $e) {
        echo "<p class='error'>Lỗi khi load footer: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
    ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>