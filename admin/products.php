<?php
session_start();
include '../config/db.php';
include '../includes/functions.php';

// Redirect if not admin
if (!isAdmin()) {
    redirect('../index.php');
}

// Handle product status change
if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $product_id = (int)$_GET['id'];
    
    if ($action == 'approve') {
        $sql = "UPDATE products SET status = 'approved' WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
    } elseif ($action == 'reject') {
        $sql = "UPDATE products SET status = 'rejected' WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
    } elseif ($action == 'delete') {
        // Delete product's favorites
        $sql = "DELETE FROM favorites WHERE product_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        
        // Delete product's reports
        $sql = "DELETE FROM reports WHERE product_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        
        // Delete product
        $sql = "DELETE FROM products WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
    }
    
    redirect('products.php');
}

// Get products with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$status = isset($_GET['status']) ? sanitize($_GET['status']) : '';
$category = isset($_GET['category']) ? sanitize($_GET['category']) : '';

// Build query
$sql = "SELECT p.*, u.username FROM products p JOIN users u ON p.user_id = u.id WHERE 1=1";
$count_sql = "SELECT COUNT(*) as total FROM products p JOIN users u ON p.user_id = u.id WHERE 1=1";
$params = [];
$types = "";

if (!empty($search)) {
    $sql .= " AND (p.name LIKE ? OR p.description LIKE ? OR u.username LIKE ?)";
    $count_sql .= " AND (p.name LIKE ? OR p.description LIKE ? OR u.username LIKE ?)";
    $search_param = "%" . $search . "%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= "sss";
}

if (!empty($status)) {
    $sql .= " AND p.status = ?";
    $count_sql .= " AND p.status = ?";
    $params[] = $status;
    $types .= "s";
}

if (!empty($category)) {
    $sql .= " AND p.category = ?";
    $count_sql .= " AND p.category = ?";
    $params[] = $category;
    $types .= "s";
}

$sql .= " ORDER BY p.created_at DESC LIMIT ?, ?";
$params[] = $offset;
$params[] = $limit;
$types .= "ii";

// Prepare and execute query
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// Get total count for pagination
$count_stmt = $conn->prepare($count_sql);
if (!empty($params)) {
    // Remove the last two parameters (offset and limit)
    array_pop($params);
    array_pop($params);
    if (!empty($params)) {
        $count_stmt->bind_param(substr($types, 0, -2), ...$params);
    }
}
$count_stmt->execute();
$count_result = $count_stmt->get_result();
$count_row = $count_result->fetch_assoc();
$total_products = $count_row['total'];
$total_pages = ceil($total_products / $limit);

// Format condition for display
$condition_display = [
    'new' => 'Mới',
    'like_new' => 'Như mới',
    'good' => 'Tốt',
    'fair' => 'Khá',
    'poor' => 'Kém'
];

// Format category for display
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
    <title>Quản lý sản phẩm - SecondLife Market</title>
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
                    <h1 class="h2">Quản lý sản phẩm</h1>
                </div>
                
                <!-- Search and Filter -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form action="products.php" method="GET" class="row g-3">
                            <div class="col-md-4">
                                <input type="text" class="form-control" name="search" placeholder="Tìm kiếm sản phẩm..." value="<?php echo htmlspecialchars($search); ?>">
                            </div>
                            <div class="col-md-3">
                                <select class="form-select" name="status">
                                    <option value="">Tất cả trạng thái</option>
                                    <option value="pending" <?php echo $status == 'pending' ? 'selected' : ''; ?>>Chờ duyệt</option>
                                    <option value="approved" <?php echo $status == 'approved' ? 'selected' : ''; ?>>Đã duyệt</option>
                                    <option value="rejected" <?php echo $status == 'rejected' ? 'selected' : ''; ?>>Đã từ chối</option>
                                    <option value="sold" <?php echo $status == 'sold' ? 'selected' : ''; ?>>Đã bán</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select class="form-select" name="category">
                                    <option value="">Tất cả danh mục</option>
                                    <option value="electronics" <?php echo $category == 'electronics' ? 'selected' : ''; ?>>Điện tử</option>
                                    <option value="furniture" <?php echo $category == 'furniture' ? 'selected' : ''; ?>>Nội thất</option>
                                    <option value="clothing" <?php echo $category == 'clothing' ? 'selected' : ''; ?>>Quần áo</option>
                                    <option value="books" <?php echo $category == 'books' ? 'selected' : ''; ?>>Sách</option>
                                    <option value="toys" <?php echo $category == 'toys' ? 'selected' : ''; ?>>Đồ chơi</option>
                                    <option value="others" <?php echo $category == 'others' ? 'selected' : ''; ?>>Khác</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100">Tìm kiếm</button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Products Table -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold">Danh sách sản phẩm</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Hình ảnh</th>
                                        <th>Tên sản phẩm</th>
                                        <th>Người đăng</th>
                                        <th>Giá</th>
                                        <th>Danh mục</th>
                                        <th>Trạng thái</th>
                                        <th>Ngày đăng</th>
                                        <th>Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($result->num_rows > 0): ?>
                                        <?php while($product = $result->fetch_assoc()): ?>
                                            <tr>
                                                <td><?php echo $product['id']; ?></td>
                                                <td>
                                                    <img src="../uploads/products/<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>" class="img-thumbnail" style="width: 50px; height: 50px; object-fit: cover;">
                                                </td>
                                                <td><?php echo $product['name']; ?></td>
                                                <td><?php echo $product['username']; ?></td>
                                                <td><?php echo number_format($product['price']); ?> VNĐ</td>
                                                <td><?php echo $category_display[$product['category']]; ?></td>
                                                <td>
                                                    <?php if ($product['status'] == 'approved'): ?>
                                                        <span class="badge bg-success">Đã duyệt</span>
                                                    <?php elseif ($product['status'] == 'pending'): ?>
                                                        <span class="badge bg-warning">Chờ duyệt</span>
                                                    <?php elseif ($product['status'] == 'rejected'): ?>
                                                        <span class="badge bg-danger">Đã từ chối</span>
                                                    <?php elseif ($product['status'] == 'sold'): ?>
                                                        <span class="badge bg-info">Đã bán</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo date('d/m/Y', strtotime($product['created_at'])); ?></td>
                                                <td>
                                                    <a href="../product-detail.php?id=<?php echo $product['id']; ?>" class="btn btn-sm btn-info" target="_blank">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <?php if ($product['status'] == 'pending'): ?>
                                                        <a href="products.php?action=approve&id=<?php echo $product['id']; ?>" class="btn btn-sm btn-success">
                                                            <i class="fas fa-check"></i> Duyệt
                                                        </a>
                                                        <a href="products.php?action=reject&id=<?php echo $product['id']; ?>" class="btn btn-sm btn-warning">
                                                            <i class="fas fa-times"></i> Từ chối
                                                        </a>
                                                    <?php endif; ?>
                                                    <a href="products.php?action=delete&id=<?php echo $product['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Bạn có chắc chắn muốn xóa sản phẩm này?')">
                                                        <i class="fas fa-trash"></i> Xóa
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="9" class="text-center">Không có dữ liệu</td>
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
                                        <a class="page-link" href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status); ?>&category=<?php echo urlencode($category); ?>" aria-label="Previous">
                                            <span aria-hidden="true">&laquo;</span>
                                        </a>
                                    </li>
                                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                        <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status); ?>&category=<?php echo urlencode($category); ?>"><?php echo $i; ?></a>
                                        </li>
                                    <?php endfor; ?>
                                    <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status); ?>&category=<?php echo urlencode($category); ?>" aria-label="Next">
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
