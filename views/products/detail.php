<?php
// Bật error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Định nghĩa hằng số CURRENCY nếu chưa có
if (!defined('CURRENCY')) {
    define('CURRENCY', '₫');
}

// Khởi tạo file log
$log_file = __DIR__ . '/../../logs/debug.log';
if (!file_exists(dirname($log_file))) {
    mkdir(dirname($log_file), 0755, true);
}
file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] Bắt đầu render views/products/detail.php\n", FILE_APPEND);

// Kiểm tra các biến cần thiết
if (!isset($product) || !is_object($product)) {
    file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] Lỗi: Biến \$product không tồn tại hoặc không hợp lệ\n", FILE_APPEND);
    die("Lỗi: Dữ liệu sản phẩm không hợp lệ");
}
if (!isset($variants)) {
    file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] Cảnh báo: Biến \$variants không được định nghĩa\n", FILE_APPEND);
    $variants = [];
}
if (!isset($category_name)) {
    file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] Cảnh báo: Biến \$category_name không được định nghĩa\n", FILE_APPEND);
    $category_name = 'Danh mục không xác định';
}
if (!isset($related_products)) {
    file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] Cảnh báo: Biến \$related_products không được định nghĩa\n", FILE_APPEND);
    $related_products = [];
}
if (!isset($product->images)) {
    file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] Cảnh báo: Biến \$product->images không được định nghĩa\n", FILE_APPEND);
    $product->images = [];
}

$page_title = htmlspecialchars($product->name ?? 'Sản phẩm');
file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] Page title: $page_title\n", FILE_APPEND);

// Kiểm tra variant màu sắc
$colors = !empty($variants) ? array_unique(array_filter(array_column($variants, 'color'), fn($color) => !empty($color))) : [];
$has_multiple_colors = count($colors) > 1;
file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] Colors available: " . json_encode($colors) . ", Has multiple colors: " . ($has_multiple_colors ? 'true' : 'false') . "\n", FILE_APPEND);

// Ghi log số lượng ảnh
file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] Main image: " . ($product->image ? $product->image : 'N/A') . ", Additional images count: " . count($product->images) . "\n", FILE_APPEND);

// Include header
try {
    $header_path = __DIR__ . '/../layouts/header.php';
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

<!-- Đảm bảo Bootstrap, Font Awesome và Animate.css được include -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">

<!-- Debug information (chỉ hiển thị nếu DEBUG_MODE bật) -->
<?php if (defined('DEBUG_MODE') && DEBUG_MODE): ?>
<div class="debug-info alert alert-info">
    <strong>Debug Info:</strong><br>
    Product ID: <?php echo htmlspecialchars($product->id ?? 'N/A'); ?><br>
    Product Name: <?php echo htmlspecialchars($product->name ?? 'N/A'); ?><br>
    Main Image: <?php echo htmlspecialchars($product->image ?? 'N/A'); ?><br>
    Additional Images Count: <?php echo count($product->images); ?><br>
    Total Stock: <?php echo !empty($product->id) ? $product->getTotalStock() : 0; ?><br>
    Variants Count: <?php echo count($variants ?? []); ?><br>
    Variants: <?php echo htmlspecialchars(json_encode($variants ?? [])); ?><br>
    Category Name: <?php echo htmlspecialchars($category_name ?? 'N/A'); ?><br>
    Related Products Count: <?php echo count($related_products ?? []); ?><br>
    Has Multiple Colors: <?php echo $has_multiple_colors ? 'Yes' : 'No'; ?>
</div>
<?php
file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] Đã hiển thị debug info\n", FILE_APPEND);
endif; ?>

<!-- Page Header with Breadcrumb -->
<div class="category-header position-relative mb-5">
    <div class="category-header-bg" style="background-color: #f8f9fa; height: 120px; position: relative; overflow: hidden;">
        <div class="container h-100">
            <div class="row h-100 align-items-center">
                <div class="col-12">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none">Home</a></li>
                            <li class="breadcrumb-item"><a href="index.php?controller=product&action=list" class="text-decoration-none">Shop</a></li>
                            <li class="breadcrumb-item"><a href="index.php?controller=product&action=list&category_id=<?php echo htmlspecialchars($product->category_id ?? 0); ?>" class="text-decoration-none"><?php echo htmlspecialchars($category_name ?? 'Danh mục'); ?></a></li>
                            <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($product->name ?? 'Sản phẩm'); ?></li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
        <div class="position-absolute" style="top:0; right:0; bottom:0; left:0; background: linear-gradient(135deg, rgba(0,123,255,0.1) 0%, rgba(255,45,85,0.05) 100%);"></div>
    </div>
</div>

