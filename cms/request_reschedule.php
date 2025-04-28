<?php
session_start();
require 'db_connect.php';

// Ensure the patient is logged in
if (!isset($_SESSION['patient_id'])) {
    header('Location: patient_login.php');
    exit;
}

// Ensure an appointment ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Invalid request. No appointment ID found. <a href='patient_dashboard.php'>Go Back</a>");
}

$follow_up_id = $_GET['id'];

// Fetch appointment details
$stmt = $conn->prepare("SELECT * FROM follow_ups WHERE id = :id AND patient_id = :patient_id");
$stmt->bindParam(':id', $follow_up_id);
$stmt->bindParam(':patient_id', $_SESSION['patient_id']);
$stmt->execute();
$follow_up = $stmt->fetch(PDO::FETCH_ASSOC);

// If the appointment does not exist, show an error
if (!$follow_up) {
    die("Appointment not found. <a href='patient_dashboard.php'>Go Back</a>");
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_date = $_POST['requested_date'];

    // Update the database with the requested reschedule date
    $updateStmt = $conn->prepare("UPDATE follow_ups SET requested_reschedule_date = :requested_date, status = 'reschedule_requested' WHERE id = :id");
    $updateStmt->bindParam(':requested_date', $new_date);
    $updateStmt->bindParam(':id', $follow_up_id);

    if ($updateStmt->execute()) {
        header('Location: patient_dashboard.php?reschedule_success=1');
        exit;
    } else {
        $error_message = "Error submitting request. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Reschedule</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <h1>Request Reschedule</h1>
    </header>

    <main>
        <p>Current Appointment: <strong><?php echo date('M j, Y h:i A', strtotime($follow_up['follow_up_date'])); ?></strong></p>

        <form action="" method="POST">
            <label for="requested_date">Select New Date & Time:</label>
            <input type="datetime-local" name="requested_date" required>
            <button type="submit">Submit Request</button>
        </form>

        <?php if (isset($error_message)): ?>
            <p style="color: red;"><?php echo $error_message; ?></p>
        <?php endif; ?>

        <br>
        <a href="patient_dashboard.php">Back to Dashboard</a>
    </main>
</body>
</html>
