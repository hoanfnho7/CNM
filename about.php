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
    <title>Giới thiệu - 2HandShop</title>
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
                <div class="card mb-4">
                    <div class="card-body p-5">
                        <h1 class="mb-4">Giới thiệu về 2HandShop</h1>
                        
                        <div class="mb-5">
                            <h3>Tầm nhìn của chúng tôi</h3>
                            <p>2HandShop ra đời với sứ mệnh tạo ra một nền tảng mua bán đồ cũ uy tín, an toàn và tiện lợi cho người dùng Việt Nam. Chúng tôi tin rằng việc tái sử dụng và mua bán đồ đã qua sử dụng không chỉ giúp tiết kiệm chi phí mà còn góp phần bảo vệ môi trường, giảm thiểu rác thải và tài nguyên tiêu thụ.</p>
                        </div>
                        
                        <div class="mb-5">
                            <h3>Giá trị cốt lõi</h3>
                            <div class="row mt-4">
                                <div class="col-md-4 mb-4">
                                    <div class="card h-100 border-0 shadow-sm">
                                        <div class="card-body text-center p-4">
                                            <i class="fas fa-shield-alt fa-3x mb-3 text-primary"></i>
                                            <h4>Uy tín</h4>
                                            <p class="text-muted">Chúng tôi đặt sự uy tín lên hàng đầu, đảm bảo mọi giao dịch đều minh bạch và đáng tin cậy.</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-4">
                                    <div class="card h-100 border-0 shadow-sm">
                                        <div class="card-body text-center p-4">
                                            <i class="fas fa-leaf fa-3x mb-3 text-success"></i>
                                            <h4>Bền vững</h4>
                                            <p class="text-muted">Chúng tôi khuyến khích lối sống bền vững thông qua việc tái sử dụng và giảm thiểu lãng phí.</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-4">
                                    <div class="card h-100 border-0 shadow-sm">
                                        <div class="card-body text-center p-4">
                                            <i class="fas fa-users fa-3x mb-3 text-info"></i>
                                            <h4>Cộng đồng</h4>
                                            <p class="text-muted">Chúng tôi xây dựng một cộng đồng người dùng thân thiện, hỗ trợ lẫn nhau.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-5">
                            <h3>Cách thức hoạt động</h3>
                            <div class="row mt-4">
                                <div class="col-md-3 mb-4">
                                    <div class="card h-100">
                                        <div class="card-body text-center p-4">
                                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 60px; height: 60px;">
                                                <h3 class="mb-0">1</h3>
                                            </div>
                                            <h5>Đăng ký</h5>
                                            <p class="text-muted small">Tạo tài khoản miễn phí trên 2HandShop</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3 mb-4">
                                    <div class="card h-100">
                                        <div class="card-body text-center p-4">
                                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 60px; height: 60px;">
                                                <h3 class="mb-0">2</h3>
                                            </div>
                                            <h5>Đăng sản phẩm</h5>
                                            <p class="text-muted small">Đăng thông tin và hình ảnh sản phẩm bạn muốn bán</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3 mb-4">
                                    <div class="card h-100">
                                        <div class="card-body text-center p-4">
                                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 60px; height: 60px;">
                                                <h3 class="mb-0">3</h3>
                                            </div>
                                            <h5>Kết nối</h5>
                                            <p class="text-muted small">Người mua liên hệ với bạn qua hệ thống tin nhắn</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3 mb-4">
                                    <div class="card h-100">
                                        <div class="card-body text-center p-4">
                                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 60px; height: 60px;">
                                                <h3 class="mb-0">4</h3>
                                            </div>
                                            <h5>Giao dịch</h5>
                                            <p class="text-muted small">Thỏa thuận và hoàn tất giao dịch an toàn</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-5">
                            <h3>Đội ngũ của chúng tôi</h3>
                            <p>2HandShop được phát triển bởi một đội ngũ đam mê công nghệ và bảo vệ môi trường. Chúng tôi không ngừng cải tiến nền tảng để mang đến trải nghiệm tốt nhất cho người dùng.</p>
                        </div>
                        
                        <div class="text-center mt-5">
                            <h3>Bắt đầu ngay hôm nay</h3>
                            <p class="mb-4">Hãy tham gia cộng đồng 2HandShop và trải nghiệm cách mua bán đồ cũ hiện đại, an toàn và tiện lợi.</p>
                            <?php if (!isLoggedIn()): ?>
                                <a href="register.php" class="btn btn-primary me-2">Đăng ký</a>
                                <a href="login.php" class="btn btn-outline-primary">Đăng nhập</a>
                            <?php else: ?>
                                <a href="product-create.php" class="btn btn-primary">Đăng sản phẩm ngay</a>
                            <?php endif; ?>
                        </div>
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
