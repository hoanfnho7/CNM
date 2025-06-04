<?php
session_start();
include 'config/db.php';
include 'includes/functions.php';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>2HandShop - Chợ Đồ Cũ</title>
    <!-- CSS Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- CSS Tùy chỉnh -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container mt-4">
        <!-- Phần Hero -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card bg-light">
                    <div class="card-body p-5 text-center">
                        <h1 class="display-4">Chào mừng đến với 2HandShop</h1>
                        <p class="lead">Nơi mua bán đồ cũ uy tín, an toàn và tiện lợi</p>
                        <div class="mt-4">
                            <?php if (!isset($_SESSION['user_id'])): ?>
                                <a href="register.php" class="btn btn-primary me-2">Đăng ký</a>
                                <a href="login.php" class="btn btn-outline-primary">Đăng nhập</a>
                            <?php else: ?>
                                <a href="product-create.php" class="btn btn-success me-2">Đăng sản phẩm mới</a>
                                <a href="my-products.php" class="btn btn-outline-primary">Sản phẩm của tôi</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Phần Tìm kiếm -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <form action="search.php" method="GET" class="row g-3">
                            <div class="col-md-6">
                                <input type="text" name="keyword" class="form-control" placeholder="Tìm kiếm sản phẩm...">
                            </div>
                            <div class="col-md-2">
                                <select name="condition" class="form-select">
                                    <option value="">Tình trạng</option>
                                    <option value="new">Mới</option>
                                    <option value="like_new">Như mới</option>
                                    <option value="good">Tốt</option>
                                    <option value="fair">Khá</option>
                                    <option value="poor">Kém</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select name="sort" class="form-select">
                                    <option value="newest">Mới nhất</option>
                                    <option value="price_asc">Giá tăng dần</option>
                                    <option value="price_desc">Giá giảm dần</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100">Tìm kiếm</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Sản phẩm nổi bật -->
        <h2 class="mb-3">Sản phẩm nổi bật</h2>
        <div class="row">
            <?php
            $sql = "SELECT p.*, u.username, u.avatar FROM products p 
                    JOIN users u ON p.user_id = u.id 
                    WHERE p.status = 'approved' 
                    ORDER BY p.created_at DESC LIMIT 8";
            $result = $conn->query($sql);
            
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    echo '<div class="col-md-3 mb-4">';
                    echo '<div class="card h-100">';
                    echo '<img src="uploads/products/' . $row['image'] . '" class="card-img-top" alt="' . $row['name'] . '" style="height: 200px; object-fit: cover;">';
                    echo '<div class="card-body">';
                    echo '<h5 class="card-title">' . $row['name'] . '</h5>';
                    echo '<p class="card-text text-danger fw-bold">' . number_format($row['price']) . ' VNĐ</p>';
                    echo '<p class="card-text"><small class="text-muted">Tình trạng: ' . ucfirst($row['condition']) . '</small></p>';
                    echo '</div>';
                    echo '<div class="card-footer bg-white d-flex justify-content-between align-items-center">';
                    echo '<small class="text-muted">Đăng bởi: ' . $row['username'] . '</small>';
                    echo '<a href="product-detail.php?id=' . $row['id'] . '" class="btn btn-sm btn-outline-primary">Xem chi tiết</a>';
                    echo '</div>';
                    echo '</div>';
                    echo '</div>';
                }
            } else {
                echo '<div class="col-12"><p class="text-center">Không có sản phẩm nào.</p></div>';
            }
            ?>
        </div>
        
        <!-- Phần Danh mục -->
        <h2 class="mb-3 mt-5">Danh mục sản phẩm</h2>
        <div class="row text-center">
            <div class="col-md-2 mb-4">
                <a href="search.php?category=electronics" class="text-decoration-none">
                    <div class="card py-3">
                        <i class="fas fa-laptop fa-3x mb-2"></i>
                        <h5>Điện tử</h5>
                    </div>
                </a>
            </div>
            <div class="col-md-2 mb-4">
                <a href="search.php?category=furniture" class="text-decoration-none">
                    <div class="card py-3">
                        <i class="fas fa-couch fa-3x mb-2"></i>
                        <h5>Nội thất</h5>
                    </div>
                </a>
            </div>
            <div class="col-md-2 mb-4">
                <a href="search.php?category=clothing" class="text-decoration-none">
                    <div class="card py-3">
                        <i class="fas fa-tshirt fa-3x mb-2"></i>
                        <h5>Quần áo</h5>
                    </div>
                </a>
            </div>
            <div class="col-md-2 mb-4">
                <a href="search.php?category=books" class="text-decoration-none">
                    <div class="card py-3">
                        <i class="fas fa-book fa-3x mb-2"></i>
                        <h5>Sách</h5>
                    </div>
                </a>
            </div>
            <div class="col-md-2 mb-4">
                <a href="search.php?category=toys" class="text-decoration-none">
                    <div class="card py-3">
                        <i class="fas fa-gamepad fa-3x mb-2"></i>
                        <h5>Đồ chơi</h5>
                    </div>
                </a>
            </div>
            <div class="col-md-2 mb-4">
                <a href="search.php?category=others" class="text-decoration-none">
                    <div class="card py-3">
                        <i class="fas fa-ellipsis-h fa-3x mb-2"></i>
                        <h5>Khác</h5>
                    </div>
                </a>
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
