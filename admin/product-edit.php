<?php
// Product add/edit page
if (!defined('ADMIN_INCLUDED')) {
    define('ADMIN_INCLUDED', true);
}

// Define debug mode
define('DEBUG_MODE', true);

// Initialize debug log
$debug_logs = [];

// Kết nối cơ sở dữ liệu đảm bảo hỗ trợ UTF-8
try {
    $conn->exec("SET NAMES utf8mb4");
    $debug_logs[] = "Database connection set to utf8mb4";
} catch (PDOException $e) {
    $debug_logs[] = "Database charset error: " . $e->getMessage();
    error_log("Database charset error: " . $e->getMessage());
}

// Load required models
require_once '../models/Product.php';
require_once '../models/Category.php';

// Initialize objects
try {
    $product = new Product($conn);
    $category = new Category($conn);
    $debug_logs[] = "Product and Category models initialized";
} catch (Exception $e) {
    $debug_logs[] = "Model initialization error: " . $e->getMessage();
    error_log("Model initialization error: " . $e->getMessage());
}

// Get categories for dropdown
$categories = [];
try {
    $category_stmt = $category->read();
    while ($row = $category_stmt->fetch(PDO::FETCH_ASSOC)) {
        $categories[] = $row;
    }
    $debug_logs[] = "Fetched " . count($categories) . " categories";
} catch (PDOException $e) {
    $debug_logs[] = "Error fetching categories: " . $e->getMessage();
    error_log("Error fetching categories: " . $e->getMessage());
}

// Check if it's an edit or add operation
$is_edit = false;
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($product_id > 0) {
    $is_edit = true;
    $product->id = $product_id;

    // Get product data
    try {
        if (!$product->readOne()) {
            $debug_logs[] = "Product ID $product_id not found";
            header("Location: index.php?page=products");
            exit;
        }
        $debug_logs[] = "Product ID $product_id loaded: " . json_encode([
            'name' => $product->name,
            'category_id' => $product->category_id,
            'price' => $product->price
        ]);
    } catch (Exception $e) {
        $debug_logs[] = "Error reading product ID $product_id: " . $e->getMessage();
        error_log("Error reading product ID $product_id: " . $e->getMessage());
        header("Location: index.php?page=products");
        exit;
    }

    // Get variants
    try {
        $variants = $product->getVariants();
        $debug_logs[] = "Fetched " . count($variants) . " variants for product ID $product_id";
    } catch (Exception $e) {
        $debug_logs[] = "Error fetching variants: " . $e->getMessage();
        error_log("Error fetching variants: " . $e->getMessage());
        $variants = [];
    }
} else {
    $variants = [];
    $debug_logs[] = "New product mode, no variants loaded";
}

