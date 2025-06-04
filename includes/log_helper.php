<?php
/**
 * Helper functions for system logging
 */

/**
 * Add a log entry to the system_logs table
 * 
 * @param string $log_type The type of log (info, warning, error, security, user, product)
 * @param string $message The log message
 * @param string $user_info Additional user information (optional)
 * @return bool True if log was added successfully, false otherwise
 */
function addLog($conn, $log_type, $message, $user_info = '') {
    // Validate log type
    $valid_types = ['info', 'warning', 'error', 'security', 'user', 'product'];
    if (!in_array($log_type, $valid_types)) {
        $log_type = 'info';
    }
    
    // Get user info if not provided
    if (empty($user_info) && isset($_SESSION['user_id'])) {
        $user_info = $_SESSION['username'] . ' (ID: ' . $_SESSION['user_id'] . ')';
    }
    
    // Get IP address
    $ip_address = $_SERVER['REMOTE_ADDR'];
    
    // Insert log entry
    $sql = "INSERT INTO system_logs (log_type, message, user_info, ip_address, created_at) 
            VALUES (?, ?, ?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $log_type, $message, $user_info, $ip_address);
    
    return $stmt->execute();
}

/**
 * Add a user activity log
 * 
 * @param string $action The user action (register, login, update, etc.)
 * @param int $user_id The user ID
 * @param string $details Additional details (optional)
 * @return bool True if log was added successfully, false otherwise
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
 * Add a product activity log
 * 
 * @param string $action The product action (create, update, delete, etc.)
 * @param int $product_id The product ID
 * @param int $user_id The user ID who performed the action
 * @param string $details Additional details (optional)
 * @return bool True if log was added successfully, false otherwise
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
 * Add a security log
 * 
 * @param string $action The security action (login, logout, failed login, etc.)
 * @param string $details Additional details
 * @param string $user_info User information (optional)
 * @return bool True if log was added successfully, false otherwise
 */
function logSecurityActivity($conn, $action, $details, $user_info = '') {
    $message = "Security: $action - $details";
    
    return addLog($conn, 'security', $message, $user_info);
}

/**
 * Add an error log
 * 
 * @param string $error The error message
 * @param string $source The error source (file, function, etc.)
 * @return bool True if log was added successfully, false otherwise
 */
function logError($conn, $error, $source = '') {
    $message = "Error";
    if (!empty($source)) {
        $message .= " in $source";
    }
    $message .= ": $error";
    
    return addLog($conn, 'error', $message);
}
