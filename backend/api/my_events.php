<?php
header('Content-Type: application/json');
session_start();

require_once __DIR__ . '/../../pdo.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    // Fetch all events the user has joined
    $sql = "SELECT e.*, u.username as created_by_name, r.registered_at
            FROM registrations r
            INNER JOIN events e ON r.event_id = e.id
            LEFT JOIN users u ON e.created_by = u.id
            WHERE r.user_id = ?
            ORDER BY e.event_date ASC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id]);
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Escape output to prevent XSS attacks
    foreach ($events as &$event) {
        $event['title'] = htmlentities($event['title'] ?? '', ENT_QUOTES, 'UTF-8');
        $event['description'] = htmlentities($event['description'] ?? '', ENT_QUOTES, 'UTF-8');
        $event['location'] = htmlentities($event['location'] ?? '', ENT_QUOTES, 'UTF-8');
    }

    echo json_encode([
        'success' => true,
        'data' => $events,
        'count' => count($events)
    ]);
} catch (PDOException $e) {
    // Log error without exposing details to client
    error_log("Fetch my events error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'System error. Please try again later.']);
}
?>