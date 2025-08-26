<?php
$host = "localhost";
$user = "root";   // change if needed
$pass = "";
$db   = "leave_portal";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("DB Connection Failed: " . $conn->connect_error);
}
?>
