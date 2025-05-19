<?php
ob_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Product add/edit page
if (!defined('ADMIN_INCLUDED')) {
    define('ADMIN_INCLUDED', true);
}

// Include database connection
require_once '../config/database.php';
try {
    $db = new Database();
    $conn = $db->getConnection();
    $conn->exec("SET NAMES utf8mb4");
} catch (PDOException $e) {
    error_log("Database connection error: " . $e->getMessage());
    die("Lỗi kết nối cơ sở dữ liệu. Vui lòng kiểm tra logs.");
}

// Load required models
require_once '../models/Product.php';
require_once '../models/Category.php';

// Initialize objects
try {
    $product = new Product($conn);
    $category = new Category($conn);
} catch (Exception $e) {
    error_log("Model initialization error: " . $e->getMessage());
    die("Lỗi khởi tạo model. Vui lòng kiểm tra logs.");
}

// Get categories for dropdown
$categories = [];
try {
    $category_stmt = $category->read();
    while ($row = $category_stmt->fetch(PDO::FETCH_ASSOC)) {
        $categories[] = $row;
    }
} catch (PDOException $e) {
    error_log("Error fetching categories: " . $e->getMessage());
    $categories = [];
}

// Check if it's an edit or add operation
$is_edit = false;
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($product_id > 0) {
    $is_edit = true;
    $product->id = $product_id;

    try {
        if (!$product->readOne()) {
            header("Location: index.php?page=products");
            exit;
        }
    } catch (Exception $e) {
        error_log("Error reading product ID $product_id: " . $e->getMessage());
        header("Location: index.php?page=products");
        exit;
    }

    try {
        $variants = $product->getVariants();
    } catch (Exception $e) {
        error_log("Error fetching variants: " . $e->getMessage());
        $variants = [];
    }
} else {
    $variants = [];
    $product->images = [];
}

