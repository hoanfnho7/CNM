<?php
// File để tạo QR code cho sản phẩm
session_start();
include 'config/db.php';
include 'includes/functions.php';

// Kiểm tra xem có product ID không
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('HTTP/1.0 404 Not Found');
    exit();
}

$product_id = (int)$_GET['id'];

// Kiểm tra sản phẩm có tồn tại không
$product = getProductById($conn, $product_id);
if (!$product) {
    header('HTTP/1.0 404 Not Found');
    exit();
}

// Tạo URL đầy đủ cho sản phẩm
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$product_url = $protocol . '://' . $host . dirname($_SERVER['PHP_SELF']) . '/product-detail.php?id=' . $product_id;

// Sử dụng API QR Server để tạo QR code
$qr_api_url = 'https://api.qrserver.com/v1/create-qr-code/';
$qr_params = http_build_query([
    'size' => '100x100',
    'data' => $product_url,
    'format' => 'png',
    'ecc' => 'M',
    'margin' => '10'
]);

$qr_image_url = $qr_api_url . '?' . $qr_params;

// Set headers để trả về hình ảnh
header('Content-Type: image/png');
header('Cache-Control: public, max-age=3600'); // Cache 1 giờ

// Lấy và trả về hình ảnh QR code
$qr_image = file_get_contents($qr_image_url);
if ($qr_image !== false) {
    echo $qr_image;
} else {
    // Nếu không thể tạo QR code, trả về hình ảnh lỗi đơn giản
    header('HTTP/1.0 500 Internal Server Error');
    exit();
}
?>
