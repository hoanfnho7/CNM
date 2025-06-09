<?php
session_start();
include '../config/db.php';
include '../includes/functions.php';

// Chuyển hướng nếu không phải admin
if (!isAdmin()) {
    redirect('../index.php');
}

// Xử lý thay đổi trạng thái báo cáo
if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $report_id = (int)$_GET['id'];
    
    if ($action == 'resolve') {
        $sql = "UPDATE reports SET status = 'resolved' WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $report_id);
        $stmt->execute();
    } elseif ($action == 'delete') {
        $sql = "DELETE FROM reports WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $report_id);
        $stmt->execute();
    } elseif ($action == 'delete_product' && isset($_GET['product_id'])) {
        $product_id = (int)$_GET['product_id'];
        
        // Xóa yêu thích của sản phẩm
        $sql = "DELETE FROM favorites WHERE product_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        
        // Xóa báo cáo của sản phẩm
        $sql = "DELETE FROM reports WHERE product_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        
        // Xóa sản phẩm
        $sql = "DELETE FROM products WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
    }
    
    redirect('reports.php');
}

// Lấy báo cáo với phân trang
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$status = isset($_GET['status']) ? sanitize($_GET['status']) : '';

// Xây dựng truy vấn
$sql = "SELECT r.*, p.name as product_name, p.image as product_image, u.username as reporter_name 
        FROM reports r 
        JOIN products p ON r.product_id = p.id 
        JOIN users u ON r.user_id = u.id 
        WHERE 1=1";
$count_sql = "SELECT COUNT(*) as total FROM reports r WHERE 1=1";
$params = [];
$types = "";

if (!empty($status)) {
    $sql .= " AND r.status = ?";
    $count_sql .= " AND r.status = ?";
    $params[] = $status;
    $types .= "s";
}

$sql .= " ORDER BY r.created_at DESC LIMIT ?, ?";
$params[] = $offset;
$params[] = $limit;
$types .= "ii";

// Chuẩn bị và thực thi truy vấn
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// Lấy tổng số lượng cho phân trang
$count_stmt = $conn->prepare($count_sql);
if (!empty($params)) {
    // Xóa hai tham số cuối cùng (offset và limit)
    array_pop($params);
    array_pop($params);
    if (!empty($params)) {
        $count_stmt->bind_param(substr($types, 0, -2), ...$params);
    }
}
$count_stmt->execute();
$count_result = $count_stmt->get_result();
$count_row = $count_result->fetch_assoc();
$total_reports = $count_row['total'];
$total_pages = ceil($total_reports / $limit);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý báo cáo - SecondLife Market</title>
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
                    <h1 class="h2">Quản lý báo cáo</h1>
                </div>
                
                <!-- Filter -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form action="reports.php" method="GET" class="row g-3">
                            <div class="col-md-4">
                                <select class="form-select" name="status">
                                    <option value="">Tất cả trạng thái</option>
                                    <option value="pending" <?php echo $status == 'pending' ? 'selected' : ''; ?>>Chờ xử lý</option>
                                    <option value="resolved" <?php echo $status == 'resolved' ? 'selected' : ''; ?>>Đã xử lý</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100">Lọc</button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Reports Table -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold">Danh sách báo cáo</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Sản phẩm</th>
                                        <th>Người báo cáo</th>
                                        <th>Lý do</th>
                                        <th>Trạng thái</th>
                                        <th>Ngày báo cáo</th>
                                        <th>Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($result->num_rows > 0): ?>
                                        <?php while($report = $result->fetch_assoc()): ?>
                                            <tr>
                                                <td><?php echo $report['id']; ?></td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <img src="../uploads/products/<?php echo $report['product_image']; ?>" alt="<?php echo $report['product_name']; ?>" class="img-thumbnail me-2" style="width: 50px; height: 50px; object-fit: cover;">
                                                        <a href="../product-detail.php?id=<?php echo $report['product_id']; ?>" target="_blank"><?php echo $report['product_name']; ?></a>
                                                    </div>
                                                </td>
                                                <td><?php echo $report['reporter_name']; ?></td>
                                                <td><?php echo $report['reason']; ?></td>
                                                <td>
                                                    <?php if ($report['status'] == 'pending'): ?>
                                                        <span class="badge bg-warning">Chờ xử lý</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-success">Đã xử lý</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo date('d/m/Y H:i', strtotime($report['created_at'])); ?></td>
                                                <td>
                                                    <?php if ($report['status'] == 'pending'): ?>
                                                        <a href="reports.php?action=resolve&id=<?php echo $report['id']; ?>" class="btn btn-sm btn-success">
                                                            <i class="fas fa-check"></i> Đánh dấu đã xử lý
                                                        </a>
                                                    <?php endif; ?>
                                                    <a href="reports.php?action=delete_product&id=<?php echo $report['id']; ?>&product_id=<?php echo $report['product_id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Bạn có chắc chắn muốn xóa sản phẩm này?')">
                                                        <i class="fas fa-trash"></i> Xóa sản phẩm
                                                    </a>
                                                    <a href="reports.php?action=delete&id=<?php echo $report['id']; ?>" class="btn btn-sm btn-warning" onclick="return confirm('Bạn có chắc chắn muốn xóa báo cáo này?')">
                                                        <i class="fas fa-times"></i> Xóa báo cáo
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="7" class="text-center">Không có dữ liệu</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
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
