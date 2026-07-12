<?php
header('Content-Type: application/json');
session_start();

require_once __DIR__ . '/../../pdo.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit;
}

// Validate event ID
$event_id = $_POST['event_id'] ?? '';

if (empty($event_id) || !is_numeric($event_id)) {
    echo json_encode(['success' => false, 'message' => 'Valid event ID is required']);
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    // Check if the event exists
    $eventCheck = "SELECT id FROM events WHERE id = ?";
    $eventStmt = $pdo->prepare($eventCheck);
    $eventStmt->execute([$event_id]);
    $event = $eventStmt->fetch(PDO::FETCH_ASSOC);

    if (!$event) {
        echo json_encode(['success' => false, 'message' => 'Event not found']);
        exit;
    }

    // Check if user is already registered for this event
    $checkSql = "SELECT id FROM registrations WHERE user_id = ? AND event_id = ?";
    $checkStmt = $pdo->prepare($checkSql);
    $checkStmt->execute([$user_id, $event_id]);
    $existing = $checkStmt->fetch(PDO::FETCH_ASSOC);

    if ($existing) {
        echo json_encode(['success' => false, 'message' => 'You have already joined this event']);
        exit;
    }

    // Register user for the event
    $sql = "INSERT INTO registrations (user_id, event_id) VALUES (?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id, $event_id]);

    echo json_encode([
        'success' => true,
        'message' => 'Successfully joined the event'
    ]);
} catch (PDOException $e) {
    // Log error without exposing details to client
    error_log("Join event error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'System error. Please try again later.']);
}
?>