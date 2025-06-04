<?php
session_start();
include 'config/db.php';
include 'includes/functions.php';

// Kiểm tra xem ID sản phẩm có được cung cấp không
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    redirect('index.php');
}

$product_id = (int)$_GET['id'];
$product = getProductById($conn, $product_id);

// Nếu sản phẩm không tồn tại hoặc chưa được duyệt (trừ khi người dùng là admin hoặc chủ sở hữu)
if (!$product || ($product['status'] !== 'approved' && !isAdmin() && (!isLoggedIn() || $_SESSION['user_id'] != $product['user_id']))) {
    redirect('index.php');
}

// Tăng số lượt xem
if (isLoggedIn() && $_SESSION['user_id'] != $product['user_id']) {
    $sql = "UPDATE products SET views = views + 1 WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
}

// Kiểm tra xem sản phẩm có trong danh sách yêu thích của người dùng không
$is_favorite = false;
if (isLoggedIn()) {
    $sql = "SELECT * FROM favorites WHERE user_id = ? AND product_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $_SESSION['user_id'], $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $is_favorite = $result->num_rows > 0;
}

// Lấy thông tin người bán
$seller = getUserById($conn, $product['user_id']);

// Định dạng hiển thị tình trạng sản phẩm
$condition_display = [
    'new' => 'Mới',
    'like_new' => 'Như mới',
    'good' => 'Tốt',
    'fair' => 'Khá',
    'poor' => 'Kém'
];

// Định dạng hiển thị danh mục
$category_display = [
    'electronics' => 'Điện tử',
    'furniture' => 'Nội thất',
    'clothing' => 'Quần áo',
    'books' => 'Sách',
    'toys' => 'Đồ chơi',
    'others' => 'Khác'
];

