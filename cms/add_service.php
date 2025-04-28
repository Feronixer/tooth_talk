<?php
session_start();
require 'db_connect.php';

// Redirect to login if not authenticated
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: admin_login.php');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = trim($_POST['price']); // Now accepts text-based prices

    // Handle file upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/'; // Directory to store uploaded images
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true); // Create the directory if it doesn't exist
        }

        $fileName = time() . '_' . basename($_FILES['image']['name']); // Prevent duplicate file names
        $filePath = $uploadDir . $fileName;

        // Move the uploaded file to the uploads directory
        if (move_uploaded_file($_FILES['image']['tmp_name'], $filePath)) {
            // Insert service data into the database
            $stmt = $conn->prepare("INSERT INTO services (name, description, price, image_url) VALUES (:name, :description, :price, :image_url)");
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':price', $price); // Now supports text-based prices
            $stmt->bindParam(':image_url', $filePath);
            $stmt->execute();

            header('Location: content_management.php');
            exit;
        } else {
            $error = "Failed to upload image.";
        }
    } else {
        $error = "No image uploaded or there was an error uploading the image.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Service</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>Add New Service</h1>
    <?php if (isset($error)): ?>
        <p class="error"><?php echo $error; ?></p>
    <?php endif; ?>
    <form method="POST" enctype="multipart/form-data">
        <label for="name">Name:</label>
        <input type="text" name="name" id="name" required>
        
        <label for="description">Description:</label>
        <textarea name="description" id="description" required></textarea>
        
        <label for="price">Price (can be a number or text):</label>
        <input type="text" name="price" id="price" required> 
        
        <label for="image">Upload Image:</label>
        <input type="file" name="image" id="image" accept="image/*" required>
        
        <button type="submit">Add Service</button>
        <div class="cancel">
            <a href="content_management.php">Cancel</a>
        </div>
    </form>
</body>
</html>