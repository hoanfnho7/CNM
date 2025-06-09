<?php
session_start();
include '../config/db.php';
include '../includes/functions.php';

// Chuyển hướng nếu không phải admin
if (!isAdmin()) {
    redirect('../index.php');
}

// Lấy khoảng thời gian
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d', strtotime('-30 days'));
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');

// Xác thực ngày tháng
if (strtotime($start_date) > strtotime($end_date)) {
    $temp = $start_date;
    $start_date = $end_date;
    $end_date = $temp;
}

// Thêm một ngày vào end_date để truy vấn bao gồm cả ngày cuối
$end_date_query = date('Y-m-d', strtotime($end_date . ' +1 day'));

// Lấy thống kê
$stats = [
    'total_users' => 0,
    'total_products' => 0,
    'total_sold' => 0,
    'total_pending' => 0,
    'total_messages' => 0,
    'total_favorites' => 0,
    'total_reports' => 0
];

// Tổng số người dùng
$sql = "SELECT COUNT(*) as count FROM users WHERE created_at BETWEEN ? AND ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $start_date, $end_date_query);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $stats['total_users'] = $row['count'];
}

// Tổng số sản phẩm
$sql = "SELECT COUNT(*) as count FROM products WHERE created_at BETWEEN ? AND ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $start_date, $end_date_query);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $stats['total_products'] = $row['count'];
}

// Tổng số sản phẩm đã bán
$sql = "SELECT COUNT(*) as count FROM products WHERE status = 'sold' AND updated_at BETWEEN ? AND ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $start_date, $end_date_query);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $stats['total_sold'] = $row['count'];
}

// Tổng số sản phẩm đang chờ duyệt
$sql = "SELECT COUNT(*) as count FROM products WHERE status = 'pending' AND created_at BETWEEN ? AND ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $start_date, $end_date_query);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $stats['total_pending'] = $row['count'];
}

// Tổng số tin nhắn
$sql = "SELECT COUNT(*) as count FROM messages WHERE created_at BETWEEN ? AND ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $start_date, $end_date_query);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $stats['total_messages'] = $row['count'];
}

// Tổng số lượt yêu thích
$sql = "SELECT COUNT(*) as count FROM favorites WHERE created_at BETWEEN ? AND ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $start_date, $end_date_query);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $stats['total_favorites'] = $row['count'];
}

// Tổng số báo cáo
$sql = "SELECT COUNT(*) as count FROM reports WHERE created_at BETWEEN ? AND ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $start_date, $end_date_query);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $stats['total_reports'] = $row['count'];
}

// Lấy số liệu đăng ký người dùng hàng ngày
$sql = "SELECT DATE(created_at) as date, COUNT(*) as count FROM users 
        WHERE created_at BETWEEN ? AND ? 
        GROUP BY DATE(created_at) 
        ORDER BY date ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $start_date, $end_date_query);
$stmt->execute();
$user_registrations = $stmt->get_result();

// Lấy số liệu đăng sản phẩm hàng ngày
$sql = "SELECT DATE(created_at) as date, COUNT(*) as count FROM products 
        WHERE created_at BETWEEN ? AND ? 
        GROUP BY DATE(created_at) 
        ORDER BY date ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $start_date, $end_date_query);
$stmt->execute();
$product_listings = $stmt->get_result();

// Lấy phân bố danh mục sản phẩm
$sql = "SELECT category, COUNT(*) as count FROM products 
        WHERE created_at BETWEEN ? AND ? 
        GROUP BY category 
        ORDER BY count DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $start_date, $end_date_query);
$stmt->execute();
$category_distribution = $stmt->get_result();

// Lấy top người bán hàng theo số lượng sản phẩm
$sql = "SELECT u.id, u.username, COUNT(p.id) as product_count 
        FROM users u 
        JOIN products p ON u.id = p.user_id 
        WHERE p.created_at BETWEEN ? AND ? 
        GROUP BY u.id 
        ORDER BY product_count DESC 
        LIMIT 10";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $start_date, $end_date_query);
$stmt->execute();
$top_sellers = $stmt->get_result();

