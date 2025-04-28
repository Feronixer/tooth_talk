<?php
session_start();
require 'db_connect.php';

// Redirect to login if not authenticated as admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: admin_login.php');
    exit;
}

// Handle form submission for adding a patient
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_patient'])) {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = password_hash(trim($_POST['password']), PASSWORD_BCRYPT); // Hash the password

    // Insert the patient into the database
    $stmt = $conn->prepare("INSERT INTO patients (full_name, email, phone, password) VALUES (:full_name, :email, :phone, :password)");
    $stmt->bindParam(':full_name', $full_name);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':phone', $phone);
    $stmt->bindParam(':password', $password);
    $stmt->execute();

    // Redirect to the same page to avoid form resubmission
    header('Location: admin_dashboard.php');
    exit;
}

// Fetch all patients
$stmt = $conn->query("SELECT * FROM patients");
$patients = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch all appointments (if applicable)
$stmt = $conn->query("SELECT appointments.*, patients.full_name as patient_name, services.name as service_name FROM appointments JOIN patients ON appointments.patient_email = patients.email JOIN services ON appointments.service_id = services.id ORDER BY appointment_date DESC");
$appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1>Admin Dashboard</h1>
        <nav>
            <ul>
                <li><a href="admin_dashboard.php">Dashboard</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <!-- List of Patients -->
        <section>
            <h2>Patients</h2>
            <?php if (empty($patients)): ?>
                <p>No patients found.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Full Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($patients as $patient): ?>
                        <tr>
                            <td><?php echo $patient['id']; ?></td>
                            <td><?php echo htmlspecialchars($patient['full_name']); ?></td>
                            <td><?php echo htmlspecialchars($patient['email']); ?></td>
                            <td><?php echo htmlspecialchars($patient['phone']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </section>

        <!-- List of Appointments -->
        <section>
            <h2>Appointments</h2>
            <?php if (empty($appointments)): ?>
                <p>No appointments found.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Patient Name</th>
                            <th>Service</th>
                            <th>Date & Time</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($appointments as $appointment): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($appointment['patient_name']); ?></td>
                            <td><?php echo htmlspecialchars($appointment['service_name']); ?></td>
                            <td><?php echo date('M j, Y h:i A', strtotime($appointment['appointment_date'])); ?></td>
                            <td><?php echo ucfirst($appointment['status']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </section>
    </main>

    <footer>
        <p>&copy; 2023 JValera Dental Clinic. All rights reserved.</p>
    </footer>
</body>
</html>