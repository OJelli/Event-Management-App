<?php
header('Content-Type: application/json');
session_start();

require_once __DIR__ . '/../../pdo.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit;
}

$event_id = $_POST['event_id'] ?? '';
$title = trim($_POST['title'] ?? '');
$description = trim($_POST['description'] ?? '');
$event_date = $_POST['event_date'] ?? '';
$location = trim($_POST['location'] ?? '');

if (empty($event_id) || !is_numeric($event_id)) {
    echo json_encode(['success' => false, 'message' => 'Valid event ID is required']);
    exit;
}

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
    $checkSql = "SELECT created_by FROM events WHERE id = ?";
    $checkStmt = $pdo->prepare($checkSql);
    $checkStmt->execute([$event_id]);
    $event = $checkStmt->fetch(PDO::FETCH_ASSOC);

    if (!$event) {
        echo json_encode(['success' => false, 'message' => 'Event not found']);
        exit;
    }

    if ($event['created_by'] != $user_id) {
        echo json_encode(['success' => false, 'message' => 'You do not have permission to update this event']);
        exit;
    }

    $sql = "UPDATE events SET title = ?, description = ?, event_date = ?, location = ? WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$title, $description, $event_date, $location ?: null, $event_id]);

    echo json_encode([
        'success' => true,
        'message' => 'Event updated successfully'
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>