<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include 'db.php'; // Use db.php for connection

$success_message = $error_message = '';

function generateVolunteerId($conn) {
    $year = date('Y');
    $stmt = $conn->query("SELECT COUNT(*) as count FROM volunteers WHERE YEAR(created_at) = '$year'");
    $row = $stmt ? $stmt->fetch_assoc() : null;
    $count = $row ? $row['count'] + 1 : 1;
    return 'VOL' . $year . str_pad($count, 4, '0', STR_PAD_LEFT);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_volunteer'])) {
    // Sanitize Inputs
    $full_name = htmlspecialchars(trim($_POST['full_name'] ?? ''), ENT_QUOTES, 'UTF-8');
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
    $father_name = htmlspecialchars(trim($_POST['father_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?: null;
    $mother_name = htmlspecialchars(trim($_POST['mother_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?: null;
    $phone_number = preg_replace('/[^0-9]/', '', trim($_POST['phone_number'] ?? ''));
    $address = htmlspecialchars(trim($_POST['address'] ?? ''), ENT_QUOTES, 'UTF-8') ?: null;
    $date_of_birth = htmlspecialchars(trim($_POST['date_of_birth'] ?? ''), ENT_QUOTES, 'UTF-8') ?: null;
    $gender = htmlspecialchars(trim($_POST['gender'] ?? ''), ENT_QUOTES, 'UTF-8') ?: null;
    $skills = htmlspecialchars(trim($_POST['skills'] ?? ''), ENT_QUOTES, 'UTF-8') ?: null;
    $role = htmlspecialchars(trim($_POST['role'] ?? ''), ENT_QUOTES, 'UTF-8') ?: null;
    $joined_date = date('Y-m-d');

    // Validations
    if (empty($full_name) || empty($email) || empty($phone_number) || empty($father_name) || empty($mother_name)) {
        $error_message = "Please fill all required fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Invalid email format.";
    } elseif (!preg_match("/^[0-9]{10}$/", $phone_number)) {
        $error_message = "Phone number must be exactly 10 digits.";
    } elseif (empty($_FILES['formal_photo']['name'])) {
        $error_message = "Please upload a formal photo.";
    } else {
        try {
            // Duplicate checks
            $stmt = $conn->prepare("SELECT COUNT(*) FROM volunteers WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->bind_result($email_count);
            $stmt->fetch();
            $stmt->close();

            $stmt = $conn->prepare("SELECT COUNT(*) FROM volunteers WHERE phone_number = ?");
            $stmt->bind_param("s", $phone_number);
            $stmt->execute();
            $stmt->bind_result($phone_count);
            $stmt->fetch();
            $stmt->close();

            if ($email_count > 0 && $phone_count > 0) {
                $error_message = "Both email and phone already exist.";
            } elseif ($email_count > 0) {
                $error_message = "Email already exists.";
            } elseif ($phone_count > 0) {
                $error_message = "Phone number already exists.";
            }
        } catch (Exception $e) {
            $error_message = "Error checking duplicates: " . $e->getMessage();
        }

        // Upload Photo
        $formal_photo = null;
        if (empty($error_message)) {
            $upload_dir = __DIR__ . '/uploads/volunteers/';
            if (!is_dir($upload_dir)) {
                if (!mkdir($upload_dir, 0755, true)) {
                    $error_message = "Failed to create directory: $upload_dir";
                } else {
                    chmod($upload_dir, 0775); // Ensure permissions after creation
                }
            }
            if (empty($error_message)) {
                $ext = strtolower(pathinfo($_FILES['formal_photo']['name'], PATHINFO_EXTENSION));
                if (!in_array($ext, ['jpg', 'jpeg', 'png'])) {
                    $error_message = "Only JPG, JPEG, PNG files allowed.";
                } elseif ($_FILES['formal_photo']['size'] > 2000000) {
                    $error_message = "File size exceeds 2MB.";
                } else {
                    $filename = uniqid('photo_', true) . '.' . $ext;
                    $filepath = $upload_dir . $filename;
                    if (is_writable($upload_dir)) {
                        if (move_uploaded_file($_FILES['formal_photo']['tmp_name'], $filepath)) {
                            $formal_photo = 'uploads/volunteers/' . $filename;
                        } else {
                            $error_message = "Failed to upload the photo. Check permissions for $upload_dir.";
                        }
                    } else {
                        $error_message = "Directory $upload_dir is not writable.";
                    }
                }
            }
        }

        // Insert to DB
        if (empty($error_message)) {
            $volunteer_id = generateVolunteerId($conn);
            $stmt = $conn->prepare("INSERT INTO volunteers (volunteer_id, full_name, father_name, mother_name, email, phone_number, address, date_of_birth, gender, formal_photo, skills, role, joined_date, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
            if ($stmt) {
                $stmt->bind_param("sssssssssssss", $volunteer_id, $full_name, $father_name, $mother_name, $email, $phone_number, $address, $date_of_birth, $gender, $formal_photo, $skills, $role, $joined_date);
                if ($stmt->execute()) {
                    $success_message = "Thank you! Your Volunteer ID is $volunteer_id.";
                } else {
                    $error_message = "Database insert error: " . $stmt->error;
                }
                $stmt->close();
            } else {
                $error_message = "Prepare statement failed: " . $conn->error;
            }
        }
    }
}
?>

<?php include 'header.php'; ?>

<!-- Page Header Start -->
<div class="container-fluid page-header mb-5 wow fadeIn" data-wow-delay="0.1s">
    <div class="container text-center">
        <h1 class="display-4 text-white animated slideInDown mb-4">Volunteer</h1>
        <nav aria-label="breadcrumb animated slideInDown">
            <ol class="breadcrumb justify-content-center mb-0">
                <li class="breadcrumb-item"><a class="text-white" href="index.php">Home</a></li>
                <li class="breadcrumb-item text-primary active" aria-current="page">Volunteer</li>
            </ol>
        </nav>
    </div>
</div>
<!-- Page Header End -->

<!-- Volunteer Form Section Start -->
<div class="container-xxl py-5">
    <div class="container">
        <div class="text-center mx-auto mb-5 wow fadeInUp" data-wow-delay="0.1s" style="max-width: 600px;">
            <div class="d-inline-block rounded-pill bg-secondary text-primary py-1 px-3 mb-3">Join Us</div>
            <h2 class="mb-4">Become a Volunteer</h2>
            <p>Your dedication helps us empower communities, provide education, healthcare, and support to those in need. Join us today!</p>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#volunteerModal">Become a Volunteer</button>
        </div>
    </div>
</div>
<!-- Volunteer Modal -->
<div class="modal fade" id="volunteerModal" tabindex="-1" aria-labelledby="volunteerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content shadow-lg rounded-3">
            <div class="modal-header bg-primary text-white border-0">
                <h5 class="modal-title fw-light fs-5" id="volunteerModalLabel">Join as a Volunteer</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <?php if ($success_message): ?>
                    <div class="alert alert-success"><?php echo $success_message; ?></div>
                <?php endif; ?>
                <?php if ($error_message): ?>
                    <div class="alert alert-danger"><?php echo $error_message; ?></div>
                <?php endif; ?>
                <form method="POST" enctype="multipart/form-data" id="volunteerForm">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="full_name" class="form-label fw-bold modern-label">Full Name <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-light modern-input"><i class="bi bi-person-fill"></i></span>
                                <input type="text" class="form-control modern-input border-light shadow-sm" id="full_name" name="full_name" placeholder="Enter your full name" value="<?php echo htmlspecialchars($full_name ?? ''); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="email" class="form-label fw-bold modern-label">Email <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-light modern-input"><i class="bi bi-envelope-fill"></i></span>
                                <input type="email" class="form-control modern-input border-light shadow-sm" id="email" name="email" placeholder="Enter your email" value="<?php echo htmlspecialchars($email ?? ''); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="father_name" class="form-label fw-bold modern-label">Father's Name <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-light modern-input"><i class="bi bi-person-fill"></i></span>
                                <input type="text" class="form-control modern-input border-light shadow-sm" id="father_name" name="father_name" placeholder="Enter father's name" value="<?php echo htmlspecialchars($father_name ?? ''); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="mother_name" class="form-label fw-bold modern-label">Mother's Name <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-light modern-input"><i class="bi bi-person-fill"></i></span>
                                <input type="text" class="form-control modern-input border-light shadow-sm" id="mother_name" name="mother_name" placeholder="Enter mother's name" value="<?php echo htmlspecialchars($mother_name ?? ''); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="phone_number" class="form-label fw-bold modern-label">Phone Number <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-light modern-input"><i class="bi bi-telephone-fill"></i></span>
                                <input type="text" class="form-control modern-input border-light shadow-sm" id="phone_number" name="phone_number" placeholder="Enter 10-digit phone number" value="<?php echo htmlspecialchars($phone_number ?? ''); ?>" pattern="[0-9]{10}" maxlength="10" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="date_of_birth" class="form-label fw-bold modern-label">Date of Birth</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-light modern-input"><i class="bi bi-calendar-fill"></i></span>
                                <input type="date" class="form-control modern-input border-light shadow-sm" id="date_of_birth" name="date_of_birth" value="<?php echo htmlspecialchars($date_of_birth ?? ''); ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="gender" class="form-label fw-bold modern-label">Gender</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-light modern-input"><i class="bi bi-person-fill"></i></span>
                                <select class="form-control modern-input border-light shadow-sm" id="gender" name="gender">
                                    <option value="" <?php if (empty($gender)) echo 'selected'; ?>>Select Gender</option>
                                    <option value="Male" <?php if (($gender ?? '') == 'Male') echo 'selected'; ?>>Male</option>
                                    <option value="Female" <?php if (($gender ?? '') == 'Female') echo 'selected'; ?>>Female</option>
                                    <option value="Other" <?php if (($gender ?? '') == 'Other') echo 'selected'; ?>>Other</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="formal_photo" class="form-label fw-bold modern-label">Formal Photo <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-light modern-input"><i class="bi bi-image-fill"></i></span>
                                <input type="file" class="form-control modern-input border-light shadow-sm" id="formal_photo" name="formal_photo" accept=".jpg,.jpeg,.png" required>
                            </div>
                            <small class="form-text text-muted">Upload a formal photo (JPG/PNG, max 2MB).</small>
                        </div>
                        <div class="col-md-6">
                            <label for="skills" class="form-label fw-bold modern-label">Skills</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-light modern-input"><i class="bi bi-tools"></i></span>
                                <input type="text" class="form-control modern-input border-light shadow-sm" id="skills" name="skills" placeholder="Enter your skills" value="<?php echo htmlspecialchars($skills ?? ''); ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="role" class="form-label fw-bold modern-label">Preferred Role</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-light modern-input"><i class="bi bi-briefcase-fill"></i></span>
                                <input type="text" class="form-control modern-input border-light shadow-sm" id="role" name="role" placeholder="Enter preferred role" value="<?php echo htmlspecialchars($role ?? ''); ?>">
                            </div>
                        </div>
                        <div class="col-12">
                            <label for="address" class="form-label fw-bold modern-label">Address</label>
                            <textarea class="form-control modern-input border-light shadow-sm" id="address" name="address" rows="3" placeholder="Enter your address"><?php echo htmlspecialchars($address ?? ''); ?></textarea>
                        </div>
                        <div class="col-12">
                            <button class="btn btn-primary w-100 py-3" type="submit" name="submit_volunteer">Register as Volunteer</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- Volunteer Modal End -->

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
    document.getElementById('volunteerForm').addEventListener('submit', function(event) {
        const phoneInput = document.querySelector('input[name="phone_number"]');
        const phonePattern = /^[0-9]{10}$/;

        if (!phonePattern.test(phoneInput.value)) {
            event.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Invalid Phone Number',
                text: 'Phone number must be exactly 10 digits.',
                confirmButtonText: 'OK'
            });
            phoneInput.focus();
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
            timer: 8000,
            timerProgressBar: true,
            willClose: () => {
                $('#volunteerModal').modal('hide');
                window.location.href = 'volunteer.php';
            }
        }).then(() => {
            $('#volunteerModal').modal('hide');
            window.location.href = 'volunteer.php';
        });
        // Trigger confetti
        confetti({
            particleCount: 100,
            spread: 70,
            origin: { y: 0.6 }
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