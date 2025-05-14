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
$log_file = '/tmp/debug.log';
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

$page_title = htmlspecialchars($product->name ?? 'Sản phẩm');
file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] Page title: $page_title\n", FILE_APPEND);

// Kiểm tra variant màu sắc
$colors = !empty($variants) ? array_unique(array_filter(array_column($variants, 'color'), fn($color) => !empty($color))) : [];
$has_multiple_colors = count($colors) > 1;
file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] Colors available: " . json_encode($colors) . ", Has multiple colors: " . ($has_multiple_colors ? 'true' : 'false') . "\n", FILE_APPEND);

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

<!-- Đảm bảo Bootstrap và Font Awesome được include -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<!-- Debug information (chỉ hiển thị nếu DEBUG_MODE bật) -->
<?php if (defined('DEBUG_MODE') && DEBUG_MODE): ?>
<div class="debug-info alert alert-info">
    <strong>Debug Info:</strong><br>
    Product ID: <?php echo htmlspecialchars($product->id ?? 'N/A'); ?><br>
    Product Name: <?php echo htmlspecialchars($product->name ?? 'N/A'); ?><br>
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
    <div class="category-header-bg" style="background-color: var(--light-bg-color); height: 120px; position: relative; overflow: hidden;">
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
        <div class="position-absolute" style="top:0; right:0; bottom:0; left:0; background: linear-gradient(135deg, rgba(255,45,85,0.1) 0%, rgba(74,0,224,0.05) 100%);"></div>
    </div>
</div>

