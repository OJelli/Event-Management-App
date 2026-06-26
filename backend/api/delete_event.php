<?php
header('Content-Type: application/json');
session_start();

require_once __DIR__ . '/../../pdo.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit;
}

$event_id = $_POST['event_id'] ?? '';

if (empty($event_id) || !is_numeric($event_id)) {
    echo json_encode(['success' => false, 'message' => 'Valid event ID is required']);
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
        echo json_encode(['success' => false, 'message' => 'You do not have permission to delete this event']);
        exit;
    }

    $pdo->beginTransaction();

    $deleteRegs = "DELETE FROM registrations WHERE event_id = ?";
    $regStmt = $pdo->prepare($deleteRegs);
    $regStmt->execute([$event_id]);

    $deleteEvent = "DELETE FROM events WHERE id = ?";
    $eventStmt = $pdo->prepare($deleteEvent);
    $eventStmt->execute([$event_id]);

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Event deleted successfully'
    ]);
} catch (PDOException $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>