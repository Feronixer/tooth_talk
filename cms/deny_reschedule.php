
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

$stmt = $conn->prepare("UPDATE follow_ups SET status = 'pending' WHERE id = ?");
$stmt->execute([$follow_up_id]);

echo "<script>alert('Reschedule denied. Appointment remains the same.'); window.location.href='manage_appointment.php';</script>";
exit;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="resched.css">
</head>
<body>
    
</body>
</html>