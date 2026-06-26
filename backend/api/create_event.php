<?php
header('Content-Type: application/json');
session_start();

require_once __DIR__ . '/../../pdo.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit;
}

$title = trim($_POST['title'] ?? '');
$description = trim($_POST['description'] ?? '');
$event_date = $_POST['event_date'] ?? '';
$location = trim($_POST['location'] ?? '');

if (empty($title) || empty($event_date)) {
    echo json_encode(['success' => false, 'message' => 'Title and event date are required']);
    exit;
}

$date = DateTime::createFromFormat('Y-m-d', $event_date);
if (!$date || $date->format('Y-m-d') !== $event_date) {
    echo json_encode(['success' => false, 'message' => 'Invalid date format. Use YYYY-MM-DD']);
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    $sql = "INSERT INTO events (title, description, event_date, location, created_by) 
            VALUES (?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$title, $description, $event_date, $location ?: null, $user_id]);

    echo json_encode([
        'success' => true,
        'message' => 'Event created successfully',
        'event_id' => $pdo->lastInsertId()
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>