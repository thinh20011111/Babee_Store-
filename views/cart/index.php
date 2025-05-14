<?php
// Bật error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<pre>DEBUG: Bắt đầu thực thi views/cart/index.php</pre>";

// Khởi tạo logging
$log_file = '/tmp/debug.log';
if (!file_exists(dirname($log_file))) {
    mkdir(dirname($log_file), 0755, true);
}
file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] Bắt đầu render views/cart/index.php\n", FILE_APPEND);

// Kiểm tra các biến cần thiết
$page_title = "Giỏ hàng";
$debug_log = []; // Lưu log để echo
echo "<pre>DEBUG: Kiểm tra biến \$cart_items và \$cart_total</pre>";
if (!isset($cart_items)) {
    $debug_log[] = "Cảnh báo: Biến \$cart_items không được định nghĩa";
    file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] Cảnh báo: Biến \$cart_items không được định nghĩa\n", FILE_APPEND);
    echo "<pre>DEBUG: \$cart_items không được định nghĩa, khởi tạo mảng rỗng</pre>";
    $cart_items = [];
}
if (!isset($cart_total)) {
    $debug_log[] = "Cảnh báo: Biến \$cart_total không được định nghĩa";
    file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] Cảnh báo: Biến \$cart_total không được định nghĩa\n", FILE_APPEND);
    echo "<pre>DEBUG: \$cart_total không được định nghĩa, đặt về 0</pre>";
    $cart_total = 0;
}
$debug_log[] = "Cart items count: " . count($cart_items);
$debug_log[] = "Cart total: " . (defined('CURRENCY') ? CURRENCY : '₫') . number_format($cart_total, 0, ',', '.');
file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] Cart items count: " . count($cart_items) . ", Cart total: $cart_total\n", FILE_APPEND);
echo "<pre>DEBUG: Số lượng sản phẩm: " . count($cart_items) . ", Tổng tiền: " . (defined('CURRENCY') ? CURRENCY : '₫') . number_format($cart_total, 0, ',', '.') . "</pre>";

// Kiểm tra constant CURRENCY
$currency = defined('CURRENCY') ? CURRENCY : '₫';
$debug_log[] = "CURRENCY: " . (defined('CURRENCY') ? CURRENCY : 'Không định nghĩa, dùng fallback: ₫');
file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] CURRENCY: $currency\n", FILE_APPEND);
echo "<pre>DEBUG: CURRENCY: $currency</pre>";

// Kiểm tra và log stock của từng item
echo "<pre>DEBUG: Bắt đầu kiểm tra danh sách sản phẩm trong giỏ hàng</pre>";
foreach ($cart_items as $key => $item) {
    $stock = isset($item['data']['stock']) ? $item['data']['stock'] : 'N/A';
    $price = (!empty($item['data']['sale_price']) && $item['data']['sale_price'] > 0) ? $item['data']['sale_price'] : ($item['data']['price'] ?? 0);
    $debug_log[] = "Item Product ID: {$item['product_id']}, Variant ID: {$item['variant_id']}, Name: {$item['data']['name']}, Quantity: {$item['quantity']}, Stock: $stock, Price: $currency" . number_format($price, 0, ',', '.');
    file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] Item Product ID: {$item['product_id']}, Variant ID: {$item['variant_id']}, Name: {$item['data']['name']}, Quantity: {$item['quantity']}, Stock: $stock\n", FILE_APPEND);
    echo "<pre>DEBUG: Item Product ID: {$item['product_id']}, Variant ID: {$item['variant_id']}, Name: {$item['data']['name']}, Quantity: {$item['quantity']}, Stock: $stock, Price: $currency" . number_format($price, 0, ',', '.') . "</pre>";
}

// Kiểm tra constants ADMIN_PHONE và ADMIN_EMAIL
$admin_phone = defined('ADMIN_PHONE') ? ADMIN_PHONE : '0359349545';
$admin_email = defined('ADMIN_EMAIL') ? ADMIN_EMAIL : 'contact@streetstyle.com';
$debug_log[] = "ADMIN_PHONE: " . (defined('ADMIN_PHONE') ? ADMIN_PHONE : 'Không định nghĩa, dùng fallback: ' . $admin_phone);
$debug_log[] = "ADMIN_EMAIL: " . (defined('ADMIN_EMAIL') ? ADMIN_EMAIL : 'Không định nghĩa, dùng fallback: ' . $admin_email);
file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] ADMIN_PHONE: $admin_phone\n", FILE_APPEND);
file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] ADMIN_EMAIL: $admin_email\n", FILE_APPEND);
echo "<pre>DEBUG: ADMIN_PHONE: $admin_phone</pre>";
echo "<pre>DEBUG: ADMIN_EMAIL: $admin_email</pre>";

