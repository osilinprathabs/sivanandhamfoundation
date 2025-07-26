<?php
// Prevent any output before JSON response
ob_start();

session_start();
require_once '../../database.php';

// Set JSON header
header('Content-Type: application/json');

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    ob_clean();
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized access. Please login to continue.'
    ]);
    exit;
}

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ob_clean();
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method.'
    ]);
    exit;
}

// Debug: Log the received data
error_log("Received POST data: " . print_r($_POST, true));

// Validate required fields
$required_fields = ['id', 'name', 'email', 'phone_number', 'message'];
foreach ($required_fields as $field) {
    if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
        ob_clean();
        echo json_encode([
            'success' => false,
            'message' => ucfirst($field) . ' is required.'
        ]);
        exit;
    }
}

// Sanitize and validate inputs
$id = (int)$_POST['id'];
$name = htmlspecialchars(trim($_POST['name']), ENT_QUOTES, 'UTF-8');
$email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
$phone_number = preg_replace('/[^0-9]/', '', trim($_POST['phone_number']));
$subject = htmlspecialchars(trim($_POST['subject'] ?? ''), ENT_QUOTES, 'UTF-8');
$message = htmlspecialchars(trim($_POST['message']), ENT_QUOTES, 'UTF-8');

// Format the date properly
$created_at = null;
if (!empty($_POST['created_at'])) {
    $date = new DateTime($_POST['created_at']);
    $created_at = $date->format('Y-m-d H:i:s');
} else {
    $created_at = date('Y-m-d H:i:s'); // Use current date if none provided
}

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    ob_clean();
    echo json_encode([
        'success' => false,
        'message' => 'Invalid email format.'
    ]);
    exit;
}

// Validate phone number format
if (!preg_match('/^[0-9]{10}$/', $phone_number)) {
    ob_clean();
    echo json_encode([
        'success' => false,
        'message' => 'Phone number must be exactly 10 digits.'
    ]);
    exit;
}

try {
    // Check if contact exists
    $check_stmt = $conn->prepare("SELECT id FROM contact WHERE id = ?");
    if (!$check_stmt) {
        throw new Exception("Failed to prepare check statement: " . $conn->error);
    }
    
    $check_stmt->bind_param("i", $id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    
    if ($result->num_rows === 0) {
        ob_clean();
        echo json_encode([
            'success' => false,
            'message' => 'Contact not found.'
        ]);
        exit;
    }
    
    // Update contact
    $update_stmt = $conn->prepare("
        UPDATE contact 
        SET name = ?, 
            email = ?, 
            phone_number = ?, 
            subject = ?, 
            message = ?,
            created_at = ?,
            updated_at = CURRENT_TIMESTAMP
        WHERE id = ?
    ");
    
    if (!$update_stmt) {
        throw new Exception("Failed to prepare update statement: " . $conn->error);
    }
    
    $update_stmt->bind_param("ssssssi", $name, $email, $phone_number, $subject, $message, $created_at, $id);
    
    if ($update_stmt->execute()) {
        ob_clean();
        echo json_encode([
            'success' => true,
            'message' => 'Contact updated successfully!'
        ]);
        exit;
    } else {
        throw new Exception("Failed to update contact: " . $update_stmt->error);
    }
    
} catch (Exception $e) {
    error_log("Contact update error: " . $e->getMessage());
    ob_clean();
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while updating the contact: ' . $e->getMessage()
    ]);
    exit;
} finally {
    // Close statements
    if (isset($check_stmt)) $check_stmt->close();
    if (isset($update_stmt)) $update_stmt->close();
    $conn->close();
}
?> 