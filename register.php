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
    $username = sanitize($_POST['username']);
    $email = sanitize($_POST['email']);
    $password = sanitize($_POST['password']);
    $confirm_password = sanitize($_POST['confirm_password']);
    $phone = sanitize($_POST['phone']);
    
    // Xác thực đầu vào
    if (empty($username)) {
        $errors[] = "Tên đăng nhập không được để trống";
    } elseif (strlen($username) < 3 || strlen($username) > 20) {
        $errors[] = "Tên đăng nhập phải từ 3 đến 20 ký tự";
    }

    
    if (empty($email)) {
        $errors[] = "Email không được để trống";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Email không hợp lệ";
    }
    
    if (empty($password)) {
        $errors[] = "Mật khẩu không được để trống";
    } elseif (strlen($password) < 6) {
        $errors[] = "Mật khẩu phải có ít nhất 6 ký tự";
    }
    
    if ($password !== $confirm_password) {
        $errors[] = "Mật khẩu xác nhận không khớp";
    }

    if (empty($phone)) {
        $errors[] = "Số điện thoại không được để trống";
    } elseif (!preg_match('/^[0-9]{10,11}$/', $phone)) {
        $errors[] = "Số điện thoại phải có 10-11 chữ số";
    }
    
    // Kiểm tra xem tên đăng nhập hoặc email đã tồn tại chưa
    $sql = "SELECT * FROM users WHERE username = ? OR email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if ($user['username'] === $username) {
            $errors[] = "Tên đăng nhập đã tồn tại";
        }
        if ($user['email'] === $email) {
            $errors[] = "Email đã tồn tại";
        }
    }
    
    // Nếu không có lỗi, đăng ký người dùng
    if (empty($errors)) {
        // Mã hóa mật khẩu
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Thêm người dùng vào cơ sở dữ liệu
        $sql = "INSERT INTO users (username, email, password, phone, role, created_at) VALUES (?, ?, ?, ?, 'user', NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssss", $username, $email, $hashed_password, $phone);
        
        if ($stmt->execute()) {
            // Gửi email chào mừng
            $subject = "Chào mừng đến với 2HandShop";
            $message = "
                <html>
                <head>
                    <title>Chào mừng đến với 2HandShop</title>
                </head>
                <body>
                    <h2>Xin chào $username,</h2>
                    <p>Cảm ơn bạn đã đăng ký tài khoản tại 2HandShop.</p>
                    <p>Thông tin tài khoản của bạn:</p>
                    <ul>
                        <li>Tên đăng nhập: $username</li>
                        <li>Email: $email</li>
                        <li>Số điện thoại: $phone</li>
                    </ul>
                    <p>Bạn có thể đăng nhập tại <a href='http://localhost/c2c/login.php'>đây</a>.</p>
                    <p>Trân trọng,<br>Đội ngũ 2HandShop</p>
                </body>
                </html>
            ";
            
            sendEmail($email, $subject, $message);
            
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
    <title>Đăng ký - 2HandShop</title>
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
                        <h2 class="text-center mb-4"><i class="fas fa-user-plus me-2"></i>Đăng ký tài khoản</h2>
                        
                        <?php if ($success): ?>
                            <div class="alert alert-success">
                                Đăng ký thành công! Bạn có thể <a href="login.php">đăng nhập</a> ngay bây giờ.
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    <?php foreach ($errors as $error): ?>
                                        <li><?php echo $error; ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!$success): ?>
                            <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                                <div class="mb-3">
                                    <label for="username" class="form-label">Tên đăng nhập</label>
                                    <input type="text" class="form-control" id="username" name="username" value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" name="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label for="phone" class="form-label">Số điện thoại</label>
                                    <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label for="password" class="form-label">Mật khẩu</label>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                </div>
                                <div class="mb-3">
                                    <label for="confirm_password" class="form-label">Xác nhận mật khẩu</label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                </div>
                                <div class="mb-3 form-check">
                                    <input type="checkbox" class="form-check-input" id="terms" required>
                                    <label class="form-check-label" for="terms">Tôi đồng ý với <a href="#">Điều khoản dịch vụ</a> và <a href="#">Chính sách bảo mật</a></label>
                                </div>
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary">Đăng ký</button>
                                </div>
                            </form>
                        <?php endif; ?>
                        
                        <div class="text-center mt-4">
                            <p>Đã có tài khoản? <a href="login.php">Đăng nhập</a></p>
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
</body>
</html>
