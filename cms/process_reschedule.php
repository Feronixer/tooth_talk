<?php
session_start();
require 'db_connect.php';

// Ensure admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: admin_login.php");
    exit;
}

// Check if action and ID are set
if (isset($_GET['id'], $_GET['action'])) {
    $follow_up_id = $_GET['id'];
    $action = $_GET['action'];

    if ($action === 'approve') {
        // Approve reschedule request
        $stmt = $conn->prepare("UPDATE follow_ups SET follow_up_date = requested_reschedule_date, requested_reschedule_date = NULL, status = 'confirmed' WHERE id = :id");
    } elseif ($action === 'deny') {
        // Deny reschedule request
        $stmt = $conn->prepare("UPDATE follow_ups SET requested_reschedule_date = NULL, status = 'pending' WHERE id = :id");
    } else {
        header("Location: manage_appointment.php");
        exit;
    }

    $stmt->bindParam(':id', $follow_up_id);
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Reschedule request processed!";
    } else {
        $_SESSION['error_message'] = "Failed to process request.";
    }
}

header("Location: manage_appointment.php");
exit;
