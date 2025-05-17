<?php
// Products management page
// Check direct script access
if (!defined('ADMIN_INCLUDED')) {
    define('ADMIN_INCLUDED', true);
}

// Include database connection
require_once '../config/database.php';
try {
    $db = new Database();
    $conn = $db->getConnection();
    // Set UTF-8 encoding
    $conn->exec("SET NAMES utf8mb4");
} catch (PDOException $e) {
    error_log("Database connection or charset error: " . $e->getMessage());
    die("Internal Server Error - Check logs for details.");
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
    die("Internal Server Error - Check logs for details.");
}

// Process actions
$action = isset($_GET['action']) ? $_GET['action'] : '';
$success_message = '';
$error_message = '';

// Delete product
if ($action == 'delete' && isset($_GET['id'])) {
    $product->id = $_GET['id'];
    try {
        if ($product->delete()) {
            $success_message = "Product deleted successfully.";
        } else {
            $error_message = "Failed to delete product.";
        }
    } catch (Exception $e) {
        $error_message = "Error deleting product: " . $e->getMessage();
        error_log("Delete product error: " . $e->getMessage());
    }
}

// Get search and filter parameters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category_id = isset($_GET['category_id']) ? intval($_GET['category_id']) : 0;
$page = isset($_GET['pg']) ? intval($_GET['pg']) : 1;
if ($page < 1) $page = 1;
$items_per_page = 10;

// Get products
$products = [];
try {
    if (!empty($search)) {
        $stmt = $product->search($search, $page, $items_per_page);
        $total_rows = $product->countSearch($search);
    } else if ($category_id > 0) {
        $stmt = $product->readByCategory($category_id, $page, $items_per_page);
        $total_rows = $product->countByCategory($category_id);
    } else {
        $stmt = $product->read($items_per_page, $page);
        $total_rows = $product->countAll();
    }

    // Process products and add total stock
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $product->id = $row['id'];
        $row['total_stock'] = $product->getTotalStock(); // Add total stock from variants
        $products[] = $row;
    }
} catch (Exception $e) {
    error_log("Error fetching products: " . $e->getMessage());
    $error_message = "Error loading products.";
    $products = [];
    $total_rows = 0;
}

// Calculate total pages and pagination range
$total_pages = ceil($total_rows / $items_per_page);
$start_item = ($page - 1) * $items_per_page + 1;
$end_item = min($page * $items_per_page, $total_rows);

// Pagination display logic
$max_visible_pages = 5; // Max page numbers to show (excluding First, Last, ellipsis)
$half_visible = floor($max_visible_pages / 2);
$start_page = max(1, $page - $half_visible);
$end_page = min($total_pages, $start_page + $max_visible_pages - 1);
if ($end_page - $start_page < $max_visible_pages - 1) {
    $start_page = max(1, $end_page - $max_visible_pages + 1);
}

// Get all categories for filter
$categories = [];
try {
    $category_stmt = $category->read();
    while ($row = $category_stmt->fetch(PDO::FETCH_ASSOC)) {
        $categories[] = $row;
    }
} catch (Exception $e) {
    error_log("Error fetching categories: " . $e->getMessage());
    $categories = [];
}

// Define currency constant if not defined
if (!defined('CURRENCY')) {
    define('CURRENCY', 'đ');
}

