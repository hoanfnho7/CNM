<?php
session_start();
include 'config/db.php';
include 'includes/functions.php';

// Redirect if not logged in
if (!isLoggedIn()) {
    redirect('login.php');
}

$errors = [];
$success = false;

// Get user data
$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $username = sanitize($_POST['username']);
    $phone = sanitize($_POST['phone']);
    $address = sanitize($_POST['address']);
    $current_password = isset($_POST['current_password']) ? sanitize($_POST['current_password']) : '';
    $new_password = isset($_POST['new_password']) ? sanitize($_POST['new_password']) : '';
    $confirm_password = isset($_POST['confirm_password']) ? sanitize($_POST['confirm_password']) : '';
    
    // Validate input
    if (empty($username)) {
        $errors[] = "Tên đăng nhập không được để trống";
    } elseif (strlen($username) < 3 || strlen($username) > 20) {
        $errors[] = "Tên đăng nhập phải từ 3 đến 20 ký tự";
    }
    
    // Check if username already exists (if changed)
    if ($username !== $user['username']) {
        $sql = "SELECT * FROM users WHERE username = ? AND id != ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $username, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $errors[] = "Tên đăng nhập đã tồn tại";
        }
    }
    
    // Validate password change if requested
    if (!empty($current_password) || !empty($new_password) || !empty($confirm_password)) {
        if (empty($current_password)) {
            $errors[] = "Vui lòng nhập mật khẩu hiện tại";
        } elseif (!password_verify($current_password, $user['password'])) {
            $errors[] = "Mật khẩu hiện tại không đúng";
        }
        
        if (empty($new_password)) {
            $errors[] = "Vui lòng nhập mật khẩu mới";
        } elseif (strlen($new_password) < 6) {
            $errors[] = "Mật khẩu mới phải có ít nhất 6 ký tự";
        }
        
        if ($new_password !== $confirm_password) {
            $errors[] = "Mật khẩu xác nhận không khớp";
        }
    }
    
    // Handle avatar upload
    $avatar = $user['avatar'];
    if (isset($_FILES['avatar']) && $_FILES['avatar']['size'] > 0) {
        $uploaded_avatar = uploadImage($_FILES['avatar'], 'avatars');
        if ($uploaded_avatar) {
            $avatar = $uploaded_avatar;
        } else {
            $errors[] = "Không thể tải lên ảnh đại diện. Vui lòng thử lại.";
        }
    }
    
    // If no errors, update user
    if (empty($errors)) {
        // Update user in database
        $sql = "UPDATE users SET username = ?, phone = ?, address = ?, avatar = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssi", $username, $phone, $address, $avatar, $user_id);
        
        if ($stmt->execute()) {
            // Update password if requested
            if (!empty($new_password)) {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $sql = "UPDATE users SET password = ? WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("si", $hashed_password, $user_id);
                $stmt->execute();
            }
            
            // Update session variables
            $_SESSION['username'] = $username;
            $_SESSION['user_avatar'] = $avatar;
            
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
    <title>Hồ sơ cá nhân - 2HandShop</title>
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
        <div class="row">
            <div class="col-md-12">
                <div class="profile-header text-center py-5">
                    <div class="mb-3">
                        <?php if (!empty($user['avatar'])): ?>
                            <img src="uploads/avatars/<?php echo $user['avatar']; ?>" alt="Avatar" class="rounded-circle profile-avatar">
                        <?php else: ?>
                            <i class="fas fa-user-circle fa-6x"></i>
                        <?php endif; ?>
                    </div>
                    <h1><?php echo $user['username']; ?></h1>
                    <p>Thành viên từ <?php echo date('d/m/Y', strtotime($user['created_at'])); ?></p>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-3">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Menu</h5>
                    </div>
                    <div class="list-group list-group-flush">
                        <a href="profile.php" class="list-group-item list-group-item-action active">
                            <i class="fas fa-user me-2"></i>Hồ sơ cá nhân
                        </a>
                        <a href="my-products.php" class="list-group-item list-group-item-action">
                            <i class="fas fa-box me-2"></i>Sản phẩm của tôi
                        </a>
                        <a href="favorites.php" class="list-group-item list-group-item-action">
                            <i class="fas fa-heart me-2"></i>Sản phẩm yêu thích
                        </a>
                        <a href="messages.php" class="list-group-item list-group-item-action">
                            <i class="fas fa-envelope me-2"></i>Tin nhắn
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-9">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Cập nhật thông tin cá nhân</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($success): ?>
                            <div class="alert alert-success">
                                Thông tin cá nhân đã được cập nhật thành công.
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
                        
                        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label for="avatar" class="form-label">Ảnh đại diện</label>
                                <input type="file" class="form-control" id="avatar" name="avatar" accept="image/*">
                                <div class="form-text">Để trống nếu không muốn thay đổi ảnh đại diện.</div>
                            </div>
                            <div class="mb-3">
                                <label for="username" class="form-label">Tên đăng nhập</label>
                                <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
                                <div class="form-text">Email không thể thay đổi.</div>
                            </div>
                            <div class="mb-3">
                                <label for="phone" class="form-label">Số điện thoại</label>
                                <input type="text" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>">
                            </div>
                            <div class="mb-3">
                                <label for="address" class="form-label">Địa chỉ</label>
                                <textarea class="form-control" id="address" name="address" rows="3"><?php echo htmlspecialchars($user['address']); ?></textarea>
                            </div>
                            
                            <h5 class="mt-4 mb-3">Đổi mật khẩu</h5>
                            <div class="mb-3">
                                <label for="current_password" class="form-label">Mật khẩu hiện tại</label>
                                <input type="password" class="form-control" id="current_password" name="current_password">
                            </div>
                            <div class="mb-3">
                                <label for="new_password" class="form-label">Mật khẩu mới</label>
                                <input type="password" class="form-control" id="new_password" name="new_password">
                            </div>
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Xác nhận mật khẩu mới</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                            </div>
                            <div class="form-text mb-3">Để trống nếu không muốn thay đổi mật khẩu.</div>
                            
                            <button type="submit" class="btn btn-primary">Cập nhật</button>
                        </form>
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
