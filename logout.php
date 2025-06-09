<?php
session_start();
include 'config/db.php';
include 'includes/functions.php';

// Xóa token ghi nhớ nếu tồn tại
if (isset($_COOKIE['remember_token'])) {
    $token = $_COOKIE['remember_token'];
    
    // Xóa token khỏi cơ sở dữ liệu
    $sql = "DELETE FROM remember_tokens WHERE token = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $token);
    $stmt->execute();
    
    // Xóa cookie
    setcookie('remember_token', '', time() - 3600, '/');
}

// Hủy phiên làm việc
session_unset();
session_destroy();

// Chuyển hướng về trang chủ
redirect('index.php');
