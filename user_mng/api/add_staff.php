<?php
// api/add_staff.php
header('Content-Type: application/json');
require_once 'db_connection.php';

$response = ['success' => false, 'message' => 'Invalid request.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get data from request body (assuming JSON)
    // For form data: use $_POST
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Fallback to $_POST if json_decode fails or input is empty
    if (empty($input)) {
        $input = $_POST;
    }

    // Validate required fields (adjust based on your form)
    $requiredFields = ['username', 'name', 'role', 'email', 'password'];
    $missingFields = [];
    foreach ($requiredFields as $field) {
        if (empty($input[$field])) {
            $missingFields[] = $field;
        }
    }

    if (!empty($missingFields)) {
        $response['message'] = 'Missing required fields: ' . implode(', ', $missingFields);
        echo json_encode($response);
        exit;
    }

    $username = trim($input['username']);
    $name = trim($input['name']);
    $role = trim($input['role']);
    $email = trim($input['email']);
    $mobile = isset($input['mobile']) ? trim($input['mobile']) : null;
    $password = $input['password']; // Plain text password from form

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['message'] = 'Invalid email format.';
        echo json_encode($response);
        exit;
    }

    // Hash the password securely
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    try {
        // Check for existing username or email
        $checkSql = "SELECT id FROM staff WHERE username = :username OR email = :email";
        $checkStmt = $pdo->prepare($checkSql);
        $checkStmt->bindParam(':username', $username, PDO::PARAM_STR);
        $checkStmt->bindParam(':email', $email, PDO::PARAM_STR);
        $checkStmt->execute();

        if ($checkStmt->fetch()) {
            $response['message'] = 'Username or email already exists.';
            echo json_encode($response);
            exit;
        }

        // Insert new staff member
        $sql = "INSERT INTO staff (username, name, role, email, mobile, password_hash) 
                VALUES (:username, :name, :role, :email, :mobile, :password_hash)";
        $stmt = $pdo->prepare($sql);

        $stmt->bindParam(':username', $username, PDO::PARAM_STR);
        $stmt->bindParam(':name', $name, PDO::PARAM_STR);
        $stmt->bindParam(':role', $role, PDO::PARAM_STR);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->bindParam(':mobile', $mobile, PDO::PARAM_STR_CHAR); // Handles NULL
        $stmt->bindParam(':password_hash', $password_hash, PDO::PARAM_STR);

        if ($stmt->execute()) {
            $response['success'] = true;
            $response['message'] = 'Staff member added successfully.';
            $response['staff_id'] = $pdo->lastInsertId();
        } else {
            $response['message'] = 'Failed to add staff member. Database error.';
        }

    } catch (PDOException $e) {
        error_log("Add Staff Error: " . $e->getMessage());
        $response['message'] = 'Database error: ' . $e->getMessage(); // Consider generic message for production
        http_response_code(500);
    }
}

echo json_encode($response);
?>
