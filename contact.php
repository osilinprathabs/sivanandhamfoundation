<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include 'db.php'; // Database connection

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
        <div class="col-lg-10">
            <div class="row g-4">
                <!-- Left: Form -->
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-gradient-primary text-white text-center p-4">
                            <h4 class="mb-0">Get in Touch</h4>
                            <p class="text-white-50 mt-2 mb-0">We'd love to hear from you! Fill out the form below to reach us.</p>
                        </div>
                        <div class="card-body p-4">
                            <form method="POST" id="contactForm" novalidate>
                                <div class="row g-3">
                                    <div class="col-12 form-floating">
                                        <input type="text" class="form-control" id="name" name="name" placeholder="Full Name" value="<?php echo htmlspecialchars($_POST['name'] ?? '', ENT_QUOTES); ?>" required>
                                        <label for="name">Full Name</label>
                                    </div>
                                    <div class="col-12 form-floating">
                                        <input type="email" class="form-control" id="email" name="email" placeholder="Email" value="<?php echo htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES); ?>" required>
                                        <label for="email">Email</label>
                                    </div>
                                    <div class="col-12 form-floating">
                                        <input type="text" class="form-control" id="phone_number" name="phone_number" placeholder="Phone" value="<?php echo htmlspecialchars($_POST['phone_number'] ?? '', ENT_QUOTES); ?>" required>
                                        <label for="phone_number">Phone Number</label>
                                    </div>
                                    <div class="col-12 form-floating">
                                        <input type="text" class="form-control" id="subject" name="subject" placeholder="Subject" value="<?php echo htmlspecialchars($_POST['subject'] ?? '', ENT_QUOTES); ?>">
                                        <label for="subject">Subject (Optional)</label>
                                    </div>
                                    <div class="col-12 form-floating">
                                        <textarea class="form-control" id="message" name="message" placeholder="Message" rows="5" required><?php echo htmlspecialchars($_POST['message'] ?? '', ENT_QUOTES); ?></textarea>
                                        <label for="message">Message</label>
                                    </div>
                                    <div class="col-12 d-grid">
                                        <button type="submit" class="btn btn-primary btn-lg">Send Message</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Right: Google Map -->
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-gradient-primary text-white text-center p-4">
                            <h4 class="mb-0">Our Location</h4>
                            <p class="text-white-50 mt-2 mb-0">Visit us at our temple</p>
                        </div>
                        <div class="card-body p-0 map-container" style="height: 400px;">
                            <iframe
                                src="https://maps.google.com/maps?q=SHRI%20AADHI%20VARAHI%20AMMAN%20PARIGARA%20TEMPLE,%20101,%20Shanmuga%20Nagar,%20Uyyakondan%20Thirumalai,%20Sholanganallur,%20Tamil%20Nadu%20620102&output=embed"
                                style="width: 100%; height: 100%; border: 0; border-radius: 0 0 0.75rem 0.75rem;"
                                allowfullscreen=""
                                loading="lazy">
                            </iframe>
                        </div>
                    </div>
                </div>
                <!-- End Google Map -->
            </div>
        </div>
    </div>
</div>

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
    document.getElementById('contactForm').addEventListener('submit', function(event) {
        const phoneInput = document.querySelector('input[name="phone_number"]');
        const phonePattern = /^[0-9]{10,15}$/;

        if (!phonePattern.test(phoneInput.value)) {
            event.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Invalid Phone Number',
                text: 'Phone number must be 10-15 digits.',
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
            timerProgressBar: true
        }).then(() => {
            window.location.href = 'contact.php';
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
    .card {
        border-radius: 0.75rem;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 1rem 2rem rgba(0,0,0,0.1) !important;
    }
    .bg-gradient-primary {
        background: linear-gradient(45deg, #0057b8, #00c4ff);
    }
    .form-floating > .form-control,
    .form-floating > .form-control:focus {
        border: none;
        border-bottom: 2px solid #dee2e6;
        border-radius: 0;
        padding-top: 1.5rem;
        height: calc(3.5rem + 2px);
    }
    .form-floating > textarea.form-control {
        height: auto;
    }
    .form-floating > label {
        color: #6c757d;
        padding-left: 0.75rem;
        transition: all 0.2s ease;
    }
    .form-floating > .form-control:focus ~ label,
    .form-floating > .form-control:not(:placeholder-shown) ~ label {
        transform: translateY(-1.5rem) scale(0.85);
        color: #007bff;
    }
    .form-floating > .form-control:focus {
        border-bottom-color: #007bff;
        box-shadow: none;
    }
    .btn-primary {
        background-color: #007bff;
        border-color: #007bff;
        transition: background-color 0.3s ease;
    }
    .btn-primary:hover {
        background-color: #0057b8;
        border-color: #0057b8;
    }
    .alert {
        animation: fadeIn 0.5s;
    }
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
</style>