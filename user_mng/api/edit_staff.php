<?php
// api/edit_staff.php
header('Content-Type: application/json');
require_once 'db_connection.php';

$response = ['success' => false, 'message' => 'Invalid request.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Or PUT, but POST is simpler with HTML forms/AJAX
    $input = json_decode(file_get_contents('php://input'), true);
     // Fallback to $_POST if json_decode fails or input is empty
    if (empty($input)) {
        $input = $_POST;
    }

    if (empty($input['id'])) {
        $response['message'] = 'Staff ID is required for editing.';
        echo json_encode($response);
        exit;
    }

    $id = filter_var($input['id'], FILTER_VALIDATE_INT);

    // Validate required fields (name, role, email are usually always required)
    $requiredFields = ['name', 'role', 'email', 'username']; // Username might be non-editable or checked for uniqueness
    $missingFields = [];
    foreach ($requiredFields as $field) {
        if (empty($input[$field])) {
            $missingFields[] = $field;
        }
    }
     if (!empty($missingFields)) {
        $response['message'] = 'Missing required fields for update: ' . implode(', ', $missingFields);
        echo json_encode($response);
        exit;
    }


    $username = trim($input['username']);
    $name = trim($input['name']);
    $role = trim($input['role']);
    $email = trim($input['email']);
    $mobile = isset($input['mobile']) ? trim($input['mobile']) : null;
    $password = isset($input['password']) ? $input['password'] : null; // Password is optional on edit

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['message'] = 'Invalid email format.';
        echo json_encode($response);
        exit;
    }

    try {
        // Check for existing username or email (excluding current staff member)
        $checkSql = "SELECT id FROM staff WHERE (username = :username OR email = :email) AND id != :id";
        $checkStmt = $pdo->prepare($checkSql);
        $checkStmt->bindParam(':username', $username, PDO::PARAM_STR);
        $checkStmt->bindParam(':email', $email, PDO::PARAM_STR);
        $checkStmt->bindParam(':id', $id, PDO::PARAM_INT);
        $checkStmt->execute();

        if ($checkStmt->fetch()) {
            $response['message'] = 'Username or email already exists for another staff member.';
            echo json_encode($response);
            exit;
        }

        // Build the update query
        $updateFields = [
            "username = :username",
            "name = :name",
            "role = :role",
            "email = :email",
            "mobile = :mobile"
        ];
        $queryParams = [
            ':username' => $username,
            ':name' => $name,
            ':role' => $role,
            ':email' => $email,
            ':mobile' => $mobile,
            ':id' => $id
        ];

        if (!empty($password)) {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $updateFields[] = "password_hash = :password_hash";
            $queryParams[':password_hash'] = $password_hash;
        }

        $sql = "UPDATE staff SET " . implode(", ", $updateFields) . " WHERE id = :id";
        $stmt = $pdo->prepare($sql);

        if ($stmt->execute($queryParams)) {
            if ($stmt->rowCount() > 0) {
                $response['success'] = true;
                $response['message'] = 'Staff member updated successfully.';
            } else {
                // No rows affected could mean the data submitted was the same as existing data,
                // or the ID was not found (though we didn't explicitly check for ID existence before update).
                // For simplicity, we'll treat it as success if execute didn't throw an error.
                // A more robust check would be to fetch first, then compare, then update.
                $response['success'] = true; // Or false if you want to indicate no actual change
                $response['message'] = 'Staff member data submitted. No changes detected or ID not found for update.';
            }
        } else {
            $response['message'] = 'Failed to update staff member. Database error.';
        }

    } catch (PDOException $e) {
        error_log("Edit Staff Error: " . $e->getMessage());
        $response['message'] = 'Database error: ' . $e->getMessage();
        http_response_code(500);
    }
}

echo json_encode($response);
?>
