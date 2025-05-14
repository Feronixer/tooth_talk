<?php
// api/fetch_staff.php
ob_start(); // Start output buffering to catch any stray output/warnings
header('Content-Type: application/json');
require_once 'db_connection.php'; 

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$sortBy = isset($_GET['sort_by']) ? trim($_GET['sort_by']) : 'id'; 
$sortDir = isset($_GET['sort_dir']) && in_array(strtoupper($_GET['sort_dir']), ['ASC', 'DESC']) ? strtoupper($_GET['sort_dir']) : 'DESC'; 

if ($page < 1) $page = 1;
if ($limit < 1) $limit = 5;
if ($limit > 100) $limit = 100; 
$offset = ($page - 1) * $limit;

$allowedSortColumns = ['id', 'username', 'name', 'role', 'email', 'mobile', 'created_at'];
if (!in_array($sortBy, $allowedSortColumns)) {
    $sortBy = 'id'; 
}

$response = ['staff' => [], 'total' => 0, 'error' => null];
// Initialize $sqlDebug keys to prevent undefined key warnings if error occurs early
$sqlDebug = ['query_count' => '', 'params_count' => [], 'query_data' => '', 'params_data' => []]; 

try {
    $baseSql = "FROM staff";
    $selectSql = "SELECT id, username, name, role, email, mobile, created_at "; 

    $conditions = [];
    // Parameters for the WHERE clause, used for both count and data queries.
    // This array will only contain parameters that are part of the $whereClause.
    $whereClauseParams = []; 

    if (!empty($search)) {
        $searchTerm = "%" . $search . "%";
        $conditions[] = "(LOWER(username) LIKE LOWER(:search_term) OR LOWER(name) LIKE LOWER(:search_term))";
        $whereClauseParams[':search_term'] = $searchTerm; 
    }

    $whereClause = "";
    if (count($conditions) > 0) {
        $whereClause = " WHERE " . implode(" AND ", $conditions);
    }

    // --- Count Query ---
    $countSql = "SELECT COUNT(*) " . $baseSql . $whereClause;
    $sqlDebug['query_count'] = $countSql;
    $sqlDebug['params_count'] = $whereClauseParams;
    $stmtCount = $pdo->prepare($countSql);
    $stmtCount->execute($whereClauseParams); // Execute with only WHERE parameters
    $totalRecords = $stmtCount->fetchColumn();
    $response['total'] = (int)$totalRecords;

    // --- Data Query ---
    $orderByClause = " ORDER BY " . $sortBy . " " . $sortDir; 
    $limitOffsetClause = " LIMIT :limit OFFSET :offset"; 
    
    $dataSql = $selectSql . $baseSql . $whereClause . $orderByClause . $limitOffsetClause;
    $sqlDebug['query_data'] = $dataSql;
    
    $stmtData = $pdo->prepare($dataSql);

    // Build the exact parameter array for the data query's execute() method.
    // Start with parameters from the WHERE clause (if any).
    $dataExecuteParams = $whereClauseParams; 
    
    // ALWAYS add :limit and :offset because they are ALWAYS in $dataSql's $limitOffsetClause.
    $dataExecuteParams[':limit'] = $limit;   
    $dataExecuteParams[':offset'] = $offset; 
    
    $sqlDebug['params_data'] = $dataExecuteParams;

    $stmtData->execute($dataExecuteParams); 
    $staffList = $stmtData->fetchAll(PDO::FETCH_ASSOC);
    
    $response['staff'] = $staffList;

} catch (PDOException $e) {
    // Safely access debug info
    $attemptedQuery = $sqlDebug['query_data'] ?: ($sqlDebug['query_count'] ?: "SQL not set before error");
    $attemptedParams = $sqlDebug['params_data'] ?: ($sqlDebug['params_count'] ?: []);
    error_log("Database query failed in fetch_staff.php: " . $e->getMessage() . " --- SQL attempted: " . $attemptedQuery . " --- Params: " . json_encode($attemptedParams));
    
    $response['error'] = "A database error occurred while fetching staff data.";
    $response['details'] = $e->getMessage(); // Send details for debugging
    // $response['debug_sql'] = $sqlDebug; // Optionally send SQL debug info for frontend
    http_response_code(500); 
} catch (Exception $e) {
    error_log("Unexpected error in fetch_staff.php: " . $e->getMessage());
    $response['error'] = "An unexpected error occurred.";
    http_response_code(500);
}

ob_end_clean(); // Clean (erase) the output buffer and turn off output buffering
echo json_encode($response);
?>
