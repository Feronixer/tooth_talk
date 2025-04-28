<?php
require 'db_connect.php';

// Decode the incoming JSON data
$data = json_decode(file_get_contents('php://input'), true);
file_put_contents('debug.log', print_r($data, true), FILE_APPEND);
$id = $data['id'] ?? null; // Ensure 'id' is set

if (!$id) {
    echo json_encode(['success' => false, 'message' => 'Missing service ID']);
    exit;
}

try {
    // Prepare and execute the DELETE query
    $stmt = $conn->prepare("DELETE FROM services WHERE id = ?");
    $stmt->execute([$id]);

    // Check if the service was deleted
    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Service not found or already deleted']);
    }
} catch (Exception $e) {
    // Handle any exceptions
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>