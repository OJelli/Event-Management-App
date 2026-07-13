<?php
header('Content-Type: application/json');
session_start();

require_once __DIR__ . '/../../pdo.php';

// Check if user is logged in, get user_id before destroying session
$user_id = $_SESSION['user_id'] ?? null;

// Clear database remember_token if user was logged in
if ($user_id) {
    try {
        $updateSql = "UPDATE users SET remember_token = NULL WHERE id = ?";
        $updateStmt = $pdo->prepare($updateSql);
        $updateStmt->execute([$user_id]);
    } catch (PDOException $e) {
        error_log("Logout error: " . $e->getMessage());
    }
}

// Clear session data
$_SESSION = array();

// Delete session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Delete remember me cookie
setcookie('remember_token', '', time() - 3600, '/');

// Destroy session
session_destroy();

echo json_encode(['success' => true, 'message' => 'Successfully logged out']);
?>