// Process form submission
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $debug_logs[] = "Processing form submission: " . json_encode($_POST);

    // Get form data
    $product->name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $product->description = isset($_POST['description']) ? trim($_POST['description']) : '';
    $product->price = isset($_POST['price']) ? floatval($_POST['price']) : 0;
    $product->sale_price = isset($_POST['sale_price']) ? floatval($_POST['sale_price']) : 0;
    $product->category_id = isset($_POST['category_id']) ? intval($_POST['category_id']) : 0;
    $product->is_featured = isset($_POST['is_featured']) ? 1 : 0;
    $product->is_sale = isset($_POST['is_sale']) ? 1 : 0;
    $product->image = isset($_POST['image']) ? trim($_POST['image']) : '';

    // Get variants data
    $variants_data = [];
    if (isset($_POST['variants']) && is_array($_POST['variants'])) {
        foreach ($_POST['variants'] as $index => $variant) {
            $variants_data[] = [
                'color' => trim($variant['color'] ?? ''),
                'size' => trim($variant['size'] ?? ''),
                'price' => floatval($variant['price'] ?? 0),
                'stock' => max(0, intval($variant['stock'] ?? 0)),
                'image' => trim($variant['image'] ?? '')
            ];
        }
        $debug_logs[] = "Received " . count($variants_data) . " variants from form: " . json_encode($variants_data);
    } else {
        $debug_logs[] = "No variants submitted in form";
    }

    // Validate form data
    if (empty($product->name)) {
        $error_message = "Vui lòng nhập tên sản phẩm.";
        $debug_logs[] = "Validation error: Product name is empty";
    } elseif ($product->price <= 0) {
        $error_message = "Giá sản phẩm phải lớn hơn 0.";
        $debug_logs[] = "Validation error: Price <= 0";
    } elseif ($product->category_id <= 0) {
        $error_message = "Vui lòng chọn danh mục.";
        $debug_logs[] = "Validation error: Invalid category_id";
    } elseif (empty($variants_data)) {
        $error_message = "Vui lòng thêm ít nhất một biến thể.";
        $debug_logs[] = "Validation error: No variants provided";
    } else {
        // Save product
        try {
            if ($is_edit) {
                if ($product->update()) {
                    if (method_exists($product, 'saveVariants')) {
                        $product->saveVariants($variants_data);
                        $debug_logs[] = "Variants saved for product ID $product_id";
                    } else {
                        $debug_logs[] = "Error: saveVariants() method not defined in Product class";
                        $error_message = "Không thể lưu biến thể: Hàm saveVariants() không tồn tại.";
                    }
                    $success_message = "Cập nhật sản phẩm thành công.";
                    $debug_logs[] = "Product ID $product_id updated successfully";
                } else {
                    $error_message = "Cập nhật sản phẩm thất bại.";
                    $debug_logs[] = "Failed to update product ID $product_id";
                }
            } else {
                if ($product_id = $product->create()) {
                    $product->id = $product_id;
                    if (method_exists($product, 'saveVariants')) {
                        $product->saveVariants($variants_data);
                        $debug_logs[] = "Variants saved for new product ID $product_id";
                    } else {
                        $debug_logs[] = "Error: saveVariants() method not defined in Product class";
                        $error_message = "Không thể lưu biến thể: Hàm saveVariants() không tồn tại.";
                    }
                    $debug_logs[] = "Created new product ID $product_id";
                    header("Location: index.php?page=product-edit&id=" . $product_id . "&success=1");
                    exit;
                } else {
                    $error_message = "Thêm sản phẩm thất bại.";
                    $debug_logs[] = "Failed to create new product";
                }
            }
        } catch (Exception $e) {
            $error_message = "Lỗi khi lưu sản phẩm: " . $e->getMessage();
            $debug_logs[] = "Save product error: " . $e->getMessage();
            error_log("Save product error: " . $e->getMessage());
        }
    }
}

// Show success if redirected from create
if (isset($_GET['success']) && $_GET['success'] == 1) {
    $success_message = "Lưu sản phẩm thành công.";
    $debug_logs[] = "Success message triggered from redirect";
}

// Define currency if not defined
if (!defined('CURRENCY')) {
    define('CURRENCY', 'đ');
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $is_edit ? 'Chỉnh sửa' : 'Thêm mới'; ?> sản phẩm</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        .sidebar {
            min-height: 100vh;
            position: sticky;
            top: 0;
        }
        .card {
            transition: transform 0.3s;
        }
        .card:hover {
            transform: translateY(-5px);
        }
        .img-preview {
            max-height: 200px;
            object-fit: contain;
        }
        .variant-img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 0.25rem;
            border: 1px solid #dee2e6;
        }
        .variant-img-placeholder {
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #f8f9fa;
            border-radius: 0.25rem;
            border: 1px solid #dee2e6;
        }
        .table-variants th, .table-variants td {
            vertical-align: middle;
        }
        .debug-info {
            background-color: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
            margin-top: 10px;
            font-size: 0.9em;
            display: <?php echo DEBUG_MODE ? 'block' : 'none'; ?>;
        }
    </style>
