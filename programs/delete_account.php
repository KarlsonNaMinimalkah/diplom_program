<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "WorldOfCustomClothing";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Delete user account based on session user_id
$user_id = $_SESSION['user_id'];
$delete_sql = "DELETE FROM users WHERE id = ?";
$stmt = $conn->prepare($delete_sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->close();

// Log out the user
session_unset();
session_destroy();

header("Location: ../login.php");
exit();

