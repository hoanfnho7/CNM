<?php
session_start();
include '../config/db.php';
include '../includes/functions.php';

// Chuyển hướng nếu không phải admin
if (!isAdmin()) {
    redirect('../index.php');
}

// Lấy thống kê
$stats = [
    'users' => 0,
    'products' => 0,
    'pending_products' => 0,
    'reports' => 0
];

// Tổng số người dùng
$sql = "SELECT COUNT(*) as count FROM users";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $stats['users'] = $row['count'];
}

// Tổng số sản phẩm
$sql = "SELECT COUNT(*) as count FROM products";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $stats['products'] = $row['count'];
}

// Sản phẩm chờ duyệt
$sql = "SELECT COUNT(*) as count FROM products WHERE status = 'pending'";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $stats['pending_products'] = $row['count'];
}

// Tổng số báo cáo
$sql = "SELECT COUNT(*) as count FROM reports WHERE status = 'pending'";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $stats['reports'] = $row['count'];
}

// Người dùng gần đây
$sql = "SELECT * FROM users ORDER BY created_at DESC LIMIT 5";
$recent_users = $conn->query($sql);

// Sản phẩm gần đây
$sql = "SELECT p.*, u.username FROM products p JOIN users u ON p.user_id = u.id ORDER BY p.created_at DESC LIMIT 5";
$recent_products = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản trị - SecondLife Market</title>
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
                    <h1 class="h2">Bảng điều khiển</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <a href="../index.php" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-home me-1"></i>Về trang chủ
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Statistics Cards -->
                <div class="row">
                    <div class="col-md-3 mb-4">
                        <div class="card border-left-primary shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                            Tổng người dùng</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['users']; ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-users fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3 mb-4">
                        <div class="card border-left-success shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                            Tổng sản phẩm</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['products']; ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-box fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3 mb-4">
                        <div class="card border-left-warning shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                            Sản phẩm chờ duyệt</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['pending_products']; ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-clock fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3 mb-4">
                        <div class="card border-left-danger shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                            Báo cáo chờ xử lý</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['reports']; ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-flag fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <!-- Recent Users -->
                    <div class="col-md-6">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                <h6 class="m-0 font-weight-bold">Người dùng mới nhất</h6>
                                <a href="users.php" class="btn btn-sm btn-primary">Xem tất cả</a>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered" width="100%" cellspacing="0">
                                        <thead>
                                            <tr>
                                                <th>Tên đăng nhập</th>
                                                <th>Email</th>
                                                <th>Ngày đăng ký</th>
                                                <th>Trạng thái</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if ($recent_users->num_rows > 0): ?>
                                                <?php while($user = $recent_users->fetch_assoc()): ?>
                                                    <tr>
                                                        <td><?php echo $user['username']; ?></td>
                                                        <td><?php echo $user['email']; ?></td>
                                                        <td><?php echo date('d/m/Y', strtotime($user['created_at'])); ?></td>
                                                        <td>
                                                            <?php if ($user['status'] == 'active'): ?>
                                                                <span class="badge bg-success">Hoạt động</span>
                                                            <?php else: ?>
                                                                <span class="badge bg-danger">Bị khóa</span>
                                                            <?php endif; ?>
                                                        </td>
                                                    </tr>
                                                <?php endwhile; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="4" class="text-center">Không có dữ liệu</td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Recent Products -->
                    <div class="col-md-6">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                <h6 class="m-0 font-weight-bold">Sản phẩm mới nhất</h6>
                                <a href="products.php" class="btn btn-sm btn-primary">Xem tất cả</a>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered" width="100%" cellspacing="0">
                                        <thead>
                                            <tr>
                                                <th>Tên sản phẩm</th>
                                                <th>Người đăng</th>
                                                <th>Giá</th>
                                                <th>Trạng thái</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if ($recent_products->num_rows > 0): ?>
                                                <?php while($product = $recent_products->fetch_assoc()): ?>
                                                    <tr>
                                                        <td><?php echo $product['name']; ?></td>
                                                        <td><?php echo $product['username']; ?></td>
                                                        <td><?php echo number_format($product['price']); ?> VNĐ</td>
                                                        <td>
                                                            <?php if ($product['status'] == 'approved'): ?>
                                                                <span class="badge bg-success">Đã duyệt</span>
                                                            <?php elseif ($product['status'] == 'pending'): ?>
                                                                <span class="badge bg-warning">Chờ duyệt</span>
                                                            <?php elseif ($product['status'] == 'sold'): ?>
                                                                <span class="badge bg-info">Đã bán</span>
                                                            <?php else: ?>
                                                                <span class="badge bg-danger">Đã xóa</span>
                                                            <?php endif; ?>
                                                        </td>
                                                    </tr>
                                                <?php endwhile; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="4" class="text-center">Không có dữ liệu</td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
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
