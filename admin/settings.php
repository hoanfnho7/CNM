<?php
session_start();
include '../config/db.php';
include '../includes/functions.php';

// Redirect if not admin
if (!isAdmin()) {
    redirect('../index.php');
}

$errors = [];
$success = false;

// Get current settings
$settings = [];
$sql = "SELECT * FROM settings";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
}

// Default settings if not in database
$default_settings = [
    'site_name' => 'SecondLife Market',
    'site_description' => 'Nền tảng mua bán đồ cũ uy tín, an toàn và ti��n lợi',
    'admin_email' => 'admin@secondlifemarket.com',
    'items_per_page' => '12',
    'maintenance_mode' => '0',
    'allow_registrations' => '1',
    'auto_approve_products' => '0',
    'enable_messaging' => '1',
    'enable_favorites' => '1',
    'enable_reports' => '1',
    'footer_text' => '&copy; ' . date('Y') . ' SecondLife Market. All rights reserved.'
];

// Merge with defaults
foreach ($default_settings as $key => $value) {
    if (!isset($settings[$key])) {
        $settings[$key] = $value;
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $site_name = sanitize($_POST['site_name']);
    $site_description = sanitize($_POST['site_description']);
    $admin_email = sanitize($_POST['admin_email']);
    $items_per_page = sanitize($_POST['items_per_page']);
    $maintenance_mode = isset($_POST['maintenance_mode']) ? '1' : '0';
    $allow_registrations = isset($_POST['allow_registrations']) ? '1' : '0';
    $auto_approve_products = isset($_POST['auto_approve_products']) ? '1' : '0';
    $enable_messaging = isset($_POST['enable_messaging']) ? '1' : '0';
    $enable_favorites = isset($_POST['enable_favorites']) ? '1' : '0';
    $enable_reports = isset($_POST['enable_reports']) ? '1' : '0';
    $footer_text = sanitize($_POST['footer_text']);
    
    // Validate input
    if (empty($site_name)) {
        $errors[] = "Tên trang web không được để trống";
    }
    
    if (empty($admin_email)) {
        $errors[] = "Email quản trị viên không được để trống";
    } elseif (!filter_var($admin_email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Email quản trị viên không hợp lệ";
    }
    
    if (!is_numeric($items_per_page) || $items_per_page < 1) {
        $errors[] = "Số mục trên mỗi trang phải là số dương";
    }
    
    // If no errors, update settings
    if (empty($errors)) {
        // Update settings in database
        $update_settings = [
            'site_name' => $site_name,
            'site_description' => $site_description,
            'admin_email' => $admin_email,
            'items_per_page' => $items_per_page,
            'maintenance_mode' => $maintenance_mode,
            'allow_registrations' => $allow_registrations,
            'auto_approve_products' => $auto_approve_products,
            'enable_messaging' => $enable_messaging,
            'enable_favorites' => $enable_favorites,
            'enable_reports' => $enable_reports,
            'footer_text' => $footer_text
        ];
        
        foreach ($update_settings as $key => $value) {
            $sql = "INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) 
                    ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $key, $value);
            $stmt->execute();
        }
        
        $success = true;
        
        // Update settings array
        $settings = $update_settings;
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cài đặt hệ thống - SecondLife Market</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <?php include 'sidebar.php'; ?>
            
            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Cài đặt hệ thống</h1>
                </div>
                
                <?php if ($success): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>Cài đặt đã được cập nhật thành công.
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo $error; ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold">Cài đặt chung</h6>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="site_name" class="form-label">Tên trang web</label>
                                    <input type="text" class="form-control" id="site_name" name="site_name" value="<?php echo htmlspecialchars($settings['site_name']); ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="admin_email" class="form-label">Email quản trị viên</label>
                                    <input type="email" class="form-control" id="admin_email" name="admin_email" value="<?php echo htmlspecialchars($settings['admin_email']); ?>" required>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="site_description" class="form-label">Mô tả trang web</label>
                                <textarea class="form-control" id="site_description" name="site_description" rows="2"><?php echo htmlspecialchars($settings['site_description']); ?></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label for="footer_text" class="form-label">Nội dung footer</label>
                                <textarea class="form-control" id="footer_text" name="footer_text" rows="2"><?php echo htmlspecialchars($settings['footer_text']); ?></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label for="items_per_page" class="form-label">Số mục trên mỗi trang</label>
                                <input type="number" class="form-control" id="items_per_page" name="items_per_page" value="<?php echo htmlspecialchars($settings['items_per_page']); ?>" min="1" required>
                            </div>
                            
                            <h5 class="mt-4 mb-3">Cài đặt tính năng</h5>
                            
                            <div class="mb-3 form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="maintenance_mode" name="maintenance_mode" <?php echo $settings['maintenance_mode'] == '1' ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="maintenance_mode">Chế độ bảo trì</label>
                                <div class="form-text">Khi bật, chỉ quản trị viên mới có thể truy cập trang web.</div>
                            </div>
                            
                            <div class="mb-3 form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="allow_registrations" name="allow_registrations" <?php echo $settings['allow_registrations'] == '1' ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="allow_registrations">Cho phép đăng ký tài khoản mới</label>
                            </div>
                            
                            <div class="mb-3 form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="auto_approve_products" name="auto_approve_products" <?php echo $settings['auto_approve_products'] == '1' ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="auto_approve_products">Tự động duyệt sản phẩm</label>
                                <div class="form-text">Khi bật, sản phẩm mới sẽ được duyệt tự động mà không cần quản trị viên xác nhận.</div>
                            </div>
                            
                            <div class="mb-3 form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="enable_messaging" name="enable_messaging" <?php echo $settings['enable_messaging'] == '1' ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="enable_messaging">Bật tính năng nhắn tin</label>
                            </div>
                            
                            <div class="mb-3 form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="enable_favorites" name="enable_favorites" <?php echo $settings['enable_favorites'] == '1' ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="enable_favorites">Bật tính năng yêu thích</label>
                            </div>
                            
                            <div class="mb-3 form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="enable_reports" name="enable_reports" <?php echo $settings['enable_reports'] == '1' ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="enable_reports">Bật tính năng báo cáo</label>
                            </div>
                            
                            <div class="mt-4">
                                <button type="submit" class="btn btn-primary">Lưu cài đặt</button>
                            </div>
                        </form>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="../assets/js/script.js"></script>
</body>
</html>
