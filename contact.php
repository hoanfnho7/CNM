<?php
session_start();
include 'config/db.php';
include 'includes/functions.php';

$errors = [];
$success = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Lấy dữ liệu từ form
    $name = sanitize($_POST['name']);
    $email = sanitize($_POST['email']);
    $subject = sanitize($_POST['subject']);
    $message = sanitize($_POST['message']);
    
    // Kiểm tra dữ liệu đầu vào
    if (empty($name)) {
        $errors[] = "Họ tên không được để trống";
    }
    
    if (empty($email)) {
        $errors[] = "Email không được để trống";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Email không hợp lệ";
    }
    
    if (empty($subject)) {
        $errors[] = "Tiêu đề không được để trống";
    }
    
    if (empty($message)) {
        $errors[] = "Nội dung không được để trống";
    }
    
    // Nếu không có lỗi, gửi email
    if (empty($errors)) {
        // Trong ứng dụng thực tế, bạn sẽ gửi email tại đây
        // Hiện tại, chúng ta chỉ giả lập thành công
        $success = true;
        
        // Bạn cũng có thể lưu tin nhắn liên hệ vào cơ sở dữ liệu
        $sql = "INSERT INTO contacts (name, email, subject, message, created_at) VALUES (?, ?, ?, ?, NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssss", $name, $email, $subject, $message);
        $stmt->execute();
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liên hệ - 2HandShop</title>
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
        <div class="row">
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-body p-5">
                        <h2 class="mb-4">Liên hệ với chúng tôi</h2>
                        
                        <?php if ($success): ?>
                            <div class="alert alert-success">
                                Cảm ơn bạn đã liên hệ với chúng tôi! Chúng tôi sẽ phản hồi trong thời gian sớm nhất.
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
                        
                        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                            <div class="mb-3">
                                <label for="name" class="form-label">Họ tên</label>
                                <input type="text" class="form-control" id="name" name="name" value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="subject" class="form-label">Tiêu đề</label>
                                <input type="text" class="form-control" id="subject" name="subject" value="<?php echo isset($_POST['subject']) ? htmlspecialchars($_POST['subject']) : ''; ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="message" class="form-label">Nội dung</label>
                                <textarea class="form-control" id="message" name="message" rows="5" required><?php echo isset($_POST['message']) ? htmlspecialchars($_POST['message']) : ''; ?></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Gửi tin nhắn</button>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-body p-5">
                        <h2 class="mb-4">Thông tin liên hệ</h2>
                        
                        <div class="d-flex mb-4">
                            <div class="me-3">
                                <i class="fas fa-map-marker-alt fa-2x text-primary"></i>
                            </div>
                            <div>
                                <h5>Địa chỉ</h5>
                                <p class="mb-0">12 Nguyễn Văn Bảo, Quận Gò Vấp, TP. Hồ Chí Minh</p>
                            </div>
                        </div>
                        
                        <div class="d-flex mb-4">
                            <div class="me-3">
                                <i class="fas fa-phone fa-2x text-primary"></i>
                            </div>
                            <div>
                                <h5>Điện thoại</h5>
                                <p class="mb-0">(+84) 939 027 936</p>
                            </div>
                        </div>
                        
                        <div class="d-flex mb-4">
                            <div class="me-3">
                                <i class="fas fa-envelope fa-2x text-primary"></i>
                            </div>
                            <div>
                                <h5>Email</h5>
                                <p class="mb-0">lhoangnhon09@gmail.com</p>
                            </div>
                        </div>
                        
                        <div class="d-flex mb-4">
                            <div class="me-3">
                                <i class="fas fa-clock fa-2x text-primary"></i>
                            </div>
                            <div>
                                <h5>Giờ làm việc</h5>
                                <p class="mb-0">Thứ Hai - Thứ Sáu: 8:00 - 17:00<br>Thứ Bảy: 8:00 - 12:00</p>
                            </div>
                        </div>
                        
                        <div class="mt-5">
                            <h5>Kết nối với chúng tôi</h5>
                            <div class="mt-3">
                                <a href="#" class="text-primary me-3"><i class="fab fa-facebook fa-2x"></i></a>
                                <a href="#" class="text-info me-3"><i class="fab fa-twitter fa-2x"></i></a>
                                <a href="#" class="text-danger me-3"><i class="fab fa-instagram fa-2x"></i></a>
                                <a href="#" class="text-danger"><i class="fab fa-youtube fa-2x"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body p-0">
                        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3918.8585960932614!2d106.6867717!3d10.822131499999998!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3174deb3ef536f31%3A0x8b7bb8b7c956157b!2zVHLGsOG7nW5nIMSQ4bqhaSBo4buNYyBDw7RuZyBuZ2hp4buHcCBUUC5IQ00!5e0!3m2!1svi!2s!4v1748316472619!5m2!1svi!2s" width="100%" height="450" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
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
