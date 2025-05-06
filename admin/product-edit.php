<?php
// Product add/edit page
// Check direct script access
if (!defined('ADMIN_INCLUDED')) {
    define('ADMIN_INCLUDED', true);
}

// Load required models
require_once '../models/Product.php';
require_once '../models/Category.php';

// Initialize objects
$product = new Product($conn);
$category = new Category($conn);

// Get categories for dropdown
$categories = [];
$category_stmt = $category->read();
while ($row = $category_stmt->fetch(PDO::FETCH_ASSOC)) {
    $categories[] = $row;
}

// Check if it's an edit or add operation
$is_edit = false;
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($product_id > 0) {
    $is_edit = true;
    $product->id = $product_id;
    
    // Get product data
    if (!$product->readOne()) {
        // Product not found, redirect to products page
        header("Location: index.php?page=products");
        exit;
    }
}

// Process form submission
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $product->name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $product->description = isset($_POST['description']) ? trim($_POST['description']) : '';
    $product->price = isset($_POST['price']) ? floatval($_POST['price']) : 0;
    $product->sale_price = isset($_POST['sale_price']) ? floatval($_POST['sale_price']) : 0;
    $product->category_id = isset($_POST['category_id']) ? intval($_POST['category_id']) : 0;
    $product->stock = isset($_POST['stock']) ? intval($_POST['stock']) : 0;
    $product->is_featured = isset($_POST['is_featured']) ? 1 : 0;
    $product->is_sale = isset($_POST['is_sale']) ? 1 : 0;
    
    // Image handling (in a real application, you would upload the file to the server)
    $product->image = isset($_POST['image']) ? trim($_POST['image']) : '';
    
    // Validate form data
    if (empty($product->name)) {
        $error_message = "Product name is required.";
    } elseif ($product->price <= 0) {
        $error_message = "Product price must be greater than zero.";
    } elseif ($product->category_id <= 0) {
        $error_message = "Please select a category.";
    } else {
        // Save product
        if ($is_edit) {
            if ($product->update()) {
                $success_message = "Product updated successfully.";
            } else {
                $error_message = "Failed to update product.";
            }
        } else {
            if ($product_id = $product->create()) {
                $success_message = "Product created successfully.";
                // Redirect to edit page for the newly created product
                header("Location: index.php?page=product-edit&id=" . $product_id . "&success=1");
                exit;
            } else {
                $error_message = "Failed to create product.";
            }
        }
    }
}

// Check for success parameter in URL
if (isset($_GET['success']) && $_GET['success'] == 1) {
    $success_message = "Product saved successfully.";
}
?>

<div class="container-fluid px-4">
    <h1 class="mt-4"><?php echo $is_edit ? 'Edit' : 'Add New'; ?> Product</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="index.php?page=products">Products</a></li>
        <li class="breadcrumb-item active"><?php echo $is_edit ? 'Edit' : 'Add New'; ?> Product</li>
    </ol>
    
    <?php if (!empty($success_message)): ?>
    <div class="alert alert-success">
        <?php echo $success_message; ?>
    </div>
    <?php endif; ?>
    
    <?php if (!empty($error_message)): ?>
    <div class="alert alert-danger">
        <?php echo $error_message; ?>
    </div>
    <?php endif; ?>
    
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-edit me-1"></i>
            <?php echo $is_edit ? 'Edit Product: ' . htmlspecialchars($product->name) : 'Add New Product'; ?>
        </div>
        <div class="card-body">
            <form action="index.php?page=product-edit<?php echo $is_edit ? '&id=' . $product_id : ''; ?>" method="POST">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="name" class="form-label">Product Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" value="<?php echo $is_edit ? htmlspecialchars($product->name) : ''; ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="category_id" class="form-label">Category <span class="text-danger">*</span></label>
                            <select class="form-select" id="category_id" name="category_id" required>
                                <option value="">Select Category</option>
                                <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['id']; ?>" <?php echo ($is_edit && $product->category_id == $category['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($category['name']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="price" class="form-label">Regular Price <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><?php echo CURRENCY; ?></span>
                                    <input type="number" class="form-control" id="price" name="price" step="0.01" min="0" value="<?php echo $is_edit ? $product->price : ''; ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="sale_price" class="form-label">Sale Price</label>
                                <div class="input-group">
                                    <span class="input-group-text"><?php echo CURRENCY; ?></span>
                                    <input type="number" class="form-control" id="sale_price" name="sale_price" step="0.01" min="0" value="<?php echo $is_edit ? $product->sale_price : ''; ?>">
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="stock" class="form-label">Stock Quantity <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="stock" name="stock" min="0" value="<?php echo $is_edit ? $product->stock : '0'; ?>" required>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="image" class="form-label">Image URL</label>
                            <input type="text" class="form-control" id="image" name="image" value="<?php echo $is_edit ? htmlspecialchars($product->image) : ''; ?>">
                            <div class="form-text">Enter a URL to the product image. For new images, upload via File Manager and paste the URL here.</div>
                        </div>
                        
                        <div class="mb-3">
                            <?php if ($is_edit && !empty($product->image)): ?>
                            <label class="form-label">Current Image</label>
                            <div class="border p-2 mb-3 text-center">
                                <img src="<?php echo htmlspecialchars($product->image); ?>" alt="<?php echo htmlspecialchars($product->name); ?>" class="img-fluid" style="max-height: 200px;">
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="is_featured" name="is_featured" <?php echo ($is_edit && $product->is_featured == 1) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="is_featured">Featured Product</label>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="is_sale" name="is_sale" <?php echo ($is_edit && $product->is_sale == 1) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="is_sale">On Sale</label>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="6"><?php echo $is_edit ? htmlspecialchars($product->description) : ''; ?></textarea>
                </div>
                
                <div class="text-center">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> <?php echo $is_edit ? 'Update' : 'Save'; ?> Product
                    </button>
                    <a href="index.php?page=products" class="btn btn-secondary ms-2">
                        <i class="fas fa-times me-1"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
