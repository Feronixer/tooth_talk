<?php
// api/delete_patient.php
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
        $patientId = filter_var($input['id'], FILTER_VALIDATE_INT);

        if ($patientId === false || $patientId <= 0) {
            $response['message'] = 'Invalid Patient ID format provided.';
        } else {
            // --- Database Operation ---
            try {
                $sql = "DELETE FROM patients WHERE id = :id";
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':id', $patientId, PDO::PARAM_INT);

                if ($stmt->execute()) {
                    if ($stmt->rowCount() > 0) {
                        $response['success'] = true;
                        $response['message'] = 'Patient record deleted successfully.';
                    } else {
                        $response['message'] = 'Patient record not found or already deleted.';
                        // $response['success'] remains false
                    }
                } else {
                    $response['message'] = 'Failed to execute delete statement.';
                    // Log server-side: print_r($stmt->errorInfo());
                }
            } catch (PDOException $e) {
                // Log error: error_log("Database error in delete_patient.php: " . $e->getMessage());
                $response['message'] = 'Database error occurred while deleting patient record.';
                // $response['details'] = $e->getMessage(); // Avoid for production
                http_response_code(500); // Internal Server Error
            }
        }
    } else {
        $response['message'] = 'Patient ID not provided in the request body.';
    }
} else {
    // --- Method Not Allowed ---
    $response['message'] = 'Invalid request method. Only POST is accepted.';
    http_response_code(405); // Method Not Allowed
}

// --- Send JSON Response ---
echo json_encode($response);
?>
