<?php
session_start();
include 'config/db.php';
include 'includes/functions.php';

// Redirect if not logged in
if (!isLoggedIn()) {
    redirect('login.php');
}

// Check if product ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    redirect('my-products.php');
}

$product_id = (int)$_GET['id'];
$user_id = $_SESSION['user_id'];

// Check if product exists and belongs to user
$sql = "SELECT * FROM products WHERE id = ? AND user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $product_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    redirect('my-products.php');
}

// Mark product as sold
$sql = "UPDATE products SET status = 'sold', updated_at = NOW() WHERE id = ? AND user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $product_id, $user_id);
$stmt->execute();

// Redirect back to product detail
redirect('product-detail.php?id=' . $product_id);
