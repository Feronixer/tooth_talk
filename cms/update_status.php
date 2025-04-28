<?php
session_start();
require 'db_connect.php';

// Ensure only admin can update status
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    die("Unauthorized access.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['follow_up_id'], $_POST['status'])) {
    $follow_up_id = $_POST['follow_up_id'];
    $status = $_POST['status'];

    // Validate status
    $valid_statuses = ['pending', 'confirmed', 'completed', 'cancelled'];
    if (!in_array($status, $valid_statuses)) {
        die("Invalid status.");
    }

    // Update status in the database
    $stmt = $conn->prepare("UPDATE follow_ups SET status = ? WHERE id = ?");
    if ($stmt->execute([$status, $follow_up_id])) {
        header("Location: manage_appointment.php");
        exit;
    } else {
        die("Failed to update status.");
    }
}
?>