<div class="container mb-5">
    <div class="row">
        <!-- Product Images -->
        <div class="col-lg-6 mb-4 mb-lg-0">
            <div class="product-image-container position-relative">
                <?php if(!empty($product->image)): ?>
                <img src="<?php echo htmlspecialchars($product->image); ?>" class="img-fluid rounded shadow-sm border main-image" alt="<?php echo htmlspecialchars($product->name ?? 'Sản phẩm'); ?>" style="max-height: 500px; width: 100%; object-fit: cover;">
                <?php else: ?>
                <div class="product-placeholder d-flex align-items-center justify-content-center bg-light rounded border" style="height: 500px;">
                    <i class="fas fa-tshirt fa-6x text-secondary"></i>
                </div>
                <?php endif; ?>
                
                <?php if(($product->is_sale ?? 0) == 1 && !empty($product->sale_price) && $product->sale_price < $product->price): ?>
                <span class="badge bg-danger position-absolute top-0 end-0 m-3">SALE</span>
                <?php endif; ?>
            </div>
            
            <!-- Product Thumbnails -->
            <div class="product-thumbnails mt-3">
                <div class="row g-2">
                    <div class="col-3">
                        <div class="thumbnail-item border rounded p-1 <?php echo !empty($product->image) ? 'active' : ''; ?>" data-image="<?php echo htmlspecialchars($product->image ?? ''); ?>">
                            <?php if(!empty($product->image)): ?>
                            <img src="<?php echo htmlspecialchars($product->image); ?>" class="img-fluid rounded" alt="Thumbnail">
                            <?php else: ?>
                            <div class="thumbnail-placeholder d-flex align-items-center justify-content-center bg-light rounded" style="height: 80px;">
                                <i class="fas fa-tshirt fa-2x text-secondary"></i>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <!-- Placeholder thumbnails -->
                    <?php for($i = 0; $i < 3; $i++): ?>
                    <div class="col-3">
                        <div class="thumbnail-item border rounded p-1" data-image="<?php echo htmlspecialchars($product->image ?? ''); ?>">
                            <?php if(!empty($product->image)): ?>
                            <img src="<?php echo htmlspecialchars($product->image); ?>" class="img-fluid rounded" alt="Thumbnail">
                            <?php else: ?>
                            <div class="thumbnail-placeholder d-flex align-items-center justify-content-center bg-light rounded" style="height: 80px;">
                                <i class="fas fa-tshirt fa-2x text-secondary"></i>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endfor; ?>
                </div>
            </div>
        </div>
        
        <!-- Product Details -->
        <div class="col-lg-6">
            <div class="product-category text-uppercase mb-2"><?php echo htmlspecialchars($category_name ?? 'Danh mục'); ?></div>
            <h1 class="product-title mb-3"><?php echo htmlspecialchars($product->name ?? 'Sản phẩm'); ?></h1>
            
            <!-- Price -->
            <div class="product-price mb-4">
                <?php if(($product->is_sale ?? 0) == 1 && !empty($product->sale_price) && $product->sale_price < $product->price): ?>
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
                    <?php if($total_stock > 0): ?>
                    <span class="badge bg-success rounded-0 py-2 px-3">CÒN HÀNG</span>
                    <?php else: ?>
                    <span class="badge bg-danger rounded-0 py-2 px-3">HẾT HÀNG</span>
                    <?php endif; ?>
                </div>
                <div class="mb-2">
                    <span class="fw-bold">Danh mục:</span> 
                    <a href="index.php?controller=product&action=list&category_id=<?php echo htmlspecialchars($product->category_id ?? 0); ?>" class="ms-2 badge bg-light text-dark text-decoration-none py-2 px-3 rounded-0"><?php echo htmlspecialchars($category_name ?? 'Danh mục'); ?></a>
                </div>
            </div>
            
            <!-- Short Description -->
            <div class="product-description mb-4">
                <p class="lead"><?php echo nl2br(htmlspecialchars($product->description ?? 'Không có mô tả')); ?></p>
            </div>
        
            <!-- Add to Cart Form -->
            <?php if($total_stock > 0): ?>
            <form id="add-to-cart-form" class="mb-4">
                <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($product->id ?? 0); ?>">
                
                <!-- Variant Selector (chỉ hiển thị nếu có variants) -->
                <?php if(!empty($variants) && is_array($variants)): ?>
                <div class="product-variants mb-4">
                    <label class="fw-bold d-block mb-2">Biến thể:</label>
                    <div class="row">
                        <!-- Size Selector -->
                        <div class="col-md-6 mb-3">
                            <label class="fw-bold d-block mb-2">Kích cỡ:</label>
                            <select class="form-select" name="size" id="variant-size" required>
                                <option value="" disabled selected>Chọn kích cỡ</option>
                                <?php
                                $sizes = !empty($variants) ? array_unique(array_filter(array_column($variants, 'size'), function($size) use ($variants) {
                                    foreach ($variants as $v) {
                                        if ($v['size'] === $size && $v['stock'] > 0) {
                                            return true;
                                        }
                                    }
                                    return false;
                                })) : [];
                                file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] Sizes available: " . json_encode($sizes) . "\n", FILE_APPEND);
                                foreach($sizes as $size):
                                ?>
                                <option value="<?php echo htmlspecialchars($size); ?>"><?php echo htmlspecialchars($size); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <!-- Color Selector (chỉ hiển thị nếu có nhiều màu) -->
                        <?php if($has_multiple_colors): ?>
                        <div class="col-md-6 mb-3">
                            <label class="fw-bold d-block mb-2">Màu sắc:</label>
                            <select class="form-select" name="color" id="variant-color" required disabled>
                                <option value="" disabled selected>Chọn màu sắc</option>
                            </select>
                        </div>
                        <?php endif; ?>
                    </div>
                    <input type="hidden" name="variant_id" id="variant-id">
                </div>
                <?php endif; ?>
                
                <div class="row align-items-end mb-4">
                    <div class="col-5 col-md-3">
                        <label for="quantity" class="form-label fw-bold mb-2">Số lượng:</label>
                        <input type="number" id="quantity" name="quantity" class="form-control text-center" value="1" min="1" max="<?php echo htmlspecialchars($total_stock); ?>">
                    </div>
                    <div class="col-7 col-md-4 mb-3 mb-md-0">
                        <button type="button" class="btn btn-outline-dark w-100 py-3 fw-bold">
                            <i class="far fa-heart"></i> WISHLIST
                        </button>
                    </div>
                    <div class="col-12 col-md-5">
                        <button type="submit" class="btn btn-primary w-100 py-3 fw-bold">
                            THÊM VÀO GIỎ HÀNG
                        </button>
                    </div>
                </div>
            </form>
            <?php else: ?>
            <div class="product-out-of-stock mb-4 p-3 bg-light text-center">
                <p class="mb-2 fw-bold text-danger">SẢN PHẨM TẠM HẾT HÀNG</p>
                <p class="mb-0 small">Vui lòng để lại email để nhận thông báo khi sản phẩm có hàng trở lại</p>
                <form class="mt-3 d-flex gap-2" id="notify-form">
                    <input type="email" class="form-control" name="email" placeholder="Email của bạn" required>
                    <button type="submit" class="btn btn-primary">Thông báo cho tôi</button>
                </form>
            </div>
            <?php endif; ?>
            
            <!-- Product Features -->
            <div class="product-features mb-4">
                <div class="row g-3">
                    <div class="col-6 col-md-3">
                        <div class="feature-item text-center p-3">
                            <i class="fas fa-truck-fast fs-3 mb-2 text-primary"></i>
                            <p class="mb-0 small">FREESHIP ĐƠN > 500K</p>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="feature-item text-center p-3">
                            <i class="fas fa-shield-alt fs-3 mb-2 text-primary"></i>
                            <p class="mb-0 small">BẢO HÀNH CHÍNH HÃNG</p>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="feature-item text-center p-3">
                            <i class="fas fa-undo fs-3 mb-2 text-primary"></i>
                            <p class="mb-0 small">ĐỔI TRẢ 30 NGÀY</p>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="feature-item text-center p-3">
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
                <div class="tab-content p-4 border border-top-0" id="productTabContent">
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
                        <table class="table table-bordered table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Size</th>
                                    <th>Chiều cao (cm)</th>
                                    <th>Cân nặng (kg)</th>
                                    <th>Ngực (cm)</th>
                                    <th>Eo (cm)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>S</td>
                                    <td>155-165</td>
                                    <td>45-55</td>
                                    <td>86-91</td>
                                    <td>71-76</td>
                                </tr>
                                <tr>
                                    <td>M</td>
                                    <td>165-170</td>
                                    <td>55-65</td>
                                    <td>91-97</td>
                                    <td>76-81</td>
                                </tr>
                                <tr>
                                    <td>L</td>
                                    <td>170-175</td>
                                    <td>65-75</td>
                                    <td>97-102</td>
                                    <td>81-86</td>
                                </tr>
                                <tr>
                                    <td>XL</td>
                                    <td>175-180</td>
                                    <td>75-85</td>
                                   świetlen

