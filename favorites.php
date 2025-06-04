<?php
session_start();
include 'config/db.php';
include 'includes/functions.php';

// Redirect if not logged in
if (!isLoggedIn()) {
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];

// Get favorites with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 8;
$offset = ($page - 1) * $limit;

// Build query
$sql = "SELECT p.*, u.username, u.avatar, f.created_at as favorited_at 
        FROM favorites f 
        JOIN products p ON f.product_id = p.id 
        JOIN users u ON p.user_id = u.id 
        WHERE f.user_id = ? AND p.status = 'approved' 
        ORDER BY f.created_at DESC LIMIT ?, ?";

$count_sql = "SELECT COUNT(*) as total 
              FROM favorites f 
              JOIN products p ON f.product_id = p.id 
              WHERE f.user_id = ? AND p.status = 'approved'";

// Prepare and execute query
$stmt = $conn->prepare($sql);
$stmt->bind_param("iii", $user_id, $offset, $limit);
$stmt->execute();
$result = $stmt->get_result();

// Get total count for pagination
$count_stmt = $conn->prepare($count_sql);
$count_stmt->bind_param("i", $user_id);
$count_stmt->execute();
$count_result = $count_stmt->get_result();
$count_row = $count_result->fetch_assoc();
$total_favorites = $count_row['total'];
$total_pages = ceil($total_favorites / $limit);

