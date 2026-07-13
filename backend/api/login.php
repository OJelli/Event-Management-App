<?php
header('Content-Type: application/json');
session_start();

require_once __DIR__ . '/../../pdo.php';

$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';
$remember_me = $_POST['remember_me'] ?? false;

if (empty($username) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Username and password must be filled in.']);
    exit;
}

$username = trim($username);

try {
    // Prepared statement to prevent SQL injection
    $sql = "SELECT id, username, password, is_active FROM users WHERE username = ? OR email = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$username, $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'Invalid credentials.']);
        exit;
    }

    if ($user['is_active'] == 0) {
        echo json_encode(['success' => false, 'message' => 'The account has been deactivated. Please contact the administrator.']);
        exit;
    }

    if (password_verify($password, $user['password'])) {
        // Prevent session fixation attacks
        session_regenerate_id(true);
        
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        
        // Handle "Remember Me" functionality
        // Check for various possible values from checkbox
        $remember = false;
        if ($remember_me === 'true' || $remember_me === true || $remember_me === 1 || $remember_me === 'on') {
            $remember = true;
        }
        
        if ($remember) {
            // Generate a secure random token
            $token = bin2hex(random_bytes(32));
            
            // Store token in database
            $updateSql = "UPDATE users SET remember_token = ? WHERE id = ?";
            $updateStmt = $pdo->prepare($updateSql);
            $updateStmt->execute([$token, $user['id']]);
            
            // Set cookie to expire in 30 days
            setcookie('remember_token', $token, time() + (86400 * 30), '/');
        } else {
            // Clear any existing remember token if checkbox is not checked
            $updateSql = "UPDATE users SET remember_token = NULL WHERE id = ?";
            $updateStmt = $pdo->prepare($updateSql);
            $updateStmt->execute([$user['id']]);
            
            // Delete existing cookie if present
            if (isset($_COOKIE['remember_token'])) {
                setcookie('remember_token', '', time() - 3600, '/');
            }
        }
        
        echo json_encode(['success' => true, 'message' => 'Login successfully', 'user_id' => $user['id']]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid credentials.']);
    }
} catch (PDOException $e) {
    // Log error without exposing details to client
    error_log("Login error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'System error. Please try again later.']);
}
?>