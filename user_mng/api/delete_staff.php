<?php
// api/delete_staff.php
header('Content-Type: application/json');
require_once 'db_connection.php'; // Handles DB connection and exits with JSON error on failure

// --- Response Initialization ---
$response = ['success' => false, 'message' => 'Invalid request.'];

// --- Request Method Check ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the input data (expecting JSON)
    $input = json_decode(file_get_contents('php://input'), true);

    // --- Input Validation ---
    if (isset($input['id']) && !empty($input['id'])) {
        $staffId = filter_var($input['id'], FILTER_VALIDATE_INT);

        if ($staffId === false || $staffId <= 0) {
            $response['message'] = 'Invalid Staff ID format provided.';
        } else {
            // --- Database Operation ---
            try {
                // Prepare SQL statement to delete staff
                $sql = "DELETE FROM staff WHERE id = :id";
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':id', $staffId, PDO::PARAM_INT);

                if ($stmt->execute()) {
                    if ($stmt->rowCount() > 0) {
                        // Successfully deleted one or more rows
                        $response['success'] = true;
                        $response['message'] = 'Staff member deleted successfully.';
                    } else {
                        // No rows affected, meaning staff member was not found
                        $response['message'] = 'Staff member not found or already deleted.';
                        // $response['success'] remains false as no change was made
                    }
                } else {
                    // Execution failed (should be caught by PDOException, but as a fallback)
                    $response['message'] = 'Failed to execute delete statement.';
                    // Log server-side: print_r($stmt->errorInfo());
                }
            } catch (PDOException $e) {
                // Log error: error_log("Database error in delete_staff.php: " . $e->getMessage());
                $response['message'] = 'Database error occurred while deleting staff member.';
                // $response['details'] = $e->getMessage(); // Avoid for production
                http_response_code(500); // Internal Server Error
            }
        }
    } else {
        $response['message'] = 'Staff ID not provided in the request body.';
    }
} else {
    // --- Method Not Allowed ---
    $response['message'] = 'Invalid request method. Only POST is accepted.';
    http_response_code(405); // Method Not Allowed
}

// --- Send JSON Response ---
echo json_encode($response);
?>
