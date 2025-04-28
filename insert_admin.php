<?php
require 'db_connect.php';

$username = "admin";
$password = "admin123";
$hashed_password = password_hash($password, PASSWORD_DEFAULT); // Hash the password

$stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (:username, :password, 'admin')");
$stmt->bindParam(':username', $username);
$stmt->bindParam(':password', $hashed_password);

if ($stmt->execute()) {
    echo "Admin user created successfully!";
} else {
    echo "Error creating admin user.";
}
?>