System: Có vẻ như phản hồi của bạn bị cắt giữa chừng. Dựa trên yêu cầu, bạn muốn sửa hai lỗi trong `views/products/detail.php`:
1. Loại bỏ nút tăng/giảm số lượng, chỉ cho phép nhập số trực tiếp vào ô input, và đảm bảo ô input hoạt động đúng (giới hạn `min="1"`, `max` dựa trên `$total_stock` hoặc `variant.stock`).
2. Ẩn dropdown màu sắc nếu sản phẩm không có variant màu sắc (tức là tất cả biến thể có cùng `color` hoặc không có `color`), nhưng vẫn hiển thị dropdown kích cỡ nếu có nhiều kích cỡ.

Tôi sẽ hoàn thiện file `views/products/detail.php` dựa trên artifact trước đó, đảm bảo:
- Xóa các nút tăng/giảm và chỉ giữ ô input số lượng với kiểm tra `min`/`max`.
- Kiểm tra `$variants` để ẩn dropdown màu sắc nếu chỉ có một màu duy nhất.
- Giữ các logic trước đó (ẩn phần chọn biến thể nếu `$variants` rỗng).

---

### Mã cập nhật cho `views/products/detail.php`

Dưới đây là nội dung hoàn chỉnh cho `views/products/detail.php`:

<xaiArtifact artifact_id="a3a6bb46-c6ca-4596-9df4-701164b3271b" artifact_version_id="203d822e-3815-4da3-9bfb-8df2ebf0a9c5" title="views/products/detail.php" contentType="text/php">
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
$log_file = '/tmp/debug.log';
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

$page_title = htmlspecialchars($product->name ?? 'Sản phẩm');
file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] Page title: $page_title\n", FILE_APPEND);