<div class="container mb-5">
    <div class="row">
        <!-- Product Images -->
        <div class="col-lg-6 mb-4 mb-lg-0">
            <div class="product-image-container position-relative shadow-sm rounded">
                <?php if (!empty($product->image)): ?>
                <img src="<?php echo htmlspecialchars($product->image); ?>" class="img-fluid rounded main-image" alt="<?php echo htmlspecialchars($product->name ?? 'Sản phẩm'); ?>" style="max-height: 500px; width: 100%; object-fit: cover;">
                <?php else: ?>
                <div class="product-placeholder d-flex align-items-center justify-content-center bg-light rounded" style="height: 500px;">
                    <i class="fas fa-tshirt fa-6x text-secondary"></i>
                </div>
                <?php endif; ?>
                
                <?php if (($product->is_sale ?? 0) == 1 && !empty($product->sale_price) && $product->sale_price < $product->price): ?>
                <span class="badge bg-danger position-absolute top-0 end-0 m-3">SALE</span>
                <?php endif; ?>
            </div>
            
            <!-- Product Thumbnails -->
            <div class="product-thumbnails mt-3">
                <div class="row g-2 flex-wrap">
                    <!-- Main Image Thumbnail -->
                    <div class="col-3 col-sm-2">
                        <div class="thumbnail-item border rounded p-1 <?php echo !empty($product->image) ? 'active' : ''; ?>" data-image="<?php echo htmlspecialchars($product->image ?? ''); ?>">
                            <?php if (!empty($product->image)): ?>
                            <img src="<?php echo htmlspecialchars($product->image); ?>" class="img-fluid rounded" alt="Main Image Thumbnail">
                            <?php else: ?>
                            <div class="thumbnail-placeholder d-flex align-items-center justify-content-center bg-light rounded" style="height: 80px;">
                                <i class="fas fa-tshirt fa-2x text-secondary"></i>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <!-- Additional Images Thumbnails -->
                    <?php foreach ($product->images as $index => $image): ?>
                    <div class="col-3 col-sm-2">
                        <div class="thumbnail-item border rounded p-1" data-image="<?php echo htmlspecialchars($image['image']); ?>">
                            <img src="<?php echo htmlspecialchars($image['image']); ?>" class="img-fluid rounded" alt="Additional Image Thumbnail <?php echo $index + 1; ?>">
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        
        <!-- Product Details -->
        <div class="col-lg-6">
            <div class="product-category text-uppercase mb-2 text-primary"><?php echo htmlspecialchars($category_name ?? 'Danh mục'); ?></div>
            <h1 class="product-title mb-3"><?php echo htmlspecialchars($product->name ?? 'Sản phẩm'); ?></h1>
            
            <!-- Price -->
            <div class="product-price mb-4">
                <?php if (($product->is_sale ?? 0) == 1 && !empty($product->sale_price) && $product->sale_price < $product->price): ?>
                <span class="text-danger fs-3 fw-bold"><?php echo CURRENCY . number_format($product->sale_price); ?></span>
                <span class="text-muted text-decoration-line-through fs-5 ms-2"><?php echo CURRENCY . number_format($product->price); ?></span>
                <?php else: ?>
                <span class="fs-3 fw-bold"><?php echo CURRENCY . number_format($product->price ?? 0); ?></span>
                <?php endif; ?>
            </div>
            
            <!-- Availability -->
            <div class="product-availability mb-4">
                <div class="d-flex align-items-center mb-2">
                    <span class="me-2 fw-bold">Tình trạng:</span>
                    <?php
                    $total_stock = !empty($product->id) ? $product->getTotalStock() : 0;
                    file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] Total stock: $total_stock\n", FILE_APPEND);
                    ?>
                    <?php if ($total_stock > 0): ?>
                    <span class="badge bg-success rounded-pill py-2 px-3">CÒN HÀNG</span>
                    <?php else: ?>
                    <span class="badge bg-danger rounded-pill py-2 px-3">HẾT HÀNG</span>
                    <?php endif; ?>
                </div>
                <div class="mb-2">
                    <span class="fw-bold">Danh mục:</span> 
                    <a href="index.php?controller=product&action=list&category_id=<?php echo htmlspecialchars($product->category_id ?? 0); ?>" class="ms-2 badge bg-light text-dark text-decoration-none py-2 px-3 rounded-pill"><?php echo htmlspecialchars($category_name ?? 'Danh mục'); ?></a>
                </div>
            </div>
        
            <!-- Add to Cart Form -->
            <?php if ($total_stock > 0): ?>
            <form id="add-to-cart-form" class="mb-4 product-detail-form">
                <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($product->id ?? 0); ?>">
                
                <!-- Variant Selector (chỉ hiển thị nếu có variants) -->
                <?php if (!empty($variants) && is_array($variants)): ?>
                <div class="product-variants mb-4">
                    <label class="fw-bold d-block mb-2">Biến thể:</label>
                    <div class="row g-3">
                        <!-- Size Selector -->
                        <div class="col-12 col-md-6">
                            <label class="fw-bold d-block mb-2">Kích cỡ:</label>
                            <select class="form-select rounded-pill" name="size" id="variant-size" required>
                                <option value="" disabled selected>Chọn kích cỡ</option>
                                <?php
                                $sizes = !empty($variants) ? array_unique(array_column($variants, 'size')) : [];
                                foreach ($sizes as $size):
                                    $size_stock = array_sum(array_map(function($v) use ($size) {
                                        return $v['size'] === $size ? $v['stock'] : 0;
                                    }, $variants));
                                    $disabled = $size_stock == 0 ? 'disabled' : '';
                                ?>
                                <option value="<?php echo htmlspecialchars($size); ?>" <?php echo $disabled; ?>>
                                    <?php echo htmlspecialchars($size); ?><?php echo $size_stock == 0 ? ' (Hết hàng)' : ''; ?>
                                </option>
                                <?php
                                    file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] Size: $size, Stock: $size_stock, Disabled: $disabled\n", FILE_APPEND);
                                endforeach; ?>
                            </select>
                        </div>
                        <!-- Color Selector (chỉ hiển thị nếu có nhiều màu) -->
                        <?php if ($has_multiple_colors): ?>
                        <div class="col-12 col-md-6">
                            <label class="fw-bold d-block mb-2">Màu sắc:</label>
                            <select class="form-select rounded-pill" name="color" id="variant-color" required disabled>
                                <option value="" disabled selected>Chọn màu sắc</option>
                            </select>
                        </div>
                        <?php endif; ?>
                    </div>
                    <input type="hidden" name="variant_id" id="variant-id">
                </div>
                <?php endif; ?>
                
                <div class="row g-3 align-items-end mb-4">
                    <div class="col-12 col-md-4">
                        <label for="quantity" class="form-label fw-bold mb-2">Số lượng:</label>
                        <div class="input-group shadow-sm">
                            <button class="btn btn-outline-primary rounded-start-pill" type="button" onclick="decreaseQuantity()">-</button>
                            <input type="number" class="form-control text-center border-primary" id="quantity" name="quantity" value="1" min="1" max="<?php echo htmlspecialchars($total_stock); ?>">
                            <button class="btn btn-outline-primary rounded-end-pill" type="button" onclick="increaseQuantity()">+</button>
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <button type="button" class="btn btn-outline-secondary rounded-pill w-100 py-2 shadow-sm">
                            <i class="far fa-heart me-1"></i> WISHLIST
                        </button>
                    </div>
                    <div class="col-12 col-md-4">
                        <button type="submit" class="btn btn-primary rounded-pill w-100 py-2 shadow-sm">
                            <i class="fas fa-cart-plus me-1"></i> THÊM VÀO GIỎ
                        </button>
                    </div>
                </div>
            </form>
            <?php else: ?>
            <div class="product-out-of-stock mb-4 p-3 bg-light text-center rounded shadow-sm">
                <p class="mb-2 fw-bold text-danger">SẢN PHẨM TẠM HẾT HÀNG</p>
                <p class="mb-0 small">Vui lòng để lại email để nhận thông báo khi sản phẩm có hàng trở lại</p>
                <form class="mt-3 d-flex gap-2 product-detail-form" id="notify-form">
                    <input type="email" class="form-control rounded-pill" name="email" placeholder="Email của bạn" required>
                    <button type="submit" class="btn btn-primary rounded-pill">Thông báo cho tôi</button>
                </form>
            </div>
            <?php endif; ?>
            
            <!-- Product Features -->
            <div class="product-features mb-4">
                <div class="row g-3">
                    <div class="col-6 col-md-3">
                        <div class="feature-item text-center p-3 rounded shadow-sm">
                            <i class="fas fa-truck-fast fs-3 mb-2 text-primary"></i>
                            <p class="mb-0 small">FREESHIP ĐƠN > 500K</p>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="feature-item text-center p-3 rounded shadow-sm">
                            <i class="fas fa-shield-alt fs-3 mb-2 text-primary"></i>
                            <p class="mb-0 small">BẢO HÀNH CHÍNH HÃNG</p>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="feature-item text-center p-3 rounded shadow-sm">
                            <i class="fas fa-undo fs-3 mb-2 text-primary"></i>
                            <p class="mb-0 small">ĐỔI TRẢ TRONG VÒNG 7 NGÀY</p>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="feature-item text-center p-3 rounded shadow-sm">
                            <i class="fas fa-credit-card fs-3 mb-2 text-primary"></i>
                            <p class="mb-0 small">THANH TOÁN AN TOÀN</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Product Information Tabs -->
            <div class="product-info mb-4">
                <ul class="nav nav-tabs" id="productTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="description-tab" data-bs-toggle="tab" data-bs-target="#description" type="button">Mô tả</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="shipping-tab" data-bs-toggle="tab" data-bs-target="#shipping" type="button">Vận chuyển</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="sizing-tab" data-bs-toggle="tab" data-bs-target="#sizing" type="button">Bảng size</button>
                    </li>
                </ul>
                <div class="tab-content p-4 border border-top-0 rounded-bottom shadow-sm" id="productTabContent">
                    <div class="tab-pane fade show active" id="description" role="tabpanel">
                        <h5 class="fw-bold mb-3">Thông tin chi tiết sản phẩm</h5>
                        <p><?php echo nl2br(htmlspecialchars($product->description ?? 'Không có mô tả')); ?></p>
                        <ul class="mb-0">
                            <li>Chất liệu: 100% Cotton</li>
                            <li>Sản xuất tại Việt Nam</li>
                            <li>Phù hợp với phong cách đường phố</li>
                            <li>Hướng dẫn giặt: Giặt máy ở nhiệt độ thấp, không tẩy</li>
                        </ul>
                    </div>
                    <div class="tab-pane fade" id="shipping" role="tabpanel">
                        <h5 class="fw-bold mb-3">Thông tin vận chuyển</h5>
                        <p>Miễn phí vận chuyển cho đơn hàng trên <?php echo CURRENCY; ?>500.000.</p>
                        <ul>
                            <li>Giao hàng tiêu chuẩn: 2-3 ngày làm việc</li>
                            <li>Giao hàng nhanh: 1-2 ngày làm việc (phí bổ sung)</li>
                            <li>Giao hàng hỏa tốc: Trong ngày (chỉ áp dụng tại Hà Nội & TP.HCM)</li>
                        </ul>
                    </div>
                    <div class="tab-pane fade" id="sizing" role="tabpanel">
                        <h5 class="fw-bold mb-3">Bảng kích cỡ áo</h5>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Size</th>
                                        <th>Chiều cao (cm)</th>
                                        <th>Cân nặng (kg)</th>
                                        <th>Tuổi (tháng)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>1</td>
                                        <td>59-66</td>
                                        <td>3-5</td>
                                        <td>1-3</td>
                                    </tr>
                                    <tr>
                                        <td>2</td>
                                        <td>66-72</td>
                                        <td>6.5-9</td>
                                        <td>6-9</td>
                                    </tr>
                                    <tr>
                                        <td>3</td>
                                        <td>72-80</td>
                                        <td>8.5-10</td>
                                        <td>9-12</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Social Sharing -->
            <div class="product-share border-top pt-4">
                <div class="d-flex align-items-center">
                    <span class="fw-bold me-3">CHIA SẺ:</span>
                    <div class="social-icons d-flex gap-2">
                        <a href="#" class="social-icon text-primary"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="social-icon text-primary"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="social-icon text-primary"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="social-icon text-primary"><i class="fab fa-pinterest"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Related Products -->
