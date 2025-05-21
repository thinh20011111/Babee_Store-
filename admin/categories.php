<?php
$upload_dir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/categories/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}
if (is_writable($upload_dir)) {
    echo "Có thể ghi vào thư mục.";
} else {
    echo "Không thể ghi vào thư mục.";
}