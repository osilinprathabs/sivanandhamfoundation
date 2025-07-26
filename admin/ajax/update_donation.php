<?php
session_start();
require_once '../../database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
    die(json_encode(['success' => false, 'message' => 'Unauthorized access']));
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die(json_encode(['success' => false, 'message' => 'Invalid request method']));
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
if (!$id) {
    die(json_encode(['success' => false, 'message' => 'Invalid donation ID']));
}

// Validate required fields
$required_fields = ['donor_name', 'donor_email', 'donation_amount', 'payment_method', 'created_at'];
foreach ($required_fields as $field) {
    if (empty($_POST[$field])) {
        die(json_encode(['success' => false, 'message' => ucfirst(str_replace('_', ' ', $field)) . ' is required']));
    }
}

// Format the created_at datetime
$created_at = date('Y-m-d H:i:s', strtotime($_POST['created_at']));

// Handle file upload if a new transfer proof is provided
$transfer_proof_path = null;
if (isset($_FILES['transfer_proof']) && $_FILES['transfer_proof']['error'] === UPLOAD_ERR_OK) {
    $upload_dir = '../uploads/donations/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $file_extension = strtolower(pathinfo($_FILES['transfer_proof']['name'], PATHINFO_EXTENSION));
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'pdf'];

    if (!in_array($file_extension, $allowed_extensions)) {
        die(json_encode(['success' => false, 'message' => 'Invalid file type. Only JPG, PNG, and PDF files are allowed.']));
    }

    $new_filename = 'donation_' . $id . '_' . time() . '.' . $file_extension;
    $upload_path = $upload_dir . $new_filename;

    if (move_uploaded_file($_FILES['transfer_proof']['tmp_name'], $upload_path)) {
        $transfer_proof_path = 'uploads/donations/' . $new_filename;
    } else {
        die(json_encode(['success' => false, 'message' => 'Error uploading file']));
    }
}

try {
    // Start transaction
    $conn->begin_transaction();

    // Update donation details
    $sql = "UPDATE donations SET 
            donor_name = ?,
            donor_email = ?,
            donation_amount = ?,
            payment_method = ?,
            aadhar_number = ?,
            district = ?,
            state = ?,
            address = ?,
            notes = ?,
            created_at = ?";

    $params = [
        $_POST['donor_name'],
        $_POST['donor_email'],
        $_POST['donation_amount'],
        $_POST['payment_method'],
        $_POST['aadhar_number'] ?? null,
        $_POST['district'] ?? null,
        $_POST['state'] ?? null,
        $_POST['address'] ?? null,
        $_POST['notes'] ?? null,
        $created_at
    ];
    $types = "ssdsssssss";

    // Add transfer proof to update if a new one was uploaded
    if ($transfer_proof_path) {
        $sql .= ", transfer_proof = ?";
        $params[] = $transfer_proof_path;
        $types .= "s";
    }

    $sql .= " WHERE id = ?";
    $params[] = $id;
    $types .= "i";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    
    if (!$stmt->execute()) {
        throw new Exception("Error updating donation: " . $stmt->error);
    }

    // Commit transaction
    $conn->commit();

    echo json_encode(['success' => true, 'message' => 'Donation updated successfully']);
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} 