// Include header
echo "<pre>DEBUG: Chuẩn bị include header.php</pre>";
try {
    $header_path = __DIR__ . '/layouts/header.php';
    if (!file_exists($header_path)) {
        $debug_log[] = "Lỗi: File $header_path không tồn tại";
        file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] Lỗi: File $header_path không tồn tại\n", FILE_APPEND);
        echo "<pre>DEBUG: Lỗi: File $header_path không tồn tại</pre>";
        echo "<p class='error'>Lỗi: File header.php không tồn tại. Vui lòng tạo file tại " . htmlspecialchars($header_path) . "</p>";
    } else {
        include $header_path;
        $debug_log[] = "Đã include header.php";
        file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] Đã include header.php\n", FILE_APPEND);
        echo "<pre>DEBUG: Đã include header.php thành công</pre>";
    }
} catch (Exception $e) {
    $debug_log[] = "Lỗi khi include header.php: " . $e->getMessage();
    file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] Lỗi khi include header.php: " . $e->getMessage() . "\n", FILE_APPEND);
    echo "<pre>DEBUG: Lỗi khi include header.php: " . htmlspecialchars($e->getMessage()) . "</pre>";
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
        .debug-info, .debug-log {
            margin-bottom: 20px;
        }
        .debug-log {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            padding: 15px;
            font-family: monospace;
            white-space: pre-wrap;
            max-height: 300px;
            overflow-y: auto;
        }
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
        <!-- Debug information (chỉ hiển thị nếu DEBUG_MODE bật) -->
        <?php 
        echo "<pre>DEBUG: Kiểm tra DEBUG_MODE</pre>";
        if (defined('DEBUG_MODE') && DEBUG_MODE): 
            echo "<pre>DEBUG: DEBUG_MODE bật, hiển thị debug info</pre>";
        ?>
        <div class="debug-info alert alert-info">
            <strong>Debug Info:</strong><br>
            Cart Items Count: <?php echo count($cart_items); ?><br>
            Cart Total: <?php echo $currency . number_format($cart_total, 0, ',', '.'); ?><br>
            <?php if (!empty($cart_items)): ?>
            Cart Items Details:<br>
            <ul>
                <?php foreach ($cart_items as $key => $item): ?>
                <li>
                    Product ID: <?php echo htmlspecialchars($item['product_id'] ?? 'N/A'); ?>,
                    Variant ID: <?php echo htmlspecialchars($item['variant_id'] ?? 'N/A'); ?>,
                    Name: <?php echo htmlspecialchars($item['data']['name'] ?? 'N/A'); ?>,
                    Quantity: <?php echo htmlspecialchars($item['quantity'] ?? 'N/A'); ?>,
                    Stock: <?php echo isset($item['data']['stock']) ? htmlspecialchars($item['data']['stock']) : 'N/A'; ?>,
                    Price: <?php echo $currency . number_format((!empty($item['data']['sale_price']) && $item['data']['sale_price'] > 0) ? $item['data']['sale_price'] : ($item['data']['price'] ?? 0), 0, ',', '.'); ?>
                </li>
                <?php endforeach; ?>
            </ul>
            <?php endif; ?>
        </div>
        <pre class="debug-log"><?php echo implode("\n", array_map('htmlspecialchars', $debug_log)); ?></pre>
        <?php
        file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] Đã hiển thị debug info và debug log\n", FILE_APPEND);
        echo "<pre>DEBUG: Đã hiển thị debug info và debug log</pre>";
        endif; ?>

        <?php echo "<pre>DEBUG: Bắt đầu render row giỏ hàng</pre>"; ?>
        <div class="row">
            <div class="col-lg-8">
                <?php echo "<pre>DEBUG: Render card giỏ hàng</pre>"; ?>
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom-0 py-3">
                        <h5 class="mb-0">Giỏ hàng của bạn</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($cart_items)): ?>
                            <?php echo "<pre>DEBUG: Giỏ hàng rỗng</pre>"; ?>
                            <div class="text-center py-5">
                                <i class="fas fa-shopping-cart fa-4x text-muted mb-3"></i>
                                <h4>Giỏ hàng của bạn đang trống</h4>
                                <p class="text-muted">Khám phá các sản phẩm và thêm vào giỏ hàng</p>
                                <a href="index.php?controller=product&action=list" class="btn btn-primary mt-3">Tiếp tục mua sắm</a>
                            </div>
                        <?php else: ?>
                            <?php echo "<pre>DEBUG: Render bảng sản phẩm</pre>"; ?>
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
                                            <?php echo "<pre>DEBUG: Render sản phẩm Product ID: {$item['product_id']}, Variant ID: {$item['variant_id']}</pre>"; ?>
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
                            <?php echo "<pre>DEBUG: Render nút tiếp tục mua sắm và xóa giỏ hàng</pre>"; ?>
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
                    <?php echo "<pre>DEBUG: Render card thông tin thanh toán</pre>"; ?>
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
                            <棠 class="mb-3">
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
                                <span class="fw-bold text-danger" id="discount-amount">- <?php echo $currency; ?>0</span>
                            </div>
                            
                            <!-- Total with horizontal line above -->
                            <hr>
                            <div class="d-flex justify-content-between mb-3">
                                <span>Tổng cộng:</span>
                                <span class="fw-bold fs-5 cart-total"><?php echo $currency . number_format($cart_total, 0, ',', '.'); ?></span>
                            </div>
                            
                            <!-- Checkout button -->
                            <a href="index.php?controller=cart&action=checkout" class="btn btn-primary w-100">
                                Thanh toán <i class="fas fa-arrow-right ms-2"></i>
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
                
                <?php echo "<pre>DEBUG: Render card hỗ trợ</pre>"; ?>
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

    <?php echo "<pre>DEBUG: Bắt đầu render JavaScript</pre>"; ?>
    <!-- Cart JavaScript -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        console.log('DOM loaded for cart page');
        console.log('Cart items count:', <?php echo count($cart_items); ?>);
        console.log('Cart total:', <?php echo json_encode($cart_total); ?>);
        console.log('DEBUG: JavaScript execution started');

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
                    console.log('Quantity decreased:', { productId: input.dataset.productId, variantId: input.dataset.variantId, quantity: input.value });
                    updateCartItem(input);
                } else {
                    input.value = min;
                    console.log('Quantity at minimum:', { productId: input.dataset.productId, variantId: input.dataset.variantId, quantity: input.value });
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
                    console.log('Quantity increased:', { productId: input.dataset.productId, variantId: input.dataset.variantId, quantity: input.value });
                    updateCartItem(input);
                } else {
                    input.value = max;
                    console.log('Quantity at maximum:', { productId: input.dataset.productId, variantId: input.dataset.variantId, quantity: input.value });
                }
            });
        });
        
        // Manual quantity input
        qtyInputs.forEach(input => {
            input.addEventListener('input', function() {
                let value = parseInt(this.value);
                const min = parseInt(this.getAttribute('min')) || 1;
                const max = parseInt(this.getAttribute('max')) || 10;
                
                console.log('Quantity input changed:', { productId: this.dataset.productId, variantId: this.dataset.variantId, value, min, max });
                if (isNaN(value) || value < min) {
                    this.value = min;
                    console.log('Quantity set to minimum:', { productId: this.dataset.productId, variantId: this.dataset.variantId, quantity: this.value });
                } else if (value > max) {
                    this.value = max;
                    console.log('Quantity set to maximum:', { productId: this.dataset.productId, variantId: this.dataset.variantId, quantity: this.value });
                } else {
                    console.log('Quantity validated:', { productId: this.dataset.productId, variantId: this.dataset.variantId, quantity: this.value });
                }
                
                updateCartItem(this);
            });
        });
        
        // Update cart item via AJAX
        function updateCartItem(input) {
            if (!input.dataset.productId || !input.dataset.variantId) {
                console.error('Không tìm thấy data-product-id hoặc data-variant-id trên item-qty');
                return;
            }
            const productId = input.dataset.productId;
            const variantId = input.dataset.variantId;
            const quantity = parseInt(input.value);
            
            console.log('Updating cart item:', { productId, variantId, quantity });
            
            fetch('index.php?controller=cart&action=update', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: `product_id=${encodeURIComponent(productId)}&variant_id=${encodeURIComponent(variantId)}&quantity=${encodeURIComponent(quantity)}`
            })
            .then(response => {
                console.log('AJAX response received:', { status: response.status, ok: response.ok });
                if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
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
                        return response.json();
                    })
                    .then(data => {
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
                    return response.json();
                })
                .then(data => {
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
    echo "<pre>DEBUG: Chuẩn bị include footer.php</pre>";
    // Include footer
    try {
        $footer_path = __DIR__ . '/layouts/footer.php';
        if (!file_exists($footer_path)) {
            $debug_log[] = "Lỗi: File $footer_path không tồn tại";
            file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] Lỗi: File $footer_path không tồn tại\n", FILE_APPEND);
            echo "<pre>DEBUG: Lỗi: File $footer_path không tồn tại</pre>";
            echo "<p class='error'>Lỗi: File footer.php không tồn tại. Vui lòng tạo file tại " . htmlspecialchars($footer_path) . "</p>";
        } else {
            include $footer_path;
            $debug_log[] = "Đã include footer.php";
            file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] Đã include footer.php\n", FILE_APPEND);
            echo "<pre>DEBUG: Đã include footer.php thành công</pre>";
        }
    } catch (Exception $e) {
        $debug_log[] = "Lỗi khi include footer.php: " . $e->getMessage();
        file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] Lỗi khi include footer.php: " . $e->getMessage() . "\n", FILE_APPEND);
        echo "<pre>DEBUG: Lỗi khi include footer.php: " . htmlspecialchars($e->getMessage()) . "</pre>";
        echo "<p class='error'>Lỗi khi load footer: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
    ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>