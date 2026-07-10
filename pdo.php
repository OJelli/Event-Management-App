<?php
try {
    $pdo = new PDO('mysql:host=localhost;port=3306;dbname=event_management', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("SET NAMES utf8");

    $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'is_active'");
    if ($stmt->rowCount() === 0) {
        $pdo->exec("ALTER TABLE users ADD COLUMN is_active TINYINT(1) NOT NULL DEFAULT 1");
    }
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>