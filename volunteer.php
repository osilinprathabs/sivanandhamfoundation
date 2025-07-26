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
        </div>
        <div class="row justify-content-center">
            <div class="col-lg-6 col-md-8 wow fadeInUp" data-wow-delay="0.2s">
                <div class="service-item bg-white text-center h-100 p-4 p-xl-5">
                    <h4 class="mb-3">Volunteer Registration Form</h4>
                    <?php if ($success_message): ?>
                        <div class="alert alert-success"><?php echo $success_message; ?></div>
                    <?php endif; ?>
                    <?php if ($error_message): ?>
                        <div class="alert alert-danger"><?php echo $error_message; ?></div>
                    <?php endif; ?>
                    <form method="POST" enctype="multipart/form-data">
                        <div class="row g-3 text-start">
                            <div class="col-12 mb-2">
                                <input type="text" class="form-control" name="full_name" placeholder="Full Name" value="<?php echo htmlspecialchars($full_name ?? ''); ?>" required>
                            </div>
                            <div class="col-12 mb-2">
                                <input type="text" class="form-control" name="father_name" placeholder="Father's Name" value="<?php echo htmlspecialchars($father_name ?? ''); ?>" required>
                            </div>
                            <div class="col-12 mb-2">
                                <input type="text" class="form-control" name="mother_name" placeholder="Mother's Name" value="<?php echo htmlspecialchars($mother_name ?? ''); ?>" required>
                            </div>
                            <div class="col-12 mb-2">
                                <input type="email" class="form-control" name="email" placeholder="Email" value="<?php echo htmlspecialchars($email ?? ''); ?>" required>
                            </div>
                            <div class="col-12 mb-2">
                                <input type="text" class="form-control" name="phone_number" placeholder="Phone Number" value="<?php echo htmlspecialchars($phone_number ?? ''); ?>" required maxlength="10" pattern="[0-9]{10}">
                            </div>
                            <div class="col-12 mb-2">
                                <input type="date" class="form-control" name="date_of_birth" placeholder="Date of Birth" value="<?php echo htmlspecialchars($date_of_birth ?? ''); ?>">
                            </div>
                            <div class="col-12 mb-2">
                                <select class="form-select" name="gender">
                                    <option value="" <?php if (empty($gender)) echo 'selected'; ?>>Select Gender</option>
                                    <option value="Male" <?php if (($gender ?? '') == 'Male') echo 'selected'; ?>>Male</option>
                                    <option value="Female" <?php if (($gender ?? '') == 'Female') echo 'selected'; ?>>Female</option>
                                    <option value="Other" <?php if (($gender ?? '') == 'Other') echo 'selected'; ?>>Other</option>
                                    </select>
                                </div>
                            <div class="col-12 mb-2">
                                <label>Formal Photo (JPG/PNG, max 2MB):
                                    <input type="file" class="form-control" name="formal_photo" accept=".jpg,.jpeg,.png" required>
                                </label>
                            </div>
                            <div class="col-12 mb-2">
                                <textarea class="form-control" name="address" placeholder="Address" rows="2"><?php echo htmlspecialchars($address ?? ''); ?></textarea>
                            </div>
                            <div class="col-12 mb-2">
                                <textarea class="form-control" name="skills" placeholder="Skills / Interests" rows="2"><?php echo htmlspecialchars($skills ?? ''); ?></textarea>
                            </div>
                            <div class="col-12 mb-2">
                                <input type="text" class="form-control" name="role" placeholder="Role (e.g., Volunteer, Coordinator)" value="<?php echo htmlspecialchars($role ?? ''); ?>">
                            </div>
                            <div class="col-12 mb-2">
                                <input type="date" class="form-control" name="joined_date" placeholder="Joined Date" value="<?php echo htmlspecialchars($joined_date ?? date('Y-m-d')); ?>" readonly>
                            </div>
                            <div class="col-12 d-grid">
                                <button type="submit" name="submit_volunteer" class="btn btn-primary">Register as Volunteer</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Volunteer Form Section End -->

    <?php include 'footer.php'; ?>

    <style>
    .service-item {
        border-top: 5px solid #007bff;
        border-radius: 0.5rem 0.5rem 0 0;
        box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.05);
        }
    </style>