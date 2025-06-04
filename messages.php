<?php
session_start();
include 'config/db.php';
include 'includes/functions.php';

// Redirect if not logged in
if (!isLoggedIn()) {
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];

// Get conversations
$sql = "SELECT 
            u.id, u.username, u.avatar, 
            MAX(m.created_at) as last_message_time,
            (SELECT message FROM messages WHERE (sender_id = u.id AND receiver_id = ?) OR (sender_id = ? AND receiver_id = u.id) ORDER BY created_at DESC LIMIT 1) as last_message,
            (SELECT COUNT(*) FROM messages WHERE sender_id = u.id AND receiver_id = ? AND is_read = 0) as unread_count
        FROM messages m
        JOIN users u ON (m.sender_id = u.id OR m.receiver_id = u.id)
        WHERE (m.sender_id = ? OR m.receiver_id = ?) AND u.id != ?
        GROUP BY u.id
        ORDER BY last_message_time DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("iiiiii", $user_id, $user_id, $user_id, $user_id, $user_id, $user_id);
$stmt->execute();
$conversations = $stmt->get_result();

// Get selected conversation if any
$selected_user = null;
$messages = null;

if (isset($_GET['user_id']) && is_numeric($_GET['user_id'])) {
    $selected_user_id = (int)$_GET['user_id'];
    
    // Get user info
    $sql = "SELECT * FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $selected_user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $selected_user = $result->fetch_assoc();
        
        // Get messages
        $sql = "SELECT m.*, 
                    CASE WHEN m.sender_id = ? THEN 1 ELSE 0 END as is_sent,
                    p.id as product_id, p.name as product_name, p.image as product_image,
                    DATE(m.created_at) as message_date
                FROM messages m
                LEFT JOIN products p ON m.product_id = p.id
                WHERE (m.sender_id = ? AND m.receiver_id = ?) OR (m.sender_id = ? AND m.receiver_id = ?)
                ORDER BY m.created_at ASC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iiiii", $user_id, $user_id, $selected_user_id, $selected_user_id, $user_id);
        $stmt->execute();
        $messages = $stmt->get_result();
        
        // Mark messages as read
        $sql = "UPDATE messages SET is_read = 1 WHERE sender_id = ? AND receiver_id = ? AND is_read = 0";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $selected_user_id, $user_id);
        $stmt->execute();
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tin nhắn - SecondLife Market</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
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
            background-color:rgb(28, 139, 218);
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
            color: black;
            font-size: 0.75rem;
            margin-top: 5px;
        }
        .conversation-item {
            transition: all 0.2s ease;
        }
        .conversation-item:hover {
            background-color: rgba(246, 246, 250, 0.05);
        }
        .conversation-item.active {
            background-color: rgba(0, 0, 0, 0.1);
            border-color:rgb(84, 145, 236);
        }
        .unread-badge {
            position: absolute;
            top: 10px;
            right: 10px;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-3">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Menu</h5>
                    </div>
                    <div class="list-group list-group-flush">
                        <a href="profile.php" class="list-group-item list-group-item-action">
                            <i class="fas fa-user me-2"></i>Hồ sơ cá nhân
                        </a>
                        <a href="my-products.php" class="list-group-item list-group-item-action">
                            <i class="fas fa-box me-2"></i>Sản phẩm của tôi
                        </a>
                        <a href="favorites.php" class="list-group-item list-group-item-action">
                            <i class="fas fa-heart me-2"></i>Sản phẩm yêu thích
                        </a>
                        <a href="messages.php" class="list-group-item list-group-item-action active">
                            <i class="fas fa-envelope me-2"></i>Tin nhắn
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-9">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Tin nhắn</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="row g-0">
                            <!-- Conversations List -->
                            <div class="col-md-4 border-end">
                                <div class="list-group list-group-flush">
                                    <?php if ($conversations->num_rows > 0): ?>
                                        <?php while($conversation = $conversations->fetch_assoc()): ?>
                                            <a href="messages.php?user_id=<?php echo $conversation['id']; ?>" class="list-group-item list-group-item-action conversation-item position-relative <?php echo (isset($selected_user) && $selected_user['id'] == $conversation['id']) ? 'active' : ''; ?>">
                                                <div class="d-flex align-items-center">
                                                    <div class="position-relative me-3">
                                                        <?php if (!empty($conversation['avatar'])): ?>
                                                            <img src="uploads/avatars/<?php echo $conversation['avatar']; ?>" alt="<?php echo $conversation['username']; ?>" class="rounded-circle" style="width: 40px; height: 40px; object-fit: cover;">
                                                        <?php else: ?>
                                                            <i class="fas fa-user-circle fa-2x"></i>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="flex-grow-1 ms-2">
                                                        <div class="d-flex justify-content-between">
                                                            <h6 class="mb-0"><?php echo $conversation['username']; ?></h6>
                                                            <small class="text-muted"><?php echo date('H:i', strtotime($conversation['last_message_time'])); ?></small>
                                                        </div>
                                                        <p class="mb-0 small text-truncate <?php echo $conversation['unread_count'] > 0 ? 'fw-bold' : 'text-muted'; ?>">
                                                            <?php echo htmlspecialchars(substr($conversation['last_message'], 0, 30)); ?>
                                                            <?php echo strlen($conversation['last_message']) > 30 ? '...' : ''; ?>
                                                        </p>
                                                    </div>
                                                </div>
                                                <?php if ($conversation['unread_count'] > 0): ?>
                                                    <span class="badge bg-danger rounded-pill unread-badge">
                                                        <?php echo $conversation['unread_count']; ?>
                                                    </span>
                                                <?php endif; ?>
                                            </a>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <div class="text-center py-5">
                                            <i class="far fa-comment fa-3x mb-3 text-muted"></i>
                                            <h5>Không có tin nhắn</h5>
                                            <p class="text-muted small">Bạn chưa có cuộc trò chuyện nào.</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <!-- Chat Area -->
                            <div class="col-md-8">
                                <?php if ($selected_user): ?>
                                    <div class="d-flex flex-column h-100" style="min-height: 500px;">
                                        <!-- Chat Header -->
                                        <div class="p-3 border-bottom d-flex align-items-center">
                                            <?php if (!empty($selected_user['avatar'])): ?>
                                                <img src="uploads/avatars/<?php echo $selected_user['avatar']; ?>" alt="<?php echo $selected_user['username']; ?>" class="rounded-circle me-2" style="width: 40px; height: 40px; object-fit: cover;">
                                            <?php else: ?>
                                                <i class="fas fa-user-circle fa-2x me-2"></i>
                                            <?php endif; ?>
                                            <div>
                                                <h6 class="mb-0"><?php echo $selected_user['username']; ?></h6>
                                                <small class="text-muted">
                                                    <?php echo $selected_user['status'] == 'active' ? 'Đang hoạt động' : 'Không hoạt động'; ?>
                                                </small>
                                            </div>
                                            <a href="seller-profile.php?id=<?php echo $selected_user['id']; ?>" class="btn btn-sm btn-outline-primary ms-auto">
                                                <i class="fas fa-user me-1"></i>Xem hồ sơ
                                            </a>
                                        </div>
                                        
                                        <!-- Chat Messages -->
                                        <div class="chat-container" id="chatMessages">
                                            <?php if ($messages && $messages->num_rows > 0): ?>
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
                                                    <?php if ($message['product_id']): ?>
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
                                                <div class="text-center py-5">
                                                    <i class="far fa-comment fa-3x mb-3 text-muted"></i>
                                                    <h5>Bắt đầu cuộc trò chuyện</h5>
                                                    <p class="text-muted">Hãy gửi tin nhắn đầu tiên cho <?php echo $selected_user['username']; ?>!</p>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <!-- Chat Input -->
                                        <div class="p-3 border-top mt-auto">
                                            <form id="chatForm" data-receiver-id="<?php echo $selected_user['id']; ?>">
                                                <div class="input-group">
                                                    <textarea class="form-control auto-resize" id="message" placeholder="Nhập tin nhắn..." rows="1" required></textarea>
                                                    <button class="btn btn-primary" type="submit">
                                                        <i class="fas fa-paper-plane"></i>
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center py-5">
                                        <i class="far fa-comment-dots fa-3x mb-3 text-muted"></i>
                                        <h4>Chọn một cuộc trò chuyện</h4>
                                        <p class="text-muted">Chọn một người dùng từ danh sách bên trái để xem tin nhắn.</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="assets/js/script.js"></script>
    
    <script>
        // Auto-scroll chat to bottom
        document.addEventListener("DOMContentLoaded", function() {
            const chatMessages = document.getElementById("chatMessages");
            if (chatMessages) {
                chatMessages.scrollTop = chatMessages.scrollHeight;
            }
            
            // Auto-resize textarea
            const messageInput = document.getElementById("message");
            if (messageInput) {
                messageInput.addEventListener("input", function() {
                    this.style.height = "auto";
                    this.style.height = (this.scrollHeight) + "px";
                });
                
                // Allow Enter to submit, Shift+Enter for new line
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
