<?php
// Products management page
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

// Process actions
$action = isset($_GET['action']) ? $_GET['action'] : '';
$success_message = '';
$error_message = '';

// Delete product
if ($action == 'delete' && isset($_GET['id'])) {
    $product->id = $_GET['id'];
    if ($product->delete()) {
        $success_message = "Product deleted successfully.";
    } else {
        $error_message = "Failed to delete product.";
    }
}

// Get search and filter parameters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category_id = isset($_GET['category_id']) ? intval($_GET['category_id']) : 0;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
if ($page < 1) $page = 1;
$items_per_page = 10;

// Get products
$products = [];
if (!empty($search)) {
    $stmt = $product->search($search, $page, $items_per_page);
    $total_rows = $product->countSearch($search);
} else if ($category_id > 0) {
    $stmt = $product->readByCategory($category_id, $page, $items_per_page);
    $total_rows = $product->countByCategory($category_id);
} else {
    $stmt = $product->read($items_per_page);
    $total_rows = $product->countAll();
}

// Process products
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $products[] = $row;
}

// Calculate total pages
$total_pages = ceil($total_rows / $items_per_page);

// Get all categories for filter
$categories = [];
$category_stmt = $category->read();
while ($row = $category_stmt->fetch(PDO::FETCH_ASSOC)) {
    $categories[] = $row;
}
?>

<div class="container-fluid px-4">
    <h1 class="mt-4">Product Management</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
        <li class="breadcrumb-item active">Products</li>
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
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <i class="fas fa-table me-1"></i>
                Products List
            </div>
            <a href="index.php?page=product-edit" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Add New Product
            </a>
        </div>
        <div class="card-body">
            <!-- Search and Filter Form -->
            <div class="row mb-3">
                <div class="col-md-6">
                    <form action="index.php" method="GET" class="d-flex">
                        <input type="hidden" name="page" value="products">
                        <input type="text" name="search" class="form-control me-2" placeholder="Search products..." value="<?php echo htmlspecialchars($search); ?>">
                        <button type="submit" class="btn btn-primary">Search</button>
                    </form>
                </div>
                <div class="col-md-6">
                    <form action="index.php" method="GET" class="d-flex justify-content-end">
                        <input type="hidden" name="page" value="products">
                        <select name="category_id" class="form-select me-2" style="max-width: 200px;">
                            <option value="0">All Categories</option>
                            <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>" <?php echo ($category_id == $cat['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" class="btn btn-secondary">Filter</button>
                    </form>
                </div>
            </div>
            
            <!-- Products Table -->
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Image</th>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($products)): ?>
                        <tr>
                            <td colspan="8" class="text-center">No products found</td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($products as $item): ?>
                        <tr>
                            <td><?php echo $item['id']; ?></td>
                            <td>
                                <?php if (!empty($item['image'])): ?>
                                <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="img-thumbnail" style="width: 50px; height: 50px;">
                                <?php else: ?>
                                <div class="bg-light d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                    <i class="fas fa-tshirt text-secondary"></i>
                                </div>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($item['name']); ?></td>
                            <td><?php echo htmlspecialchars($item['category_name'] ?? ''); ?></td>
                            <td>
                                <?php if ($item['is_sale'] == 1 && !empty($item['sale_price']) && $item['sale_price'] < $item['price']): ?>
                                <span class="text-danger"><?php echo CURRENCY . number_format($item['sale_price']); ?></span>
                                <br>
                                <small class="text-muted text-decoration-line-through"><?php echo CURRENCY . number_format($item['price']); ?></small>
                                <?php else: ?>
                                <?php echo CURRENCY . number_format($item['price']); ?>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($item['stock'] <= 5): ?>
                                <span class="text-danger"><?php echo $item['stock']; ?></span>
                                <?php else: ?>
                                <?php echo $item['stock']; ?>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($item['is_featured'] == 1): ?>
                                <span class="badge bg-info text-dark">Featured</span>
                                <?php endif; ?>
                                
                                <?php if ($item['is_sale'] == 1): ?>
                                <span class="badge bg-danger">Sale</span>
                                <?php endif; ?>
                                
                                <?php if ($item['stock'] <= 0): ?>
                                <span class="badge bg-secondary">Out of Stock</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="index.php?page=product-edit&id=<?php echo $item['id']; ?>" class="btn btn-primary btn-sm">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="index.php?page=products&action=delete&id=<?php echo $item['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this product?')">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
            <nav>
                <ul class="pagination justify-content-center">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                        <a class="page-link" href="index.php?page=products&<?php 
                            echo (!empty($search)) ? 'search=' . urlencode($search) . '&' : '';
                            echo ($category_id > 0) ? 'category_id=' . $category_id . '&' : '';
                            echo 'page=' . $i;
                        ?>"><?php echo $i; ?></a>
                    </li>
                    <?php endfor; ?>
                </ul>
            </nav>
            <?php endif; ?>
        </div>
    </div>
</div>
