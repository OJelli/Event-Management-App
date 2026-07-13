<?php
header('Content-Type: application/json');
session_start();

require_once __DIR__ . '/../../pdo.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit;
}

// Validate and sanitize input
$event_id = $_POST['event_id'] ?? '';
$title = trim($_POST['title'] ?? '');
$description = trim($_POST['description'] ?? '');
$event_date = $_POST['event_date'] ?? '';
$location = trim($_POST['location'] ?? '');
$status = $_POST['status'] ?? 'upcoming';
$action = $_POST['action'] ?? 'update';  // 'update' or 'postpone'

// Validate event ID
if (empty($event_id) || !is_numeric($event_id)) {
    echo json_encode(['success' => false, 'message' => 'Valid event ID is required']);
    exit;
}

// Validate required fields
if (empty($title) || empty($event_date)) {
    echo json_encode(['success' => false, 'message' => 'Title and event date are required']);
    exit;
}

// Validate date format
$date = DateTime::createFromFormat('Y-m-d', $event_date);
if (!$date || $date->format('Y-m-d') !== $event_date) {
    echo json_encode(['success' => false, 'message' => 'Invalid date format. Use YYYY-MM-DD']);
    exit;
}

// Validate status
$allowed_statuses = ['upcoming', 'ongoing', 'cancelled', 'completed', 'postponed'];
if (!in_array($status, $allowed_statuses)) {
    $status = 'upcoming';
}

$user_id = $_SESSION['user_id'];

try {
    // Check if event exists and get creator information
    $checkSql = "SELECT created_by, event_date, original_date FROM events WHERE id = ?";
    $checkStmt = $pdo->prepare($checkSql);
    $checkStmt->execute([$event_id]);
    $event = $checkStmt->fetch(PDO::FETCH_ASSOC);

    if (!$event) {
        echo json_encode(['success' => false, 'message' => 'Event not found']);
        exit;
    }

    // Verify that the current user is the event creator
    if ($event['created_by'] != $user_id) {
        echo json_encode(['success' => false, 'message' => 'You do not have permission to update this event']);
        exit;
    }

    // Handle postpone action
    if ($action === 'postpone') {
        // If original_date is not set, store the current event_date as original
        if (is_null($event['original_date'])) {
            $original_date = $event['event_date'];
        } else {
            // Keep the original original_date
            $original_date = $event['original_date'];
        }

        // Update event with postponed status and new date
        $sql = "UPDATE events SET title = ?, description = ?, event_date = ?, location = ?, status = 'postponed', original_date = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$title, $description, $event_date, $location ?: null, $original_date, $event_id]);

        echo json_encode([
            'success' => true,
            'message' => 'Event postponed successfully'
        ]);
    } else {
        // Normal update
        $sql = "UPDATE events SET title = ?, description = ?, event_date = ?, location = ?, status = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$title, $description, $event_date, $location ?: null, $status, $event_id]);

        echo json_encode([
            'success' => true,
            'message' => 'Event updated successfully'
        ]);
    }
} catch (PDOException $e) {
    // Log error without exposing details to client
    error_log("Update event error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'System error. Please try again later.']);
}
?>