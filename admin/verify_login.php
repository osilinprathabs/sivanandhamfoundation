<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'sri_swarna_vaarahi_trust';

$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die(json_encode([
        'success' => false,
        'message' => 'Database connection failed: ' . $conn->connect_error
    ]));
}

// Set charset
$conn->set_charset("utf8");

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['username']);  
    $password = $_POST['password'];

    // Log the received data
    error_log("Login attempt - Username/Email: " . $login);

    if (empty($login) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Please enter both name/email and password']);
        exit();
    }

    try {
        // First try to find user by email
        $stmt = $conn->prepare("SELECT * FROM admin WHERE email = ?");
        if (!$stmt) {
            throw new Exception("Database error: " . $conn->error);
        }

        $stmt->bind_param("s", $login);
        $stmt->execute();
        $result = $stmt->get_result();

        // Log the email search result
        error_log("Email search result rows: " . $result->num_rows);

        // If not found by email, try by name
        if ($result->num_rows === 0) {
            $stmt->close();
            $stmt = $conn->prepare("SELECT * FROM admin WHERE name = ?");
            if (!$stmt) {
                throw new Exception("Database error: " . $conn->error);
            }
            $stmt->bind_param("s", $login);
            $stmt->execute();
            $result = $stmt->get_result();

            // Log the name search result
            error_log("Name search result rows: " . $result->num_rows);
        }

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // Log the password comparison
            error_log("Stored password: " . $user['password']);
            error_log("Entered password: " . $password);
            
            // Check if password matches
            if ($password === $user['password']) {
                $_SESSION['admin_id'] = $user['id'];
                $_SESSION['admin_name'] = $user['name'];
                $_SESSION['admin_email'] = $user['email'];
                
                // Update last login time
                $updateStmt = $conn->prepare("UPDATE admin SET created_at = CURRENT_TIMESTAMP WHERE id = ?");
                if (!$updateStmt) {
                    throw new Exception("Error updating login time: " . $conn->error);
                }
                $updateStmt->bind_param("i", $user['id']);
                $updateStmt->execute();
                $updateStmt->close();

                error_log("Login successful for user: " . $user['name']);
                echo json_encode(['success' => true, 'redirect' => 'admin/dashboard.php']);
            } else {
                error_log("Password mismatch for user: " . $user['name']);
                echo json_encode(['success' => false, 'message' => 'Invalid password. Please try again.']);
            }
        } else {
            error_log("No user found with login: " . $login);
            echo json_encode(['success' => false, 'message' => 'No user found with this name or email. Please check your credentials.']);
        }
        $stmt->close();
    } catch (Exception $e) {
        error_log("Login error: " . $e->getMessage());
        echo json_encode([
            'success' => false, 
            'message' => 'Login failed. Please try again.',
            'debug' => $e->getMessage()
        ]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

$conn->close();
?> 