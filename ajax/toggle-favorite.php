<?php
session_start();
include '../config/db.php';
include '../includes/functions.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set content type to JSON
header('Content-Type: application/json');

// Log the request for debugging
error_log("Favorite toggle request: " . print_r($_POST, true));

// Check if user is logged in
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Bạn cần đăng nhập để thực hiện chức năng này']);
    exit;
}

// Check if product ID is provided
if (!isset($_POST['product_id']) || !is_numeric($_POST['product_id'])) {
    echo json_encode(['success' => false, 'message' => 'ID sản phẩm không hợp lệ']);
    exit;
}

$user_id = $_SESSION['user_id'];
$product_id = (int)$_POST['product_id'];

// Log the IDs for debugging
error_log("User ID: $user_id, Product ID: $product_id");

// Check if product exists and is approved
$sql = "SELECT * FROM products WHERE id = ? AND status = 'approved'";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    error_log("Prepare failed: " . $conn->error);
    echo json_encode(['success' => false, 'message' => 'Lỗi database: ' . $conn->error]);
    exit;
}

$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo json_encode(['success' => false, 'message' => 'Sản phẩm không tồn tại hoặc chưa được duyệt']);
    exit;
}

// Check if product is already in favorites
$sql = "SELECT * FROM favorites WHERE user_id = ? AND product_id = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    error_log("Prepare failed: " . $conn->error);
    echo json_encode(['success' => false, 'message' => 'Lỗi database: ' . $conn->error]);
    exit;
}

$stmt->bind_param("ii", $user_id, $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Remove from favorites
    $sql = "DELETE FROM favorites WHERE user_id = ? AND product_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $user_id, $product_id);

    if ($stmt->execute()) {
        error_log("Removed from favorites successfully");
        echo json_encode(['success' => true, 'action' => 'removed', 'message' => 'Đã xóa khỏi danh sách yêu thích']);
    } else {
        error_log("Delete failed: " . $stmt->error);
        echo json_encode(['success' => false, 'message' => 'Đã xảy ra lỗi khi xóa. Vui lòng thử lại sau.']);
    }
} else {
    // Add to favorites
    $sql = "INSERT INTO favorites (user_id, product_id, created_at) VALUES (?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $user_id, $product_id);

    if ($stmt->execute()) {
        error_log("Added to favorites successfully");
        echo json_encode(['success' => true, 'action' => 'added', 'message' => 'Đã thêm vào danh sách yêu thích']);
    } else {
        error_log("Insert failed: " . $stmt->error);
        echo json_encode(['success' => false, 'message' => 'Đã xảy ra lỗi khi thêm. Vui lòng thử lại sau.']);
    }
}

$stmt->close();
$conn->close();
?>
