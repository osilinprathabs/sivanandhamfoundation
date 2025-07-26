<?php
session_start();
require_once '../../database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

try {
    // Get and sanitize input data
    $id = (int)$_POST['id'];
    $full_name = htmlspecialchars(trim($_POST['full_name'] ?? ''), ENT_QUOTES, 'UTF-8');
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
    $phone_number = preg_replace('/[^0-9]/', '', trim($_POST['phone_number'] ?? ''));
    $role = htmlspecialchars(trim($_POST['role'] ?? ''), ENT_QUOTES, 'UTF-8');
    $father_name = htmlspecialchars(trim($_POST['father_name'] ?? ''), ENT_QUOTES, 'UTF-8');
    $mother_name = htmlspecialchars(trim($_POST['mother_name'] ?? ''), ENT_QUOTES, 'UTF-8');
    $date_of_birth = htmlspecialchars(trim($_POST['date_of_birth'] ?? ''), ENT_QUOTES, 'UTF-8');
    $gender = htmlspecialchars(trim($_POST['gender'] ?? ''), ENT_QUOTES, 'UTF-8');
    $address = htmlspecialchars(trim($_POST['address'] ?? ''), ENT_QUOTES, 'UTF-8');
    $skills = htmlspecialchars(trim($_POST['skills'] ?? ''), ENT_QUOTES, 'UTF-8');

    // Validate required fields
    if (empty($full_name) || empty($email) || empty($phone_number)) {
        throw new Exception('Required fields cannot be empty');
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Invalid email format');
    }

    if (!preg_match("/^[0-9]{10}$/", $phone_number)) {
        throw new Exception('Phone number must be exactly 10 digits');
    }

    // Check for duplicate email (excluding current volunteer)
    $stmt = $conn->prepare("SELECT COUNT(*) FROM volunteers WHERE email = ? AND id != ?");
    $stmt->bind_param("si", $email, $id);
    $stmt->execute();
    $stmt->bind_result($email_count);
    $stmt->fetch();
    $stmt->close();

    if ($email_count > 0) {
        throw new Exception('Email already exists');
    }

    // Check for duplicate phone number (excluding current volunteer)
    $stmt = $conn->prepare("SELECT COUNT(*) FROM volunteers WHERE phone_number = ? AND id != ?");
    $stmt->bind_param("si", $phone_number, $id);
    $stmt->execute();
    $stmt->bind_result($phone_count);
    $stmt->fetch();
    $stmt->close();

    if ($phone_count > 0) {
        throw new Exception('Phone number already exists');
    }

    // Update volunteer information
    $stmt = $conn->prepare("
        UPDATE volunteers SET 
            full_name = ?,
            email = ?,
            phone_number = ?,
            role = ?,
            father_name = ?,
            mother_name = ?,
            date_of_birth = ?,
            gender = ?,
            address = ?,
            skills = ?,
            updated_at = CURRENT_TIMESTAMP
        WHERE id = ?
    ");

    $stmt->bind_param(
        "ssssssssssi",
        $full_name,
        $email,
        $phone_number,
        $role,
        $father_name,
        $mother_name,
        $date_of_birth,
        $gender,
        $address,
        $skills,
        $id
    );

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Volunteer information updated successfully']);
    } else {
        throw new Exception('Failed to update volunteer information');
    }

    $stmt->close();

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
?> 