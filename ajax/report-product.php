<?php
session_start();
include '../config/db.php';
include '../includes/functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Bạn cần đăng nhập để thực hiện chức năng này']);
    exit;
}

// Check if product ID and reason are provided
if (!isset($_POST['product_id']) || !is_numeric($_POST['product_id']) || !isset($_POST['reason']) || empty($_POST['reason'])) {
    echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ']);
    exit;
}

$user_id = $_SESSION['user_id'];
$product_id = (int)$_POST['product_id'];
$reason = sanitize($_POST['reason']);
$description = isset($_POST['description']) ? sanitize($_POST['description']) : '';

// Check if product exists
$sql = "SELECT * FROM products WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo json_encode(['success' => false, 'message' => 'Sản phẩm không tồn tại']);
    exit;
}

// Check if user has already reported this product
$sql = "SELECT * FROM reports WHERE user_id = ? AND product_id = ? AND status = 'pending'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $user_id, $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'Bạn đã báo cáo sản phẩm này rồi']);
    exit;
}

// Insert report
$sql = "INSERT INTO reports (user_id, product_id, reason, description, created_at) VALUES (?, ?, ?, ?, NOW())";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iiss", $user_id, $product_id, $reason, $description);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Báo cáo đã được gửi. Chúng tôi sẽ xem xét sớm nhất có thể.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Đã xảy ra lỗi. Vui lòng thử lại sau.']);
}
