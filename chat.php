<?php
session_start();
include 'config/db.php';
include 'includes/functions.php';

// Chuyển hướng nếu chưa đăng nhập
if (!isLoggedIn()) {
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];

// Kiểm tra xem ID người bán có được cung cấp không
if (!isset($_GET['seller_id']) || !is_numeric($_GET['seller_id'])) {
    redirect('index.php');
}

$seller_id = (int)$_GET['seller_id'];

// Kiểm tra xem người bán có tồn tại không
$sql = "SELECT * FROM users WHERE id = ? AND status = 'active'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $seller_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    redirect('index.php');
}

$seller = $result->fetch_assoc();

// Kiểm tra xem ID sản phẩm có được cung cấp không
$product = null;
if (isset($_GET['product_id']) && is_numeric($_GET['product_id'])) {
    $product_id = (int)$_GET['product_id'];
    
    // Lấy thông tin sản phẩm
    $sql = "SELECT * FROM products WHERE id = ? AND status = 'approved'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $product = $result->fetch_assoc();
    }
}

// Lấy tin nhắn
$sql = "SELECT m.*, 
            CASE WHEN m.sender_id = ? THEN 1 ELSE 0 END as is_sent,
            p.id as product_id, p.name as product_name, p.image as product_image,
            DATE(m.created_at) as message_date
        FROM messages m
        LEFT JOIN products p ON m.product_id = p.id
        WHERE (m.sender_id = ? AND m.receiver_id = ?) OR (m.sender_id = ? AND m.receiver_id = ?)
        ORDER BY m.created_at ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iiiii", $user_id, $user_id, $seller_id, $seller_id, $user_id);
$stmt->execute();
$messages = $stmt->get_result();