// Debug output (for development)
$debug_info = [
    'total_rows' => $total_rows,
    'total_pages' => $total_pages,
    'current_page' => $page,
    'items_per_page' => $items_per_page,
    'products_count' => count($products)
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Management</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" integrity="sha512-z3gLpd7yknf1YoNbCzqRKc4qyor8gaKU1qmn+CShxbuBusANI9QpRohGBreCFkKxLhei6S9CQXFEbbKuqLg0DA==" crossorigin="anonymous">
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
        .badge {
            padding: 8px 12px;
        }
        .table img {
            object-fit: cover;
        }
        .pagination .page-link {
            color: #007bff;
            transition: background-color 0.2s, color 0.2s;
        }
        .pagination .page-link:hover {
            background-color: #e9ecef;
            color: #0056b3;
        }
        .pagination .page-item.active .page-link {
            background-color: #007bff;
            border-color: #007bff;
            color: white;
        }
        .pagination .page-item.disabled .page-link {
            color: #6c757d;
            pointer-events: none;
            background-color: #f8f9fa;
        }
        .pagination-info {
            font-size: 0.9rem;
            color: #6c757d;
            margin-bottom: 1rem;
        }
        .debug-info {
            display: none; /* Enable in development */
            position: fixed;
            bottom: 10px;
            right: 10px;
            background: rgba(0, 0, 0, 0.8);
            color: white;
            padding: 10px;
            border-radius: 4px;
            font-size: 12px;
            max-width: 300px;
        }
    </style>
</head>
<body>
    <div class="d-flex">
        <!-- Sidebar -->
        <?php include 'sidebar.php'; ?>

        <!-- Main Content -->
        <div class="flex-grow-1 p-4">
            <div class="container-fluid">
                <h1 class="mt-4 mb-3">Product Management</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.php?page=dashboard">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Products</li>
                    </ol>
                </nav>

                <?php if (!empty($success_message)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo $success_message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo $error_message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>
                
                <div class="card shadow-sm mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="m-0 fw-bold text-primary"><i class="fas fa-table me-2"></i> Products List</h6>
                        <a href="index.php?page=product-edit" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus me-1"></i> Add New Product
                        </a>
                    </div>
                    <div class="card-body">
                        <!-- Search and Filter Form -->
                        <div class="row mb-4">
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
                                            <?php if ($item['total_stock'] <= 5): ?>
                                            <span class="text-danger"><?php echo $item['total_stock']; ?></span>
                                            <?php else: ?>
                                            <?php echo $item['total_stock']; ?>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($item['is_featured'] == 1): ?>
                                            <span class="badge bg-info text-dark">Featured</span>
                                            <?php endif; ?>
                                            <?php if ($item['is_sale'] == 1): ?>
                                            <span class="badge bg-danger text-white">Sale</span>
                                            <?php endif; ?>
                                            <?php if ($item['total_stock'] <= 0): ?>
                                            <span class="badge bg-secondary text-white">Out of Stock</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="index.php?page=product-edit&id=<?php echo $item['id']; ?>" class="btn btn-primary btn-sm me-1">
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
                        <?php if ($total_rows > 0): ?>
                        <div class="pagination-info text-center">
                            Showing <?php echo $start_item; ?> to <?php echo $end_item; ?> of <?php echo $total_rows; ?> products
                        </div>
                        <nav aria-label="Page navigation">
                            <ul class="pagination justify-content-center mt-3">
                                <!-- First Page -->
                                <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="index.php?page=products&<?php 
                                        echo (!empty($search)) ? 'search=' . urlencode($search) . '&' : '';
                                        echo ($category_id > 0) ? 'category_id=' . $category_id . '&' : '';
                                        echo 'pg=1';
                                    ?>" aria-label="First">
                                        <i class="fas fa-angle-double-left"></i>
                                    </a>
                                </li>
                                <!-- Previous Page -->
                                <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="index.php?page=products&<?php 
                                        echo (!empty($search)) ? 'search=' . urlencode($search) . '&' : '';
                                        echo ($category_id > 0) ? 'category_id=' . $category_id . '&' : '';
                                        echo 'pg=' . ($page - 1);
                                    ?>" aria-label="Previous">
                                        <i class="fas fa-angle-left"></i>
                                    </a>
                                </li>
                                <!-- Page Numbers -->
                                <?php if ($start_page > 1): ?>
                                <li class="page-item disabled">
                                    <span class="page-link">...</span>
                                </li>
                                <?php endif; ?>
                                <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                                <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                                    <a class="page-link" href="index.php?page=products&<?php 
                                        echo (!empty($search)) ? 'search=' . urlencode($search) . '&' : '';
                                        echo ($category_id > 0) ? 'category_id=' . $category_id . '&' : '';
                                        echo 'pg=' . $i;
                                    ?>"><?php echo $i; ?></a>
                                </li>
                                <?php endfor; ?>
                                <?php if ($end_page < $total_pages): ?>
                                <li class="page-item disabled">
                                    <span class="page-link">...</span>
                                </li>
                                <?php endif; ?>
                                <!-- Next Page -->
                                <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="index.php?page=products&<?php 
                                        echo (!empty($search)) ? 'search=' . urlencode($search) . '&' : '';
                                        echo ($category_id > 0) ? 'category_id=' . $category_id . '&' : '';
                                        echo 'pg=' . ($page + 1);
                                    ?>" aria-label="Next">
                                        <i class="fas fa-angle-right"></i>
                                    </a>
                                </li>
                                <!-- Last Page -->
                                <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="index.php?page=products&<?php 
                                        echo (!empty($search)) ? 'search=' . urlencode($search) . '&' : '';
                                        echo ($category_id > 0) ? 'category_id=' . $category_id . '&' : '';
                                        echo 'pg=' . $total_pages;
                                    ?>" aria-label="Last">
                                        <i class="fas fa-angle-double-right"></i>
                                    </a>
                                </li>
                            </ul>
                        </nav>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS and Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>