<?php
require_once 'config/database.php';
try {
    $db = new Database();
    $conn = $db->getConnection();
    session_start();
    $traffic = new TrafficLog($conn);
    $traffic->logAccess();
    echo "Log access attempted.";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>