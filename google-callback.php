<?php
session_start();
include 'config/db.php';
include 'config/google-config.php';
include 'includes/functions.php';

// Check if code is present
if (!isset($_GET['code'])) {
    redirect('login.php?error=google_auth_failed');
}

$code = $_GET['code'];

try {
    // Get access token
    $token_data = getGoogleAccessToken($code);
    
    if (!isset($token_data['access_token'])) {
        redirect('login.php?error=google_token_failed');
    }
    
    // Get user info
    $user_info = getGoogleUserInfo($token_data['access_token']);
    
    if (!$user_info || !isset($user_info['email'])) {
        redirect('login.php?error=google_user_info_failed');
    }
    
    $google_id = $user_info['id'];
    $email = $user_info['email'];
    $name = $user_info['name'];
    $picture = $user_info['picture'];
    
    // Check if user exists
    $sql = "SELECT * FROM users WHERE email = ? OR google_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $email, $google_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // User exists, update Google ID if not set
        $user = $result->fetch_assoc();
        
        if (empty($user['google_id'])) {
            $sql = "UPDATE users SET google_id = ?, avatar = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssi", $google_id, $picture, $user['id']);
            $stmt->execute();
        }
        
        // Check if account is active
        if ($user['status'] !== 'active') {
            redirect('login.php?error=account_suspended');
        }
        
        // Set session variables
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_avatar'] = $user['avatar'];
        
        // Update last login
        $sql = "UPDATE users SET last_login = NOW() WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user['id']);
        $stmt->execute();
        
    } else {
        // Create new user
        $username = generateUsernameFromEmail($email);
        
        // Make sure username is unique
        $original_username = $username;
        $counter = 1;
        while (usernameExists($conn, $username)) {
            $username = $original_username . $counter;
            $counter++;
        }
        
        $sql = "INSERT INTO users (username, email, google_id, avatar, role, status, created_at) VALUES (?, ?, ?, ?, 'user', 'active', NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssss", $username, $email, $google_id, $picture);
        
        if ($stmt->execute()) {
            $user_id = $conn->insert_id;
            
            // Set session variables
            $_SESSION['user_id'] = $user_id;
            $_SESSION['username'] = $username;
            $_SESSION['user_email'] = $email;
            $_SESSION['user_role'] = 'user';
            $_SESSION['user_avatar'] = $picture;
            
            // Send welcome email
            $subject = "Chào mừng đến với 2HandShop";
            $message = "
                <html>
                <head>
                    <title>Chào mừng đến với 2HandShop</title>
                </head>
                <body>
                    <h2>Xin chào $username,</h2>
                    <p>Cảm ơn bạn đã đăng ký tài khoản tại 2HandShop thông qua Google.</p>
                    <p>Thông tin tài khoản của bạn:</p>
                    <ul>
                        <li>Tên đăng nhập: $username</li>
                        <li>Email: $email</li>
                    </ul>
                    <p>Bạn có thể cập nhật thông tin cá nhân tại <a href='http://localhost/c2c/profile.php'>đây</a>.</p>
                    <p>Trân trọng,<br>Đội ngũ 2HandShop</p>
                </body>
                </html>
            ";
            
            sendEmail($email, $subject, $message);
        } else {
            redirect('login.php?error=registration_failed');
        }
    }
    
    // Redirect to appropriate page
    if ($_SESSION['user_role'] == 'admin') {
        redirect('admin/index.php');
    } else {
        redirect('index.php');
    }
    
} catch (Exception $e) {
    error_log("Google OAuth Error: " . $e->getMessage());
    redirect('login.php?error=google_auth_error');
}

// Helper functions
function generateUsernameFromEmail($email) {
    $username = explode('@', $email)[0];
    $username = preg_replace('/[^a-zA-Z0-9]/', '', $username);
    return strtolower($username);
}

function usernameExists($conn, $username) {
    $sql = "SELECT id FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->num_rows > 0;
}
?>
