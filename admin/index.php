<?php
// Admin main controller
session_start();

// Check if user is logged in and has admin/staff role
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['admin', 'staff'])) {
    header("Location: login.php");
    exit;
}

// Include database connection
require_once '../config/database.php';
$db = new Database();
$conn = $db->getConnection();

// Get action parameter (default to dashboard)
$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';

// Define allowed pages based on user role
$allowed_pages = [
    'dashboard', 'products', 'product-edit', 'orders', 'users'
];

// Add admin-only pages
if ($_SESSION['user_role'] == 'admin') {
    $allowed_pages = array_merge($allowed_pages, [
        'promotions', 'banners', 'settings', 'reports'
    ]);
}

// Validate the requested page
if (!in_array($page, $allowed_pages)) {
    $page = 'dashboard';
}

// Include header and sidebar
include_once 'includes/header.php';
include_once 'includes/sidebar.php';

// Include the requested page
include_once $page . '.php';

// Include footer
include_once 'includes/footer.php';
?>