</head>
<body>
<div class="d-flex">
    <div class="bg-dark sidebar p-3 text-white" style="width: 250px;">
        <h4 class="text-center mb-4">Bảng Quản Trị</h4>
        <ul class="nav flex-column">
            <li class="nav-item"><a class="nav-link text-white" href="index.php?page=dashboard"><i class="fas fa-home me-2"></i> Trang chủ</a></li>
            <li class="nav-item"><a class="nav-link text-white" href="index.php?page=orders"><i class="fas fa-shopping-cart me-2"></i> Đơn hàng</a></li>
            <li class="nav-item"><a class="nav-link text-white active" href="index.php?page=products"><i class="fas fa-box me-2"></i> Sản phẩm</a></li>
            <li class="nav-item"><a class="nav-link text-white" href="index.php?page=users"><i class="fas fa-users me-2"></i> Người dùng</a></li>
        </ul>
    </div>

    <div class="flex-grow-1 p-4">
        <div class="container-fluid">
            <h1 class="mt-4 mb-3"><?php echo $is_edit ? 'Chỉnh sửa' : 'Thêm mới'; ?> sản phẩm</h1>

            <?php if (!empty($success_message)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo $success_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Đóng"></button>
            </div>
            <?php endif; ?>

            <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo $error_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Đóng"></button>
            </div>
            <?php endif; ?>

            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="fw-bold text-primary m-0">
                        <i class="fas fa-edit me-2"></i>
                        <?php echo $is_edit ? 'Sửa sản phẩm: ' . htmlspecialchars($product->name) : 'Thêm sản phẩm mới'; ?>
                    </h5>
                </div>
                <div class="card-body">
                    <form action="index.php?page=product-edit<?php echo $is_edit ? '&id=' . $product_id : ''; ?>" method="POST">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Tên sản phẩm <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="name" value="<?php echo htmlspecialchars($product->name); ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Danh mục <span class="text-danger">*</span></label>
                                    <select class="form-select" name="category_id" required>
                                        <option value="">-- Chọn danh mục --</option>
                                        <?php foreach ($categories as $cat): ?>
                                        <option value="<?php echo $cat['id']; ?>" <?php echo $product->category_id == $cat['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($cat['name']); ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Giá <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text"><?php echo CURRENCY; ?></span>
                                            <input type="number" class="form-control" name="price" step="0.01" min="0" value="<?php echo $product->price; ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Giá khuyến mãi</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><?php echo CURRENCY; ?></span>
                                            <input type="number" class="form-control" name="sale_price" step="0.01" min="0" value="<?php echo $product->sale_price; ?>">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Link hình ảnh</label>
                                    <input type="text" class="form-control" name="image" value="<?php echo htmlspecialchars($product->image); ?>">
                                </div>
                                <?php if ($product->image): ?>
                                <div class="mb-3">
                                    <label class="form-label">Hình hiện tại</label>
                                    <div class="border p-2 text-center">
                                        <img src="<?php echo htmlspecialchars($product->image); ?>" class="img-fluid img-preview" alt="Preview">
                                    </div>
                                </div>
                                <?php endif; ?>
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" name="is_featured" <?php echo $product->is_featured ? 'checked' : ''; ?>>
                                    <label class="form-check-label">Sản phẩm nổi bật</label>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="is_sale" <?php echo $product->is_sale ? 'checked' : ''; ?>>
                                    <label class="form-check-label">Đang giảm giá</label>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Mô tả sản phẩm</label>
                            <textarea class="form-control" name="description" rows="5"><?php echo htmlspecialchars($product->description); ?></textarea>
                        </div>

                        <!-- Variants Section -->
                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <label class="form-label fw-bold">Biến thể sản phẩm <span class="text-danger">*</span></label>
                                <button type="button" class="btn btn-success btn-sm" onclick="addVariantRow()">
                                    <i class="fas fa-plus me-1"></i> Thêm biến thể
                                </button>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-bordered table-variants">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Màu sắc</th>
                                            <th>Kích thước</th>
                                            <th>Giá</th>
                                            <th>Số lượng <span class="text-danger">*</span></th>
                                            <th>Hình ảnh</th>
                                            <th>Hành động</th>
                                        </tr>
                                    </thead>
                                    <tbody id="variants-table-body">
                                        <?php foreach ($variants as $index => $variant): ?>
                                        <tr class="variant-row">
                                            <td>
                                                <input type="text" class="form-control" name="variants[<?php echo $index; ?>][color]" value="<?php echo htmlspecialchars($variant['color']); ?>">
                                            </td>
                                            <td>
                                                <input type="text" class="form-control" name="variants[<?php echo $index; ?>][size]" value="<?php echo htmlspecialchars($variant['size']); ?>">
                                            </td>
                                            <td>
                                                <div class="input-group">
                                                    <span class="input-group-text"><?php echo CURRENCY; ?></span>
                                                    <input type="number" class="form-control" name="variants[<?php echo $index; ?>][price]" step="0.01" min="0" value="<?php echo $variant['price']; ?>">
                                                </div>
                                            </td>
                                            <td>
                                                <input type="number" class="form-control" name="variants[<?php echo $index; ?>][stock]" min="0" value="<?php echo $variant['stock']; ?>" required>
                                            </td>
                                            <td>
                                                <input type="text" class="form-control mb-2" name="variants[<?php echo $index; ?>][image]" value="<?php echo htmlspecialchars($variant['image']); ?>">
                                                <?php if ($variant['image']): ?>
                                                <img src="<?php echo htmlspecialchars($variant['image']); ?>" class="variant-img img-fluid" alt="Variant Image">
                                                <?php else: ?>
                                                <div class="variant-img-placeholder">
                                                    <i class="fas fa-tshirt fa-2x text-secondary"></i>
                                                </div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-danger btn-sm" onclick="removeVariantRow(this)">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="text-center">
                            <button class="btn btn-primary"><i class="fas fa-save me-1"></i> <?php echo $is_edit ? 'Cập nhật' : 'Lưu'; ?></button>
                            <a href="index.php?page=products" class="btn btn-secondary ms-2"><i class="fas fa-times me-1"></i> Hủy</a>
                        </div>
                    </form>

                    <!-- Debug Info -->
                    <?php if (DEBUG_MODE): ?>
                    <div class="debug-info mt-4">
                        <h6>Debug Information</h6>
                        <ul>
                            <?php foreach ($debug_logs as $log): ?>
                            <li><?php echo htmlspecialchars($log); ?></li>
                            <?php endforeach; ?>
                            <li>Categories: <?php echo htmlspecialchars(json_encode($categories)); ?></li>
                            <li>Variants: <?php echo htmlspecialchars(json_encode($variants)); ?></li>
                            <li>Product Data: <?php echo htmlspecialchars(json_encode([
                                'id' => $product->id,
                                'name' => $product->name,
                                'category_id' => $product->category_id,
                                'price' => $product->price,
                                'image' => $product->image
                            ])); ?></li>
                        </ul>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
<script>
    console.log("Product edit script started at <?php echo date('Y-m-d H:i:s'); ?>");

    let variantIndex = <?php echo count($variants); ?>;

    function addVariantRow() {
        console.log("Adding new variant row, index: " + variantIndex);
        const tbody = document.getElementById('variants-table-body');
        const row = document.createElement('tr');
        row.className = 'variant-row';
        row.innerHTML = `
            <td>
                <input type="text" class="form-control" name="variants[${variantIndex}][color]">
            </td>
            <td>
                <input type="text" class="form-control" name="variants[${variantIndex}][size]">
            </td>
            <td>
                <div class="input-group">
                    <span class="input-group-text"><?php echo CURRENCY; ?></span>
                    <input type="number" class="form-control" name="variants[${variantIndex}][price]" step="0.01" min="0">
                </div>
            </td>
            <td>
                <input type="number" class="form-control" name="variants[${variantIndex}][stock]" min="0" required>
            </td>
            <td>
                <input type="text" class="form-control mb-2" name="variants[${variantIndex}][image]">
                <div class="variant-img-placeholder">
                    <i class="fas fa-tshirt fa-2x text-secondary"></i>
                </div>
            </td>
            <td>
                <button type="button" class="btn btn-danger btn-sm" onclick="removeVariantRow(this)">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        `;
        tbody.appendChild(row);
        variantIndex++;
        console.log("Variant row added, new index: " + variantIndex);
    }

    function removeVariantRow(button) {
        console.log("Removing variant row");
        button.closest('tr').remove();
        console.log("Variant row removed");
    }

    document.addEventListener("DOMContentLoaded", function() {
        console.log("DOM fully loaded. Checking Bootstrap components...");

        // Debug Bootstrap components
        const cards = document.querySelectorAll('.card');
        console.log(`Found ${cards.length} card elements`);
        if (cards.length === 0) {
            console.error("No Bootstrap cards found. Check Bootstrap CSS inclusion.");
        }

        const variantRows = document.querySelectorAll('.variant-row');
        console.log(`Found ${variantRows.length} variant rows`);

        const images = document.querySelectorAll('.variant-img, .img-preview');
        console.log(`Found ${images.length} images`);
        images.forEach((img, index) => {
            if (!img.complete || img.naturalWidth === 0) {
                console.warn(`Image ${index + 1} failed to load: ${img.src}`);
            }
        });

        const placeholders = document.querySelectorAll('.variant-img-placeholder');
        console.log(`Found ${placeholders.length} image placeholders`);
    });
</script>
</body>
</html>