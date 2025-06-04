<?php
session_start();
include '../config/db.php';
include '../includes/functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Bạn cần đăng nhập để thực hiện chức năng này']);
    exit;
}

// Check if receiver ID is provided
if (!isset($_GET['receiver_id']) || !is_numeric($_GET['receiver_id'])) {
    echo json_encode(['success' => false, 'message' => 'ID người nhận không hợp lệ']);
    exit;
}

$user_id = $_SESSION['user_id'];
$receiver_id = (int)$_GET['receiver_id'];
$last_id = isset($_GET['last_id']) && is_numeric($_GET['last_id']) ? (int)$_GET['last_id'] : 0;

// Get new messages
$sql = "SELECT m.*, 
            p.id as product_id, p.name as product_name, p.image as product_image,
            DATE(m.created_at) as date
        FROM messages m
        LEFT JOIN products p ON m.product_id = p.id
        WHERE ((m.sender_id = ? AND m.receiver_id = ?) OR (m.sender_id = ? AND m.receiver_id = ?)) 
        AND m.id > ? 
        ORDER BY m.created_at ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iiiii", $user_id, $receiver_id, $receiver_id, $user_id, $last_id);
$stmt->execute();
$result = $stmt->get_result();

$messages = [];
while ($row = $result->fetch_assoc()) {
    $messages[] = [
        'id' => $row['id'],
        'message' => $row['message'],
        'is_sent' => $row['sender_id'] == $user_id,
        'time' => date('H:i', strtotime($row['created_at'])),
        'date' => $row['date'],
        'product_id' => $row['product_id'],
        'product_name' => $row['product_name'],
        'product_image' => $row['product_image']
    ];
    
    // Mark as read if user is receiver
    if ($row['receiver_id'] == $user_id && !$row['is_read']) {
        $update_sql = "UPDATE messages SET is_read = 1 WHERE id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("i", $row['id']);
        $update_stmt->execute();
    }
}

echo json_encode(['success' => true, 'messages' => $messages]);
