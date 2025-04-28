<?php
session_start();
require 'db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: admin_login.php');
    exit;
}

if (!isset($_GET['id'])) {
    header('Location: manage_appointment.php');
    exit;
}

$follow_up_id = $_GET['id'];

$stmt = $conn->prepare("UPDATE follow_ups SET status = 'confirmed' WHERE id = ?");
$stmt->execute([$follow_up_id]);

echo "<script>alert('Reschedule approved!'); window.location.href='manage_appointment.php';</script>";
exit;
?>