// Kiểm tra variant màu sắc
$colors = !empty($variants) ? array_unique(array_filter(array_column($variants, 'color'), fn($color) => !empty($color))) : [];
$has_multiple_colors = count($colors) > 1;
file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] Colors available: " . json_encode($colors) . ", Has multiple colors: " . ($has_multiple_colors ? 'true' : 'false') . "\n", FILE_APPEND);

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

<!-- Đảm bảo Bootstrap và Font Awesome được include -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<!-- Debug information (chỉ hiển thị nếu DEBUG_MODE bật) -->
<?php if (defined('DEBUG_MODE') && DEBUG_MODE): ?>
<div class="debug-info alert alert-info">
    <strong>Debug Info:</strong><br>
    Product ID: <?php echo htmlspecialchars($product->id ?? 'N/A'); ?><br>
    Product Name: <?php echo htmlspecialchars($product->name ?? 'N/A'); ?><br>
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
    <div class="category-header-bg" style="background-color: var(--light-bg-color); height: 120px; position: relative; overflow: hidden;">
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
        <div class="position-absolute" style="top:0; right:0; bottom:0; left:0; background: linear-gradient(135deg, rgba(255,45,85,0.1) 0%, rgba(74,0,224,0.05) 100%);"></div>
    </div>
</div>

