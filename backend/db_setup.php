<?php
$host = "localhost";
$user = "root";
$pass = "";

// Connect without selecting a DB
$conn = new mysqli($host, $user, $pass);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
