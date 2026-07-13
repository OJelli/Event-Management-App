<?php
try {
    $pdo = new PDO('mysql:host=localhost;port=3306;dbname=event_management', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("SET NAMES utf8");

    $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'is_active'");
    if ($stmt->rowCount() === 0) {
        $pdo->exec("ALTER TABLE users ADD COLUMN is_active TINYINT(1) NOT NULL DEFAULT 1");
    }

    // Check if remember_token column exists, add if missing
    $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'remember_token'");
    if ($stmt->rowCount() === 0) {
        $pdo->exec("ALTER TABLE users ADD COLUMN remember_token VARCHAR(255) NULL");
    }

    // Auto-login via remember_token cookie
    if (!isset($_SESSION)) {
        session_start();
    }

    if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_token'])) {
        $token = $_COOKIE['remember_token'];
        
        $stmt = $pdo->prepare("SELECT id, username FROM users WHERE remember_token = ?");
        $stmt->execute([$token]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
        }
    }

} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>