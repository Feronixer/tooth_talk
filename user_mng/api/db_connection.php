<?php
// api/db_connection.php

// --- Database Configuration ---
// Replace with your actual database credentials
$db_host = "localhost";     // Usually "localhost" or an IP address
$db_name = "toothtalk_db";  // The name of your database
$db_user = "root";          // Your database username
$db_pass = "";              // Your database password (leave empty for default XAMPP/MAMP root user)

// --- Establish PDO Connection ---
try {
    // Data Source Name (DSN)
    $dsn = "mysql:host=$db_host;dbname=$db_name;charset=utf8mb4";

    // PDO options
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Turn on errors in the form of exceptions
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Fetch results as associative arrays
        PDO::ATTR_EMULATE_PREPARES   => false,                  // Disable emulation of prepared statements for security
    ];

    // Create a new PDO instance
    $pdo = new PDO($dsn, $db_user, $db_pass, $options);

} catch (PDOException $e) {
    // --- Connection Error Handling ---
    // It's crucial that this output is valid JSON for the frontend to parse correctly,
    // especially if this file is included at the top of other API scripts.

    header('Content-Type: application/json'); // Set content type to JSON
    http_response_code(500); // Set HTTP status code to 500 (Internal Server Error)

    // Output a JSON error message
    echo json_encode([
        "error" => "Database connection failed.",
        "message" => "Could not connect to the database. Please check your configuration and ensure the database server is running.",
        // "details" => $e->getMessage() // Avoid exposing detailed SQL error messages in a production environment for security reasons.
                                         // Log this to a server file instead.
    ]);

    // Stop script execution if the database connection fails
    exit;
}

// The $pdo object is now available for use in any script that includes this file.
// No need to explicitly close PDO connection; it closes when the script ends.
?>