// Định dạng danh mục để hiển thị
$category_display = [
    'electronics' => 'Điện tử',
    'furniture' => 'Nội thất',
    'clothing' => 'Quần áo',
    'books' => 'Sách',
    'toys' => 'Đồ chơi',
    'others' => 'Khác'
];

// Chuẩn bị dữ liệu cho biểu đồ
$user_data = [];
$product_data = [];

// Khởi tạo mảng với tất cả các ngày trong khoảng
$period = new DatePeriod(
    new DateTime($start_date),
    new DateInterval('P1D'),
    new DateTime($end_date_query)
);

foreach ($period as $date) {
    $date_str = $date->format('Y-m-d');
    $user_data[$date_str] = 0;
    $product_data[$date_str] = 0;
}

// Điền dữ liệu thực tế
while ($row = $user_registrations->fetch_assoc()) {
    $user_data[$row['date']] = (int)$row['count'];
}

while ($row = $product_listings->fetch_assoc()) {
    $product_data[$row['date']] = (int)$row['count'];
}

// Chuyển đổi thành JSON cho biểu đồ
$user_data_json = json_encode(array_values($user_data));
$product_data_json = json_encode(array_values($product_data));
$dates_json = json_encode(array_map(function($date) {
    return date('d/m', strtotime($date));
}, array_keys($user_data)));

// Chuẩn bị dữ liệu danh mục cho biểu đồ tròn
$category_labels = [];
$category_counts = [];

while ($row = $category_distribution->fetch_assoc()) {
    $category_labels[] = isset($category_display[$row['category']]) ? $category_display[$row['category']] : $row['category'];
    $category_counts[] = (int)$row['count'];
}

