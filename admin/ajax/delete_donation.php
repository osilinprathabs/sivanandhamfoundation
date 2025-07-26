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

try {
    // Start transaction
    $conn->begin_transaction();

    // Get the transfer proof path before deleting
    $stmt = $conn->prepare("SELECT transfer_proof FROM donations WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $donation = $result->fetch_assoc();

    // Delete the donation
    $stmt = $conn->prepare("DELETE FROM donations WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if (!$stmt->execute()) {
        throw new Exception("Error deleting donation: " . $stmt->error);
    }

    // Delete the transfer proof file if it exists
    if ($donation && $donation['transfer_proof']) {
        $file_path = '../' . $donation['transfer_proof'];
        if (file_exists($file_path)) {
            unlink($file_path);
        }
    }

    // Commit transaction
    $conn->commit();

    echo json_encode(['success' => true, 'message' => 'Donation deleted successfully']);
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} 