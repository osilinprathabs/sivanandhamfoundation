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
    // Sanitize and validate inputs
    $donor_name = htmlspecialchars(trim($_POST['donor_name'] ?? ''), ENT_QUOTES, 'UTF-8');
    $donor_email = filter_var(trim($_POST['donor_email'] ?? ''), FILTER_SANITIZE_EMAIL);
    $donation_amount = filter_var(trim($_POST['donation_amount'] ?? ''), FILTER_VALIDATE_FLOAT);
    $payment_method = htmlspecialchars(trim($_POST['payment_method'] ?? ''), ENT_QUOTES, 'UTF-8');
    $aadhar_number = preg_replace('/[^0-9]/', '', trim($_POST['aadhar_number'] ?? ''));
    $address = !empty(trim($_POST['address'] ?? '')) ? htmlspecialchars(trim($_POST['address']), ENT_QUOTES, 'UTF-8') : null;
    $district = !empty(trim($_POST['district'] ?? '')) ? htmlspecialchars(trim($_POST['district']), ENT_QUOTES, 'UTF-8') : null;
    $state = !empty(trim($_POST['state'] ?? '')) ? htmlspecialchars(trim($_POST['state']), ENT_QUOTES, 'UTF-8') : null;
    $notes = !empty(trim($_POST['notes'] ?? '')) ? htmlspecialchars(trim($_POST['notes']), ENT_QUOTES, 'UTF-8') : null;
    $donation_id = generateDonationId($conn);

    // Validate required fields
    if (empty($donor_name) || empty($donor_email) || $donation_amount === false || empty($payment_method) || empty($aadhar_number)) {
        $error_message = "Please fill in all required fields (Name, Email, Donation Amount, Payment Method, Aadhar Number).";
    } elseif (!filter_var($donor_email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Invalid email format.";
    } elseif ($donation_amount < 100) {
        $error_message = "Donation amount must be at least ₹100.";
    } elseif (!preg_match("/^[0-9]{12}$/", $aadhar_number)) {
        $error_message = "Aadhar number must be exactly 12 digits.";
    } elseif (!in_array($payment_method, ['Bank Transfer', 'UPI', 'Cash', 'Cheque'])) {
        $error_message = "Invalid payment method selected.";
    } else {
        // Handle file upload for transfer_proof
        $transfer_proof = null;
        if (!empty($_FILES['transfer_proof']['name'])) {
            $upload_dir = __DIR__ . '/Uploads/';
            if (!is_dir($upload_dir)) {
                if (!mkdir($upload_dir, 0755, true)) {
                    $error_message = "Failed to create upload directory. Please contact support.";
                    error_log("Failed to create upload directory: $upload_dir", 3, __DIR__ . '/upload_errors.log');
                }
            } elseif (!is_writable($upload_dir)) {
                $error_message = "Upload directory is not writable. Please contact support.";
                error_log("Upload directory not writable: $upload_dir", 3, __DIR__ . '/upload_errors.log');
            } else {
                if ($_FILES['transfer_proof']['error'] !== UPLOAD_ERR_OK) {
                    $error_message = "File upload error: ";
                    switch ($_FILES['transfer_proof']['error']) {
                        case UPLOAD_ERR_INI_SIZE:
                        case UPLOAD_ERR_FORM_SIZE:
                            $error_message .= "File size exceeds limit (max 2MB).";
                            break;
                        case UPLOAD_ERR_NO_FILE:
                            $error_message .= "No file uploaded.";
                            break;
                        default:
                            $error_message .= "Unknown error (code: {$_FILES['transfer_proof']['error']}).";
                            break;
                    }
                } else {
                    $file_type = strtolower(pathinfo($_FILES['transfer_proof']['name'], PATHINFO_EXTENSION));
                    if (!in_array($file_type, ['jpg', 'jpeg', 'png'])) {
                        $error_message = "Only JPG, JPEG, or PNG files are allowed.";
                    } elseif ($_FILES['transfer_proof']['size'] > 2000000) {
                        $error_message = "File size must not exceed 2MB.";
                    } else {
                        $original_name = preg_replace("/[^A-Za-z0-9._-]/", "", basename($_FILES['transfer_proof']['name']));
                        $file_name = uniqid(mt_rand(), true) . '_' . $original_name;
                        $file_path = $upload_dir . $file_name;

                        if (!move_uploaded_file($_FILES['transfer_proof']['tmp_name'], $file_path)) {
                            $error_message = "Failed to upload file. Please try again.";
                            error_log("File upload failed for $file_path: " . error_get_last()['message'], 3, __DIR__ . '/upload_errors.log');
                        } else {
                            $transfer_proof = 'Uploads/' . $file_name;
                        }
                    }
                }
            }
        }

        // Proceed to database insertion if no errors
        if (empty($error_message)) {
            try {
                $transaction_number = null; // Not collected in the form, set to NULL

                $stmt = $conn->prepare("
                    INSERT INTO donations (
                        donation_id, donor_name, donor_email, donation_amount, payment_method, 
                        transaction_number, transfer_proof, aadhar_number, 
                        address, district, state, notes, created_at, updated_at
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)
                ");
                if (!$stmt) {
                    $error = $conn->error;
                    throw new Exception("Failed to prepare statement: $error");
                }
                $stmt->bind_param(
                    "sssdsdssssss",
                    $donation_id,
                    $donor_name,
                    $donor_email,
                    $donation_amount,
                    $payment_method,
                    $transaction_number,
                    $transfer_proof,
                    $aadhar_number,
                    $address,
                    $district,
                    $state,
                    $notes
                );
                if (!$stmt->execute()) {
                    $error = $stmt->error;
                    throw new Exception("Failed to insert donation: $error");
                }
                $stmt->close();
                $success_message = "Thank you for your donation! Your Donation ID is $donation_id. Your support helps us serve people and uplift our community.";
            } catch (Exception $e) {
                $error_message = "Failed to save your donation: " . htmlspecialchars($e->getMessage());
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
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#donationModal">Donate Now</button>
        </div>
    </div>
</div>
<!-- Donation Modal -->
<div class="modal fade" id="donationModal" tabindex="-1" aria-labelledby="donationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content shadow-lg rounded-3">
            <div class="modal-header bg-primary text-white border-0">
                <h5 class="modal-title fw-light fs-5" id="donationModalLabel">Make a Donation</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <?php if ($success_message): ?>
                    <div class="alert alert-success"><?php echo $success_message; ?></div>
                <?php endif; ?>
                <?php if ($error_message): ?>
                    <div class="alert alert-danger"><?php echo $error_message; ?></div>
                <?php endif; ?>
                <form method="POST" enctype="multipart/form-data" id="donationForm">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="donor_name" class="form-label fw-bold modern-label">Donor Name <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-light modern-input"><i class="bi bi-person-fill"></i></span>
                                <input type="text" class="form-control modern-input border-light shadow-sm" id="donor_name" name="donor_name" placeholder="Enter your name" value="<?php echo htmlspecialchars($donor_name ?? ''); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="donor_email" class="form-label fw-bold modern-label">Email <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-light modern-input"><i class="bi bi-envelope-fill"></i></span>
                                <input type="email" class="form-control modern-input border-light shadow-sm" id="donor_email" name="donor_email" placeholder="Enter your email" value="<?php echo htmlspecialchars($donor_email ?? ''); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="donation_amount" class="form-label fw-bold modern-label">Donation Amount (INR) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-light modern-input"><i class="bi bi-currency-rupee"></i></span>
                                <input type="number" class="form-control modern-input border-light shadow-sm" id="donation_amount" name="donation_amount" placeholder="Enter amount" min="100" value="<?php echo htmlspecialchars($donation_amount ?? ''); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="payment_method" class="form-label fw-bold modern-label">Payment Method <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-light modern-input"><i class="bi bi-credit-card-fill"></i></span>
                                <select class="form-control modern-input border-light shadow-sm" id="payment_method" name="payment_method" required>
                                    <option value="" <?php if (empty($payment_method)) echo 'selected'; ?>>Select Payment Method</option>
                                    <option value="Bank Transfer" <?php if (($payment_method ?? '') == 'Bank Transfer') echo 'selected'; ?>>Bank Transfer</option>
                                    <option value="UPI" <?php if (($payment_method ?? '') == 'UPI') echo 'selected'; ?>>UPI</option>
                                    <option value="Cash" <?php if (($payment_method ?? '') == 'Cash') echo 'selected'; ?>>Cash</option>
                                    <option value="Cheque" <?php if (($payment_method ?? '') == 'Cheque') echo 'selected'; ?>>Cheque</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="aadhar_number" class="form-label fw-bold modern-label">Aadhar Number <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-light modern-input"><i class="bi bi-person-vcard-fill"></i></span>
                                <input type="text" class="form-control modern-input border-light shadow-sm" id="aadhar_number" name="aadhar_number" placeholder="Enter 12-digit Aadhar number" pattern="[0-9]{12}" maxlength="12" value="<?php echo htmlspecialchars($aadhar_number ?? ''); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="transaction_number" class="form-label fw-bold modern-label">Transaction Number</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-light modern-input"><i class="bi bi-receipt"></i></span>
                                <input type="text" class="form-control modern-input border-light shadow-sm" id="transaction_number" name="transaction_number" placeholder="Enter transaction number" value="<?php echo htmlspecialchars($transaction_number ?? ''); ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="district" class="form-label fw-bold modern-label">District</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-light modern-input"><i class="bi bi-geo-alt-fill"></i></span>
                                <input type="text" class="form-control modern-input border-light shadow-sm" id="district" name="district" placeholder="Enter district" value="<?php echo htmlspecialchars($district ?? ''); ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="state" class="form-label fw-bold modern-label">State</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-light modern-input"><i class="bi bi-geo-fill"></i></span>
                                <input type="text" class="form-control modern-input border-light shadow-sm" id="state" name="state" placeholder="Enter state" value="<?php echo htmlspecialchars($state ?? ''); ?>">
                            </div>
                        </div>
                        <div class="col-12">
                            <label for="address" class="form-label fw-bold modern-label">Address</label>
                            <textarea class="form-control modern-input border-light shadow-sm" id="address" name="address" rows="3" placeholder="Enter your address"><?php echo htmlspecialchars($address ?? ''); ?></textarea>
                        </div>
                        <div class="col-12">
                            <label for="notes" class="form-label fw-bold modern-label">Notes (Optional)</label>
                            <textarea class="form-control modern-input border-light shadow-sm" id="notes" name="notes" rows="3" placeholder="Enter any additional notes"><?php echo htmlspecialchars($notes ?? ''); ?></textarea>
                        </div>
                        <div class="col-12">
                            <label for="transfer_proof" class="form-label fw-bold modern-label">Transfer Proof (JPG/PNG, max 2MB)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-light modern-input"><i class="bi bi-image-fill"></i></span>
                                <input type="file" class="form-control modern-input border-light shadow-sm" id="transfer_proof" name="transfer_proof" accept=".jpg,.jpeg,.png">
                            </div>
                            <small class="form-text text-muted">Required for Bank Transfer, UPI, or Cheque.</small>
                        </div>
                        <div class="col-12">
                            <button class="btn btn-primary w-100 py-3" type="submit" name="submit_donation">Donate Now</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- Donation Modal End -->

<?php include 'footer.php'; ?>

<!-- JavaScript Libraries -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="lib/easing/easing.min.js"></script>
<script src="lib/waypoints/waypoints.min.js"></script>
<script src="lib/counterup/counterup.min.js"></script>
<script src="lib/owlcarousel/owl.carousel.min.js"></script>
<script src="lib/lightbox/js/lightbox.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.5.1/dist/confetti.browser.min.js"></script>

<!-- Template Javascript -->
<script src="js/main.js"></script>

<!-- Client-Side Validation and SweetAlert2 -->
<script>
    document.getElementById('donationForm').addEventListener('submit', function(event) {
        const aadharInput = document.querySelector('input[name="aadhar_number"]');
        const aadharPattern = /^[0-9]{12}$/;
        const donationAmount = document.querySelector('input[name="donation_amount"]');
        const paymentMethod = document.querySelector('select[name="payment_method"]').value;
        const transferProof = document.querySelector('input[name="transfer_proof"]');

        if (!aadharPattern.test(aadharInput.value)) {
            event.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Invalid Aadhar Number',
                text: 'Aadhar number must be exactly 12 digits.',
                confirmButtonText: 'OK'
            });
            aadharInput.focus();
            return;
        }

        if (donationAmount.value < 100) {
            event.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Invalid Donation Amount',
                text: 'Donation amount must be at least ₹100.',
                confirmButtonText: 'OK'
            });
            donationAmount.focus();
            return;
        }

        if (['Bank Transfer', 'UPI', 'Cheque'].includes(paymentMethod) && !transferProof.files.length) {
            event.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Missing Transfer Proof',
                text: 'Transfer proof is required for Bank Transfer, UPI, or Cheque.',
                confirmButtonText: 'OK'
            });
            transferProof.focus();
            return;
        }
    });

    <?php if ($error_message): ?>
        Swal.fire({
            icon: 'error',
            title: 'Submission Error',
            text: '<?php echo htmlspecialchars($error_message); ?>',
            confirmButtonText: 'OK'
        });
    <?php endif; ?>

    <?php if ($success_message): ?>
        Swal.fire({
            icon: 'success',
            title: '<?php echo htmlspecialchars($success_message); ?>',
            showConfirmButton: true,
            confirmButtonText: 'OK',
            timer: 8000,
            timerProgressBar: true
        }).then((result) => {
            if (result.isConfirmed || result.isDismissed) {
                // Reset the form
                document.getElementById('donationForm').reset();
                // Hide the modal
                $('#donationModal').modal('hide');
                // Trigger confetti
                confetti({
                    particleCount: 100,
                    spread: 70,
                    origin: { y: 0.6 }
                });
                // Redirect to donation.php
                window.location.href = 'donation.php';
            }
        });
    <?php endif; ?>
</script>

<style>
    .modern-label {
        font-size: 0.9rem;
        color: #333;
    }
    .modern-input {
        border-radius: 0;
        border: none;
        background-color: #f8f9fa;
    }
    .modern-input:focus {
        box-shadow: none;
        background-color: #e9ecef;
    }
    .alert {
        animation: fadeIn 0.5s;
    }
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
    .service-item {
        border-top: 5px solid #007bff;
        border-radius: 0.5rem 0.5rem 0 0;
        box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.05);
    }
</style>