// Tạo URL đầy đủ
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$product_url = $protocol . '://' . $host . dirname($_SERVER['PHP_SELF']) . '/product-detail.php?id=' . $product_id;
$qr_url = $protocol . '://' . $host . dirname($_SERVER['PHP_SELF']) . '/generate-qr.php?id=' . $product_id;
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $product['name']; ?> - 2HandShop</title>
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
        <div id="alertContainer"></div>

        <?php if ($product['status'] === 'pending'): ?>
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle me-2"></i>Sản phẩm này đang chờ duyệt và chưa hiển thị công khai.
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-body p-0 product-img-container">
                        <img src="uploads/products/<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>" class="img-fluid w-100">
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-body">
                        <h1 class="mb-3"><?php echo $product['name']; ?></h1>
                        <h3 class="text-danger mb-4"><?php echo number_format($product['price']); ?> VNĐ</h3>

                        <div class="d-flex align-items-center mb-4">
                            <div class="me-3">
                                <?php if (!empty($product['avatar'])): ?>
                                    <img src="uploads/avatars/<?php echo $product['avatar']; ?>" alt="<?php echo $product['username']; ?>" class="rounded-circle" style="width: 40px; height: 40px; object-fit: cover;">
                                <?php else: ?>
                                    <i class="fas fa-user-circle fa-2x"></i>
                                <?php endif; ?>
                            </div>
                            <div>
                                <p class="mb-0">Đăng bởi: <a href="seller-profile.php?id=<?php echo $product['user_id']; ?>"><?php echo $product['username']; ?></a></p>
                                <p class="text-muted mb-0 small">Đăng lúc: <?php echo formatDate($product['created_at']); ?></p>
                                <?php if (!empty($seller['phone'])): ?>
                                    <p class="mb-0"><i class="fas fa-phone me-1"></i><strong>SĐT:</strong> 
                                        <a href="tel:<?php echo $seller['phone']; ?>" class="text-decoration-none">
                                            <?php echo $seller['phone']; ?>
                                        </a>
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="mb-4">
                            <p><strong>Tình trạng:</strong> <?php echo $condition_display[$product['condition']]; ?></p>
                            <p><strong>Danh mục:</strong> <?php echo $category_display[$product['category']]; ?></p>
                            <p><strong>Lượt xem:</strong> <?php echo $product['views']; ?></p>
                        </div>

                        <div class="d-flex gap-2 mb-4">
                            <?php if (isLoggedIn() && $_SESSION['user_id'] != $product['user_id']): ?>
                                <a href="chat.php?seller_id=<?php echo $product['user_id']; ?>&product_id=<?php echo $product_id; ?>" class="btn btn-primary">
                                    <i class="fas fa-comment me-2"></i>Nhắn tin với người bán
                                </a>
                                <?php if (!empty($seller['phone'])): ?>
                                    <a href="tel:<?php echo $seller['phone']; ?>" class="btn btn-success">
                                        <i class="fas fa-phone me-2"></i>Gọi điện
                                    </a>
                                <?php endif; ?>
                                <button type="button" class="btn btn-outline-danger favorite-btn" data-product-id="<?php echo $product_id; ?>" style="z-index: 10; position: relative;">
                                    <i class="<?php echo $is_favorite ? 'fas' : 'far'; ?> fa-heart <?php echo $is_favorite ? 'text-danger' : ''; ?>"></i>
                                    <?php echo $is_favorite ? 'Bỏ thích' : 'Yêu thích'; ?>
                                </button>
                                <button class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#reportModal">
                                    <i class="fas fa-flag"></i>
                                </button>
                            <?php elseif (isLoggedIn() && $_SESSION['user_id'] == $product['user_id']): ?>
                                <a href="product-edit.php?id=<?php echo $product_id; ?>" class="btn btn-primary">
                                    <i class="fas fa-edit me-2"></i>Chỉnh sửa
                                </a>
                                <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
                                    <i class="fas fa-trash me-2"></i>Xóa
                                </button>
                                <?php if ($product['status'] === 'sold'): ?>
                                    <button class="btn btn-success" disabled>
                                        <i class="fas fa-check-circle me-2"></i>Đã bán
                                    </button>
                                <?php else: ?>
                                    <a href="mark-as-sold.php?id=<?php echo $product_id; ?>" class="btn btn-outline-success">
                                        <i class="fas fa-check-circle me-2"></i>Đánh dấu đã bán
                                    </a>
                                <?php endif; ?>
                            <?php else: ?>
                                <a href="login.php" class="btn btn-primary">
                                    <i class="fas fa-sign-in-alt me-2"></i>Đăng nhập để liên hệ
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Mô tả sản phẩm</h5>
                    </div>
                    <div class="card-body">
                        <p class="mb-0"><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
                    </div>
                </div>
                
                <div class="card mt-3">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-qrcode me-2"></i>QR Code</h6>
                    </div>
                    <div class="card-body text-center">
                        <div class="qr-code-container">
                            <img src="<?php echo $qr_url; ?>" alt="QR Code" class="qr-code-image">
                        </div>
                        <p class="mt-2 text-muted small">Quét mã QR để chia sẻ sản phẩm</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Related Products -->
        <h3 class="mt-5 mb-4">Sản phẩm tương tự</h3>
        <div class="row">
            <?php
            $sql = "SELECT p.*, u.username, u.avatar FROM products p 
                    JOIN users u ON p.user_id = u.id 
                    WHERE p.category = ? AND p.id != ? AND p.status = 'approved' 
                    ORDER BY p.created_at DESC LIMIT 4";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $product['category'], $product_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo '<div class="col-md-3 mb-4">';
                    echo '<div class="card h-100">';
                    echo '<img src="uploads/products/' . $row['image'] . '" class="card-img-top" alt="' . $row['name'] . '" style="height: 200px; object-fit: cover;">';
                    echo '<div class="card-body">';
                    echo '<h5 class="card-title">' . $row['name'] . '</h5>';
                    echo '<p class="card-text text-danger fw-bold">' . number_format($row['price']) . ' VNĐ</p>';
                    echo '<p class="card-text"><small class="text-muted">Tình trạng: ' . $condition_display[$row['condition']] . '</small></p>';
                    echo '</div>';
                    echo '<div class="card-footer bg-white d-flex justify-content-between align-items-center">';
                    echo '<small class="text-muted">Đăng bởi: ' . $row['username'] . '</small>';
                    echo '<a href="product-detail.php?id=' . $row['id'] . '" class="btn btn-sm btn-outline-primary">Xem chi tiết</a>';
                    echo '</div>';
                    echo '</div>';
                    echo '</div>';
                }
            } else {
                echo '<div class="col-12"><p class="text-center">Không có sản phẩm tương tự.</p></div>';
            }
            ?>
        </div>
    </div>

    <!-- Report Modal -->
    <div class="modal fade" id="reportModal" tabindex="-1" aria-labelledby="reportModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="reportModalLabel">Báo cáo sản phẩm</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="reportForm" data-product-id="<?php echo $product_id; ?>">
                        <div class="mb-3">
                            <label for="reportReason" class="form-label">Lý do báo cáo</label>
                            <select class="form-select" id="reportReason" required>
                                <option value="" selected disabled>Chọn lý do</option>
                                <option value="fake">Sản phẩm giả mạo</option>
                                <option value="inappropriate">Nội dung không phù hợp</option>
                                <option value="scam">Lừa đảo</option>
                                <option value="other">Lý do khác</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="reportDescription" class="form-label">Mô tả chi tiết</label>
                            <textarea class="form-control" id="reportDescription" rows="3"></textarea>
                        </div>
                        <button type="submit" class="btn btn-danger">Gửi báo cáo</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Modal -->
    <?php if (isLoggedIn() && $_SESSION['user_id'] == $product['user_id']): ?>
        <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="deleteModalLabel">Xác nhận xóa</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>Bạn có chắc chắn muốn xóa sản phẩm này?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                        <a href="product-delete.php?id=<?php echo $product_id; ?>" class="btn btn-danger">Xóa</a>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php include 'includes/footer.php'; ?>

    <!-- jQuery (required for AJAX) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
    $(document).ready(function() {
        console.log('Product detail page loaded');
        
        // Check if favorite button exists
        const favoriteBtn = $('.favorite-btn');
        console.log('Favorite buttons found:', favoriteBtn.length);
        
        // Favorite toggle functionality
        $(document).on('click', '.favorite-btn', function(e) {
            e.preventDefault();
            console.log('Favorite button clicked');
            
            const button = $(this);
            const productId = button.data('product-id');
            
            console.log('Product ID:', productId);
            
            if (!productId) {
                console.error('No product ID found');
                showAlert('Lỗi: Không tìm thấy ID sản phẩm', 'danger');
                return;
            }
            
            // Show loading state
            const originalHtml = button.html();
            button.html('<i class="fas fa-spinner fa-spin"></i> Đang xử lý...');
            button.prop('disabled', true);
            
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
                        if (response.action === 'added') {
                            // Update button for "added to favorites"
                            button.html('<i class="fas fa-heart text-danger"></i> Bỏ thích');
                            button.removeClass('btn-outline-danger').addClass('btn-danger');
                            console.log('Updated button to "remove" state');
                        } else {
                            // Update button for "removed from favorites"
                            button.html('<i class="far fa-heart"></i> Yêu thích');
                            button.removeClass('btn-danger').addClass('btn-outline-danger');
                            console.log('Updated button to "add" state');
                        }
                        
                        // Show success message
                        showAlert(response.message, 'success');
                    } else {
                        console.error('Server error:', response.message);
                        showAlert(response.message, 'danger');
                        // Restore original button state
                        button.html(originalHtml);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', error);
                    console.error('Status:', status);
                    console.error('Response:', xhr.responseText);
                    
                    let errorMessage = 'Đã xảy ra lỗi. Vui lòng thử lại.';
                    
                    if (xhr.status === 0) {
                        errorMessage = 'Lỗi kết nối. Vui lòng kiểm tra internet.';
                    } else if (xhr.status === 404) {
                        errorMessage = 'Không tìm thấy trang xử lý yêu cầu.';
                    } else if (xhr.status === 500) {
                        errorMessage = 'Lỗi server. Vui lòng thử lại sau.';
                    }
                    
                    showAlert(errorMessage, 'danger');
                    // Restore original button state
                    button.html(originalHtml);
                },
                complete: function() {
                    // Re-enable button
                    button.prop('disabled', false);
                }
            });
        });
        
        // Report product functionality
        $('#reportForm').on('submit', function(e) {
            e.preventDefault();
            
            const form = $(this);
            const productId = form.data('product-id');
            const reason = $('#reportReason').val();
            const description = $('#reportDescription').val();
            
            if (!reason) {
                showAlert('Vui lòng chọn lý do báo cáo', 'warning');
                return;
            }
            
            $.ajax({
                url: 'ajax/report-product.php',
                type: 'POST',
                data: {
                    product_id: productId,
                    reason: reason,
                    description: description
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        showAlert('Báo cáo đã được gửi. Cảm ơn bạn!', 'success');
                        $('#reportModal').modal('hide');
                        form[0].reset();
                    } else {
                        showAlert(response.message, 'danger');
                    }
                },
                error: function() {
                    showAlert('Đã xảy ra lỗi khi gửi báo cáo.', 'danger');
                }
            });
        });
        
        // Function to show alerts
        function showAlert(message, type) {
            const alertHtml = `
                <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
            
            // Remove existing alerts
            $('#alertContainer .alert').remove();
            
            // Add new alert
            $('#alertContainer').html(alertHtml);
            
            // Auto hide after 5 seconds
            setTimeout(function() {
                $('#alertContainer .alert').fadeOut();
            }, 5000);
            
            // Scroll to top to show alert
            $('html, body').animate({scrollTop: 0}, 300);
        }
    });
    </script>
</body>

</html>
