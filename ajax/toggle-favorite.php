<?php
session_start();
include '../config/db.php';
include '../includes/functions.php';

// Bật báo cáo lỗi để gỡ lỗi
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Đặt kiểu nội dung là JSON
header('Content-Type: application/json');

// Ghi log yêu cầu để gỡ lỗi
error_log("Favorite toggle request: " . print_r($_POST, true));

// Kiểm tra xem người dùng đã đăng nhập chưa
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Bạn cần đăng nhập để thực hiện chức năng này']);
    exit;
}

// Kiểm tra xem ID sản phẩm có được cung cấp không
if (!isset($_POST['product_id']) || !is_numeric($_POST['product_id'])) {
    echo json_encode(['success' => false, 'message' => 'ID sản phẩm không hợp lệ']);
    exit;
}

$user_id = $_SESSION['user_id'];
$product_id = (int)$_POST['product_id'];

// Ghi log các ID để gỡ lỗi
error_log("User ID: $user_id, Product ID: $product_id");

// Kiểm tra xem sản phẩm có tồn tại và đã được duyệt chưa
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

// Kiểm tra xem sản phẩm đã có trong danh sách yêu thích chưa
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
    // Xóa khỏi danh sách yêu thích
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
    // Thêm vào danh sách yêu thích
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