<div class="container mb-5">
    <div class="row">
        <!-- Product Images -->
        <div class="col-lg-6 mb-4 mb-lg-0">
            <div class="product-image-container position-relative">
                <?php if(!empty($product->image)): ?>
                <img src="<?php echo htmlspecialchars($product->image); ?>" class="img-fluid rounded shadow-sm border main-image" alt="<?php echo htmlspecialchars($product->name ?? 'Sản phẩm'); ?>" style="max-height: 500px; width: 100%; object-fit: cover;">
                <?php else: ?>
                <div class="product-placeholder d-flex align-items-center justify-content-center bg-light rounded border" style="height: 500px;">
                    <i class="fas fa-tshirt fa-6x text-secondary"></i>
                </div>
                <?php endif; ?>
                
                <?php if(($product->is_sale ?? 0) == 1 && !empty($product->sale_price) && $product->sale_price < $product->price): ?>
                <span class="badge bg-danger position-absolute top-0 end-0 m-3">SALE</span>
                <?php endif; ?>
            </div>
            
            <!-- Product Thumbnails -->
            <div class="product-thumbnails mt-3">
                <div class="row g-2">
                    <div class="col-3">
                        <div class="thumbnail-item border rounded p-1 <?php echo !empty($product->image) ? 'active' : ''; ?>" data-image="<?php echo htmlspecialchars($product->image ?? ''); ?>">
                            <?php if(!empty($product->image)): ?>
                            <img src="<?php echo htmlspecialchars($product->image); ?>" class="img-fluid rounded" alt="Thumbnail">
                            <?php else: ?>
                            <div class="thumbnail-placeholder d-flex align-items-center justify-content-center bg-light rounded" style="height: 80px;">
                                <i class="fas fa-tshirt fa-2x text-secondary"></i>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <!-- Placeholder thumbnails -->
                    <?php for($i = 0; $i < 3; $i++): ?>
                    <div class="col-3">
                        <div class="thumbnail-item border rounded p-1" data-image="<?php echo htmlspecialchars($product->image ?? ''); ?>">
                            <?php if(!empty($product->image)): ?>
                            <img src="<?php echo htmlspecialchars($product->image); ?>" class="img-fluid rounded" alt="Thumbnail">
                            <?php else: ?>
                            <div class="thumbnail-placeholder d-flex align-items-center justify-content-center bg-light rounded" style="height: 80px;">
                                <i class="fas fa-tshirt fa-2x text-secondary"></i>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endfor; ?>
                </div>
            </div>
        </div>
        
        <!-- Product Details -->
        <div class="col-lg-6">
            <div class="product-category text-uppercase mb-2"><?php echo htmlspecialchars($category_name ?? 'Danh mục'); ?></div>
            <h1 class="product-title mb-3"><?php echo htmlspecialchars($product->name ?? 'Sản phẩm'); ?></h1>
            
            <!-- Price -->
            <div class="product-price mb-4">
                <?php if(($product->is_sale ?? 0) == 1 && !empty($product->sale_price) && $product->sale_price < $product->price): ?>
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
                    <?php if($total_stock > 0): ?>
                    <span class="badge bg-success rounded-0 py-2 px-3">CÒN HÀNG</span>
                    <?php else: ?>
                    <span class="badge bg-danger rounded-0 py-2 px-3">HẾT HÀNG</span>
                    <?php endif; ?>
                </div>
                <div class="mb-2">
                    <span class="fw-bold">Danh mục:</span> 
                    <a href="index.php?controller=product&action=list&category_id=<?php echo htmlspecialchars($product->category_id ?? 0); ?>" class="ms-2 badge bg-light text-dark text-decoration-none py-2 px-3 rounded-0"><?php echo htmlspecialchars($category_name ?? 'Danh mục'); ?></a>
                </div>
            </div>
            
            <!-- Short Description -->
            <div class="product-description mb-4">
                <p class="lead"><?php echo nl2br(htmlspecialchars($product->description ?? 'Không có mô tả')); ?></p>
            </div>
        
            <!-- Add to Cart Form -->
            <?php if($total_stock > 0): ?>
            <form id="add-to-cart-form" class="mb-4">
                <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($product->id ?? 0); ?>">
                
                <!-- Variant Selector (chỉ hiển thị nếu có variants) -->
                <?php if(!empty($variants) && is_array($variants)): ?>
                <div class="product-variants mb-4">
                    <label class="fw-bold d-block mb-2">Biến thể:</label>
                    <div class="row">
                        <!-- Size Selector -->
                        <div class="col-md-6 mb-3">
                            <label class="fw-bold d-block mb-2">Kích cỡ:</label>
                            <select class="form-select" name="size" id="variant-size" required>
                                <option value="" disabled selected>Chọn kích cỡ</option>
                                <?php
                                $sizes = !empty($variants) ? array_unique(array_filter(array_column($variants, 'size'), function($size) use ($variants) {
                                    foreach ($variants as $v) {
                                        if ($v['size'] === $size && $v['stock'] > 0) {
                                            return true;
                                        }
                                    }
                                    return false;
                                })) : [];
                                file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] Sizes available: " . json_encode($sizes) . "\n", FILE_APPEND);
                                foreach($sizes as $size):
                                ?>
                                <option value="<?php echo htmlspecialchars($size); ?>"><?php echo htmlspecialchars($size); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <!-- Color Selector (chỉ hiển thị nếu có nhiều màu) -->
                        <?php if($has_multiple_colors): ?>
                        <div class="col-md-6 mb-3">
                            <label class="fw-bold d-block mb-2">Màu sắc:</label>
                            <select class="form-select" name="color" id="variant-color" required disabled>
                                <option value="" disabled selected>Chọn màu sắc</option>
                            </select>
                        </div>
                        <?php endif; ?>
                    </div>
                    <input type="hidden" name="variant_id" id="variant-id">
                </div>
                <?php endif; ?>
                
                <div class="row align-items-end mb-4">
                    <div class="col-5 col-md-3">
                        <label for="quantity" class="form-label fw-bold mb-2">Số lượng:</label>
                        <input type="number" id="quantity" name="quantity" class="form-control text-center" value="1" min="1" max="<?php echo htmlspecialchars($total_stock); ?>">
                    </div>
                    <div class="col-7 col-md-4 mb-3 mb-md-0">
                        <button type="button" class="btn btn-outline-dark w-100 py-3 fw-bold">
                            <i class="far fa-heart"></i> WISHLIST
                        </button>
                    </div>
                    <div class="col-12 col-md-5">
                        <button type="submit" class="btn btn-primary w-100 py-3 fw-bold">
                            THÊM VÀO GIỎ HÀNG
                        </button>
                    </div>
                </div>
            </form>
            <?php else: ?>
            <div class="product-out-of-stock mb-4 p-3 bg-light text-center">
                <p class="mb-2 fw-bold text-danger">SẢN PHẨM TẠM HẾT HÀNG</p>
                <p class="mb-0 small">Vui lòng để lại email để nhận thông báo khi sản phẩm có hàng trở lại</p>
                <form class="mt-3 d-flex gap-2" id="notify-form">
                    <input type="email" class="form-control" name="email" placeholder="Email của bạn" required>
                    <button type="submit" class="btn btn-primary">Thông báo cho tôi</button>
                </form>
            </div>
            <?php endif; ?>
            
            <!-- Product Features -->
            <div class="product-features mb-4">
                <div class="row g-3">
                    <div class="col-6 col-md-3">
                        <div class="feature-item text-center p-3">
                            <i class="fas fa-truck-fast fs-3 mb-2 text-primary"></i>
                            <p class="mb-0 small">FREESHIP ĐƠN > 500K</p>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="feature-item text-center p-3">
                            <i class="fas fa-shield-alt fs-3 mb-2 text-primary"></i>
                            <p class="mb-0 small">BẢO HÀNH CHÍNH HÃNG</p>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="feature-item text-center p-3">
                            <i class="fas fa-undo fs-3 mb-2 text-primary"></i>
                            <p class="mb-0 small">ĐỔI TRẢ 30 NGÀY</p>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="feature-item text-center p-3">
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
                <div class="tab-content p-4 border border-top-0" id="productTabContent">
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
                        <table class="table table-bordered table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Size</th>
                                    <th>Chiều cao (cm)</th>
                                    <th>Cân nặng (kg)</th>
                                    <th>Ngực (cm)</th>
                                    <th>Eo (cm)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>S</td>
                                    <td>155-165</td>
                                    <td>45-55</td>
                                    <td>86-91</td>
                                    <td>71-76</td>
                                </tr>
                                <tr>
                                    <td>M</td>
                                    <td>165-170</td>
                                    <td>55-65</td>
                                    <td>91-97</td>
                                    <td>76-81</td>
                                </tr>
                                <tr>
                                    <td>L</td>
                                    <td>170-175</td>
                                    <td>65-75</td>
                                    <td>97-102</td>
                                    <td>81-86</td>
                                </tr>
                                <tr>
                                    <td>XL</td>
                                    <td>175-180</td>
                                    <td>75-85</td>
                                    <td>102-107</td>
                                    <td>86-91</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Social Sharing -->
            <div class="product-share border-top pt-4">
                <div class="d-flex align-items-center">
                    <span class="fw-bold me-3">CHIA SẺ:</span>
                    <div class="social-icons d-flex gap-2">
                        <a href="#" class="social-icon"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="social-icon"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="social-icon"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="social-icon"><i class="fab fa-pinterest"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Related Products -->
