<?php
session_start();
include '../config/db.php';
include '../includes/functions.php';

// Redirect if already logged in as admin
if (isLoggedIn() && isAdmin()) {
    redirect('index.php');
}

$errors = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $email = sanitize($_POST['email']);
    $password = sanitize($_POST['password']);
    
    // Validate input
    if (empty($email)) {
        $errors[] = "Email không được để trống";
    }
    
    if (empty($password)) {
        $errors[] = "Mật khẩu không được để trống";
    }
    
    // If no errors, attempt login
    if (empty($errors)) {
        // Get user from database
        $sql = "SELECT * FROM users WHERE email = ? AND role = 'admin'";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            
            // Verify password
            if (password_verify($password, $user['password'])) {
                // Check if account is active
                if ($user['status'] !== 'active') {
                    $errors[] = "Tài khoản của bạn đã bị khóa. Vui lòng liên hệ quản trị viên khác.";
                } else {
                    // Set session variables
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['user_role'] = $user['role'];
                    $_SESSION['user_avatar'] = $user['avatar'];
                    
                    // Update last login time
                    $sql = "UPDATE users SET last_login = NOW() WHERE id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("i", $user['id']);
                    $stmt->execute();
                    
                    // Log admin login
                    $sql = "INSERT INTO system_logs (log_type, message, user_info, ip_address, created_at) 
                            VALUES ('security', 'Admin login successful', ?, ?, NOW())";
                    $stmt = $conn->prepare($sql);
                    $user_info = $user['username'] . ' (ID: ' . $user['id'] . ')';
                    $ip = $_SERVER['REMOTE_ADDR'];
                    $stmt->bind_param("ss", $user_info, $ip);
                    $stmt->execute();
                    
                    // Redirect to admin dashboard
                    redirect('index.php');
                }
            } else {
                $errors[] = "Email hoặc mật khẩu không đúng";
                
                // Log failed login attempt
                $sql = "INSERT INTO system_logs (log_type, message, user_info, ip_address, created_at) 
                        VALUES ('security', 'Failed admin login attempt', ?, ?, NOW())";
                $stmt = $conn->prepare($sql);
                $user_info = 'Email: ' . $email;
                $ip = $_SERVER['REMOTE_ADDR'];
                $stmt->bind_param("ss", $user_info, $ip);
                $stmt->execute();
            }
        } else {
            $errors[] = "Email hoặc mật khẩu không đúng";
            
            // Log failed login attempt
            $sql = "INSERT INTO system_logs (log_type, message, user_info, ip_address, created_at) 
                    VALUES ('security', 'Failed admin login attempt', ?, ?, NOW())";
            $stmt = $conn->prepare($sql);
            $user_info = 'Email: ' . $email;
            $ip = $_SERVER['REMOTE_ADDR'];
            $stmt->bind_param("ss", $user_info, $ip);
            $stmt->execute();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập quản trị - SecondLife Market</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body {
            background-color: #f8f9fc;
        }
        .login-card {
            max-width: 400px;
            margin: 100px auto;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-card">
            <div class="card shadow">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <h1 class="h4 text-gray-900">
                            <i class="fas fa-recycle me-2"></i>SecondLife Market
                        </h1>
                        <h2 class="h5 text-gray-700">Đăng nhập quản trị</h2>
                    </div>
                    
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo $error; ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Mật khẩu</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Đăng nhập</button>
                        </div>
                    </form>
                    
                    <div class="text-center mt-4">
                        <a href="../index.php" class="text-decoration-none">
                            <i class="fas fa-arrow-left me-1"></i>Quay lại trang chủ
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
