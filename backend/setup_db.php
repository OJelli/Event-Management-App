<?php
include 'db.php';

// Run schema
$schema = file_get_contents(__DIR__ . '/../database/schema.sql');
if ($conn->multi_query($schema)) {
    echo "Schema created successfully.<br>";
}
while ($conn->more_results() && $conn->next_result()) {}

// Run seed
$seed = file_get_contents(__DIR__ . '/../database/seed.sql');
if ($conn->multi_query($seed)) {
    echo "Seed data inserted successfully.<br>";
}

$conn->close();
?>
