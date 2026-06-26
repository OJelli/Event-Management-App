<?php
try {
	$pdo = new PDO('mysql:host=localhost;port=3306;dbname=event_management', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("SET NAMES utf8");
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>