// Đánh dấu tin nhắn đã đọc
$sql = "UPDATE messages SET is_read = 1 WHERE sender_id = ? AND receiver_id = ? AND is_read = 0";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $seller_id, $user_id);
$stmt->execute();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trò chuyện với <?php echo $seller['username']; ?> - SecondLife Market</title>
    <!-- CSS Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- CSS Tùy chỉnh -->
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .chat-container {
            height: 400px;
            overflow-y: auto;
            padding: 15px;
        }
        .chat-message {
            margin-bottom: 15px;
            max-width: 80%;
            clear: both;
        }
        .chat-message-sent {
            float: right;
            background-color:rgb(53, 174, 190);
            border-radius: 15px 0 15px 15px;
            padding: 10px 15px;
            position: relative;
        }
        .chat-message-received {
            float: left;
            background-color: #f1f0f0;
            border-radius: 0 15px 15px 15px;
            padding: 10px 15px;
            position: relative;
        }
        .message-content {
            word-wrap: break-word;
        }
        .message-time {
            text-align: right;
            color: #777;
            font-size: 0.75rem;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <?php if (!empty($seller['avatar'])): ?>
                                <img src="uploads/avatars/<?php echo $seller['avatar']; ?>" alt="<?php echo $seller['username']; ?>" class="rounded-circle me-2" style="width: 40px; height: 40px; object-fit: cover;">
                            <?php else: ?>
                                <i class="fas fa-user-circle fa-2x me-2"></i>
                            <?php endif; ?>
                            <div>
                                <h5 class="mb-0"><?php echo $seller['username']; ?></h5>
                                <small class="text-muted">
                                    <?php echo $seller['status'] == 'active' ? 'Đang hoạt động' : 'Không hoạt động'; ?>
                                </small>
                            </div>
                        </div>
                        <a href="seller-profile.php?id=<?php echo $seller_id; ?>" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-user me-1"></i>Xem hồ sơ
                        </a>
                    </div>
                    
                    <?php if ($product): ?>
                        <div class="card-header bg-light">
                            <div class="d-flex align-items-center">
                                <img src="uploads/products/<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>" class="me-3" style="width: 60px; height: 60px; object-fit: cover;">
                                <div>
                                    <h6 class="mb-1"><?php echo $product['name']; ?></h6>
                                    <p class="mb-0 text-danger fw-bold"><?php echo number_format($product['price']); ?> VNĐ</p>
                                </div>
                                <a href="product-detail.php?id=<?php echo $product['id']; ?>" class="btn btn-sm btn-outline-primary ms-auto">
                                    <i class="fas fa-eye me-1"></i>Xem sản phẩm
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <div class="card-body p-0">
                        <div class="chat-container" id="chatMessages">
                            <?php if ($messages->num_rows > 0): ?>
                                <?php 
                                $last_date = null;
                                while($message = $messages->fetch_assoc()): 
                                    $message_date = $message['message_date'];
                                    if ($last_date != $message_date):
                                        $last_date = $message_date;
                                ?>
                                    <div class="text-center my-3">
                                        <span class="badge bg-light text-dark"><?php echo date('d/m/Y', strtotime($message_date)); ?></span>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="chat-message <?php echo $message['is_sent'] ? 'chat-message-sent' : 'chat-message-received'; ?>" data-id="<?php echo $message['id']; ?>">
                                    <?php if ($message['product_id'] && $message['product_id'] != ($product ? $product['id'] : 0)): ?>
                                        <div class="card mb-2" style="max-width: 200px;">
                                            <img src="uploads/products/<?php echo $message['product_image']; ?>" class="card-img-top" alt="<?php echo $message['product_name']; ?>" style="height: 100px; object-fit: cover;">
                                            <div class="card-body p-2">
                                                <p class="card-text small"><?php echo $message['product_name']; ?></p>
                                                <a href="product-detail.php?id=<?php echo $message['product_id']; ?>" class="btn btn-sm btn-primary w-100">Xem sản phẩm</a>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                    <div class="message-content"><?php echo nl2br(htmlspecialchars($message['message'])); ?></div>
                                    <div class="message-time small"><?php echo date('H:i', strtotime($message['created_at'])); ?></div>
                                </div>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <?php if ($product): ?>
                                    <div class="text-center py-5">
                                        <i class="far fa-comment fa-3x mb-3 text-muted"></i>
                                        <h5>Bắt đầu cuộc trò chuyện về sản phẩm</h5>
                                        <p class="text-muted">Hãy gửi tin nhắn đầu tiên cho <?php echo $seller['username']; ?>!</p>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center py-5">
                                        <i class="far fa-comment fa-3x mb-3 text-muted"></i>
                                        <h5>Bắt đầu cuộc trò chuyện</h5>
                                        <p class="text-muted">Hãy gửi tin nhắn đầu tiên cho <?php echo $seller['username']; ?>!</p>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="card-footer">
                        <form id="chatForm" data-receiver-id="<?php echo $seller_id; ?>" <?php echo $product ? 'data-product-id="' . $product['id'] . '"' : ''; ?>>
                            <div class="input-group">
                                <textarea class="form-control auto-resize" id="message" placeholder="Nhập tin nhắn..." rows="1" required></textarea>
                                <button class="btn btn-primary" type="submit">
                                    <i class="fas fa-paper-plane"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <div class="text-center mt-3">
                    <a href="messages.php" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i>Quay lại tin nhắn
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
    
    <!-- JS Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- JS Tùy chỉnh -->
    <script src="assets/js/script.js"></script>
    
    <script>
        // Tự động cuộn chat xuống dưới cùng
        document.addEventListener("DOMContentLoaded", function() {
            const chatMessages = document.getElementById("chatMessages");
            if (chatMessages) {
                chatMessages.scrollTop = chatMessages.scrollHeight;
            }
            
            // Tự động điều chỉnh kích thước textarea
            const messageInput = document.getElementById("message");
            if (messageInput) {
                messageInput.addEventListener("input", function() {
                    this.style.height = "auto";
                    this.style.height = (this.scrollHeight) + "px";
                });
                
                // Cho phép Enter để gửi, Shift+Enter để xuống dòng
                messageInput.addEventListener("keydown", function(e) {
                    if (e.key === "Enter" && !e.shiftKey) {
                        e.preventDefault();
                        document.getElementById("chatForm").dispatchEvent(new Event("submit"));
                    }
                });
            }
        });
    </script>
</body>
</html>
