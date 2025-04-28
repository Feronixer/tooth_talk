<?php
session_start();
require 'db_connect.php';

// Redirect to login if not authenticated
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: admin_login.php');
    exit;
}

// Check if the service ID is provided in the URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: admin_dashboard.php');
    exit;
}

$id = $_GET['id'];

// Fetch the service details from the database
$stmt = $conn->prepare("SELECT * FROM services WHERE id = :id");
$stmt->bindParam(':id', $id, PDO::PARAM_INT);
$stmt->execute();
$service = $stmt->fetch(PDO::FETCH_ASSOC);

// Redirect if the service does not exist
if (!$service) {
    header('Location: admin_dashboard.php');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = trim($_POST['price']); // Allow text input for price

    // Handle file upload if a new image is provided
    if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $fileType = mime_content_type($_FILES['image']['tmp_name']);

        if (!in_array($fileType, $allowedTypes)) {
            $error = "Invalid file type. Only JPG, PNG, and GIF are allowed.";
        } else {
            $uploadDir = 'uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $fileName = uniqid() . '_' . basename($_FILES['image']['name']);
            $filePath = $uploadDir . $fileName;

            if (move_uploaded_file($_FILES['image']['tmp_name'], $filePath)) {
                if (!empty($service['image_url']) && file_exists($service['image_url'])) {
                    unlink($service['image_url']);
                }

                $stmt = $conn->prepare("UPDATE services SET name = :name, description = :description, price = :price, image_url = :image_url WHERE id = :id");
                $stmt->bindParam(':name', $name);
                $stmt->bindParam(':description', $description);
                $stmt->bindParam(':price', $price);
                $stmt->bindParam(':image_url', $filePath);
                $stmt->bindParam(':id', $id);
                $stmt->execute();

                header('Location: admin_dashboard.php');
                exit;
            } else {
                $error = "Failed to upload image.";
            }
        }
    } else {
        $stmt = $conn->prepare("UPDATE services SET name = :name, description = :description, price = :price WHERE id = :id");
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':price', $price);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        header('Location: admin_dashboard.php');
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Service</title>
    <link rel="stylesheet" href="edit_services.css">
</head>
<body>
    <header>
        <h1>Edit Service</h1>
        <nav>
            <ul>
                <li><a href="admin_dashboard.php">Dashboard</a></li>
                <li><a href="manage_services.php">Manage Services</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <section>
            <h2>Edit Service: <?php echo htmlspecialchars($service['name']); ?></h2>
            <?php if (isset($error)): ?>
                <p class="error"><?php echo $error; ?></p>
            <?php endif; ?>
            <form method="POST" enctype="multipart/form-data">
                <div>
                    <label for="name">Service Name:</label>
                    <input type="text" name="name" id="name" value="<?php echo htmlspecialchars($service['name']); ?>" required>
                </div>
                <div>
                    <label for="description">Description:</label>
                    <textarea name="description" id="description" required><?php echo htmlspecialchars($service['description']); ?></textarea>
                </div>
                <div>
                    <label for="price">Price:</label>
                    <input type="text" name="price" id="price" value="<?php echo htmlspecialchars($service['price']); ?>" required>
                </div>
                <div>
                    <label for="image">Upload New Image:</label>
                    <input type="file" name="image" id="image" accept="image/*">
                    <?php if (!empty($service['image_url'])): ?>
                        <p>Current Image:</p>
                        <img src="<?php echo htmlspecialchars($service['image_url']); ?>" alt="Current Service Image" style="width: 150px; height: 150px; object-fit: cover; border-radius: 8px;">
                    <?php endif; ?>
                </div>
                <div>
                    <button type="submit">Update Service</button>
     
