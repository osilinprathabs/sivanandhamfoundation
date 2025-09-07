<?php
$host = 'localhost';
$user = 'root';
$password = '';
$dbname = 'Sivanandham_foundation';

// Create connection
$conn = new mysqli($host, $user, $password, $dbname);

// Simple logging function
function log_message($message, $type = 'INFO') {
    $logfile = __DIR__ . '/logfile.log';
    $date = date('Y-m-d H:i:s');
    $entry = "[$date][$type] $message\n";
    // file_put_contents($logfile, $entry, FILE_APPEND | LOCK_EX);
}

// // Check connection
if ($conn->connect_error) {
    $errorMsg = 'Connection failed: ' . $conn->connect_error;
    log_message($errorMsg, 'ERROR');
    die($errorMsg);
} else {
    log_message("Database connection successful.");
  //  echo "Connected to database successfully.";
}
 

?>
