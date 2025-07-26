<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include 'db.php';
$success_message = $error_message = '';

function generateDonationId($conn) {
    $year = date('Y');
    $result = $conn->query("SELECT COUNT(*) as count FROM donations WHERE YEAR(created_at) = '$year'");
    $row = $result ? $result->fetch_assoc() : null;
    $count = $row ? $row['count'] + 1 : 1;
    return 'DON' . $year . str_pad($count, 4, '0', STR_PAD_LEFT);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_donation'])) {
    $donor_name = htmlspecialchars(trim($_POST['donor_name'] ?? ''), ENT_QUOTES, 'UTF-8');
    $donor_email = filter_var(trim($_POST['donor_email'] ?? ''), FILTER_SANITIZE_EMAIL);
    $donation_amount = floatval($_POST['donation_amount'] ?? 0);
    $payment_method = htmlspecialchars(trim($_POST['payment_method'] ?? ''), ENT_QUOTES, 'UTF-8');
    $transaction_number = htmlspecialchars(trim($_POST['transaction_number'] ?? ''), ENT_QUOTES, 'UTF-8') ?: null;
    $aadhar_number = preg_replace('/[^0-9]/', '', trim($_POST['aadhar_number'] ?? ''));
    $district = htmlspecialchars(trim($_POST['district'] ?? ''), ENT_QUOTES, 'UTF-8') ?: null;
    $state = htmlspecialchars(trim($_POST['state'] ?? ''), ENT_QUOTES, 'UTF-8') ?: null;
    $notes = htmlspecialchars(trim($_POST['notes'] ?? ''), ENT_QUOTES, 'UTF-8') ?: null;
    $address = htmlspecialchars(trim($_POST['address'] ?? ''), ENT_QUOTES, 'UTF-8') ?: null;
    $donation_id = generateDonationId($conn);
    $transfer_proof = null;

    // Validation
    if (empty($donor_name) || empty($donor_email) || empty($donation_amount) || empty($payment_method) || empty($aadhar_number)) {
        $error_message = "Please fill all required fields.";
    } elseif (!filter_var($donor_email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Invalid email format.";
    } elseif ($donation_amount < 1) {
        $error_message = "Donation amount must be at least 1.";
    } elseif (!preg_match("/^[0-9]{12}$/", $aadhar_number)) {
        $error_message = "Aadhar number must be exactly 12 digits.";
    } else {
        // Duplicate checks
        try {
            $stmt = $conn->prepare("SELECT COUNT(*) FROM donations WHERE donor_email = ?");
            $stmt->bind_param("s", $donor_email);
            $stmt->execute();
            $stmt->bind_result($email_count);
            $stmt->fetch();
            $stmt->close();

            $stmt = $conn->prepare("SELECT COUNT(*) FROM donations WHERE aadhar_number = ?");
            $stmt->bind_param("s", $aadhar_number);
            $stmt->execute();
            $stmt->bind_result($aadhar_count);
            $stmt->fetch();
            $stmt->close();

            if ($email_count > 0 && $aadhar_count > 0) {
                $error_message = "Both email and Aadhar number already exist.";
            } elseif ($email_count > 0) {
                $error_message = "Email already exists.";
            } elseif ($aadhar_count > 0) {
                $error_message = "Aadhar number already exists.";
            }
        } catch (Exception $e) {
            $error_message = "Error checking duplicates: " . $e->getMessage();
        }

        // File upload
        if (empty($error_message) && isset($_FILES['transfer_proof']) && $_FILES['transfer_proof']['error'] != UPLOAD_ERR_NO_FILE) {
            $upload_dir = __DIR__ . '/uploads/donations/';
            if (!is_dir($upload_dir)) {
                if (!mkdir($upload_dir, 0755, true)) {
                    $error_message = "Failed to create directory: $upload_dir";
                } else {
                    chmod($upload_dir, 0775);
                }
            }
            if (empty($error_message)) {
                $ext = strtolower(pathinfo($_FILES['transfer_proof']['name'], PATHINFO_EXTENSION));
                if (!in_array($ext, ['jpg', 'jpeg', 'png'])) {
                    $error_message = "Only JPG, JPEG, PNG files allowed for transfer proof.";
                } elseif ($_FILES['transfer_proof']['size'] > 2000000) {
                    $error_message = "File size exceeds 2MB.";
                } else {
                    $filename = uniqid('proof_', true) . '.' . $ext;
                    $filepath = $upload_dir . $filename;
                    if (is_writable($upload_dir)) {
                        if (move_uploaded_file($_FILES['transfer_proof']['tmp_name'], $filepath)) {
                            $transfer_proof = 'uploads/donations/' . $filename;
                        } else {
                            $error_message = "Failed to upload transfer proof. Check permissions for $upload_dir.";
                            error_log("Upload failed: Unable to move file to $filepath");
                        }
                    } else {
                        $error_message = "Directory $upload_dir is not writable.";
                        error_log("Directory not writable: $upload_dir");
                    }
                }
            }
        }

        // Insert to DB
        if (empty($error_message)) {
            $stmt = $conn->prepare("INSERT INTO donations (donation_id, donor_name, donor_email, donation_amount, payment_method, transaction_number, transfer_proof, aadhar_number, district, state, notes, address, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
            if ($stmt) {
                $stmt->bind_param("sssdsdssssss", $donation_id, $donor_name, $donor_email, $donation_amount, $payment_method, $transaction_number, $transfer_proof, $aadhar_number, $district, $state, $notes, $address);
                if ($stmt->execute()) {
                    $success_message = "Thank you for your donation! Your Donation ID is $donation_id.";
                } else {
                    $error_message = "Database error: " . $stmt->error;
                    error_log("SQL Error: " . $stmt->error);
                }
                $stmt->close();
            } else {
                $error_message = "Prepare statement failed: " . $conn->error;
                error_log("Prepare Error: " . $conn->error);
            }
        }
    }
}
?>

<?php include 'header.php'; ?>

<!-- Page Header Start -->
<div class="container-fluid page-header mb-5 wow fadeIn" data-wow-delay="0.1s">
    <div class="container text-center">
        <h1 class="display-4 text-white animated slideInDown mb-4">Donate</h1>
        <nav aria-label="breadcrumb animated slideInDown">
            <ol class="breadcrumb justify-content-center mb-0">
                <li class="breadcrumb-item"><a class="text-white" href="index.php">Home</a></li>
                <li class="breadcrumb-item text-primary active" aria-current="page">Donate</li>
            </ol>
        </nav>
    </div>
</div>
<!-- Page Header End -->

<!-- Donation Form Section Start -->
<div class="container-xxl py-5">
    <div class="container">
        <div class="text-center mx-auto mb-5 wow fadeInUp" data-wow-delay="0.1s" style="max-width: 600px;">
            <div class="d-inline-block rounded-pill bg-secondary text-primary py-1 px-3 mb-3">Support Us</div>
            <h2 class="mb-4">Make a Donation</h2>
            <p>Your generosity helps us empower communities, provide education, healthcare, and support to those in need. Every contribution makes a difference.</p>
        </div>
        <div class="row justify-content-center">
            <div class="col-lg-6 col-md-8 wow fadeInUp" data-wow-delay="0.2s">
                <div class="service-item bg-white text-center h-100 p-4 p-xl-5">
                    <h4 class="mb-3">Donation Form</h4>
                    <?php if ($success_message): ?>
                        <div class="alert alert-success"><?php echo $success_message; ?></div>
                    <?php endif; ?>
                    <?php if ($error_message): ?>
                        <div class="alert alert-danger"><?php echo $error_message; ?></div>
                    <?php endif; ?>
                    <form enctype="multipart/form-data" method="POST">
                        <div class="row g-3 text-start">
                            <div class="col-12 mb-2">
                                <input type="text" class="form-control" name="donor_name" placeholder="Donor Name" value="<?php echo htmlspecialchars($donor_name ?? ''); ?>" required>
                            </div>
                            <div class="col-12 mb-2">
                                <input type="email" class="form-control" name="donor_email" placeholder="Email" value="<?php echo htmlspecialchars($donor_email ?? ''); ?>" required>
                            </div>
                            <div class="col-12 mb-2">
                                <input type="number" class="form-control" name="donation_amount" placeholder="Donation Amount (INR)" min="1" value="<?php echo htmlspecialchars($donation_amount ?? ''); ?>" required>
                            </div>
                            <div class="col-12 mb-2">
                                <select class="form-select" name="payment_method" required>
                                    <option value="">Select Payment Method</option>
                                    <option value="Bank Transfer" <?php if(($payment_method ?? '')=='Bank Transfer') echo 'selected'; ?>>Bank Transfer</option>
                                    <option value="UPI" <?php if(($payment_method ?? '')=='UPI') echo 'selected'; ?>>UPI</option>
                                    <option value="Cash" <?php if(($payment_method ?? '')=='Cash') echo 'selected'; ?>>Cash</option>
                                    <option value="Cheque" <?php if(($payment_method ?? '')=='Cheque') echo 'selected'; ?>>Cheque</option>
                                </select>
                            </div>
                            <div class="col-12 mb-2">
                                <input type="text" class="form-control" name="aadhar_number" placeholder="Aadhar Number" maxlength="12" pattern="[0-9]{12}" value="<?php echo htmlspecialchars($aadhar_number ?? ''); ?>" required>
                            </div>
                            <div class="col-12 mb-2">
                                <input type="text" class="form-control" name="transaction_number" placeholder="Transaction Number" value="<?php echo htmlspecialchars($transaction_number ?? ''); ?>">
                            </div>
                            <div class="col-12 mb-2">
                                <input type="text" class="form-control" name="district" placeholder="District" value="<?php echo htmlspecialchars($district ?? ''); ?>">
                            </div>
                            <div class="col-12 mb-2">
                                <input type="text" class="form-control" name="state" placeholder="State" value="<?php echo htmlspecialchars($state ?? ''); ?>">
                            </div>
                            <div class="col-12 mb-2">
                                <textarea class="form-control" name="address" placeholder="Address" rows="2"><?php echo htmlspecialchars($address ?? ''); ?></textarea>
                            </div>
                            <div class="col-12 mb-2">
                                <textarea class="form-control" name="notes" placeholder="Notes (Optional)" rows="2"><?php echo htmlspecialchars($notes ?? ''); ?></textarea>
                            </div>
                            <div class="col-12 mb-2">
                                <label>Transfer Proof (JPG/PNG, max 2MB):
                                    <input type="file" class="form-control" name="transfer_proof" accept=".jpg,.jpeg,.png">
                                </label>
                            </div>
                            <div class="col-12 d-grid">
                                <button type="submit" name="submit_donation" class="btn btn-primary">Donate Now</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Donation Form Section End -->

<?php include 'footer.php'; ?>

<style>
    .service-item {
        border-top: 5px solid #007bff;
        border-radius: 0.5rem 0.5rem 0 0;
        box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.05);
    }
</style>