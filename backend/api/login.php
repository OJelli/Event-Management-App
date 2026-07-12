<?php
header('Content-Type: application/json');
session_start();

require_once __DIR__ . '/../../pdo.php';

$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

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