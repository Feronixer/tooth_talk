<?php
require 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $patientId = $_POST['patient_id'];
    $subject = trim($_POST['subject']);
    $message = trim($_POST['message']);

    // Fetch the patient's email address
    $stmt = $conn->prepare("SELECT email FROM patients WHERE id = ?");
    $stmt->execute([$patientId]);
    $patient = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($patient) {
        $to = $patient['email'];
        $headers = "From: clinic@example.com\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

        // Send the email
        if (mail($to, $subject, nl2br($message), $headers)) {
            echo "<script>alert('Mail sent successfully to {$to}'); window.location.href = 'content_management.php';</script>";
        } else {
            echo "<script>alert('Failed to send mail. Please try again.'); window.location.href = 'content_management.php';</script>";
        }
    } else {
        echo "<script>alert('Patient not found.'); window.location.href = 'content_management.php';</script>";
    }
}
?>