<?php
session_start();
include 'config/db.php';
include 'includes/functions.php';

// Chuyển hướng nếu chưa đăng nhập
if (!isLoggedIn()) {
    redirect('login.php');
}

$errors = [];
$success = false;
$product_id = 0;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Lấy dữ liệu từ form
    $name = sanitize($_POST['name']);
    $description = sanitize($_POST['description']);
    $price = sanitize($_POST['price']);
    $category = sanitize($_POST['category']);
    $condition = sanitize($_POST['condition']);
    
    // Xác thực đầu vào
    if (empty($name)) {
        $errors[] = "Tên sản phẩm không được để trống";
    }
    
    if (empty($description)) {
        $errors[] = "Mô tả sản phẩm không được để trống";
    }
    
    if (empty($price)) {
        $errors[] = "Giá sản phẩm không được để trống";
    } elseif (!is_numeric($price) || $price <= 0) {
        $errors[] = "Giá sản phẩm phải là số dương";
    }
    
    if (empty($category)) {
        $errors[] = "Danh mục sản phẩm không được để trống";
    }
    
    if (empty($condition)) {
        $errors[] = "Tình trạng sản phẩm không được để trống";
    }
    
    // Xử lý tải lên hình ảnh
    $image = '';
    if (isset($_FILES['image']) && $_FILES['image']['size'] > 0) {
        $uploaded_image = uploadImage($_FILES['image'], 'products');
        if ($uploaded_image) {
            $image = $uploaded_image;
        } else {
            $errors[] = "Không thể tải lên hình ảnh sản phẩm. Vui lòng thử lại.";
        }
    } else {
        $errors[] = "Vui lòng tải lên hình ảnh sản phẩm";
    }
    
    // Nếu không có lỗi, tạo sản phẩm
    if (empty($errors)) {
        $user_id = $_SESSION['user_id'];
        $status = isAdmin() ? 'approved' : 'pending'; // Tự động duyệt nếu là admin
        
        // Thêm sản phẩm vào cơ sở dữ liệu
        $sql = "INSERT INTO products (user_id, name, description, price, category, `condition`, image, status, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("issdssss", $user_id, $name, $description, $price, $category, $condition, $image, $status);
        
        if ($stmt->execute()) {
            $product_id = $stmt->insert_id;
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
    <title>Đăng sản phẩm mới - 2HandShop</title>
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
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-body p-5">
                        <h2 class="text-center mb-4"><i class="fas fa-box-open me-2"></i>Đăng sản phẩm mới</h2>
                        
                        <?php if ($success): ?>
                            <div class="alert alert-success">
                                Sản phẩm đã được đăng thành công!
                                <?php if (!isAdmin()): ?>
                                    Sản phẩm của bạn đang chờ duyệt.
                                <?php endif; ?>
                                <div class="mt-3">
                                    <a href="product-detail.php?id=<?php echo $product_id; ?>" class="btn btn-primary me-2">Xem sản phẩm</a>
                                    <a href="product-create.php" class="btn btn-outline-primary">Đăng sản phẩm khác</a>
                                </div>
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
                            
                            <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" enctype="multipart/form-data">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Tên sản phẩm</label>
                                    <input type="text" class="form-control" id="name" name="name" value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label for="description" class="form-label">Mô tả sản phẩm</label>
                                    <textarea class="form-control" id="description" name="description" rows="5" required><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                                </div>
                                <div class="mb-3">
                                    <label for="price" class="form-label">Giá (VNĐ)</label>
                                    <input type="number" class="form-control" id="price" name="price" value="<?php echo isset($_POST['price']) ? htmlspecialchars($_POST['price']) : ''; ?>" min="1000" step="1000" required>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="category" class="form-label">Danh mục</label>
                                        <select class="form-select" id="category" name="category" required>
                                            <option value="" disabled <?php echo !isset($_POST['category']) ? 'selected' : ''; ?>>Chọn danh mục</option>
                                            <option value="electronics" <?php echo (isset($_POST['category']) && $_POST['category'] == 'electronics') ? 'selected' : ''; ?>>Điện tử</option>
                                            <option value="furniture" <?php echo (isset($_POST['category']) && $_POST['category'] == 'furniture') ? 'selected' : ''; ?>>Nội thất</option>
                                            <option value="clothing" <?php echo (isset($_POST['category']) && $_POST['category'] == 'clothing') ? 'selected' : ''; ?>>Quần áo</option>
                                            <option value="books" <?php echo (isset($_POST['category']) && $_POST['category'] == 'books') ? 'selected' : ''; ?>>Sách</option>
                                            <option value="toys" <?php echo (isset($_POST['category']) && $_POST['category'] == 'toys') ? 'selected' : ''; ?>>Đồ chơi</option>
                                            <option value="others" <?php echo (isset($_POST['category']) && $_POST['category'] == 'others') ? 'selected' : ''; ?>>Khác</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="condition" class="form-label">Tình trạng</label>
                                        <select class="form-select" id="condition" name="condition" required>
                                            <option value="" disabled <?php echo !isset($_POST['condition']) ? 'selected' : ''; ?>>Chọn tình trạng</option>
                                            <option value="new" <?php echo (isset($_POST['condition']) && $_POST['condition'] == 'new') ? 'selected' : ''; ?>>Mới</option>
                                            <option value="like_new" <?php echo (isset($_POST['condition']) && $_POST['condition'] == 'like_new') ? 'selected' : ''; ?>>Như mới</option>
                                            <option value="good" <?php echo (isset($_POST['condition']) && $_POST['condition'] == 'good') ? 'selected' : ''; ?>>Tốt</option>
                                            <option value="fair" <?php echo (isset($_POST['condition']) && $_POST['condition'] == 'fair') ? 'selected' : ''; ?>>Khá</option>
                                            <option value="poor" <?php echo (isset($_POST['condition']) && $_POST['condition'] == 'poor') ? 'selected' : ''; ?>>Kém</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="mb-4">
                                    <label for="image" class="form-label">Hình ảnh sản phẩm</label>
                                    <input type="file" class="form-control" id="image" name="image" accept="image/*" required>
                                    <div class="form-text">Tải lên hình ảnh rõ ràng của sản phẩm. Kích thước tối đa: 5MB.</div>
                                    <div class="mt-2">
                                        <img id="imagePreview" src="#" alt="Preview" class="img-thumbnail" style="max-height: 200px; display: none;">
                                    </div>
                                </div>
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary">Đăng sản phẩm</button>
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