<?php if(!empty($related_products)): ?>
<section class="related-products mt-5">
    <h3 class="mb-4">Sản phẩm liên quan</h3>
    <div class="row">
        <?php foreach($related_products as $related_product): ?>
        <div class="col-6 col-md-3 mb-4">
            <div class="product-card h-100">
                <div class="card border-0 shadow-sm h-100">
                    <div class="position-relative">
                        <a href="index.php?controller=product&action=detail&id=<?php echo htmlspecialchars($related_product['id'] ?? 0); ?>">
                            <?php if(!empty($related_product['image'])): ?>
                            <img src="<?php echo htmlspecialchars($related_product['image']); ?>" class="card-img-top img-fluid rounded" alt="<?php echo htmlspecialchars($related_product['name'] ?? 'Sản phẩm'); ?>">
                            <?php else: ?>
                            <div class="card-img-top bg-light p-4 d-flex align-items-center justify-content-center" style="height: 180px;">
                                <i class="fas fa-tshirt fa-3x text-secondary"></i>
                            </div>
                            <?php endif; ?>
                        </a>
                        <?php if(($related_product['is_sale'] ?? 0) == 1 && !empty($related_product['sale_price']) && $related_product['sale_price'] < $related_product['price']): ?>
                        <span class="badge bg-danger position-absolute top-0 end-0 m-2">Giảm giá</span>
                        <?php endif; ?>
                    </div>
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title">
                            <a href="index.php?controller=product&action=detail&id=<?php echo htmlspecialchars($related_product['id'] ?? 0); ?>" class="text-decoration-none text-dark"><?php echo htmlspecialchars($related_product['name'] ?? 'Sản phẩm'); ?></a>
                        </h5>
                        <div class="price-block mb-3">
                            <?php if(($related_product['is_sale'] ?? 0) == 1 && !empty($related_product['sale_price']) && $related_product['sale_price'] < $related_product['price']): ?>
                            <span class="text-danger fw-bold"><?php echo CURRENCY . number_format($related_product['sale_price']); ?></span>
                            <span class="text-muted text-decoration-line-through ms-2"><?php echo CURRENCY . number_format($related_product['price']); ?></span>
                            <?php else: ?>
                            <span class="fw-bold"><?php echo CURRENCY . number_format($related_product['price'] ?? 0); ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="mt-auto">
                            <button class="btn btn-primary btn-sm w-100 add-to-cart-btn" data-product-id="<?php echo htmlspecialchars($related_product['id'] ?? 0); ?>">
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
<section class="customer-reviews mt-5">
    <h3 class="mb-4">Đánh giá của khách hàng</h3>
    <div class="alert alert-info">
        <p class="mb-0">Sản phẩm này chưa có đánh giá nào. Hãy là người đầu tiên đánh giá!</p>
    </div>
