<?php
session_start();
include 'config/db.php';
include 'includes/functions.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect('index.php');
}

$errors = [];
$success = false;
$valid_token = false;
$token = '';

// Check if token is provided
if (isset($_GET['token'])) {
    $token = sanitize($_GET['token']);
    
    // Validate token
    $sql = "SELECT pr.*, u.username, u.email FROM password_resets pr 
            JOIN users u ON pr.user_id = u.id 
            WHERE pr.token = ? AND pr.expires_at > NOW() AND pr.used = 0";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $reset_data = $result->fetch_assoc();
        $valid_token = true;
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && $valid_token) {
    // Get form data
    $password = sanitize($_POST['password']);
    $confirm_password = sanitize($_POST['confirm_password']);
    
    // Validate input
    if (empty($password)) {
        $errors[] = "Mật khẩu không được để trống";
    } elseif (strlen($password) < 6) {
        $errors[] = "Mật khẩu phải có ít nhất 6 ký tự";
    }
    
    if ($password !== $confirm_password) {
        $errors[] = "Mật khẩu xác nhận không khớp";
    }
    
    // If no errors, reset password
    if (empty($errors)) {
        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Update user password
        $sql = "UPDATE users SET password = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $hashed_password, $reset_data['user_id']);
        
        if ($stmt->execute()) {
            // Mark token as used
            $sql = "UPDATE password_resets SET used = 1 WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $reset_data['id']);
            $stmt->execute();
            
            // Send confirmation email
            $subject = "Mật khẩu đã được đặt lại - SecondLife Market";
            $message = "
                <html>
                <head>
                    <title>Mật khẩu đã được đặt lại - SecondLife Market</title>
                </head>
                <body>
                    <h2>Xin chào " . $reset_data['username'] . ",</h2>
                    <p>Mật khẩu của bạn đã được đặt lại thành công.</p>
                    <p>Nếu bạn không thực hiện thay đổi này, vui lòng liên hệ với chúng tôi ngay lập tức.</p>
                    <p>Trân trọng,<br>Đội ngũ SecondLife Market</p>
                </body>
                </html>
            ";
            
            sendEmail($reset_data['email'], $subject, $message);
            
            $success = true;
        } else {
            $errors[] = "Đã xảy ra lỗi. Vui lòng thử lại sau.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt lại mật khẩu - SecondLife Market</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-body p-5">
                        <h2 class="text-center mb-4"><i class="fas fa-key me-2"></i>Đặt lại mật khẩu</h2>
                        
                        <?php if (!$valid_token && !$success): ?>
                            <div class="alert alert-danger">
                                Liên kết đặt lại mật khẩu không hợp lệ hoặc đã hết hạn. Vui lòng yêu cầu <a href="forgot-password.php">đặt lại mật khẩu</a> mới.
                            </div>
                        <?php elseif ($success): ?>
                            <div class="alert alert-success">
                                Mật khẩu của bạn đã được đặt lại thành công. Bạn có thể <a href="login.php">đăng nhập</a> ngay bây giờ.
                            </div>
                        <?php else: ?>
                            <?php if (!empty($errors)): ?>
                                <div class="alert alert-danger">
                                    <ul class="mb-0">
                                        <?php foreach ($errors as $error): ?>
                                            <li><?php echo $error; ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>
                            
                            <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . "?token=" . $token); ?>">
                                <div class="mb-3">
                                    <label for="password" class="form-label">Mật khẩu mới</label>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                </div>
                                <div class="mb-3">
                                    <label for="confirm_password" class="form-label">Xác nhận mật khẩu mới</label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                </div>
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary">Đặt lại mật khẩu</button>
                                </div>
                            </form>
                        <?php endif; ?>
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
</body>
</html>
