<?php
// Hàm xử lý dữ liệu đầu vào
function sanitize($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Hàm kiểm tra người dùng đã đăng nhập chưa
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Hàm kiểm tra người dùng có phải admin không
function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'admin';
}

// Hàm chuyển hướng trang
function redirect($url) {
    header("Location: $url");
    exit();
}

// Hàm tạo chuỗi ngẫu nhiên
function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

// Hàm tải lên hình ảnh
function uploadImage($file, $folder) {
    $target_dir = "uploads/" . $folder . "/";
    
    // Tạo thư mục nếu chưa tồn tại
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $file_extension = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
    $new_filename = generateRandomString() . "_" . time() . "." . $file_extension;
    $target_file = $target_dir . $new_filename;
    
    // Kiểm tra file có phải là hình ảnh không
    $check = getimagesize($file["tmp_name"]);
    if($check === false) {
        return false;
    }
    
    // Kiểm tra kích thước file (tối đa 5MB)
    if ($file["size"] > 5000000) {
        return false;
    }
    
    // Cho phép các định dạng file
    if($file_extension != "jpg" && $file_extension != "png" && $file_extension != "jpeg" && $file_extension != "gif" ) {
        return false;
    }
    
    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        return $new_filename;
    } else {
        return false;
    }
}

// Hàm định dạng ngày tháng
function formatDate($date) {
    return date("d/m/Y H:i", strtotime($date));
}

// Hàm lấy thông tin người dùng theo ID
function getUserById($conn, $id) {
    $sql = "SELECT * FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    return null;
}

// Hàm lấy thông tin sản phẩm theo ID
function getProductById($conn, $id) {
    $sql = "SELECT p.*, u.username, u.avatar FROM products p 
            JOIN users u ON p.user_id = u.id 
            WHERE p.id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    return null;
}

// Hàm gửi email
function sendEmail($to, $subject, $message) {
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= 'From: SecondLife Market <noreply@secondlifemarket.com>' . "\r\n";
    
    // return mail($to, $subject, $message, $headers);
}
