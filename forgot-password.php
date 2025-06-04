<?php
session_start();
include 'config/db.php';
include 'includes/functions.php';

// Chuyển hướng nếu đã đăng nhập
if (isLoggedIn()) {
    redirect('index.php');
}

$errors = [];
$success = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Lấy dữ liệu từ form
    $email = sanitize($_POST['email']);
    
    // Kiểm tra dữ liệu đầu vào
    if (empty($email)) {
        $errors[] = "Email không được để trống";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Email không hợp lệ";
    }
    
    // Nếu không có lỗi, xử lý đặt lại mật khẩu
    if (empty($errors)) {
        // Kiểm tra email có tồn tại không
        $sql = "SELECT * FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            
            // Tạo mã token đặt lại mật khẩu
            $token = generateRandomString(32);
            $expiry = time() + (24 * 60 * 60); // 24 giờ
            
            // Lưu token vào cơ sở dữ liệu
            $sql = "INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, FROM_UNIXTIME(?))";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("isi", $user['id'], $token, $expiry);
            $stmt->execute();
            
            // Gửi email đặt lại mật khẩu
            $reset_link = "http://localhost/secondlife-market/reset-password.php?token=" . $token;
            $subject = "Đặt lại mật khẩu - SecondLife Market";
            $message = "
                <html>
                <head>
                    <title>Đặt lại mật khẩu - SecondLife Market</title>
                </head>
                <body>
                    <h2>Xin chào " . $user['username'] . ",</h2>
                    <p>Chúng tôi nhận được yêu cầu đặt lại mật khẩu cho tài khoản của bạn.</p>
                    <p>Vui lòng nhấp vào liên kết dưới đây để đặt lại mật khẩu:</p>
                    <p><a href='$reset_link'>Đặt lại mật khẩu</a></p>
                    <p>Liên kết này sẽ hết hạn sau 24 giờ.</p>
                    <p>Nếu bạn không yêu cầu đặt lại mật khẩu, vui lòng bỏ qua email này.</p>
                    <p>Trân trọng,<br>Đội ngũ SecondLife Market</p>
                </body>
                </html>
            ";
            
            if (sendEmail($email, $subject, $message)) {
                $success = true;
            } else {
                $errors[] = "Không thể gửi email. Vui lòng thử lại sau.";
            }
        } else {
            // Không tiết lộ email không tồn tại vì lý do bảo mật
            $success = true;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quên mật khẩu - SecondLife Market</title>
    <!-- CSS Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- CSS Tùy chỉnh -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-body p-5">
                        <h2 class="text-center mb-4"><i class="fas fa-key me-2"></i>Quên mật khẩu</h2>
                        
                        <?php if ($success): ?>
                            <div class="alert alert-success">
                                Nếu email của bạn tồn tại trong hệ thống, chúng tôi đã gửi hướng dẫn đặt lại mật khẩu đến email của bạn. Vui lòng kiểm tra hộp thư đến và thư rác.
                            </div>
                            <div class="text-center mt-4">
                                <a href="login.php" class="btn btn-primary">Quay lại đăng nhập</a>
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
                            
                            <p class="text-center mb-4">Nhập email của bạn và chúng tôi sẽ gửi cho bạn hướng dẫn để đặt lại mật khẩu.</p>
                            
                            <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" name="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                                </div>
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary">Gửi yêu cầu đặt lại mật khẩu</button>
                                </div>
                            </form>
                            
                            <div class="text-center mt-4">
                                <p><a href="login.php">Quay lại đăng nhập</a></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
    
    <!-- JS Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- JS Tùy chỉnh -->
    <script src="assets/js/script.js"></script>
</body>
</html>
