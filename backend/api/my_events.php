<?php
header('Content-Type: application/json');
session_start();

require_once __DIR__ . '/../../pdo.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    $sql = "SELECT e.*, u.username as created_by_name, r.registered_at
            FROM registrations r
            INNER JOIN events e ON r.event_id = e.id
            LEFT JOIN users u ON e.created_by = u.id
            WHERE r.user_id = ?
            ORDER BY e.event_date ASC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id]);
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'data' => $events,
        'count' => count($events)
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>