<?php if (!empty($related_products)): ?>
<section class="related-products mt-5">
    <h3 class="mb-4">Sản phẩm liên quan</h3>
    <div class="row">
        <?php foreach ($related_products as $related_product): ?>
        <div class="col-12 col-sm-6 col-md-3 mb-4">
            <div class="product-card h-100">
                <div class="card border-0 shadow-sm h-100 rounded">
                    <div class="position-relative">
                        <a href="index.php?controller=product&action=detail&id=<?php echo htmlspecialchars($related_product['id'] ?? 0); ?>">
                            <?php if (!empty($related_product['image'])): ?>
                            <img src="<?php echo htmlspecialchars($related_product['image']); ?>" class="card-img-top img-fluid rounded" alt="<?php echo htmlspecialchars($related_product['name'] ?? 'Sản phẩm'); ?>">
                            <?php else: ?>
                            <div class="card-img-top bg-light p-4 d-flex align-items-center justify-content-center" style="height: 180px;">
                                <i class="fas fa-tshirt fa-3x text-secondary"></i>
                            </div>
                            <?php endif; ?>
                        </a>
                        <?php if (($related_product['is_sale'] ?? 0) == 1 && !empty($related_product['sale_price']) && $related_product['sale_price'] < $related_product['price']): ?>
                        <span class="badge bg-danger position-absolute top-0 end-0 m-2">Giảm giá</span>
                        <?php endif; ?>
                    </div>
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title">
                            <a href="index.php?controller=product&action=detail&id=<?php echo htmlspecialchars($related_product['id'] ?? 0); ?>" class="text-decoration-none text-dark"><?php echo htmlspecialchars($related_product['name'] ?? 'Sản phẩm'); ?></a>
                        </h5>
                        <div class="price-block mb-3">
                            <?php if (($related_product['is_sale'] ?? 0) == 1 && !empty($related_product['sale_price']) && $related_product['sale_price'] < $related_product['price']): ?>
                            <span class="text-danger fw-bold"><?php echo CURRENCY . number_format($related_product['sale_price']); ?></span>
                            <span class="text-muted text-decoration-line-through ms-2"><?php echo CURRENCY . number_format($related_product['price']); ?></span>
                            <?php else: ?>
                            <span class="fw-bold"><?php echo CURRENCY . number_format($related_product['price'] ?? 0); ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="mt-auto">
                            <button class="btn btn-primary btn-sm w-100 add-to-cart-btn rounded-pill" data-product-id="<?php echo htmlspecialchars($related_product['id'] ?? 0); ?>">
                                <i class="fas fa-shopping-cart me-1"></i> Thêm vào giỏ hàng
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</section>
<?php
file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] Đã render related products\n", FILE_APPEND);
endif; ?>

