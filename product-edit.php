<?php
session_start();
include 'config/db.php';
include 'includes/functions.php';

// Chuyển hướng nếu chưa đăng nhập
if (!isLoggedIn()) {
    redirect('login.php');
}

// Kiểm tra xem ID sản phẩm đã được cung cấp chưa
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    redirect('my-products.php');
}

$product_id = (int)$_GET['id'];
$user_id = $_SESSION['user_id'];

// Lấy thông tin sản phẩm
$sql = "SELECT * FROM products WHERE id = ? AND user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $product_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Nếu sản phẩm không tồn tại hoặc không thuộc về người dùng
if ($result->num_rows == 0) {
    redirect('my-products.php');
}

$product = $result->fetch_assoc();

$errors = [];
$success = false;
$debug_info = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $debug_info[] = "Form đã được gửi";
    $debug_info[] = "Hình ảnh gốc trong DB: " . $product['image'];
    
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
    
    // Xử lý tải lên hình ảnh - ĐÃ SỬA LOGIC
    $final_image = $product['image']; // Giữ hình ảnh hiện tại làm mặc định
    $debug_info[] = "Hình ảnh cuối cùng ban đầu: " . $final_image;
    
    // Chỉ xử lý nếu có hình ảnh mới được tải lên
    if (isset($_FILES['image']) && $_FILES['image']['size'] > 0) {
        $debug_info[] = "Phát hiện hình ảnh mới, đang xử lý tải lên...";
        $uploaded_image = uploadImage($_FILES['image'], 'products');
        if ($uploaded_image) {
            $debug_info[] = "Tải lên thành công: " . $uploaded_image;
            // Xóa hình ảnh cũ nếu tồn tại và khác với hình ảnh mới
            if ($product['image'] && $product['image'] !== $uploaded_image && file_exists("uploads/products/" . $product['image'])) {
                unlink("uploads/products/" . $product['image']);
                $debug_info[] = "Đã xóa hình ảnh cũ: " . $product['image'];
            }
            $final_image = $uploaded_image;
        } else {
            $errors[] = "Không thể tải lên hình ảnh sản phẩm. Vui lòng thử lại.";
            $debug_info[] = "Tải lên thất bại";
        }
    } else {
        $debug_info[] = "Không có hình ảnh mới được tải lên, giữ nguyên hình ảnh hiện tại: " . $final_image;
    }
    
    $debug_info[] = "Hình ảnh cuối cùng để lưu: " . $final_image;
    
    // Nếu không có lỗi, cập nhật sản phẩm
    if (empty($errors)) {
        // FIXED: Make sure we're using the right variable and data types
        $sql = "UPDATE products SET name = ?, description = ?, price = ?, category = ?, `condition` = ?, image = ?, updated_at = NOW() WHERE id = ? AND user_id = ?";
        $stmt = $conn->prepare($sql);
        
        // FIXED: Ensure correct binding - price as double, others as string, IDs as integer
        $stmt->bind_param("ssdsssii", $name, $description, $price, $category, $condition, $final_image, $product_id, $user_id);
        
        $debug_info[] = "SQL: " . $sql;
        $debug_info[] = "Binding: name=$name, desc=$description, price=$price, cat=$category, cond=$condition, img=$final_image, id=$product_id, uid=$user_id";
        
        if ($stmt->execute()) {
            $debug_info[] = "Cập nhật cơ sở dữ liệu thành công";
            $success = true;
            
            // Làm mới dữ liệu sản phẩm từ cơ sở dữ liệu để xác minh
            $verify_sql = "SELECT * FROM products WHERE id = ?";
            $verify_stmt = $conn->prepare($verify_sql);
            $verify_stmt->bind_param("i", $product_id);
            $verify_stmt->execute();
            $verify_result = $verify_stmt->get_result();
            $updated_product = $verify_result->fetch_assoc();
            
            $debug_info[] = "Hình ảnh đã xác minh trong DB sau khi cập nhật: " . $updated_product['image'];
            
            // Cập nhật mảng sản phẩm với dữ liệu mới
            $product = $updated_product;
        } else {
            $errors[] = "Đã xảy ra lỗi khi cập nhật database: " . $conn->error;
            $debug_info[] = "Cập nhật cơ sở dữ liệu thất bại: " . $conn->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chỉnh sửa sản phẩm - 2HandShop</title>
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
                        <h2 class="text-center mb-4"><i class="fas fa-edit me-2"></i>Chỉnh sửa sản phẩm</h2>
                        <?php if ($success): ?>
                            <div class="alert alert-success">
                                Sản phẩm đã được cập nhật thành công!
                                <div class="mt-3">
                                    <a href="product-detail.php?id=<?php echo $product_id; ?>" class="btn btn-primary me-2">Xem sản phẩm</a>
                                    <a href="my-products.php" class="btn btn-outline-primary">Quay lại danh sách</a>
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
                            
                            <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . "?id=" . $product_id); ?>" enctype="multipart/form-data">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Tên sản phẩm</label>
                                    <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label for="description" class="form-label">Mô tả sản phẩm</label>
                                    <textarea class="form-control" id="description" name="description" rows="5" required><?php echo htmlspecialchars($product['description']); ?></textarea>
                                </div>
                                <div class="mb-3">
                                    <label for="price" class="form-label">Giá (VNĐ)</label>
                                    <input type="number" class="form-control" id="price" name="price" value="<?php echo htmlspecialchars($product['price']); ?>" min="1000" step="1000" required>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="category" class="form-label">Danh mục</label>
                                        <select class="form-select" id="category" name="category" required>
                                            <option value="" disabled>Chọn danh mục</option>
                                            <option value="electronics" <?php echo $product['category'] == 'electronics' ? 'selected' : ''; ?>>Điện tử</option>
                                            <option value="furniture" <?php echo $product['category'] == 'furniture' ? 'selected' : ''; ?>>Nội thất</option>
                                            <option value="clothing" <?php echo $product['category'] == 'clothing' ? 'selected' : ''; ?>>Quần áo</option>
                                            <option value="books" <?php echo $product['category'] == 'books' ? 'selected' : ''; ?>>Sách</option>
                                            <option value="toys" <?php echo $product['category'] == 'toys' ? 'selected' : ''; ?>>Đồ chơi</option>
                                            <option value="others" <?php echo $product['category'] == 'others' ? 'selected' : ''; ?>>Khác</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="condition" class="form-label">Tình trạng</label>
                                        <select class="form-select" id="condition" name="condition" required>
                                            <option value="" disabled>Chọn tình trạng</option>
                                            <option value="new" <?php echo $product['condition'] == 'new' ? 'selected' : ''; ?>>Mới</option>
                                            <option value="like_new" <?php echo $product['condition'] == 'like_new' ? 'selected' : ''; ?>>Như mới</option>
                                            <option value="good" <?php echo $product['condition'] == 'good' ? 'selected' : ''; ?>>Tốt</option>
                                            <option value="fair" <?php echo $product['condition'] == 'fair' ? 'selected' : ''; ?>>Khá</option>
                                            <option value="poor" <?php echo $product['condition'] == 'poor' ? 'selected' : ''; ?>>Kém</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="mb-4">
                                    <label for="image" class="form-label">Hình ảnh sản phẩm</label>
                                    <input type="file" class="form-control" id="image" name="image" accept="image/*">
                                    <div class="form-text">Để trống nếu không muốn thay đổi hình ảnh hiện tại.</div>
                                    
                                    <?php if (!empty($product['image']) && $product['image'] !== '0'): ?>
                                        <div class="mt-2">
                                            <p class="text-muted mb-2">Hình ảnh hiện tại:</p>
                                            <?php 
                                            $image_path = "uploads/products/" . $product['image'];
                                            $image_url = $image_path . "?v=" . time(); // Cache busting
                                            ?>
                                            
                                            <?php if (file_exists($image_path)): ?>
                                                <img src="<?php echo htmlspecialchars($image_url); ?>" 
                                                     alt="Hình sản phẩm" 
                                                     class="img-thumbnail" 
                                                     style="max-height: 200px;"
                                                     onerror="this.style.display='none'; document.getElementById('imageError').style.display='block';">
                                                <div id="imageError" style="display: none;" class="alert alert-danger mt-2">
                                                    Không thể tải hình ảnh: <?php echo htmlspecialchars($image_url); ?>
                                                </div>
                                            <?php else: ?>
                                                <div class="alert alert-warning">
                                                    File không tồn tại: <?php echo htmlspecialchars($image_path); ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="alert alert-info mt-2">
                                            Chưa có hình ảnh (giá trị trong DB: "<?php echo htmlspecialchars($product['image']); ?>")
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div id="newImagePreview" class="mt-2" style="display: none;">
                                        <p class="text-success mb-2">Hình ảnh mới sẽ thay thế:</p>
                                        <img id="imagePreview" src="/placeholder.svg" alt="Preview" class="img-thumbnail" style="max-height: 200px;">
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <a href="my-products.php" class="btn btn-outline-secondary">Hủy</a>
                                    <button type="submit" class="btn btn-primary">Cập nhật sản phẩm</button>
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
    <script>
document.getElementById('image').addEventListener('change', function(e) {
    const file = e.target.files[0];
    const newImagePreview = document.getElementById('newImagePreview');
    const imagePreview = document.getElementById('imagePreview');
    
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            imagePreview.src = e.target.result;
            newImagePreview.style.display = 'block';
        };
        reader.readAsDataURL(file);
    } else {
        newImagePreview.style.display = 'none';
    }
});
</script>
</body>
</html>
