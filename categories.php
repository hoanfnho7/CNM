<?php
session_start();
include 'config/db.php';
include 'includes/functions.php';


// Lấy danh mục từ cơ sở dữ liệu
$sql = "SELECT * FROM categories ORDER BY name ASC";
$categories_result = $conn->query($sql);
$category_display = [];

if ($categories_result->num_rows > 0) {
    while($category = $categories_result->fetch_assoc()) {
        $category_display[$category['slug']] = [
            'name' => $category['name'],
            'icon' => $category['icon']
        ];
    }
}

// Nếu không có danh mục trong cơ sở dữ liệu, sử dụng danh mục mặc định
if (empty($category_display)) {
    $category_display = [
        'electronics' => ['name' => 'Điện tử', 'icon' => 'fas fa-laptop'],
        'furniture' => ['name' => 'Nội thất', 'icon' => 'fas fa-couch'],
        'clothing' => ['name' => 'Quần áo', 'icon' => 'fas fa-tshirt'],
        'books' => ['name' => 'Sách', 'icon' => 'fas fa-book'],
        'toys' => ['name' => 'Đồ chơi', 'icon' => 'fas fa-gamepad'],
        'others' => ['name' => 'Khác', 'icon' => 'fas fa-ellipsis-h']
    ];
}

// Lấy số lượng sản phẩm cho mỗi danh mục
$category_counts = [];
foreach (array_keys($category_display) as $category) {
    $sql = "SELECT COUNT(*) as count FROM products WHERE category = ? AND status = 'approved'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $category);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $category_counts[$category] = $row['count'];
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danh mục sản phẩm - SecondLife Market</title>
    <!-- CSS Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- CSS Tùy chỉnh -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>
    <?php include 'includes/header.php'; ?>

    <div class="container mt-5">
        <h1 class="mb-4">Danh mục sản phẩm</h1>

        <div class="row">
            <?php foreach ($category_display as $category_key => $category_info): ?>
                <div class="col-md-4 mb-4">
                    <a href="search.php?category=<?php echo $category_key; ?>" class="text-decoration-none">
                        <div class="card h-100">
                            <div class="card-body text-center py-5">
                                <i class="<?php echo $category_info['icon']; ?> fa-4x mb-3"></i>
                                <h3><?php echo $category_info['name']; ?></h3>
                                <p class="text-muted"><?php echo $category_counts[$category_key]; ?> sản phẩm</p>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">Tìm kiếm nâng cao</h5>
            </div>
            <div class="card-body">
                <form action="search.php" method="GET" class="row g-3">
                    <div class="col-md-6">
                        <input type="text" class="form-control" name="keyword" placeholder="Tìm kiếm sản phẩm...">
                    </div>
                    <div class="col-md-2">
                        <select name="category" class="form-select">
                            <option value="">Tất cả danh mục</option>
                            <?php foreach ($category_display as $category_key => $category_info): ?>
                                <option value="<?php echo $category_key; ?>"><?php echo $category_info['name']; ?></option>
                            <?php endforeach; ?>
                        </select>
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
                        <button type="submit" class="btn btn-primary w-100">Tìm kiếm</button>
                    </div>
                </form>
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