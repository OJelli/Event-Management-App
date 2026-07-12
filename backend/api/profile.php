<?php
header('Content-Type: application/json');
session_start();

require_once __DIR__ . '/../../pdo.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit;
}

$user_id = $_SESSION['user_id'];
$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'POST') {
        // Sanitize input
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');

        // Validate required fields
        if (empty($username) || empty($email)) {
            echo json_encode(['success' => false, 'message' => 'Username and email are required']);
            exit;
        }

        // Update user profile
        $sql = "UPDATE users SET username = ?, email = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$username, $email, $user_id]);

        echo json_encode([
            'success' => true,
            'message' => 'Profile updated successfully'
        ]);
    } else {
        // Fetch user profile
        $sql = "SELECT id, username, email, created_at, is_active FROM users WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Escape output to prevent XSS attacks
            $user['username'] = htmlspecialchars($user['username'] ?? '', ENT_QUOTES, 'UTF-8');
            $user['email'] = htmlspecialchars($user['email'] ?? '', ENT_QUOTES, 'UTF-8');

            echo json_encode([
                'success' => true,
                'data' => $user
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'User not found']);
        }
    }
} catch (PDOException $e) {
    // Log error without exposing details to client
    error_log("Profile error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'System error. Please try again later.']);
}
?>