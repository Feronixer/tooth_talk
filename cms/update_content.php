<?php
require 'db_connect.php';

// Decode the incoming JSON data
$data = json_decode(file_get_contents('php://input'), true);

// Log the incoming data for debugging
file_put_contents('debug.log', print_r($data, true), FILE_APPEND);

$type = $data['type'] ?? null; // Ensure 'type' is set
$id = $data['id'] ?? null; // Ensure 'id' is set
$value = $data['value'] ?? null; // Ensure 'value' is set

try {
    switch ($type) {
        case 'announcement_title':
            // Update the title of the announcement
            $stmt = $conn->prepare("UPDATE announcements SET title = ? WHERE id = ?");
            $stmt->execute([$value, $id]);
            echo json_encode(['success' => true]);
            break;

        case 'announcement_message':
            // Update the message of the announcement
            $stmt = $conn->prepare("UPDATE announcements SET message = ? WHERE id = ?");
            $stmt->execute([$value, $id]);
            echo json_encode(['success' => true]);
            break;

        case 'update_service':
            // Ensure all required fields are present
            $name = $data['name'] ?? null;
            $price = $data['price'] ?? null;
            $description = $data['description'] ?? null;

            if (!$name || !$price || !$description || !$id) {
                echo json_encode(['success' => false, 'message' => 'Missing required fields']);
                exit;
            }

            // Update the service in the database
            $stmt = $conn->prepare("UPDATE services SET name = ?, price = ?, description = ? WHERE id = ?");
            $stmt->execute([$name, $price, $description, $id]);

            echo json_encode(['success' => true]);
            break;

        case 'delete_service':
            // Ensure the ID is provided
            if (!$id) {
                echo json_encode(['success' => false, 'message' => 'Missing service ID']);
                exit;
            }

            // Delete the service from the database
            $stmt = $conn->prepare("DELETE FROM services WHERE id = ?");
            $stmt->execute([$id]);

            if ($stmt->rowCount() > 0) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Service not found or already deleted']);
            }
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Invalid type']);
            break;
    }
} catch (Exception $e) {
    // Handle any exceptions
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>