<?php
// services.php
require 'db_connect.php';

$stmt = $conn->query("SELECT * FROM services");
$services = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dental Clinic Services</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .service-image img {
            width: 300px; /* Set fixed width */
            height: 200px; /* Set fixed height */
            object-fit: cover; /* Ensure images fit without distortion */
            border-radius: 10px; /* Optional: Rounded corners */
        }
    </style>
</head>
<body>
    <header>
        <h1>JValera Dental Clinic</h1>
        <nav>
            <ul>
                <li><a href="overview.php">Overview</a></li>
                <li><a href="index.php" class="active">Services</a></li>
                <li><a href="appointments.php">Appointment Schedule</a></li>
                <li><a href="about.php">About Us</a></li>
                <li><a href="patient_list.php">Patients</a></li>
                <li><a href="patient_login.php">Log in as Patient</a></li>
                <li><a href="admin.php">Log in as Admin</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <section class="hero">
            <h2>Dental Clinic Services</h2>
        </section>
        
        <?php foreach ($services as $service): ?>
        <section class="service">
            <div class="service-content">
                <div class="service-text">
                    <h3><?php echo htmlspecialchars($service['name']); ?></h3>
                    <p><?php echo htmlspecialchars($service['description']); ?></p>
                    <p class="price">
                        PHP 
                        <?php 
                        if (is_numeric($service['price'])) {
                            echo number_format((float)$service['price'], 2);
                        } else {
                            echo htmlspecialchars($service['price']); // Display text-based price safely
                        }
                        ?>
                    </p>
                </div>
                <div class="service-image">
                    <img src="<?php echo htmlspecialchars($service['image_url']); ?>" alt="<?php echo htmlspecialchars($service['name']); ?>">
                </div>
            </div>
        </section>
        <?php endforeach; ?>
    </main>
    
    <script src="script.js"></script>
</body>
</html>
