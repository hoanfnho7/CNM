<?php
/**
 * Các hàm hỗ trợ ghi log hệ thống
 */

/**
 * Thêm một bản ghi log vào bảng system_logs
 * 
 * @param string $log_type Loại log (info, warning, error, security, user, product)
 * @param string $message Nội dung log
 * @param string $user_info Thông tin người dùng bổ sung (tùy chọn)
 * @return bool True nếu thêm log thành công, false nếu thất bại
 */
function addLog($conn, $log_type, $message, $user_info = '') {
    // Xác thực loại log
    $valid_types = ['info', 'warning', 'error', 'security', 'user', 'product'];
    if (!in_array($log_type, $valid_types)) {
        $log_type = 'info';
    }
    
    // Lấy thông tin người dùng nếu chưa được cung cấp
    if (empty($user_info) && isset($_SESSION['user_id'])) {
        $user_info = $_SESSION['username'] . ' (ID: ' . $_SESSION['user_id'] . ')';
    }
    
    // Lấy địa chỉ IP
    $ip_address = $_SERVER['REMOTE_ADDR'];
    
    // Thêm bản ghi log
    $sql = "INSERT INTO system_logs (log_type, message, user_info, ip_address, created_at) 
            VALUES (?, ?, ?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $log_type, $message, $user_info, $ip_address);
    
    return $stmt->execute();
}

/**
 * Thêm log hoạt động người dùng
 * 
 * @param string $action Hành động của người dùng (đăng ký, đăng nhập, cập nhật, v.v.)
 * @param int $user_id ID người dùng
 * @param string $details Chi tiết bổ sung (tùy chọn)
 * @return bool True nếu thêm log thành công, false nếu thất bại
 */
function logUserActivity($conn, $action, $user_id, $details = '') {
    $user = getUserById($conn, $user_id);
    $username = $user ? $user['username'] : 'Unknown';
    
    $message = "User action: $action";
    if (!empty($details)) {
        $message .= " - $details";
    }
    
    $user_info = "$username (ID: $user_id)";
    
    return addLog($conn, 'user', $message, $user_info);
}

/**
 * Thêm log hoạt động sản phẩm
 * 
 * @param string $action Hành động với sản phẩm (tạo, cập nhật, xóa, v.v.)
 * @param int $product_id ID sản phẩm
 * @param int $user_id ID người dùng thực hiện hành động
 * @param string $details Chi tiết bổ sung (tùy chọn)
 * @return bool True nếu thêm log thành công, false nếu thất bại
 */
function logProductActivity($conn, $action, $product_id, $user_id, $details = '') {
    $product = getProductById($conn, $product_id);
    $product_name = $product ? $product['name'] : 'Unknown';
    
    $user = getUserById($conn, $user_id);
    $username = $user ? $user['username'] : 'Unknown';
    
    $message = "Product action: $action - Product: $product_name (ID: $product_id)";
    if (!empty($details)) {
        $message .= " - $details";
    }
    
    $user_info = "$username (ID: $user_id)";
    
    return addLog($conn, 'product', $message, $user_info);
}

/**
 * Thêm log bảo mật
 * 
 * @param string $action Hành động bảo mật (đăng nhập, đăng xuất, đăng nhập thất bại, v.v.)
 * @param string $details Chi tiết bổ sung
 * @param string $user_info Thông tin người dùng (tùy chọn)
 * @return bool True nếu thêm log thành công, false nếu thất bại
 */
function logSecurityActivity($conn, $action, $details, $user_info = '') {
    $message = "Security: $action - $details";
    
    return addLog($conn, 'security', $message, $user_info);
}

/**
 * Thêm log lỗi
 * 
 * @param string $error Thông báo lỗi
 * @param string $source Nguồn gốc lỗi (tệp, hàm, v.v.)
 * @return bool True nếu thêm log thành công, false nếu thất bại
 */
function logError($conn, $error, $source = '') {
    $message = "Error";
    if (!empty($source)) {
        $message .= " in $source";
    }
    $message .= ": $error";
    
    return addLog($conn, 'error', $message);
}
