<?php
// api/edit_patient.php
header('Content-Type: application/json');
require_once 'db_connection.php';

$response = ['success' => false, 'message' => 'Invalid request.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Or PUT
    $input = json_decode(file_get_contents('php://input'), true);
    if (empty($input)) {
        $input = $_POST;
    }

    if (empty($input['id'])) {
        $response['message'] = 'Patient ID is required for editing.';
        echo json_encode($response);
        exit;
    }
    $id = filter_var($input['id'], FILTER_VALIDATE_INT);

    $requiredFields = ['patient_no', 'name', 'appointment_date', 'appointment_time', 'treatment', 'patient_type', 'status'];
    $missingFields = [];
    foreach ($requiredFields as $field) {
        if (!isset($input[$field]) || ($input[$field] === '' && $input[$field] !== null)) { // Check for empty string but allow null
             // For some fields, empty might be okay if they are nullable. Adjust as needed.
             if ($field === 'email' || $field === 'phone' || $field === 'address' || $field === 'birth_date' || $field === 'reason' || $field === 'notes') {
                // these can be empty or null
             } else {
                $missingFields[] = $field;
             }
        }
    }
     if (!empty($missingFields)) {
        $response['message'] = 'Missing required fields for update: ' . implode(', ', $missingFields);
        echo json_encode($response);
        exit;
    }

    $patient_no = trim($input['patient_no']);
    $name = trim($input['name']);
    $email = isset($input['email']) && !empty(trim($input['email'])) ? trim($input['email']) : null;
    $phone = isset($input['phone']) ? trim($input['phone']) : null;
    $address = isset($input['address']) ? trim($input['address']) : null;
    $birth_date = isset($input['birth_date']) && !empty($input['birth_date']) ? trim($input['birth_date']) : null;
    
    $appointment_date = trim($input['appointment_date']);
    $appointment_time = trim($input['appointment_time']);
    $treatment = trim($input['treatment']);
    $patient_type = trim($input['patient_type']);
    $status = trim($input['status']);
    $reason = isset($input['reason']) ? trim($input['reason']) : null;
    $notes = isset($input['notes']) ? trim($input['notes']) : null;

    if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['message'] = 'Invalid email format.';
        echo json_encode($response);
        exit;
    }

    try {
        // Check for existing patient_no or email (excluding current patient)
        $checkConditions = ["(patient_no = :patient_no"];
        $checkParams = [':patient_no' => $patient_no, ':id' => $id];
        if ($email) {
            $checkConditions[0] .= " OR email = :email)"; // Group with OR
            $checkParams[':email'] = $email;
        } else {
             $checkConditions[0] .= ")"; // Close patient_no condition
        }
        $checkConditions[0] .= " AND id != :id"; // Exclude current patient
        
        $checkSql = "SELECT id FROM patients WHERE " . implode(" AND ", $checkConditions); // This logic might need refinement if email can be null
        $checkStmt = $pdo->prepare($checkSql);
        $checkStmt->execute($checkParams);

        if ($checkStmt->fetch()) {
            $response['message'] = 'Patient Number or Email already exists for another patient.';
            echo json_encode($response);
            exit;
        }

        $sql = "UPDATE patients SET 
                    patient_no = :patient_no, 
                    name = :name, 
                    email = :email, 
                    phone = :phone, 
                    address = :address, 
                    birth_date = :birth_date, 
                    appointment_date = :appointment_date, 
                    appointment_time = :appointment_time, 
                    treatment = :treatment, 
                    patient_type = :patient_type, 
                    status = :status, 
                    reason = :reason, 
                    notes = :notes 
                WHERE id = :id";
        
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
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        if ($stmt->execute()) {
             if ($stmt->rowCount() > 0) {
                $response['success'] = true;
                $response['message'] = 'Patient record updated successfully.';
            } else {
                $response['success'] = true; // Or false, depending on desired behavior
                $response['message'] = 'Patient data submitted. No changes detected or patient ID not found.';
            }
        } else {
            $response['message'] = 'Failed to update patient record. Database error.';
        }

    } catch (PDOException $e) {
        error_log("Edit Patient Error: " . $e->getMessage());
        $response['message'] = 'Database error: ' . $e->getMessage();
        http_response_code(500);
    }
}

echo json_encode($response);
?>
