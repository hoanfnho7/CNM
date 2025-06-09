<?php
session_start();
include 'config/db.php';
include 'includes/functions.php';

// Chuyển hướng nếu chưa đăng nhập
if (!isLoggedIn()) {
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];

// Lấy sản phẩm với phân trang
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 8;
$offset = ($page - 1) * $limit;

$status = isset($_GET['status']) ? sanitize($_GET['status']) : '';

// Xây dựng truy vấn
$sql = "SELECT * FROM products WHERE user_id = ?";
$count_sql = "SELECT COUNT(*) as total FROM products WHERE user_id = ?";
$params = [$user_id];
$types = "i";

if (!empty($status)) {
    $sql .= " AND status = ?";
    $count_sql .= " AND status = ?";
    $params[] = $status;
    $types .= "s";
}

$sql .= " ORDER BY created_at DESC LIMIT ?, ?";
$params[] = $offset;
$params[] = $limit;
$types .= "ii";

// Chuẩn bị và thực thi truy vấn
$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

// Lấy tổng số lượng cho phân trang
$count_stmt = $conn->prepare($count_sql);
$count_types = substr($types, 0, -2); // Xóa hai tham số cuối (offset và limit)
$count_params = array_slice($params, 0, -2);
$count_stmt->bind_param($count_types, ...$count_params);
$count_stmt->execute();
$count_result = $count_stmt->get_result();
$count_row = $count_result->fetch_assoc();
$total_products = $count_row['total'];
$total_pages = ceil($total_products / $limit);

// Định dạng điều kiện để hiển thị
$condition_display = [
    'new' => 'Mới',
    'like_new' => 'Như mới',
    'good' => 'Tốt',
    'fair' => 'Khá',
    'poor' => 'Kém'
];

// Định dạng trạng thái để hiển thị
$status_display = [
    'pending' => '<span class="badge bg-warning">Chờ duyệt</span>',
    'approved' => '<span class="badge bg-success">Đã duyệt</span>',
    'rejected' => '<span class="badge bg-danger">Đã từ chối</span>',
    'sold' => '<span class="badge bg-info">Đã bán</span>'
];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sản phẩm của tôi - SecondLife Market</title>
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
            <div class="col-md-3">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Menu</h5>
                    </div>
                    <div class="list-group list-group-flush">
                        <a href="profile.php" class="list-group-item list-group-item-action">
                            <i class="fas fa-user me-2"></i>Hồ sơ cá nhân
                        </a>
                        <a href="my-products.php" class="list-group-item list-group-item-action active">
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
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Sản phẩm của tôi</h5>
                        <a href="product-create.php" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus me-1"></i>Đăng sản phẩm mới
                        </a>
                    </div>
                    <div class="card-body">
                        <!-- Filter -->
                        <div class="mb-4">
                            <form action="my-products.php" method="GET" class="row g-2">
                                <div class="col-md-4">
                                    <select class="form-select" name="status" onchange="this.form.submit()">
                                        <option value="">Tất cả trạng thái</option>
                                        <option value="pending" <?php echo $status == 'pending' ? 'selected' : ''; ?>>Chờ duyệt</option>
                                        <option value="approved" <?php echo $status == 'approved' ? 'selected' : ''; ?>>Đã duyệt</option>
                                        <option value="rejected" <?php echo $status == 'rejected' ? 'selected' : ''; ?>>Đã từ chối</option>
                                        <option value="sold" <?php echo $status == 'sold' ? 'selected' : ''; ?>>Đã bán</option>
                                    </select>
                                </div>
                            </form>
                        </div>
                        
                        <?php if ($result->num_rows > 0): ?>
                            <div class="row">
                                <?php while($product = $result->fetch_assoc()): ?>
                                    <div class="col-md-6 mb-4">
                                        <div class="card h-100">
                                            <div class="position-relative">
                                                <img src="uploads/products/<?php echo $product['image']; ?>" class="card-img-top" alt="<?php echo $product['name']; ?>" style="height: 200px; object-fit: cover;">
                                                <div class="position-absolute top-0 end-0 p-2">
                                                    <?php echo $status_display[$product['status']]; ?>
                                                </div>
                                            </div>
                                            <div class="card-body">
                                                <h5 class="card-title"><?php echo $product['name']; ?></h5>
                                                <p class="card-text text-danger fw-bold"><?php echo number_format($product['price']); ?> VNĐ</p>
                                                <p class="card-text"><small class="text-muted">Tình trạng: <?php echo $condition_display[$product['condition']]; ?></small></p>
                                                <p class="card-text"><small class="text-muted">Đăng lúc: <?php echo formatDate($product['created_at']); ?></small></p>
                                                <p class="card-text"><small class="text-muted">Lượt xem: <?php echo $product['views']; ?></small></p>
                                            </div>
                                            <div class="card-footer bg-white">
                                                <div class="d-flex justify-content-between">
                                                    <a href="product-detail.php?id=<?php echo $product['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-eye me-1"></i>Xem
                                                    </a>
                                                    <?php if ($product['status'] != 'sold'): ?>
                                                        <div>
                                                            <a href="product-edit.php?id=<?php echo $product['id']; ?>" class="btn btn-sm btn-outline-secondary">
                                                                <i class="fas fa-edit me-1"></i>Sửa
                                                            </a>
                                                            <?php if ($product['status'] == 'approved'): ?>
                                                                <a href="mark-as-sold.php?id=<?php echo $product['id']; ?>" class="btn btn-sm btn-outline-success" onclick="return confirm('Bạn có chắc chắn muốn đánh dấu sản phẩm này là đã bán?')">
                                                                    <i class="fas fa-check-circle me-1"></i>Đã bán
                                                                </a>
                                                            <?php endif; ?>
                                                            <a href="product-delete.php?id=<?php echo $product['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Bạn có chắc chắn muốn xóa sản phẩm này?')">
                                                                <i class="fas fa-trash me-1"></i>Xóa
                                                            </a>
                                                        </div>
                                                    <?php else: ?>
                                                        <span class="text-success"><i class="fas fa-check-circle me-1"></i>Đã bán</span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                            
                            <!-- Pagination -->
                            <?php if ($total_pages > 1): ?>
                                <nav aria-label="Page navigation">
                                    <ul class="pagination justify-content-center">
                                        <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $page - 1; ?>&status=<?php echo urlencode($status); ?>" aria-label="Previous">
                                                <span aria-hidden="true">&laquo;</span>
                                            </a>
                                        </li>
                                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                            <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
                                                <a class="page-link" href="?page=<?php echo $i; ?>&status=<?php echo urlencode($status); ?>"><?php echo $i; ?></a>
                                            </li>
                                        <?php endfor; ?>
                                        <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $page + 1; ?>&status=<?php echo urlencode($status); ?>" aria-label="Next">
                                                <span aria-hidden="true">&raquo;</span>
                                            </a>
                                        </li>
                                    </ul>
                                </nav>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="text-center py-5">
                                <i class="fas fa-box-open fa-3x mb-3 text-muted"></i>
                                <h4>Bạn chưa có sản phẩm nào</h4>
                                <p class="text-muted">Hãy đăng sản phẩm đầu tiên của bạn ngay bây giờ!</p>
                                <a href="product-create.php" class="btn btn-primary mt-2">
                                    <i class="fas fa-plus me-1"></i>Đăng sản phẩm mới
                                </a>
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
