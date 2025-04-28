<?php
session_start(); // Ensure session is started before any output

require 'db_connect.php';
$error = ""; // Initialize error variable

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (!empty($email) && !empty($password)) {
        try {
            // Fetch the patient from the database
            $stmt = $conn->prepare("SELECT * FROM patients WHERE email = :email LIMIT 1");
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->execute();
            $patient = $stmt->fetch(PDO::FETCH_ASSOC);

            // Verify the password
            if ($patient && password_verify($password, $patient['password'])) {
                // Set session variables
                $_SESSION['patient_id'] = $patient['id'];
                $_SESSION['patient_name'] = $patient['full_name'];
                
                // Redirect to dashboard
                header('Location: patient_dashboard.php');
                exit;
            } else {
                $error = "Invalid email or password.";
            }
        } catch (PDOException $e) {
            $error = "Database error: " . $e->getMessage();
        }
    } else {
        $error = "Please fill in both fields.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Login</title>
    <link rel="stylesheet" href="patient.css">
</head>
<body>
    <header>
        <h1>Patient Login</h1>
        <nav>
            <ul>
                <li><a href="index.php">Home</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <section class="login-form">
            <h2>Login to Your Account</h2>
            <?php if (!empty($error)): ?>
                <p class="error"><?php echo htmlspecialchars($error); ?></p>
            <?php endif; ?>
            <form method="POST">
                <div>
                    <label for="email">Email:</label>
                    <input type="email" name="email" id="email" required>
                </div>
                <div>
                    <label for="password">Password:</label>
                    <input type="password" name="password" id="password" required>
                </div>
                <div>
                    <button type="submit">Login</button>
                </div>
            </form>
        </section>
    </main>

    <footer>
        <p>&copy; 2023 JValera Dental Clinic. All rights reserved.</p>
    </footer>
</body>
</html>
