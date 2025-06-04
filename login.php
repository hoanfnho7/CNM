<?php
session_start();
include 'config/db.php';
include 'config/google-config.php';
include 'includes/functions.php';

// Redirect if already logged in
if (isLoggedIn()) {
    // Check if user is admin and redirect to admin panel
    if (isAdmin()) {
        redirect('admin/index.php');
    } else {
        redirect('index.php');
    }
}

$errors = [];

// Handle error messages from Google OAuth
if (isset($_GET['error'])) {
    switch ($_GET['error']) {
        case 'google_auth_failed':
            $errors[] = "Đăng nhập Google thất bại. Vui lòng thử lại.";
            break;
        case 'google_token_failed':
            $errors[] = "Không thể lấy token từ Google. Vui lòng thử lại.";
            break;
        case 'google_user_info_failed':
            $errors[] = "Không thể lấy thông tin người dùng từ Google. Vui lòng thử lại.";
            break;
        case 'account_suspended':
            $errors[] = "Tài khoản của bạn đã bị khóa. Vui lòng liên hệ quản trị viên.";
            break;
        case 'registration_failed':
            $errors[] = "Đăng ký tài khoản thất bại. Vui lòng thử lại.";
            break;
        case 'google_auth_error':
            $errors[] = "Có lỗi xảy ra trong quá trình đăng nhập Google. Vui lòng thử lại.";
            break;
    }
}

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
        $sql = "SELECT * FROM users WHERE email = ?";
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
                    $errors[] = "Tài khoản của bạn đã bị khóa. Vui lòng liên hệ quản trị viên.";
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
                    
                    // Redirect based on role
                    if ($user['role'] == 'admin') {
                        redirect('admin/index.php');
                    } else {
                        redirect('index.php');
                    }
                }
            } else {
                $errors[] = "Email hoặc mật khẩu không đúng";
            }
        } else {
            $errors[] = "Email hoặc mật khẩu không đúng";
        }
    }
}

// Get Google OAuth URL
$google_oauth_url = getGoogleOAuthURL();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập - SecondLife Market</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .google-btn {
            background-color: #4285f4;
            border: none;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 5px;
            font-weight: 500;
            transition: background-color 0.3s;
            width: 100%;
        }
        .google-btn:hover {
            background-color: #357ae8;
            color: white;
            text-decoration: none;
        }
        .google-btn i {
            margin-right: 8px;
        }
        .divider {
            text-align: center;
            margin: 20px 0;
            position: relative;
        }
        .divider::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background: #dee2e6;
        }
        .divider span {
            background: white;
            padding: 0 15px;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">Đăng nhập</h4>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    <?php foreach ($errors as $error): ?>
                                        <li><?php echo $error; ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Google Login Button -->
                        <div class="mb-4">
                            <a href="<?php echo $google_oauth_url; ?>" class="google-btn">
                                <i class="fab fa-google"></i>
                                Đăng nhập bằng Google
                            </a>
                        </div>
                        
                        <div class="divider">
                            <span>hoặc</span>
                        </div>
                        
                        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Mật khẩu</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="remember" name="remember">
                                <label class="form-check-label" for="remember">Ghi nhớ đăng nhập</label>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">Đăng nhập</button>
                            </div>
                        </form>
                        
                        <div class="mt-3 text-center">
                            <a href="forgot-password.php">Quên mật khẩu?</a>
                        </div>
                    </div>
                    <div class="card-footer text-center">
                        <p class="mb-0">Chưa có tài khoản? <a href="register.php">Đăng ký ngay</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
