<?php
session_start();
include '../config/db.php';
include '../includes/functions.php';

// Kiểm tra xem người dùng đã đăng nhập chưa
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Bạn cần đăng nhập để thực hiện chức năng này']);
    exit;
}

// Kiểm tra xem ID người nhận và nội dung tin nhắn có được cung cấp không
if (!isset($_POST['receiver_id']) || !is_numeric($_POST['receiver_id']) || !isset($_POST['message']) || empty($_POST['message'])) {
    echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ']);
    exit;
}

$sender_id = $_SESSION['user_id'];
$receiver_id = (int)$_POST['receiver_id'];
$message = sanitize($_POST['message']);
$product_id = isset($_POST['product_id']) && is_numeric($_POST['product_id']) ? (int)$_POST['product_id'] : null;

// Kiểm tra xem người nhận có tồn tại không
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $receiver_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo json_encode(['success' => false, 'message' => 'Người nhận không tồn tại']);
    exit;
}

// Thêm tin nhắn
if ($product_id) {
    $sql = "INSERT INTO messages (sender_id, receiver_id, product_id, message, created_at) VALUES (?, ?, ?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiis", $sender_id, $receiver_id, $product_id, $message);
} else {
    $sql = "INSERT INTO messages (sender_id, receiver_id, message, created_at) VALUES (?, ?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iis", $sender_id, $receiver_id, $message);
}

if ($stmt->execute()) {
    echo json_encode([
        'success' => true, 
        'message' => 'Tin nhắn đã được gửi', 
        'time' => date('H:i')
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Đã xảy ra lỗi. Vui lòng thử lại sau.']);
}
