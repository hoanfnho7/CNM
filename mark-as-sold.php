<?php
session_start();
include 'config/db.php';
include 'includes/functions.php';

// Chuyển hướng nếu chưa đăng nhập
if (!isLoggedIn()) {
    redirect('login.php');
}

// Kiểm tra xem ID sản phẩm có được cung cấp không
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    redirect('my-products.php');
}

$product_id = (int)$_GET['id'];
$user_id = $_SESSION['user_id'];

// Kiểm tra xem sản phẩm có tồn tại và thuộc về người dùng không
$sql = "SELECT * FROM products WHERE id = ? AND user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $product_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    redirect('my-products.php');
}

// Đánh dấu sản phẩm đã bán
$sql = "UPDATE products SET status = 'sold', updated_at = NOW() WHERE id = ? AND user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $product_id, $user_id);
$stmt->execute();

// Chuyển hướng về trang chi tiết sản phẩm
redirect('product-detail.php?id=' . $product_id);
