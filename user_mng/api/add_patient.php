<?php
// api/add_patient.php
header('Content-Type: application/json');
require_once 'db_connection.php';

$response = ['success' => false, 'message' => 'Invalid request.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (empty($input)) {
        $input = $_POST;
    }

    // Based on your "Add Patient Form" image:
    // Patient Number, First Name, Middle Name, Last Name, Suffix, Birthdate, Sex, Contact No, Email Address, Address
    // Appointment Date, Time, Service, Type, Status (Status might be default)
    $requiredFields = ['patient_no', 'name', 'appointment_date', 'appointment_time', 'treatment', 'patient_type'];
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

    $patient_no = trim($input['patient_no']);
    $name = trim($input['name']); // Assuming 'name' combines first, middle, last, suffix for simplicity here
    $email = isset($input['email']) && !empty(trim($input['email'])) ? trim($input['email']) : null;
    $phone = isset($input['phone']) ? trim($input['phone']) : null;
    $address = isset($input['address']) ? trim($input['address']) : null;
    $birth_date = isset($input['birth_date']) && !empty($input['birth_date']) ? trim($input['birth_date']) : null;
    
    $appointment_date = trim($input['appointment_date']);
    $appointment_time = trim($input['appointment_time']);
    $treatment = trim($input['treatment']);
    $patient_type = trim($input['patient_type']); // 'New' or 'Old'
    $status = isset($input['status']) && !empty($input['status']) ? trim($input['status']) : 'Pending'; // Default to Pending
    $reason = isset($input['reason']) ? trim($input['reason']) : null;
    $notes = isset($input['notes']) ? trim($input['notes']) : null;

    if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['message'] = 'Invalid email format.';
        echo json_encode($response);
        exit;
    }
    // Add more specific validations (date formats, phone format, patient_no uniqueness etc.) as needed

    try {
        // Check for existing patient_no or email (if email is provided and should be unique)
        $checkConditions = ["patient_no = :patient_no"];
        $checkParams = [':patient_no' => $patient_no];
        if ($email) {
            $checkConditions[] = "email = :email";
            $checkParams[':email'] = $email;
        }
        
        $checkSql = "SELECT id FROM patients WHERE " . implode(" OR ", $checkConditions);
        $checkStmt = $pdo->prepare($checkSql);
        $checkStmt->execute($checkParams);

        if ($checkStmt->fetch()) {
            $response['message'] = 'Patient Number or Email already exists.';
            echo json_encode($response);
            exit;
        }

        $sql = "INSERT INTO patients (patient_no, name, email, phone, address, birth_date, 
                                   appointment_date, appointment_time, treatment, patient_type, 
                                   status, reason, notes) 
                VALUES (:patient_no, :name, :email, :phone, :address, :birth_date, 
                        :appointment_date, :appointment_time, :treatment, :patient_type, 
                        :status, :reason, :notes)";
        $stmt = $pdo->prepare($sql);

        $stmt->bindParam(':patient_no', $patient_no);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':email', $email, $email ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $stmt->bindParam(':phone', $phone, $phone ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $stmt->bindParam(':address', $address, $address ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $stmt->bindParam(':birth_date', $birth_date, $birth_date ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $stmt->bindParam(':appointment_date', $appointment_date);
        $stmt->bindParam(':appointment_time', $appointment_time);
        $stmt->bindParam(':treatment', $treatment);
        $stmt->bindParam(':patient_type', $patient_type);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':reason', $reason, $reason ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $stmt->bindParam(':notes', $notes, $notes ? PDO::PARAM_STR : PDO::PARAM_NULL);

        if ($stmt->execute()) {
            $response['success'] = true;
            $response['message'] = 'Patient added successfully.';
            $response['patient_id'] = $pdo->lastInsertId();
        } else {
            $response['message'] = 'Failed to add patient. Database error.';
        }

    } catch (PDOException $e) {
        error_log("Add Patient Error: " . $e->getMessage());
        $response['message'] = 'Database error: ' . $e->getMessage();
        http_response_code(500);
    }
}

echo json_encode($response);
?>