// Format condition for display
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
    <title>Sản phẩm yêu thích - 2HandShop</title>
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
                        <a href="my-products.php" class="list-group-item list-group-item-action">
                            <i class="fas fa-box me-2"></i>Sản phẩm của tôi
                        </a>
                        <a href="favorites.php" class="list-group-item list-group-item-action active">
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
                    <div class="card-header">
                        <h5 class="mb-0">Sản phẩm yêu thích</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($result->num_rows > 0): ?>
                            <div class="row">
                                <?php while($product = $result->fetch_assoc()): ?>
                                    <div class="col-md-6 mb-4">
                                        <div class="card h-100">
                                            <img src="uploads/products/<?php echo $product['image']; ?>" class="card-img-top" alt="<?php echo $product['name']; ?>" style="height: 200px; object-fit: cover;">
                                            <div class="card-body">
                                                <h5 class="card-title"><?php echo $product['name']; ?></h5>
                                                <p class="card-text text-danger fw-bold"><?php echo number_format($product['price']); ?> VNĐ</p>
                                                <p class="card-text"><small class="text-muted">Tình trạng: <?php echo $condition_display[$product['condition']]; ?></small></p>
                                                <div class="d-flex align-items-center mb-2">
                                                    <?php if (!empty($product['avatar'])): ?>
                                                        <img src="uploads/avatars/<?php echo $product['avatar']; ?>" alt="<?php echo $product['username']; ?>" class="rounded-circle me-2" style="width: 24px; height: 24px; object-fit: cover;">
                                                    <?php else: ?>
                                                        <i class="fas fa-user-circle me-2"></i>
                                                    <?php endif; ?>
                                                    <small class="text-muted">Đăng bởi: <?php echo $product['username']; ?></small>
                                                </div>
                                                <p class="card-text"><small class="text-muted">Đã thêm vào yêu thích: <?php echo formatDate($product['favorited_at']); ?></small></p>
                                            </div>
                                            <div class="card-footer bg-white">
                                                <div class="d-flex justify-content-between">
                                                    <a href="product-detail.php?id=<?php echo $product['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-eye me-1"></i>Xem chi tiết
                                                    </a>
                                                    <button class="btn btn-sm btn-outline-danger favorite-btn" data-product-id="<?php echo $product['id']; ?>">
                                                        <i class="fas fa-heart text-danger"></i> Bỏ thích
                                                    </button>
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
                                            <a class="page-link" href="?page=<?php echo $page - 1; ?>" aria-label="Previous">
                                                <span aria-hidden="true">&laquo;</span>
                                            </a>
                                        </li>
                                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                            <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
                                                <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                            </li>
                                        <?php endfor; ?>
                                        <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $page + 1; ?>" aria-label="Next">
                                                <span aria-hidden="true">&raquo;</span>
                                            </a>
                                        </li>
                                    </ul>
                                </nav>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="text-center py-5">
                                <i class="far fa-heart fa-3x mb-3 text-muted"></i>
                                <h4>Bạn chưa có sản phẩm yêu thích nào</h4>
                                <p class="text-muted">Hãy khám phá các sản phẩm và thêm vào danh sách yêu thích của bạn!</p>
                                <a href="index.php" class="btn btn-primary mt-2">
                                    <i class="fas fa-search me-1"></i>Khám phá sản phẩm
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
    
    <!-- jQuery (required for AJAX) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
    $(document).ready(function() {
        console.log('Favorites page loaded');
        
        // Message display function
        function showMessage(message, type) {
            var alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
            var alertHtml = '<div class="alert ' + alertClass + ' alert-dismissible fade show position-fixed" style="top: 20px; right: 20px; z-index: 9999;" role="alert">' +
                message +
                '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' +
                '</div>';
            
            // Remove existing alerts
            $('.alert').remove();
            
            // Add new alert
            $('body').append(alertHtml);
            
            // Auto hide after 3 seconds
            setTimeout(function() {
                $('.alert').fadeOut();
            }, 3000);
        }
        
        // Favorite toggle functionality
        $(document).on('click', '.favorite-btn', function(e) {
            e.preventDefault();
            console.log('Favorite button clicked');
            
            var button = $(this);
            var productId = button.data('product-id');
            
            console.log('Product ID:', productId);
            
            if (!productId) {
                console.error('No product ID found');
                showMessage('Lỗi: Không tìm thấy ID sản phẩm', 'error');
                return;
            }
            
            // Disable button during request
            button.prop('disabled', true);
            var originalHtml = button.html();
            button.html('<i class="fas fa-spinner fa-spin"></i> Đang xử lý...');
            
            $.ajax({
                url: 'ajax/toggle-favorite.php',
                type: 'POST',
                data: {
                    product_id: productId
                },
                dataType: 'json',
                beforeSend: function() {
                    console.log('Sending AJAX request...');
                },
                success: function(response) {
                    console.log('AJAX Success:', response);
                    
                    if (response.success) {
                        if (response.action === 'removed') {
                            // Remove the product card from favorites page
                            button.closest('.col-md-6').fadeOut(500, function() {
                                $(this).remove();
                                
                                // Check if no more favorites
                                if ($('.col-md-6').length === 0) {
                                    location.reload();
                                }
                            });
                            showMessage(response.message, 'success');
                        } else {
                            // This shouldn't happen on favorites page, but handle it
                            button.html('<i class="fas fa-heart text-danger"></i> Bỏ thích');
                            showMessage(response.message, 'success');
                        }
                    } else {
                        console.error('Server error:', response.message);
                        showMessage(response.message, 'error');
                        button.html(originalHtml);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', error);
                    console.error('Status:', status);
                    console.error('Response:', xhr.responseText);
                    
                    if (xhr.status === 0) {
                        showMessage('Lỗi kết nối. Vui lòng kiểm tra internet.', 'error');
                    } else if (xhr.status === 404) {
                        showMessage('Không tìm thấy trang xử lý yêu cầu.', 'error');
                    } else if (xhr.status === 500) {
                        showMessage('Lỗi server. Vui lòng thử lại sau.', 'error');
                    } else {
                        showMessage('Đã xảy ra lỗi: ' + error, 'error');
                    }
                    
                    button.html(originalHtml);
                },
                complete: function() {
                    // Re-enable button
                    button.prop('disabled', false);
                }
            });
        });
        
        // Test if buttons are clickable
        $('.favorite-btn').each(function() {
            console.log('Found favorite button with product ID:', $(this).data('product-id'));
        });
    });
    </script>
</body>
</html>
