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
    $checkSql = "SELECT id FROM registrations WHERE user_id = ? AND event_id = ?";
    $checkStmt = $pdo->prepare($checkSql);
    $checkStmt->execute([$user_id, $event_id]);
    $registration = $checkStmt->fetch(PDO::FETCH_ASSOC);

    if (!$registration) {
        echo json_encode(['success' => false, 'message' => 'You are not registered for this event']);
        exit;
    }

    $sql = "DELETE FROM registrations WHERE user_id = ? AND event_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id, $event_id]);

    echo json_encode([
        'success' => true,
        'message' => 'Successfully cancelled registration'
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>