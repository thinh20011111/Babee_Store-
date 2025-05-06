<?php
// Application configuration

// Base URL - set this to your InfinityFree domain
define('BASE_URL', 'https://babee.wuaze.com');

// Site settings
define('SITE_NAME', 'Babee Store');
define('SITE_DESCRIPTION', 'Quality Baby Clothing at Affordable Prices');

// Admin email
define('ADMIN_EMAIL', 'admin@example.com');

// Pagination settings
define('ITEMS_PER_PAGE', 12);

// Upload settings
define('UPLOAD_DIR', 'uploads/');
define('MAX_FILE_SIZE', 2097152); // 2MB

// Default currency
define('CURRENCY', '₫'); // Vietnamese Dong

// Available product categories
define('CATEGORIES', [
    'boys' => 'Boys Clothing',
    'girls' => 'Girls Clothing',
    'unisex' => 'Unisex Clothing',
    'accessories' => 'Accessories',
    'seasonal' => 'Seasonal Items',
    'sale' => 'Sale Items'
]);

// Order statuses
define('ORDER_STATUSES', [
    'pending' => 'Pending',
    'processing' => 'Processing',
    'shipped' => 'Shipped',
    'delivered' => 'Delivered',
    'cancelled' => 'Cancelled'
]);

// User roles
define('USER_ROLES', [
    'customer' => 'Customer',
    'staff' => 'Staff',
    'admin' => 'Administrator'
]);

// Error reporting settings
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Time zone
date_default_timezone_set('Asia/Ho_Chi_Minh');