</section>

<style>
.thumbnail-item {
    transition: all 0.3s ease;
    cursor: pointer;
}
.thumbnail-item:hover {
    border-color: #0d6efd !important;
    transform: scale(1.05);
}
.thumbnail-item.active {
    border-color: #0d6efd !important;
    border-width: 2px;
}
.product-image-container img, .product-placeholder {
    transition: opacity 0.3s ease;
}
.product-image-container img:hover {
    opacity: 0.9;
}
.quantity-selector input {
    max-width: 80px;
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
                    .filter(v => v.size === selectedSize && v.stock > 0)
                    .map(v => v.color)
                    .filter(color => color); // Loại bỏ color rỗng
                const uniqueColors = [...new Set(availableColors)];
                
                uniqueColors.forEach(color => {
                    const option = document.createElement('option');
                    option.value = color;
                    option.textContent = color;
                    colorSelect.appendChild(option);
                });
                
                colorSelect.disabled = uniqueColors.length === 0;
                if (uniqueColors.length > 0) {
                    colorSelect.disabled = false;
                    colorSelect.focus();
                }
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
            quantityInput.max = 1;
            quantityInput.value = 1;
        }
        console.log('Cập nhật quantity:', { value: quantityInput.value, max: quantityInput.max });
    }
    
    // Prevent invalid quantity input
    if (quantityInput) {
        quantityInput.addEventListener('input', function() {
            const maxQuantity = parseInt(this.max) || totalStock;
            const minQuantity = parseInt(this.min) || 1;
            let value = parseInt(this.value);
            
            console.log('Quantity input changed:', { value, maxQuantity, minQuantity });
            if (isNaN(value) || value < minQuantity) {
                this.value = minQuantity;
            } else if (value > maxQuantity) {
                this.value = maxQuantity;
            }
            console.log('Quantity validated:', this.value);
        });
    } else {
        console.error('Không tìm thấy quantityInput element');
    }
    
    // Form submission
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const productId = this.querySelector('[name="product_id"]').value;
            const variantId = variants.length > 0 ? (this.querySelector('[name="variant_id"]')?.value || '') : '';
            const quantity = parseInt(this.querySelector('[name="quantity"]').value);
            
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
            
            // AJAX request to add to cart
            fetch('index.php?controller=cart&action=add', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: `product_id=${encodeURIComponent(productId)}&variant_id=${encodeURIComponent(variantId)}&quantity=${encodeURIComponent(quantity)}`
            })
            .then(response => {
                console.log('AJAX response received');
                return response.json();
            })
            .then(data => {
                console.log('AJAX data:', data);
                if (data.success) {
                    alert(data.message || 'Đã thêm vào giỏ hàng!');
                    const cartBadge = document.querySelector('.fa-shopping-cart')?.nextElementSibling;
                    if (cartBadge) {
                        cartBadge.textContent = data.cart_count || 0;
                    }
                } else {
                    console.error('Lỗi từ server:', data.message);
                    alert(data.message || 'Không thể thêm vào giỏ hàng. Vui lòng thử lại.');
                }
            })
            .catch(error => {
                console.error('Lỗi AJAX:', error);
                alert('Đã xảy ra lỗi khi thêm vào giỏ hàng. Vui lòng thử lại.');
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