<?php
header('Content-Type: application/json');
session_start();

require_once __DIR__ . '/../../pdo.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit;
}

$user_id = $_SESSION['user_id'];
$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'POST') {
        $username = $_POST['username'] ?? '';
        $email = $_POST['email'] ?? '';

        if (empty($username) || empty($email)) {
            echo json_encode(['success' => false, 'message' => 'Username and email are required']);
            exit;
        }

        $sql = "UPDATE users SET username = ?, email = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$username, $email, $user_id]);

        echo json_encode([
            'success' => true,
            'message' => 'Profile updated successfully'
        ]);
    } else {
        $sql = "SELECT id, username, email, created_at, is_active FROM users WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            echo json_encode([
                'success' => true,
                'data' => $user
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'User not found']);
        }
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>