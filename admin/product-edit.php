<?php
// Check direct script access
if (!defined('ADMIN_INCLUDED')) {
    define('ADMIN_INCLUDED', true);
}

// Kiểm tra product
$log_file = '../logs/debug.log';
if (!isset($product) || !is_object($product)) {
    file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] Lỗi: Biến \$product không tồn tại hoặc không hợp lệ trong product-edit.php\n", FILE_APPEND);
    echo "<pre>DEBUG ERROR: Biến \$product không tồn tại hoặc không hợp lệ trong product-edit.php\n</pre>";
    $_SESSION['error_message'] = "Không tìm thấy sản phẩm để chỉnh sửa.";
    header("Location: index.php?page=products");
    exit;
}
file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] product-edit.php - Product ID: {$product->id}, Name: {$product->name}\n", FILE_APPEND);
echo "<pre>DEBUG: product-edit.php - Product ID: {$product->id}, Name: {$product->name}\n</pre>";

// Xử lý form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product->name = $_POST['name'] ?? '';
    $product->price = $_POST['price'] ?? 0;
    $product->sale_price = $_POST['sale_price'] ?? 0;
    $product->category_id = $_POST['category_id'] ?? 0;

    if ($product->id > 0) {
        if ($product->update()) {
            $_SESSION['success_message'] = "Product updated successfully.";
        } else {
            $_SESSION['error_message'] = "Failed to update product.";
        }
    } else {
        if ($product->create()) {
            $_SESSION['success_message'] = "Product created successfully.";
            header("Location: index.php?page=product-edit&id=" . $product->id);
            exit;
        } else {
            $_SESSION['error_message'] = "Failed to create product.";
        }
    }
    header("Location: index.php?page=products");
    exit;
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $product->id > 0 ? 'Chỉnh sửa sản phẩm' : 'Thêm sản phẩm mới'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body>
    <div class="d-flex">
        <!-- Sidebar (đã include trong index.php) -->
        <!-- Main Content -->
        <div class="flex-grow-1 p-4">
            <div class="container-fluid">
                <h1 class="mt-4 mb-3"><?php echo $product->id > 0 ? 'Chỉnh sửa sản phẩm' : 'Thêm sản phẩm mới'; ?></h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="index.php?page=products">Products</a></li>
                        <li class="breadcrumb-item active" aria-current="page"><?php echo $product->id > 0 ? 'Edit' : 'Add'; ?></li>
                    </ol>
                </nav>

                <?php if (isset($_SESSION['success_message'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php echo htmlspecialchars($_SESSION['success_message']); unset($_SESSION['success_message']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['error_message'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo htmlspecialchars($_SESSION['error_message']); unset($_SESSION['error_message']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <form method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="id" value="<?php echo $product->id; ?>">
                            
                            <div class="mb-3">
                                <label for="name" class="form-label">Tên sản phẩm</label>
                                <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($product->name); ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="price" class="form-label">Giá</label>
                                <input type="number" class="form-control" id="price" name="price" value="<?php echo htmlspecialchars($product->price); ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="sale_price" class="form-label">Giá khuyến mãi</label>
                                <input type="number" class="form-control" id="sale_price" name="sale_price" value="<?php echo htmlspecialchars($product->sale_price ?? 0); ?>">
                            </div>
                            
                            <div class="mb-3">
                                <label for="category_id" class="form-label">Danh mục</label>
                                <select class="form-select" id="category_id" name="category_id" required>
                                    <option value="">Chọn danh mục</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?php echo $cat['id']; ?>" <?php echo $product->category_id == $cat['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($cat['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="image" class="form-label">Hình ảnh</label>
                                <input type="file" class="form-control" id="image" name="image">
                                <?php if (!empty($product->image)): ?>
                                    <img src="../<?php echo htmlspecialchars($product->image); ?>" alt="Product Image" class="img-thumbnail mt-2" style="max-width: 200px;">
                                <?php endif; ?>
                            </div>
                            
                            <button type="submit" class="btn btn-primary"><?php echo $product->id > 0 ? 'Cập nhật' : 'Thêm mới'; ?></button>
                            <a href="index.php?page=products" class="btn btn-secondary">Quay lại</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] Hoàn thành render product-edit.php\n", FILE_APPEND);
echo "<pre>DEBUG: Hoàn thành render product-edit.php\n</pre>";
?>