<!-- Customer Reviews Section -->
<section class="customer-reviews mt-5 mb-5">
    <h3 class="mb-4">Đánh giá của khách hàng</h3>
    <div class="alert alert-info rounded shadow-sm">
        <p class="mb-0">Sản phẩm này chưa có đánh giá nào. Hãy là người đầu tiên đánh giá!</p>
    </div>
</section>

<style>
/* Import Google Fonts for related products button */
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;700&display=swap');

/* General styles */
* {
    box-sizing: border-box;
}
.container {
    padding-left: 15px;
    padding-right: 15px;
}

/* Scoped styles for product detail form */
.product-detail-form .input-group .btn {
    background-color: #fff;
    border-color: #007bff;
    color: #007bff;
    transition: all 0.2s ease;
}
.product-detail-form .input-group .btn:hover {
    background-color: #007bff;
    color: #fff;
}
.product-detail-form .input-group .form-control {
    border-color: #007bff;
}
.product-detail-form .btn-primary,
.product-detail-form .btn-outline-secondary {
    transition: all 0.2s ease;
}
.product-detail-form .btn-primary:hover {
    background-color: #0056b3;
    border-color: #0056b3;
}
.product-detail-form .btn-outline-secondary:hover {
    background-color: #6c757d;
    color: #fff;
}
.product-detail-form .btn-primary {
    position: relative;
    overflow: hidden;
}
.product-detail-form .btn-primary::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 0;
    height: 0;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 50%;
    transform: translate(-50%, -50%);
    transition: width 0.4s ease, height 0.4s ease;
}
.product-detail-form .btn-primary:active::after {
    width: 200px;
    height: 200px;
}

