<?php
// Admin main controller
session_start();

// Check if user is logged in and has admin/staff role
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['admin', 'staff'])) {
    header("Location: ../index.php?controller=user&action=login");
    exit;
}

// Include database connection
require_once '../config/database.php';
$db = new Database();
$conn = $db->getConnection();

// Get action parameter (default to dashboard)
$page = isset($_GET['page']) ? htmlspecialchars($_GET['page']) : 'dashboard';

// Define allowed pages based on user role
$allowed_pages = [
    'dashboard', 'products', 'product-edit', 'orders', 'users'
];

// Add admin-only pages
if ($_SESSION['user_role'] == 'admin') {
    $allowed_pages = array_merge($allowed_pages, [
        'promotions', 'banners', 'settings', 'reports', 'traffic'
    ]);
}

// Validate the requested page
if (!in_array($page, $allowed_pages)) {
    $page = 'dashboard';
}

// Khởi tạo biến cho product-edit
$product = null;
if ($page == 'product-edit') {
    require_once '../models/Product.php';
    require_once '../models/Category.php';
    $product_model = new Product($conn);
    $product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

    if ($product_id > 0) {
        $product_model->id = $product_id;
        if (!$product_model->readOne()) {
            $_SESSION['error_message'] = "Không tìm thấy sản phẩm.";
            header("Location: index.php?page=products");
            exit;
        }
        $product = $product_model;
    } else {
        // Thêm mới sản phẩm
        $product = $product_model;
        $product->id = 0;
        $product->name = '';
        $product->price = 0;
        $product->sale_price = 0;
        $product->category_id = 0;
        $product->image = '';
    }

    // Lấy danh sách danh mục
    $category_model = new Category($conn);
    $categories = $category_model->read();
}

// Include header and sidebar
include_once 'includes/header.php';
include_once 'includes/sidebar.php';

// Include the requested page
if (file_exists($page . '.php')) {
    include_once $page . '.php';
} else {
    echo "<div class='alert alert-danger'>Page not found</div>";
}

// Include footer
include_once 'includes/footer.php';
?>