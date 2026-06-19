<?php
include __DIR__ . '/db_setup.php';  // connect without DB

// Run schema.sql (creates DB + tables)
$schema = file_get_contents(__DIR__ . '/../database/schema.sql');
if ($conn->multi_query($schema)) {
    echo "Schema created successfully.<br>";
}
while ($conn->more_results() && $conn->next_result()) {}

// Switch to the new DB
$conn->select_db("event_management");

// Run seed.sql (inserts dummy data)
$seed = file_get_contents(__DIR__ . '/../database/seed.sql');
if ($conn->multi_query($seed)) {
    echo "Seed data inserted successfully.<br>";
}

$conn->close();
?>
