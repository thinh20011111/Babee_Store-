<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error_log.txt');

try {
    require_once 'models/Product.php';
    error_log("Loaded Product.php");
    require_once 'models/Banner.php';
    error_log("Loaded Banner.php");
    require_once 'models/Category.php';
    error_log("Loaded Category.php");
    require_once 'models/Settings.php';
    error_log("Loaded Settings.php");
    echo "All models loaded successfully!";
} catch (Exception $e) {
    error_log("Error in test_models.php: " . $e->getMessage());
    echo "Error: " . $e->getMessage();
}
?>