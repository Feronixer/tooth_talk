<?php
session_start();
require 'db_connect.php';

// Redirect to login if not authenticated
if (!isset($_SESSION['patient_id'])) {
    header('Location: patient_login.php');
    exit;
}

// Fetch patient details securely
$stmt = $conn->prepare("SELECT * FROM patients WHERE id = :id");
$stmt->bindParam(':id', $_SESSION['patient_id'], PDO::PARAM_INT);
$stmt->execute();
$patient = $stmt->fetch(PDO::FETCH_ASSOC);

// Update profile information
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $contact_no = trim($_POST['phone']);

    if (!empty($full_name) && !empty($email) && !empty($contact_no)) {
        $stmt = $conn->prepare("UPDATE patients SET full_name = :full_name, email = :email, phone = :phone WHERE id = :id");
        $stmt->bindParam(':full_name', $full_name, PDO::PARAM_STR);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->bindParam(':phone', $contact_no, PDO::PARAM_STR); // Now using correct column name
        $stmt->bindParam(':id', $_SESSION['patient_id'], PDO::PARAM_INT);
        if ($stmt->execute()) {
            $success_message = "Profile updated successfully!";
            $patient['full_name'] = $full_name;
            $patient['email'] = $email;
            $patient['contact_no'] = $contact_no;
        } else {
            $error_message = "Error updating profile.";
        }
    } else {
        $error_message = "All fields are required.";
    }
}

// Update Password
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if (!empty($current_password) && !empty($new_password) && !empty($confirm_password)) {
        if ($new_password === $confirm_password) {
            if (password_verify($current_password, $patient['password'])) {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE patients SET password = :password WHERE id = :id");
                $stmt->bindParam(':password', $hashed_password, PDO::PARAM_STR);
                $stmt->bindParam(':id', $_SESSION['patient_id'], PDO::PARAM_INT);
                if ($stmt->execute()) {
                    $success_message = "Password updated successfully!";
                } else {
                    $error_message = "Error updating password.";
                }
            } else {
                $error_message = "Current password is incorrect.";
            }
        } else {
            $error_message = "New passwords do not match.";
        }
    } else {
        $error_message = "All fields are required.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Profile</title>
    <link rel="stylesheet" href="patient.css">
</head>
<body>
    <header>
        <h1>Patient Profile</h1>
        <nav>
            <ul>
                <li><a href="patient_dashboard.php">Dashboard</a></li>
                <li><a href="logout_patient.php">Logout</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <h2>Your Profile</h2>

        <?php if (isset($success_message)): ?>
            <p class="success"><?php echo $success_message; ?></p>
        <?php endif; ?>
        <?php if (isset($error_message)): ?>
            <p class="error"><?php echo $error_message; ?></p>
        <?php endif; ?>

        <!-- Profile Update Form -->
        <form action="patient_profile.php" method="POST">
            <label for="full_name">Full Name:</label>
            <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($patient['full_name']); ?>" required>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($patient['email']); ?>" required>

            <label for="phone">Contact No.:</label>
            <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($patient['phone'] ?? ''); ?>" required>


            <button type="submit" name="update_profile">Update Profile</button>
        </form>

        <h2>Change Password</h2>

        <!-- Password Change Form -->
        <form action="patient_profile.php" method="POST">
            <label for="current_password">Current Password:</label>
            <input type="password" id="current_password" name="current_password" required>

            <label for="new_password">New Password:</label>
            <input type="password" id="new_password" name="new_password" required>

            <label for="confirm_password">Confirm New Password:</label>
            <input type="password" id="confirm_password" name="confirm_password" required>

            <button type="submit" name="update_password">Update Password</button>
        </form>
    </main>

    <footer>
        <p>&copy; 2025 JValera Dental Clinic. All rights reserved.</p>
    </footer>
</body>
</html>