/* Styles for related products button */
.related-products .btn-primary {
    font-family: 'Poppins', sans-serif;
    padding: 10px 25px;
    font-weight: 500;
    background-color: #f8f9fa;
    color: #212529;
    border: none;
    transition: background-color 0.3s ease, transform 0.2s ease;
}
.related-products .btn-primary:hover {
    background-color: #dee2e6;
    transform: scale(1.05);
}

/* Styles for cart badge and notification */
.cart-count-badge {
    display: inline-block;
    min-width: 20px;
    padding: 2px 6px;
    font-size: 0.75rem;
    font-weight: 700;
    line-height: 1;
    text-align: center;
    white-space: nowrap;
    vertical-align: baseline;
    background-color: var(--primary-color, #FF2D55);
    color: white;
    border-radius: 10px;
    position: absolute;
    top: -5px;
    right: -10px;
    transition: transform 0.2s ease;
}
.notification {
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    border-radius: 8px;
    animation: slideInRight 0.3s ease-in-out;
}
@keyframes slideInRight {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

/* General styles for product detail page */
.thumbnail-item {
    transition: all 0.3s ease;
    cursor: pointer;
}
.thumbnail-item:hover {
    border-color: #0d6efd;
    transform: scale(1.05);
}
.thumbnail-item.active {
    border-color: #0d6efd;
    border-width: 2px;
}
.product-image-container img, .product-placeholder {
    transition: opacity 0.3s ease;
}
.product-image-container img:hover {
    opacity: 0.9;
}
.product-image-container .badge {
    z-index: 10;
}
.category-header {
    margin-bottom: 2rem !important;
}
.product-title {
    font-size: 1.8rem;
}
.product-price {
    margin-bottom: 1.5rem;
}
.product-availability {
    margin-bottom: 1.5rem;
}
.product-variants {
    margin-bottom: 1.5rem;
}
.product-features {
    margin-bottom: 1.5rem;
}
.product-info {
    margin-bottom: 1.5rem;
}
.product-share {
    margin-bottom: 1.5rem;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .container {
        padding-left: 10px;
        padding-right: 10px;
    }
    .category-header-bg {
        height: 100px;
    }
    .breadcrumb {
        font-size: 0.9rem;
        flex-wrap: wrap;
    }
    .product-image-container img,
    .product-placeholder {
        max-height: 350px;
    }
    .product-thumbnails .col-sm-2 {
        flex: 0 0 20%;
        max-width: 20%;
    }
    .thumbnail-item img,
    .thumbnail-placeholder {
        height: 60px;
        object-fit: cover;
    }
    .product-title {
        font-size: 1.5rem;
    }
    .product-price .fs-3 {
        font-size: 1.4rem !important;
    }
    .product-price .fs-5 {
        font-size: 1rem !important;
    }
    .product-variants .form-select {
        font-size: 0.9rem;
        padding: 0.5rem;
    }
    .product-detail-form .input-group {
        flex-wrap: nowrap;
    }
    .product-detail-form .input-group .btn {
        padding: 0.5rem;
        font-size: 0.9rem;
    }
    .product-detail-form .input-group .form-control {
        font-size: 0.9rem;
        padding: 0.5rem;
    }
    .product-detail-form .btn-primary,
    .product-detail-form .btn-outline-secondary {
        font-size: 0.9rem;
        padding: 0.5rem 1rem;
    }
    .product-features .feature-item {
        padding: 1rem;
    }
    .product-features .fs-3 {
        font-size: 1.5rem !important;
    }
    .product-features .small {
        font-size: 0.8rem;
    }
    .product-info .nav-tabs .nav-link {
        font-size: 0.9rem;
        padding: 0.5rem 1rem;
    }
    .product-info .tab-content {
        padding: 1rem;
    }
    .product-info h5 {
        font-size: 1.2rem;
    }
    .product-info p,
    .product-info ul li {
        font-size: 0.9rem;
    }
    .table-responsive table {
        font-size: 0.85rem;
    }
    .product-share .social-icon {
        font-size: 1rem;
    }
    .related-products .btn-primary {
        font-size: 1rem;
        padding: 8px 16px;
    }
    .related-products .card-img-top,
    .related-products .card-img-top div {
        height: 150px;
    }
    .related-products .card-title {
        font-size: 1rem;
    }
    .related-products .price-block {
        font-size: 0.9rem;
    }
}

@media (max-width: 576px) {
    .container {
        padding-left: 8px;
        padding-right: 8px;
    }
    .category-header-bg {
        height: 80px;
    }
    .breadcrumb {
        font-size: 0.8rem;
    }
    .product-image-container img,
    .product-placeholder {
        max-height: 300px;
    }
    .product-thumbnails .col-3 {
        flex: 0 0 25%;
        max-width: 25%;
    }
    .thumbnail-item img,
    .thumbnail-placeholder {
        height: 50px;
    }
    .product-title {
        font-size: 1.3rem;
    }
    .product-price .fs-3 {
        font-size: 1.2rem !important;
    }
    .product-price .fs-5 {
        font-size: 0.9rem !important;
    }
    .product-variants .form-select {
        font-size: 0.85rem;
        padding: 0.4rem;
    }
    .product-detail-form .row {
        flex-direction: column;
    }
    .product-detail-form .col-12 {
        margin-bottom: 0.5rem;
    }
    .product-detail-form .input-group .btn {
        padding: 0.4rem;
        font-size: 0.85rem;
    }
    .product-detail-form .input-group .form-control {
        font-size: 0.85rem;
        padding: 0.4rem;
    }
    .product-detail-form .btn-primary,
    .product-detail-form .btn-outline-secondary {
        font-size: 0.85rem;
        padding: 0.4rem 0.8rem;
    }
    .product-out-of-stock .form-control,
    .product-out-of-stock .btn {
        font-size: 0.85rem;
        padding: 0.4rem;
    }
    .product-features .col-6 {
        flex: 0 0 50%;
        max-width: 50%;
    }
    .product-features .feature-item {
        padding: 0.8rem;
    }
    .product-features .fs-3 {
        font-size: 1.2rem !important;
    }
    .product-features .small {
        font-size: 0.75rem;
    }
    .product-info .nav-tabs {
        flex-wrap: nowrap;
        overflow-x: auto;
        white-space: nowrap;
    }
    .product-info .nav-tabs .nav-link {
        font-size: 0.85rem;
        padding: 0.4rem 0.8rem;
    }
    .product-info .tab-content {
        padding: 0.8rem;
    }
    .product-info h5 {
        font-size: 1.1rem;
    }
    .product-info p,
    .product-info ul li {
        font-size: 0.85rem;
    }
    .table-responsive table {
        font-size: 0.8rem;
    }
    .product-share .social-icon {
        font-size: 0.9rem;
    }
    .related-products .col-sm-6 {
        flex: 0 0 100%;
        max-width: 100%;
    }
    .related-products .btn-primary {
        font-size: 0.9rem;
        padding: 6px 12px;
    }
    .related-products .card-img-top,
    .related-products .card-img-top div {
        height: 120px;
    }
    .related-products .card-title {
        font-size: 0.9rem;
    }
    .related-products .price-block {
        font-size: 0.85rem;
    }
}
</style>

<script>
console.log('Bắt đầu script views/products/detail.php');
console.log('Product ID:', <?php echo json_encode($product->id ?? 'N/A'); ?>);
console.log('Variants:', <?php echo json_encode($variants ?? []); ?>);
console.log('Category Name:', <?php echo json_encode($category_name ?? 'N/A'); ?>);
console.log('Related Products Count:', <?php echo json_encode(count($related_products ?? [])); ?>);
console.log('Total Stock:', <?php echo json_encode($total_stock); ?>);
console.log('Has Multiple Colors:', <?php echo json_encode($has_multiple_colors); ?>);
console.log('Main Image:', <?php echo json_encode($product->image ?? 'N/A'); ?>);
console.log('Additional Images:', <?php echo json_encode($product->images); ?>);

document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded');
    const form = document.getElementById('add-to-cart-form');
    const sizeSelect = document.getElementById('variant-size');
    const colorSelect = document.getElementById('variant-color');
    const variantIdInput = document.getElementById('variant-id');
    const quantityInput = document.getElementById('quantity');
    const variants = <?php echo json_encode($variants ?? []); ?>;
    const totalStock = <?php echo json_encode($total_stock); ?>;
    
    // Khởi tạo số lượng ban đầu
    if (quantityInput) {
        quantityInput.value = 1;
        quantityInput.max = variants.length > 0 ? 1 : totalStock;
        console.log('Khởi tạo quantity:', { value: quantityInput.value, max: quantityInput.max });
    }
    
    // Thumbnail click handling
    try {
        document.querySelectorAll('.thumbnail-item').forEach(thumbnail => {
            thumbnail.addEventListener('click', function() {
                console.log('Thumbnail clicked:', this.dataset.image);
                document.querySelectorAll('.thumbnail-item').forEach(t => t.classList.remove('active'));
                this.classList.add('active');
                const mainImage = document.querySelector('.main-image');
                const imageSrc = this.dataset.image;
                if (mainImage && imageSrc) {
                    mainImage.src = imageSrc;
                    console.log('Main image updated to:', imageSrc);
                } else {
                    console.warn('Không thể cập nhật ảnh chính:', { mainImage: !!mainImage, imageSrc });
                }
            });
        });
    } catch (e) {
        console.error('Lỗi khi xử lý thumbnail:', e);
    }
    
    // Update color options based on size
    if (sizeSelect) {
        sizeSelect.addEventListener('change', function() {
            console.log('Size selected:', this.value);
            const selectedSize = this.value;
            if (colorSelect) {
                colorSelect.innerHTML = '<option value="" disabled selected>Chọn màu sắc</option>';
                const availableColors = variants
                    .filter(v => v.size === selectedSize && v.color)
                    .map(v => ({ color: v.color, stock: v.stock }));
                const uniqueColors = [...new Set(availableColors.map(c => c.color))];
                
                uniqueColors.forEach(color => {
                    const colorStock = availableColors.find(c => c.color === color)?.stock || 0;
                    const option = document.createElement('option');
                    option.value = color;
                    option.textContent = color + (colorStock === 0 ? ' (Hết hàng)' : '');
                    if (colorStock === 0) {
                        option.disabled = true;
                    }
                    colorSelect.appendChild(option);
                });
                
                colorSelect.disabled = uniqueColors.length === 0 || uniqueColors.every(color => {
                    return availableColors.find(c => c.color === color)?.stock === 0;
                });
                if (!colorSelect.disabled) {
                    colorSelect.focus();
                }
                console.log('Updated colors:', uniqueColors, 'Disabled:', colorSelect.disabled);
            }
            updateVariant();
        });
    } else {
        console.log('Không có sizeSelect, bỏ qua xử lý biến thể');
    }
    
    // Update variant ID and max quantity
    if (colorSelect) {
        colorSelect.addEventListener('change', updateVariant);
    }
    
    function updateVariant() {
        if (!sizeSelect) {
            console.log('Bỏ qua updateVariant vì không có sizeSelect');
            return;
        }
        const selectedSize = sizeSelect.value;
        const selectedColor = colorSelect ? colorSelect.value : (variants.length > 0 ? variants[0].color : '');
        const variant = variants.find(v => v.size === selectedSize && (!colorSelect || v.color === selectedColor));
        
        if (variant && variant.stock > 0) {
            console.log('Selected variant:', variant);
            variantIdInput.value = variant.id;
            quantityInput.max = variant.stock;
            quantityInput.value = 1;
        } else {
            console.log('No valid variant selected');
            variantIdInput.value = '';
            quantityInput.max = variants.length > 0 ? 1 : totalStock;
            quantityInput.value = 1;
        }
        console.log('Cập nhật quantity:', { value: quantityInput.value, max: quantityInput.max });
    }
    
    // Quantity increase/decrease handling
    window.decreaseQuantity = function() {
        if (quantityInput) {
            let value = parseInt(quantityInput.value) || 1;
            const min = parseInt(quantityInput.min) || 1;
            if (value > min) {
                quantityInput.value = value - 1;
                console.log('Quantity decreased:', quantityInput.value);
            } else {
                quantityInput.value = min;
                console.log('Quantity at minimum:', quantityInput.value);
            }
        } else {
            console.error('Không tìm thấy quantityInput');
        }
    };
    
    window.increaseQuantity = function() {
        if (quantityInput) {
            let value = parseInt(quantityInput.value) || 1;
            const max = parseInt(quantityInput.max) || totalStock;
            if (value < max) {
                quantityInput.value = value + 1;
                console.log('Quantity increased:', quantityInput.value);
            } else {
                quantityInput.value = max;
                console.log('Quantity at maximum:', quantityInput.value);
            }
        } else {
            console.error('Không tìm thấy quantityInput');
        }
    };
    
    // Validate quantity input
    if (quantityInput) {
        quantityInput.addEventListener('input', function() {
            let value = parseInt(this.value);
            const min = parseInt(this.min) || 1;
            const max = parseInt(this.max) || totalStock;
            
            console.log('Quantity input changed:', { value, min, max });
            if (isNaN(value) || value < min) {
                this.value = min;
            } else if (value > max) {
                this.value = max;
            }
            console.log('Quantity validated:', this.value);
        });
    } else {
        console.error('Không tìm thấy quantityInput element');
    }
    
    // Form submission with animation
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const productId = this.querySelector('[name="product_id"]').value;
            const variantId = variants.length > 0 ? (this.querySelector('[name="variant_id"]')?.value || '') : '';
            const quantity = parseInt(this.querySelector('[name="quantity"]').value);
            const addToCartBtn = this.querySelector('button[type="submit"]');
            
            console.log('Form submitted:', { productId, variantId, quantity });
            
            if (variants.length > 0 && !variantId) {
                console.error('Lỗi: Chưa chọn biến thể hợp lệ');
                alert('Vui lòng chọn kích cỡ' + (colorSelect ? ' và màu sắc' : '') + ' hợp lệ.');
                return;
            }
            
            if (quantity < 1 || isNaN(quantity)) {
                console.error('Lỗi: Số lượng không hợp lệ');
                alert('Số lượng không hợp lệ.');
                return;
            }
            
            // Disable button and show loading state
            addToCartBtn.disabled = true;
            const originalBtnText = addToCartBtn.innerHTML;
            addToCartBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Đang thêm...';
            
            // Get main image for animation
            const mainImage = document.querySelector('.main-image');
            const cartIcon = document.querySelector('.fa-shopping-cart');
            
            // Create animation effect
            if (mainImage && cartIcon) {
                const imgClone = mainImage.cloneNode(true);
                imgClone.style.position = 'fixed';
                imgClone.style.zIndex = '1000';
                imgClone.style.width = mainImage.offsetWidth + 'px';
                imgClone.style.height = mainImage.offsetHeight + 'px';
                imgClone.style.top = mainImage.getBoundingClientRect().top + 'px';
                imgClone.style.left = mainImage.getBoundingClientRect().left + 'px';
                imgClone.style.transition = 'all 0.7s ease-in-out';
                document.body.appendChild(imgClone);
                
                setTimeout(() => {
                    const cartRect = cartIcon.getBoundingClientRect();
                    imgClone.style.width = '30px';
                    imgClone.style.height = '30px';
                    imgClone.style.top = cartRect.top + 'px';
                    imgClone.style.left = cartRect.left + 'px';
                    imgClone.style.opacity = '0.5';
                }, 100);
                
                setTimeout(() => {
                    imgClone.remove();
                }, 800);
            }
            
            // AJAX request to add to cart
            fetch('index.php?controller=product&action=addToCart', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: `product_id=${encodeURIComponent(productId)}&variant_id=${encodeURIComponent(variantId)}&quantity=${encodeURIComponent(quantity)}`
            })
            .then(response => {
                console.log('AJAX response received', { status: response.status, ok: response.ok });
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
                        // Update all cart count badges
                        const cartBadges = document.querySelectorAll('.cart-count-badge');
                        cartBadges.forEach(badge => {
                            badge.textContent = data.cart_count || 0;
                            badge.style.display = data.cart_count > 0 ? 'inline-block' : 'none';
                            badge.classList.add('animate__animated', 'animate__bounce');
                            setTimeout(() => {
                                badge.classList.remove('animate__animated', 'animate__bounce');
                            }, 1000);
                        });

                        // Dispatch custom event for header and other pages
                        const cartUpdatedEvent = new CustomEvent('cartUpdated', {
                            detail: { cart_count: data.cart_count }
                        });
                        document.dispatchEvent(cartUpdatedEvent);

                        // Show success notification
                        showNotification('Đã thêm vào giỏ hàng!', 'success');
                    } else {
                        console.error('Lỗi từ server:', data.message);
                        showNotification(data.message || 'Không thể thêm vào giỏ hàng.', 'error');
                    }
                } catch (e) {
                    console.error('Lỗi phân tích JSON:', e, 'Response text:', text);
                    showNotification('Lỗi server: Không nhận được dữ liệu hợp lệ.', 'error');
                }
            })
            .catch(error => {
                console.error('Lỗi AJAX:', error);
                showNotification('Đã xảy ra lỗi: ' + error.message, 'error');
            })
            .finally(() => {
                // Restore button state
                addToCartBtn.disabled = false;
                addToCartBtn.innerHTML = originalBtnText;
            });
        });
    } else {
        console.error('Không tìm thấy add-to-cart-form');
    }

    // Notify form submission
    const notifyForm = document.getElementById('notify-form');
    if (notifyForm) {
        notifyForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const email = this.querySelector('[name="email"]').value;
            console.log('Notify form submitted:', email);
            if (email) {
                alert('Cảm ơn bạn! Chúng tôi sẽ thông báo khi sản phẩm có hàng.');
                this.reset();
            } else {
                console.error('Lỗi: Email không hợp lệ');
                alert('Vui lòng nhập email hợp lệ.');
            }
        });
    } else {
        console.log('Không có notify-form (sản phẩm còn hàng)');
    }
    
    // Notification function
    function showNotification(message, type) {
        const notification = document.createElement('div');
        notification.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show notification`;
        notification.style.position = 'fixed';
        notification.style.top = '20px';
        notification.style.right = '20px';
        notification.style.zIndex = '2000';
        notification.style.minWidth = '300px';
        notification.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        document.body.appendChild(notification);
        
        // Auto dismiss after 3 seconds
        setTimeout(() => {
            notification.classList.remove('show');
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    }
});
</script>

<?php
// Include footer
try {
    $footer_path = __DIR__ . '/../layouts/footer.php';
    if (!file_exists($footer_path)) {
        file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] Lỗi: File $footer_path không tồn tại\n", FILE_APPEND);
        die("Lỗi: File footer.php không tồn tại tại " . htmlspecialchars($footer_path));
    }
    include $footer_path;
    file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] Đã include footer.php\n", FILE_APPEND);
    file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] Hoàn thành render views/products/detail.php\n", FILE_APPEND);
} catch (Exception $e) {
    file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] Lỗi khi include footer.php: " . $e->getMessage() . "\n", FILE_APPEND);
    die("Lỗi khi load footer: " . htmlspecialchars($e->getMessage()));
}
?>