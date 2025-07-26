<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include 'db.php'; // Use db.php for connection

$success_message = $error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = htmlspecialchars(trim($_POST['name'] ?? ''), ENT_QUOTES, 'UTF-8');
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
    $phone_number = trim($_POST['phone_number'] ?? '');
    $subject = htmlspecialchars(trim($_POST['subject'] ?? ''), ENT_QUOTES, 'UTF-8');
    $message = htmlspecialchars(trim($_POST['message'] ?? ''), ENT_QUOTES, 'UTF-8');

    if (empty($name) || empty($email) || empty($phone_number) || empty($message)) {
        $error_message = "Please fill in all required fields (Name, Email, Phone Number, Message).";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Invalid email format.";
    } elseif (!preg_match("/^[0-9]{10,15}$/", $phone_number)) {
        $error_message = "Phone number must be 10-15 digits.";
    } else {
        $stmt = $conn->prepare("INSERT INTO contact (name, email, phone_number, subject, message, created_at) VALUES (?, ?, ?, ?, ?, CURRENT_TIMESTAMP)");
        $stmt->bind_param("sssss", $name, $email, $phone_number, $subject, $message);
        if ($stmt->execute()) {
            $success_message = "We have received your message successfully!";
        } else {
            $error_message = "Failed to send your message: " . $stmt->error;
        }
        $stmt->close();
    }
}
?>

<?php include 'header.php'; ?>

<!-- Page Header Start -->
<div class="container-fluid page-header mb-5 wow fadeIn" data-wow-delay="0.1s">
    <div class="container text-center">
        <h1 class="display-4 text-white animated slideInDown mb-4">Contact Us</h1>
        <nav aria-label="breadcrumb animated slideInDown">
            <ol class="breadcrumb justify-content-center mb-0">
                <li class="breadcrumb-item"><a class="text-white" href="index.php">Home</a></li>
                <li class="breadcrumb-item text-primary active" aria-current="page">Contact</li>
            </ol>
        </nav>
    </div>
</div>
<!-- Page Header End -->

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="service-item bg-white text-center h-100 p-4 p-xl-5">
                <h4 class="mb-3">Contact Us</h4>
                <?php if ($success_message): ?>
                    <div class="alert alert-success"><?php echo $success_message; ?></div>
                <?php endif; ?>
                <?php if ($error_message): ?>
                    <div class="alert alert-danger"><?php echo $error_message; ?></div>
                <?php endif; ?>
                <form method="POST">
                    <div class="row g-3 text-start">
                        <div class="col-12 mb-2"><input type="text" class="form-control" name="name" placeholder="Full Name" required></div>
                        <div class="col-12 mb-2"><input type="email" class="form-control" name="email" placeholder="Email" required></div>
                        <div class="col-12 mb-2"><input type="text" class="form-control" name="phone_number" placeholder="Phone" required></div>
                        <div class="col-12 mb-2"><input type="text" class="form-control" name="subject" placeholder="Subject" required></div>
                        <div class="col-12 mb-2"><textarea class="form-control" name="message" placeholder="Message" rows="4" required></textarea></div>
                        <div class="col-12 d-grid"><button type="submit" class="btn btn-primary">Send Message</button></div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>

<style>
    .service-item {
        border-top: 5px solid #007bff;
        border-radius: 0.5rem 0.5rem 0 0;
        box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.05);
    }
</style>