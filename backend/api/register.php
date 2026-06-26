<?php
header('Content-Type: application/json');
session_start();

require_once __DIR__ . '/../../pdo.php';

$username = $_POST['username'] ?? '';
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

if (empty($username) || empty($email) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'All fields must be filled in!']);
    exit;
}

try {
    $checkSql = "SELECT id FROM users WHERE username = ? OR email = ?";
    $checkStmt = $pdo->prepare($checkSql);
    $checkStmt->execute([$username, $email]);
    $existingUser = $checkStmt->fetch(PDO::FETCH_ASSOC);

    if ($existingUser) {
        echo json_encode(['success' => false, 'message' => 'The username or email address has already been registered.']);
        exit;
    }

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $sql = "INSERT INTO users (username, email, password, created_at) VALUES (?, ?, ?, NOW())";
    $stmt = $pdo->prepare($sql);
    
    if ($stmt->execute([$username, $email, $hashedPassword])) {
        echo json_encode(['success' => true, 'message' => 'Registered successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Fail to register.']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>