// Process form submission
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $product->name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $product->description = isset($_POST['description']) ? trim($_POST['description']) : '';
    $product->price = isset($_POST['price']) ? floatval($_POST['price']) : 0;
    $product->sale_price = isset($_POST['sale_price']) ? floatval($_POST['sale_price']) : 0;
    $product->category_id = isset($_POST['category_id']) ? intval($_POST['category_id']) : 0;
    $product->is_featured = isset($_POST['is_featured']) ? 1 : 0;
    $product->is_sale = isset($_POST['is_sale']) ? 1 : 0;

    $variants_data = [];
    if (isset($_POST['variants']) && is_array($_POST['variants'])) {
        foreach ($_POST['variants'] as $index => $variant) {
            $variants_data[] = [
                'color' => trim($variant['color'] ?? ''),
                'size' => trim($variant['size'] ?? ''),
                'price' => floatval($variant['price'] ?? 0),
                'stock' => max(0, intval($variant['stock'] ?? 0))
            ];
        }
    }

    $upload_dir = '../Uploads/images/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    if (isset($_FILES['main_image']) && $_FILES['main_image']['error'] == UPLOAD_ERR_OK) {
        $main_image_path = $upload_dir . time() . '_' . basename($_FILES['main_image']['name']);
        if (move_uploaded_file($_FILES['main_image']['tmp_name'], $main_image_path)) {
            $product->image = $main_image_path;
        } else {
            $error_message = "Lỗi khi upload ảnh chính.";
        }
    } elseif (!$is_edit) {
        $error_message = "Vui lòng chọn ảnh chính.";
    } elseif ($is_edit && empty($product->image)) {
        $query = "SELECT image FROM products WHERE id = :id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':id', $product_id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $product->image = $row['image'] ?? '';
    }

    $new_images = [];
    $has_new_images = false;
    $image_count = 0;

    if (isset($_FILES['additional_images']) && is_array($_FILES['additional_images']['name'])) {
        foreach ($_FILES['additional_images']['name'] as $key => $name) {
            if ($image_count >= 3) {
                $error_message = "Chỉ được phép upload tối đa 3 ảnh bổ sung.";
                break;
            }
            if ($_FILES['additional_images']['error'][$key] == UPLOAD_ERR_OK && !empty($name)) {
                $tmp_name = $_FILES['additional_images']['tmp_name'][$key];
                $image_path = $upload_dir . time() . '_' . basename($name);
                if (move_uploaded_file($tmp_name, $image_path)) {
                    $new_images[] = $image_path;
                    $image_count++;
                    $has_new_images = true;
                } else {
                    $error_message = "Lỗi khi upload ảnh bổ sung: " . htmlspecialchars($name);
                }
            }
        }
    }

    $delete_image_ids = isset($_POST['delete_image_ids']) && is_array($_POST['delete_image_ids']) ? $_POST['delete_image_ids'] : [];
    if ($is_edit && !empty($delete_image_ids)) {
        try {
            foreach ($delete_image_ids as $image_id) {
                $product->deleteImage($image_id);
            }
        } catch (Exception $e) {
            $error_message = "Lỗi khi xóa ảnh bổ sung: " . $e->getMessage();
            error_log("Delete image error: " . $e->getMessage() . "\nStack trace: " . $e->getTraceAsString());
        }
    }

    // Fix: Ensure $product->images contains only image paths (strings)
    if ($has_new_images) {
        $product->images = $new_images;
    } elseif ($is_edit) {
        $current_images = [];
        foreach ($product->images as $image) {
            // Ensure we only add the 'image' field (path) if it exists and not marked for deletion
            if (isset($image['image']) && !in_array($image['id'], $delete_image_ids)) {
                $current_images[] = $image['image'];
            }
        }
        $product->images = $current_images;
    } else {
        $product->images = [];
    }

    if (empty($product->name)) {
        $error_message = "Vui lòng nhập tên sản phẩm.";
    } elseif ($product->price <= 0) {
        $error_message = "Giá sản phẩm phải lớn hơn 0.";
    } elseif ($product->category_id <= 0) {
        $error_message = "Vui lòng chọn danh mục.";
    } elseif (empty($variants_data)) {
        $error_message = "Vui lòng thêm ít nhất một biến thể.";
    } elseif (empty($product->image) && !$is_edit) {
        $error_message = "Vui lòng chọn ảnh chính.";
    } else {
        try {
            if ($is_edit) {
                if ($product->update()) {
                    if (method_exists($product, 'saveVariants')) {
                        $product->saveVariants($variants_data);
                    } else {
                        $error_message = "Không thể lưu biến thể: Hàm saveVariants() không tồn tại.";
                    }
                    $success_message = "Cập nhật sản phẩm thành công.";
                    header("Location: index.php?page=products");
                    exit;
                } else {
                    $error_message = "Cập nhật sản phẩm thất bại.";
                }
            } else {
                if ($product_id = $product->create()) {
                    $product->id = $product_id;
                    if (method_exists($product, 'saveVariants')) {
                        $product->saveVariants($variants_data);
                    } else {
                        $error_message = "Không thể lưu biến thể: Hàm saveVariants() không tồn tại.";
                    }
                    header("Location: index.php?page=product-edit&id=" . $product_id . "&success=1");
                    exit;
                } else {
                    $error_message = "Thêm sản phẩm thất bại.";
                }
            }
        } catch (Exception $e) {
            $error_message = "Lỗi khi lưu sản phẩm: " . $e->getMessage();
            error_log("Save product error: " . $e->getMessage() . "\nStack trace: " . $e->getTraceAsString());
        }
    }
}

