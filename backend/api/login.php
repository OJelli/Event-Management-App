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

try {
    $sql = "SELECT id, username, password, is_active FROM users WHERE username = ? OR email = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$username, $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'The user does not exist.']);
        exit;
    }

    if ($user['is_active'] == 0) {
        echo json_encode(['success' => false, 'message' => 'The account has been deactivated. Please contact the administrator.']);
        exit;
    }

    if (password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        echo json_encode(['success' => true, 'message' => 'Login successfully', 'user_id' => $user['id']]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Wrong password']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>