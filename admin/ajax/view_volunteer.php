<?php
session_start();
require_once '../../database.php';

if (!isset($_SESSION['admin_id'])) {
    die('Unauthorized access');
}

if (!isset($_GET['id'])) {
    die('No volunteer ID provided');
}

$id = (int)$_GET['id'];
$stmt = $conn->prepare("SELECT * FROM volunteers WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$volunteer = $result->fetch_assoc();

if (!$volunteer) {
    die('Volunteer not found');
}
?>

<div class="container-fluid p-4">
    <!-- View Header -->
    <div class="form-header mb-4">
        <h4 class="text-primary mb-2"><i class="fas fa-user-friends me-2"></i>Volunteer Details</h4>
        <p class="text-muted">View the complete volunteer information below.</p>
    </div>

    <!-- Profile Photo Section -->
    <?php if (!empty($volunteer['formal_photo'])): ?>
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body text-center">
                    <div class="profile-photo-container">
                        <img src="../<?php echo htmlspecialchars($volunteer['formal_photo']); ?>" 
                             class="profile-photo" 
                             alt="Volunteer Photo">
                    </div>
                    <h3 class="mt-3 mb-1"><?php echo htmlspecialchars($volunteer['full_name']); ?></h3>
                    <p class="text-muted mb-0">
                        <i class="fas fa-id-badge me-1"></i><?php echo htmlspecialchars($volunteer['volunteer_id']); ?>
                    </p>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="row g-4">
        <!-- Personal Information Section -->
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-user-circle me-2"></i>Personal Information</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Full Name</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fas fa-user text-primary"></i></span>
                                <input type="text" class="form-control bg-white" value="<?php echo htmlspecialchars($volunteer['full_name']); ?>" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Email</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fas fa-envelope text-primary"></i></span>
                                <input type="email" class="form-control bg-white" value="<?php echo htmlspecialchars($volunteer['email']); ?>" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Father's Name</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fas fa-user text-primary"></i></span>
                                <input type="text" class="form-control bg-white" value="<?php echo htmlspecialchars($volunteer['father_name']); ?>" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Mother's Name</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fas fa-user text-primary"></i></span>
                                <input type="text" class="form-control bg-white" value="<?php echo htmlspecialchars($volunteer['mother_name']); ?>" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Phone Number</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fas fa-phone text-primary"></i></span>
                                <input type="text" class="form-control bg-white" value="<?php echo htmlspecialchars($volunteer['phone_number']); ?>" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Date of Birth</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fas fa-calendar text-primary"></i></span>
                                <input type="text" class="form-control bg-white" value="<?php echo htmlspecialchars($volunteer['date_of_birth'] ?? 'Not provided'); ?>" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Gender</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fas fa-venus-mars text-primary"></i></span>
                                <input type="text" class="form-control bg-white" value="<?php echo htmlspecialchars($volunteer['gender'] ?? 'Not provided'); ?>" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Joined Date</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fas fa-calendar-check text-primary"></i></span>
                                <input type="text" class="form-control bg-white" value="<?php echo date('F j, Y', strtotime($volunteer['joined_date'])); ?>" readonly>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Additional Information Section -->
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Additional Information</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Skills</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fas fa-tools text-primary"></i></span>
                                <input type="text" class="form-control bg-white" value="<?php echo htmlspecialchars($volunteer['skills'] ?? 'Not provided'); ?>" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Role</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fas fa-briefcase text-primary"></i></span>
                                <input type="text" class="form-control bg-white" value="<?php echo htmlspecialchars($volunteer['role'] ?? 'Not provided'); ?>" readonly>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Address</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fas fa-home text-primary"></i></span>
                                <textarea class="form-control bg-white" rows="2" readonly><?php echo htmlspecialchars($volunteer['address'] ?? 'Not provided'); ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Close Button -->
        <div class="col-12 text-end mt-4">
            <button type="button" class="btn btn-primary px-4" data-bs-dismiss="modal">
                <i class="fas fa-times me-2"></i>Close
            </button>
        </div>
    </div>
</div>

<style>
.form-control, .form-select {
    border: 1px solid #dee2e6;
    padding: 0.5rem 0.75rem;
    transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
}

.input-group-text {
    border: 1px solid #dee2e6;
}

.card {
    border: none;
    border-radius: 0.5rem;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.card-header {
    border-bottom: 1px solid rgba(0,0,0,.125);
    background-color: #f8f9fa;
    padding: 1rem;
}

.card-body {
    padding: 1.5rem;
}

.btn {
    padding: 0.5rem 1rem;
    font-weight: 500;
    border-radius: 0.25rem;
    transition: all 0.2s ease-in-out;
}

.btn-primary {
    background-color: #0d6efd;
    border-color: #0d6efd;
}

.btn-primary:hover {
    background-color: #0b5ed7;
    border-color: #0a58ca;
}

.form-label {
    margin-bottom: 0.5rem;
    color: #495057;
}

.form-header {
    border-bottom: 2px solid #e9ecef;
    padding-bottom: 1rem;
}

.form-header h4 {
    color: #0d6efd;
    font-weight: 600;
}

.form-header p {
    font-size: 0.9rem;
    margin-bottom: 0;
}

textarea.form-control {
    min-height: 80px;
    resize: vertical;
}

.bg-white {
    background-color: #ffffff !important;
}

.profile-photo-container {
    width: 200px;
    height: 200px;
    margin: 0 auto;
    border-radius: 50%;
    overflow: hidden;
    border: 4px solid #fff;
    box-shadow: 0 0 20px rgba(0,0,0,0.1);
}

.profile-photo {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.profile-photo:hover {
    transform: scale(1.05);
}
</style> 