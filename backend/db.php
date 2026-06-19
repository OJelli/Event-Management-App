<?php
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "event_management";

// Connect with DB selected
$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
