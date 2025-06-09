<?php
session_start();
include 'config/db.php';
include 'includes/functions.php';

// Kiểm tra xem ID người bán đã được cung cấp chưa
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    redirect('index.php');
}

$seller_id = (int)$_GET['id'];

// Lấy thông tin người bán
$sql = "SELECT * FROM users WHERE id = ? AND status = 'active'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $seller_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    redirect('index.php');
}

$seller = $result->fetch_assoc();

// Lấy sản phẩm của người bán với phân trang
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 8;
$offset = ($page - 1) * $limit;

// Xây dựng truy vấn
$sql = "SELECT * FROM products WHERE user_id = ? AND status = 'approved' ORDER BY created_at DESC LIMIT ?, ?";
$count_sql = "SELECT COUNT(*) as total FROM products WHERE user_id = ? AND status = 'approved'";

// Chuẩn bị và thực thi truy vấn
$stmt = $conn->prepare($sql);
$stmt->bind_param("iii", $seller_id, $offset, $limit);
$stmt->execute();
$products = $stmt->get_result();

// Lấy tổng số lượng sản phẩm để phân trang
$count_stmt = $conn->prepare($count_sql);
$count_stmt->bind_param("i", $seller_id);
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
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $seller['username']; ?> - SecondLife Market</title>
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
                        <?php if (!empty($seller['avatar'])): ?>
                            <img src="uploads/avatars/<?php echo $seller['avatar']; ?>" alt="Avatar" class="rounded-circle profile-avatar">
                        <?php else: ?>
                            <i class="fas fa-user-circle fa-6x"></i>
                        <?php endif; ?>
                    </div>
                    <h1><?php echo $seller['username']; ?></h1>
                    <p>Thành viên từ <?php echo date('d/m/Y', strtotime($seller['created_at'])); ?></p>
                    
                    <?php if (isLoggedIn() && $_SESSION['user_id'] != $seller_id): ?>
                        <a href="chat.php?seller_id=<?php echo $seller_id; ?>" class="btn btn-primary mt-2">
                            <i class="fas fa-comment me-2"></i>Nhắn tin
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Sản phẩm đang bán</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($products->num_rows > 0): ?>
                            <div class="row">
                                <?php while($product = $products->fetch_assoc()): ?>
                                    <div class="col-md-3 mb-4">
                                        <div class="card h-100">
                                            <img src="uploads/products/<?php echo $product['image']; ?>" class="card-img-top" alt="<?php echo $product['name']; ?>" style="height: 200px; object-fit: cover;">
                                            <div class="card-body">
                                                <h5 class="card-title"><?php echo $product['name']; ?></h5>
                                                <p class="card-text text-danger fw-bold"><?php echo number_format($product['price']); ?> VNĐ</p>
                                                <p class="card-text"><small class="text-muted">Tình trạng: <?php echo $condition_display[$product['condition']]; ?></small></p>
                                            </div>
                                            <div class="card-footer bg-white">
                                                <a href="product-detail.php?id=<?php echo $product['id']; ?>" class="btn btn-sm btn-outline-primary w-100">Xem chi tiết</a>
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
                                            <a class="page-link" href="?id=<?php echo $seller_id; ?>&page=<?php echo $page - 1; ?>" aria-label="Previous">
                                                <span aria-hidden="true">&laquo;</span>
                                            </a>
                                        </li>
                                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                            <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
                                                <a class="page-link" href="?id=<?php echo $seller_id; ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                            </li>
                                        <?php endfor; ?>
                                        <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                                            <a class="page-link" href="?id=<?php echo $seller_id; ?>&page=<?php echo $page + 1; ?>" aria-label="Next">
                                                <span aria-hidden="true">&raquo;</span>
                                            </a>
                                        </li>
                                    </ul>
                                </nav>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="text-center py-5">
                                <i class="fas fa-box-open fa-3x mb-3 text-muted"></i>
                                <h4>Không có sản phẩm nào</h4>
                                <p class="text-muted">Người dùng này chưa đăng sản phẩm nào.</p>
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
