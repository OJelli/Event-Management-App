<?php
header('Content-Type: application/json');
session_start();

require_once __DIR__ . '/../../pdo.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit;
}

// Sanitize input
$title = trim($_POST['title'] ?? '');
$description = trim($_POST['description'] ?? '');
$event_date = $_POST['event_date'] ?? '';
$location = trim($_POST['location'] ?? '');

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

$user_id = $_SESSION['user_id'];

try {
    // Start transaction
    $pdo->beginTransaction();

    // Insert new event with default status 'upcoming' and NULL original_date
    $sql = "INSERT INTO events (title, description, event_date, location, created_by, status, original_date) 
            VALUES (?, ?, ?, ?, ?, 'upcoming', NULL)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$title, $description, $event_date, $location ?: null, $user_id]);

    $event_id = $pdo->lastInsertId();

    // Auto-register the creator as a participant
    $regSql = "INSERT INTO registrations (user_id, event_id) VALUES (?, ?)";
    $regStmt = $pdo->prepare($regSql);
    $regStmt->execute([$user_id, $event_id]);

    // Commit transaction
    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Event created successfully',
        'event_id' => $event_id
    ]);
} catch (PDOException $e) {
    // Rollback transaction on error
    $pdo->rollBack();
    // Log error without exposing details to client
    error_log("Create event error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'System error. Please try again later.']);
}
?>