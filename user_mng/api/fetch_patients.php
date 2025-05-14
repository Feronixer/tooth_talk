<?php
// api/fetch_patients.php
ob_start(); // Start output buffering
header('Content-Type: application/json');
require_once 'db_connection.php'; 

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$statusFilter = isset($_GET['status']) ? trim($_GET['status']) : '';
$sortBy = isset($_GET['sort_by']) ? trim($_GET['sort_by']) : 'appointment_date'; 
$sortDir = isset($_GET['sort_dir']) && in_array(strtoupper($_GET['sort_dir']), ['ASC', 'DESC']) ? strtoupper($_GET['sort_dir']) : 'DESC'; 

if ($page < 1) $page = 1;
if ($limit < 1) $limit = 5;
if ($limit > 100) $limit = 100;
$offset = ($page - 1) * $limit;

$allowedSortColumns = ['id', 'patient_no', 'name', 'email', 'phone', 'appointment_date', 'appointment_time', 'treatment', 'patient_type', 'status', 'created_at'];
if (!in_array($sortBy, $allowedSortColumns)) {
    $sortBy = 'appointment_date'; 
}

$response = ['patients' => [], 'total' => 0, 'error' => null];
$sqlDebug = ['query_count' => '', 'params_count' => [], 'query_data' => '', 'params_data' => []];

try {
    $baseSql = "FROM patients";
    $selectSql = "SELECT id, patient_no, name, email, phone, address, birth_date, appointment_date, appointment_time, treatment, patient_type, status, reason, notes, created_at ";
    
    $conditions = [];
    $whereExecuteParams = []; // Parameters for the WHERE clause

    if (!empty($search)) {
        $searchTerm = "%" . $search . "%";
        $conditions[] = "(LOWER(patient_no) LIKE LOWER(:search_term) OR LOWER(name) LIKE LOWER(:search_term))";
        $whereExecuteParams[':search_term'] = $searchTerm;
    }

    if (!empty($statusFilter)) {
        $allowedStatuses = ['Pending', 'Confirmed', 'Completed', 'Rescheduled', 'Cancelled'];
        if (in_array($statusFilter, $allowedStatuses)) {
            $conditions[] = "status = :statusFilter";
            $whereExecuteParams[':statusFilter'] = $statusFilter;
        }
    }

    $whereClause = "";
    if (count($conditions) > 0) {
        $whereClause = " WHERE " . implode(" AND ", $conditions);
    }

    // Count query
    $countSql = "SELECT COUNT(*) " . $baseSql . $whereClause;
    $sqlDebug['query_count'] = $countSql;
    $sqlDebug['params_count'] = $whereExecuteParams;
    $stmtCount = $pdo->prepare($countSql);
    $stmtCount->execute($whereExecuteParams);
    $totalRecords = $stmtCount->fetchColumn();
    $response['total'] = (int)$totalRecords;

    // Data query
    $orderByClause = " ORDER BY " . $sortBy . " " . $sortDir;
    if ($sortBy === 'appointment_date') { 
         $orderByClause .= ", appointment_time " . $sortDir;
    }
    $limitOffsetClause = " LIMIT :limit OFFSET :offset"; 
    
    $dataSql = $selectSql . $baseSql . $whereClause . $orderByClause . $limitOffsetClause;
    $sqlDebug['query_data'] = $dataSql;
    
    $stmtData = $pdo->prepare($dataSql);

    // Build the exact parameter array for the data query's execute() method.
    // Start with parameters from the WHERE clause (if any).
    $dataExecuteParams = $whereExecuteParams; 
    
    // ALWAYS add :limit and :offset because they are ALWAYS in $dataSql's $limitOffsetClause.
    $dataExecuteParams[':limit'] = $limit;   
    $dataExecuteParams[':offset'] = $offset; 
    
    $sqlDebug['params_data'] = $dataExecuteParams;
    
    $stmtData->execute($dataExecuteParams); 
    $patientList = $stmtData->fetchAll(PDO::FETCH_ASSOC);
    
    $response['patients'] = $patientList;

} catch (PDOException $e) {
    $attemptedQuery = $sqlDebug['query_data'] ?: ($sqlDebug['query_count'] ?: "SQL not set before error");
    $attemptedParams = $sqlDebug['params_data'] ?: ($sqlDebug['params_count'] ?: []);
    error_log("Database error in fetch_patients.php: " . $e->getMessage() . " --- SQL attempted: " . $attemptedQuery . " --- Params: " . json_encode($attemptedParams));
    
    $response['error'] = "A database error occurred while fetching patient data.";
    $response['details'] = $e->getMessage(); 
    // $response['debug_sql'] = $sqlDebug; 
    http_response_code(500);
} catch (Exception $e) {
    error_log("Unexpected error in fetch_patients.php: " . $e->getMessage());
    $response['error'] = "An unexpected error occurred.";
    http_response_code(500);
}

ob_end_clean(); 
echo json_encode($response);
?>