$category_labels_json = json_encode($category_labels);
$category_counts_json = json_encode($category_counts);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thống kê - SecondLife Market</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                    <h1 class="h2">Thống kê</h1>
                </div>
                
                <!-- Date Range Filter -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form action="statistics.php" method="GET" class="row g-3">
                            <div class="col-md-4">
                                <label for="start_date" class="form-label">Từ ngày</label>
                                <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo $start_date; ?>">
                            </div>
                            <div class="col-md-4">
                                <label for="end_date" class="form-label">Đến ngày</label>
                                <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo $end_date; ?>">
                            </div>
                            <div class="col-md-4 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary">Áp dụng</button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Statistics Cards -->
                <div class="row">
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-primary shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                            Người dùng mới</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['total_users']; ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-users fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-success shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                            Sản phẩm mới</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['total_products']; ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-box fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-info shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                            Sản phẩm đã bán</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['total_sold']; ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-warning shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                            Sản phẩm chờ duyệt</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['total_pending']; ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-clock fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Charts -->
                <div class="row">
                    <!-- Line Chart - User Registrations and Product Listings -->
                    <div class="col-xl-8 col-lg-7">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold">Người dùng mới và Sản phẩm mới</h6>
                            </div>
                            <div class="card-body">
                                <div class="chart-area">
                                    <canvas id="userProductChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Pie Chart - Category Distribution -->
                    <div class="col-xl-4 col-lg-5">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold">Phân bố danh mục</h6>
                            </div>
                            <div class="card-body">
                                <div class="chart-pie">
                                    <canvas id="categoryChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Top Sellers Table -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold">Top người bán</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Tên người dùng</th>
                                        <th>Số sản phẩm</th>
                                        <th>Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($top_sellers->num_rows > 0): ?>
                                        <?php while($seller = $top_sellers->fetch_assoc()): ?>
                                            <tr>
                                                <td><?php echo $seller['id']; ?></td>
                                                <td><?php echo $seller['username']; ?></td>
                                                <td><?php echo $seller['product_count']; ?></td>
                                                <td>
                                                    <a href="../seller-profile.php?id=<?php echo $seller['id']; ?>" class="btn btn-sm btn-info" target="_blank">
                                                        <i class="fas fa-eye"></i> Xem
                                                    </a>
                                                    <a href="users.php?action=view&id=<?php echo $seller['id']; ?>" class="btn btn-sm btn-primary">
                                                        <i class="fas fa-user"></i> Quản lý
                                                    </a>
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
                
                <!-- Additional Statistics -->
                <div class="row">
                    <div class="col-md-4 mb-4">
                        <div class="card shadow h-100">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold">Tin nhắn</h6>
                            </div>
                            <div class="card-body">
                                <div class="text-center">
                                    <i class="fas fa-envelope fa-3x mb-3 text-primary"></i>
                                    <h2 class="mb-0"><?php echo $stats['total_messages']; ?></h2>
                                    <p class="text-muted">Tin nhắn đã gửi</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4 mb-4">
                        <div class="card shadow h-100">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold">Yêu thích</h6>
                            </div>
                            <div class="card-body">
                                <div class="text-center">
                                    <i class="fas fa-heart fa-3x mb-3 text-danger"></i>
                                    <h2 class="mb-0"><?php echo $stats['total_favorites']; ?></h2>
                                    <p class="text-muted">Lượt yêu thích</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4 mb-4">
                        <div class="card shadow h-100">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold">Báo cáo</h6>
                            </div>
                            <div class="card-body">
                                <div class="text-center">
                                    <i class="fas fa-flag fa-3x mb-3 text-warning"></i>
                                    <h2 class="mb-0"><?php echo $stats['total_reports']; ?></h2>
                                    <p class="text-muted">Báo cáo đã nhận</p>
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
    
    <script>
        // Line Chart - User Registrations and Product Listings
        var ctx = document.getElementById('userProductChart').getContext('2d');
        var userProductChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?php echo $dates_json; ?>,
                datasets: [
                    {
                        label: 'Người dùng mới',
                        data: <?php echo $user_data_json; ?>,
                        backgroundColor: 'rgba(78, 115, 223, 0.05)',
                        borderColor: 'rgba(78, 115, 223, 1)',
                        pointBackgroundColor: 'rgba(78, 115, 223, 1)',
                        pointBorderColor: '#fff',
                        pointHoverBackgroundColor: '#fff',
                        pointHoverBorderColor: 'rgba(78, 115, 223, 1)',
                        borderWidth: 2,
                        fill: true
                    },
                    {
                        label: 'Sản phẩm mới',
                        data: <?php echo $product_data_json; ?>,
                        backgroundColor: 'rgba(28, 200, 138, 0.05)',
                        borderColor: 'rgba(28, 200, 138, 1)',
                        pointBackgroundColor: 'rgba(28, 200, 138, 1)',
                        pointBorderColor: '#fff',
                        pointHoverBackgroundColor: '#fff',
                        pointHoverBorderColor: 'rgba(28, 200, 138, 1)',
                        borderWidth: 2,
                        fill: true
                    }
                ]
            },
            options: {
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        mode: 'index',
                        intersect: false
                    }
                }
            }
        });
        
        // Pie Chart - Category Distribution
        var ctx2 = document.getElementById('categoryChart').getContext('2d');
        var categoryChart = new Chart(ctx2, {
            type: 'pie',
            data: {
                labels: <?php echo $category_labels_json; ?>,
                datasets: [{
                    data: <?php echo $category_counts_json; ?>,
                    backgroundColor: [
                        'rgba(78, 115, 223, 0.8)',
                        'rgba(28, 200, 138, 0.8)',
                        'rgba(246, 194, 62, 0.8)',
                        'rgba(231, 74, 59, 0.8)',
                        'rgba(54, 185, 204, 0.8)',
                        'rgba(133, 135, 150, 0.8)'
                    ],
                    hoverBackgroundColor: [
                        'rgba(78, 115, 223, 1)',
                        'rgba(28, 200, 138, 1)',
                        'rgba(246, 194, 62, 1)',
                        'rgba(231, 74, 59, 1)',
                        'rgba(54, 185, 204, 1)',
                        'rgba(133, 135, 150, 1)'
                    ],
                    hoverBorderColor: "rgba(234, 236, 244, 1)",
                }]
            },
            options: {
                maintainAspectRatio: false,
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                var label = context.label || '';
                                var value = context.raw || 0;
                                var total = context.dataset.data.reduce((a, b) => a + b, 0);
                                var percentage = Math.round((value / total) * 100);
                                return label + ': ' + value + ' (' + percentage + '%)';
                            }
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>
