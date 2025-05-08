<?php
require_once 'config/database.php';
try {
    $db = new Database();
    $conn = $db->getConnection();
    echo "Database connected successfully!";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>