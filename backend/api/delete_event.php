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
    // Check if event exists and get creator information
    $checkSql = "SELECT created_by FROM events WHERE id = ?";
    $checkStmt = $pdo->prepare($checkSql);
    $checkStmt->execute([$event_id]);
    $event = $checkStmt->fetch(PDO::FETCH_ASSOC);

    if (!$event) {
        echo json_encode(['success' => false, 'message' => 'Event not found']);
        exit;
    }

    // Verify that the current user is the event creator
    if ($event['created_by'] != $user_id) {
        echo json_encode(['success' => false, 'message' => 'You do not have permission to delete this event']);
        exit;
    }

    // Check if there are any registrations for this event
    $countSql = "SELECT COUNT(*) as count FROM registrations WHERE event_id = ?";
    $countStmt = $pdo->prepare($countSql);
    $countStmt->execute([$event_id]);
    $result = $countStmt->fetch(PDO::FETCH_ASSOC);
    $registrationCount = $result['count'] ?? 0;

    // Start transaction
    $pdo->beginTransaction();

    if ($registrationCount == 0) {
        // No registrations - hard delete the event
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
    } else {
        // Has registrations - soft delete (cancel the event)
        $cancelSql = "UPDATE events SET status = 'cancelled' WHERE id = ?";
        $cancelStmt = $pdo->prepare($cancelSql);
        $cancelStmt->execute([$event_id]);

        $pdo->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Event has been cancelled. ' . $registrationCount . ' participant(s) have been notified.'
        ]);
    }
} catch (PDOException $e) {
    // Rollback transaction on error
    $pdo->rollBack();
    // Log error without exposing details to client
    error_log("Delete/Cancel event error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'System error. Please try again later.']);
}
?>