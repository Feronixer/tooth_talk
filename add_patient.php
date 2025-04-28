<?php
session_start();
require 'db_connect.php';

// Redirect to login if not authenticated as admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: admin_login.php');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT); // Hash the password

    // Insert the patient into the database
    $stmt = $conn->prepare("INSERT INTO patients (full_name, email, phone, password) VALUES (:full_name, :email, :phone, :password)");
    $stmt->bindParam(':full_name', $full_name);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':phone', $phone);
    $stmt->bindParam(':password', $password);
    $stmt->execute();

    // Redirect to the admin dashboard
    header('Location: patient_list.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Patient</title>
    <link rel="stylesheet" href="patient.css">
</head>
<body>
    <header>
        <h1>Add Patient</h1>
        <nav>
            <ul>
                <li><a href="admin_dashboard.php">Dashboard</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <section class="patient-form">
            <h2>Create Patient Account</h2>
            <form method="POST">
                <div>
                    <label for="full_name">Full Name:</label>
                    <input type="text" name="full_name" id="full_name" required>
                </div>
                <div>
                    <label for="email">Email:</label>
                    <input type="email" name="email" id="email" required>
                </div>
                <div>
                    <label for="phone">Phone Number:</label>
                    <input type="tel" name="phone" id="phone" required>
                </div>
                <div>
                    <label for="password">Password:</label>
                    <input type="password" name="password" id="password" required>
                </div>
                <div>
                    <button type="submit">Create Account</button>
                </div>
            </form>
        </section>
    </main>

    <footer>
        <p>&copy; 2023 JValera Dental Clinic. All rights reserved.</p>
    </footer>
</body>
</html>