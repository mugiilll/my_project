<?php
$servername = "localhost";
$username   = "root";   // default XAMPP username
$password   = "";       // default XAMPP has empty password
$dbname     = "leave_portal";  // use your database name

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
