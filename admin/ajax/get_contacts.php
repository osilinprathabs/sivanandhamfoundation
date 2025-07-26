<?php
require_once '../config/database.php';
require_once '../includes/auth_check.php';

// Start output buffering
ob_start();

// Set JSON header
header('Content-Type: application/json');

try {
    // Check if admin is logged in
    if (!isset($_SESSION['admin_id'])) {
        throw new Exception('Unauthorized access');
    }

    // Prepare and execute the query
    $query = "SELECT id, name, email, phone_number, subject, created_at FROM contact ORDER BY created_at DESC";
    $result = $conn->query($query);

    if (!$result) {
        throw new Exception('Database query failed: ' . $conn->error);
    }

    // Fetch all rows
    $contacts = [];
    while ($row = $result->fetch_assoc()) {
        // Format the date
        $row['created_at'] = date('Y-m-d H:i:s', strtotime($row['created_at']));
        $contacts[] = $row;
    }

    // Clean output buffer and send JSON response
    ob_clean();
    echo json_encode(['data' => $contacts]);

} catch (Exception $e) {
    // Log the error
    error_log('Error in get_contacts.php: ' . $e->getMessage());
    
    // Clean output buffer and send error response
    ob_clean();
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => 'An error occurred while fetching contacts data'
    ]);
} finally {
    // Close database connection
    if (isset($conn)) {
        $conn->close();
    }
}
?> 