if (isset($_GET['success']) && $_GET['success'] == 1) {
    $success_message = "Lưu sản phẩm thành công.";
}

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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        .sidebar { min-height: 100vh; position: sticky; top: 0; }
        .card { transition: transform 0.3s; }
        .card:hover { transform: translateY(-5px); }
        .img-preview { max-height: 200px; object-fit: contain; }
        .table-variants th, .table-variants td { vertical-align: middle; }
        .additional-image-container {
            position: relative;
            border: 1px solid #ddd;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
            text-align: center;
            min-height: 150px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
        .additional-image-container .delete-btn {
            position: absolute;
            top: 5px;
            right: 5px;
            background: rgba(255, 0, 0, 0.7);
            color: white;
            border: none;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .additional-image-preview {
            max-width: 100px;
            max-height: 100px;
            margin-top: 10px;
            object-fit: cover;
        }
        #main-image-preview {
            min-height: 100px;
            text-align: center;
        }
        .preview-image {
            max-width: 100px;
            margin: 5px;
            object-fit: cover;
        }
    </style>
</head>
<body>
<div class="d-flex">
    <?php include 'sidebar.php'; ?>
    
    <div class="flex-grow-1 p-4">
        <div class="container-fluid">
            <h1 class="mt-4 mb-3"><?php echo $is_edit ? 'Chỉnh sửa' : 'Thêm mới'; ?> sản phẩm</h1>

            <?php if (!empty($success_message)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo $success_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo $error_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
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
                    <form action="index.php?page=product-edit<?php echo $is_edit ? '&id=' . $product_id : ''; ?>" method="POST" enctype="multipart/form-data">
                        <div class="text-center mb-4">
                            <button class="btn btn-primary"><i class="fas fa-save me-1"></i> <?php echo $is_edit ? 'Cập nhật' : 'Lưu'; ?></button>
                            <a href="index.php?page=products" class="btn btn-secondary ms-2"><i class="fas fa-arrow-left me-1"></i> Back</a>
                            <a href="index.php?page=products" class="btn btn-secondary ms-2"><i class="fas fa-times me-1"></i> Hủy</a>
                        </div>

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
                                    <label class="form-label">Ảnh chính <span class="text-danger"><?php echo $is_edit ? '' : '*'; ?></span></label>
                                    <input type="file" class="form-control" id="main-image-input" name="main_image" accept="image/*" <?php echo $is_edit ? '' : 'required'; ?>>
                                    <div id="main-image-preview" class="border p-2 mt-2 text-center"></div>
                                </div>
                                <?php if ($product->image): ?>
                                <div class="mb-3">
                                    <label class="form-label">Ảnh chính hiện tại</label>
                                    <div class="border p-2 text-center">
                                        <img src="<?php echo htmlspecialchars($product->image); ?>" class="img-fluid img-preview" alt="Main Image">
                                    </div>
                                </div>
                                <?php endif; ?>
                                <div class="mb-3">
                                    <label class="form-label">Ảnh bổ sung (tối đa 3 ảnh)</label>
                                    <div class="row">
                                        <?php for ($i = 0; $i < 3; $i++): ?>
                                        <div class="col-md-4">
                                            <div class="additional-image-container" id="additional-image-container-<?php echo $i; ?>">
                                                <input type="file" class="form-control mb-2" name="additional_images[]" accept="image/*" onchange="previewAdditionalImage(this, 'additional-image-preview-<?php echo $i; ?>')">
                                                <div id="additional-image-preview-<?php echo $i; ?>" class="additional-image-preview">
                                                    <?php if ($is_edit && isset($product->images[$i])): ?>
                                                    <img src="<?php echo htmlspecialchars($product->images[$i]['image']); ?>" class="img-fluid additional-image-preview" alt="Additional Image">
                                                    <button type="button" class="delete-btn" onclick="removeAdditionalImage(<?php echo htmlspecialchars($product->images[$i]['id']); ?>, 'additional-image-preview-<?php echo $i; ?>')">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endfor; ?>
                                    </div>
                                </div>
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

                        <div id="delete-image-ids"></div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    let variantIndex = <?php echo count($variants); ?>;

    function addVariantRow() {
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
                <button type="button" class="btn btn-danger btn-sm" onclick="removeVariantRow(this)">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        `;
        tbody.appendChild(row);
        variantIndex++;
    }

    function removeVariantRow(button) {
        button.closest('tr').remove();
    }

    document.getElementById('main-image-input').addEventListener('change', function(event) {
        const file = event.target.files[0];
        const preview = document.getElementById('main-image-preview');
        preview.innerHTML = '';

        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const img = document.createElement('img');
                img.src = e.target.result;
                img.className = 'img-fluid img-preview';
                img.alt = 'Main Image Preview';
                preview.appendChild(img);
            };
            reader.readAsDataURL(file);
        }
    });

    function previewAdditionalImage(input, previewId) {
        const file = input.files[0];
        const preview = document.getElementById(previewId);
        preview.innerHTML = '';

        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const img = document.createElement('img');
                img.src = e.target.result;
                img.className = 'img-fluid additional-image-preview';
                img.alt = 'Additional Image Preview';
                preview.appendChild(img);
            };
            reader.readAsDataURL(file);
        }
    }

    function removeAdditionalImage(imageId, previewId) {
        const preview = document.getElementById(previewId);
        preview.innerHTML = ''; // Clear the preview

        const deleteIdsContainer = document.getElementById('delete-image-ids');
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'delete_image_ids[]';
        input.value = imageId;
        deleteIdsContainer.appendChild(input);
    }
</script>
<?php ob_end_flush(); ?>
</body>
</html>