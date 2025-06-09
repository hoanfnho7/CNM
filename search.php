<?php
session_start();
include 'config/db.php';
include 'includes/functions.php';

// Lấy các tham số tìm kiếm
$keyword = isset($_GET['keyword']) ? sanitize($_GET['keyword']) : '';
$category = isset($_GET['category']) ? sanitize($_GET['category']) : '';
$condition = isset($_GET['condition']) ? sanitize($_GET['condition']) : '';
$min_price = isset($_GET['min_price']) && is_numeric($_GET['min_price']) ? (int)$_GET['min_price'] : 0;
$max_price = isset($_GET['max_price']) && is_numeric($_GET['max_price']) ? (int)$_GET['max_price'] : 0;
$sort = isset($_GET['sort']) ? sanitize($_GET['sort']) : 'newest';

// Xây dựng truy vấn
$sql = "SELECT p.*, u.username, u.avatar FROM products p 
        JOIN users u ON p.user_id = u.id 
        WHERE p.status = 'approved'";
$params = [];
$types = "";

if (!empty($keyword)) {
    $sql .= " AND (p.name LIKE ? OR p.description LIKE ?)";
    $keyword_param = "%" . $keyword . "%";
    $params[] = $keyword_param;
    $params[] = $keyword_param;
    $types .= "ss";
}

if (!empty($category)) {
    $sql .= " AND p.category = ?";
    $params[] = $category;
    $types .= "s";
}

if (!empty($condition)) {
    $sql .= " AND p.condition = ?";
    $params[] = $condition;
    $types .= "s";
}

if ($min_price > 0) {
    $sql .= " AND p.price >= ?";
    $params[] = $min_price;
    $types .= "i";
}

if ($max_price > 0) {
    $sql .= " AND p.price <= ?";
    $params[] = $max_price;
    $types .= "i";
}

// Thêm sắp xếp
switch ($sort) {
    case 'price_asc':
        $sql .= " ORDER BY p.price ASC";
        break;
    case 'price_desc':
        $sql .= " ORDER BY p.price DESC";
        break;
    case 'oldest':
        $sql .= " ORDER BY p.created_at ASC";
        break;
    case 'newest':
    default:
        $sql .= " ORDER BY p.created_at DESC";
        break;
}

// Chuẩn bị và thực thi truy vấn
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// Định dạng điều kiện để hiển thị
$condition_display = [
    'new' => 'Mới',
    'like_new' => 'Như mới',
    'good' => 'Tốt',
    'fair' => 'Khá',
    'poor' => 'Kém'
];

// Định dạng danh mục để hiển thị
$category_display = [
    'electronics' => 'Điện tử',
    'furniture' => 'Nội thất',
    'clothing' => 'Quần áo',
    'books' => 'Sách',
    'toys' => 'Đồ chơi',
    'others' => 'Khác'
];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tìm kiếm - SecondLife Market</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container mt-4">
        <div class="row">
            <!-- Filters Sidebar -->
            <div class="col-md-3">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Bộ lọc</h5>
                    </div>
                    <div class="card-body">
                        <form action="search.php" method="GET">
                            <div class="mb-3">
                                <label for="keyword" class="form-label">Từ khóa</label>
                                <input type="text" class="form-control" id="keyword" name="keyword" value="<?php echo htmlspecialchars($keyword); ?>">
                            </div>
                            
                            <div class="mb-3">
                                <label for="category" class="form-label">Danh mục</label>
                                <select class="form-select" id="category" name="category">
                                    <option value="">Tất cả danh mục</option>
                                    <option value="electronics" <?php echo $category == 'electronics' ? 'selected' : ''; ?>>Điện tử</option>
                                    <option value="furniture" <?php echo $category == 'furniture' ? 'selected' : ''; ?>>Nội thất</option>
                                    <option value="clothing" <?php echo $category == 'clothing' ? 'selected' : ''; ?>>Quần áo</option>
                                    <option value="books" <?php echo $category == 'books' ? 'selected' : ''; ?>>Sách</option>
                                    <option value="toys" <?php echo $category == 'toys' ? 'selected' : ''; ?>>Đồ chơi</option>
                                    <option value="others" <?php echo $category == 'others' ? 'selected' : ''; ?>>Khác</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="condition" class="form-label">Tình trạng</label>
                                <select class="form-select" id="condition" name="condition">
                                    <option value="">Tất cả tình trạng</option>
                                    <option value="new" <?php echo $condition == 'new' ? 'selected' : ''; ?>>Mới</option>
                                    <option value="like_new" <?php echo $condition == 'like_new' ? 'selected' : ''; ?>>Như mới</option>
                                    <option value="good" <?php echo $condition == 'good' ? 'selected' : ''; ?>>Tốt</option>
                                    <option value="fair" <?php echo $condition == 'fair' ? 'selected' : ''; ?>>Khá</option>
                                    <option value="poor" <?php echo $condition == 'poor' ? 'selected' : ''; ?>>Kém</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Khoảng giá</label>
                                <div class="row g-2">
                                    <div class="col-6">
                                        <input type="number" class="form-control" name="min_price" placeholder="Từ" value="<?php echo $min_price > 0 ? $min_price : ''; ?>">
                                    </div>
                                    <div class="col-6">
                                        <input type="number" class="form-control" name="max_price" placeholder="Đến" value="<?php echo $max_price > 0 ? $max_price : ''; ?>">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="sort" class="form-label">Sắp xếp theo</label>
                                <select class="form-select" id="sort" name="sort">
                                    <option value="newest" <?php echo $sort == 'newest' ? 'selected' : ''; ?>>Mới nhất</option>
                                    <option value="oldest" <?php echo $sort == 'oldest' ? 'selected' : ''; ?>>Cũ nhất</option>
                                    <option value="price_asc" <?php echo $sort == 'price_asc' ? 'selected' : ''; ?>>Giá tăng dần</option>
                                    <option value="price_desc" <?php echo $sort == 'price_desc' ? 'selected' : ''; ?>>Giá giảm dần</option>
                                </select>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">Áp dụng</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Search Results -->
            <div class="col-md-9">
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Kết quả tìm kiếm</h5>
                        <span class="text-muted"><?php echo $result->num_rows; ?> sản phẩm</span>
                    </div>
                    <div class="card-body">
                        <?php if ($result->num_rows > 0): ?>
                            <div class="row">
                                <?php while($row = $result->fetch_assoc()): ?>
                                    <div class="col-md-4 mb-4">
                                        <div class="card h-100">
                                            <img src="uploads/products/<?php echo $row['image']; ?>" class="card-img-top" alt="<?php echo $row['name']; ?>" style="height: 200px; object-fit: cover;">
                                            <div class="card-body">
                                                <h5 class="card-title"><?php echo $row['name']; ?></h5>
                                                <p class="card-text text-danger fw-bold"><?php echo number_format($row['price']); ?> VNĐ</p>
                                                <p class="card-text"><small class="text-muted">Tình trạng: <?php echo $condition_display[$row['condition']]; ?></small></p>
                                            </div>
                                            <div class="card-footer bg-white d-flex justify-content-between align-items-center">
                                                <small class="text-muted">Đăng bởi: <?php echo $row['username']; ?></small>
                                                <a href="product-detail.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-outline-primary">Xem chi tiết</a>
                                            </div>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-5">
                                <i class="fas fa-search fa-3x mb-3 text-muted"></i>
                                <h4>Không tìm thấy sản phẩm nào</h4>
                                <p class="text-muted">Hãy thử tìm kiếm với từ khóa khác hoặc điều chỉnh bộ lọc.</p>
                            </div>
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
