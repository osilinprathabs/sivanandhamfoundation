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
    <form id="editVolunteerForm" method="POST" action="ajax/update_volunteer.php" class="needs-validation" novalidate>
        <input type="hidden" name="id" value="<?php echo $volunteer['id']; ?>">
        
        <!-- Form Header -->
        <div class="form-header mb-4">
            <h4 class="text-primary mb-2"><i class="fas fa-user-edit me-2"></i>Edit Volunteer Information</h4>
            <p class="text-muted">Update the volunteer's details below. Fields marked with <span class="text-danger">*</span> are required.</p>
        </div>

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
                                <label class="form-label fw-bold">Full Name <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fas fa-user text-primary"></i></span>
                                    <input type="text" class="form-control" name="full_name" value="<?php echo htmlspecialchars($volunteer['full_name'] ?? ''); ?>" required>
                                </div>
                                <div class="invalid-feedback">Please enter full name.</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Father's Name <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fas fa-user text-primary"></i></span>
                                    <input type="text" class="form-control" name="father_name" value="<?php echo htmlspecialchars($volunteer['father_name'] ?? ''); ?>" required>
                                </div>
                                <div class="invalid-feedback">Please enter father's name.</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Mother's Name <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fas fa-user text-primary"></i></span>
                                    <input type="text" class="form-control" name="mother_name" value="<?php echo htmlspecialchars($volunteer['mother_name'] ?? ''); ?>" required>
                                </div>
                                <div class="invalid-feedback">Please enter mother's name.</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Gender <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fas fa-venus-mars text-primary"></i></span>
                                    <select class="form-select" name="gender" required>
                                        <option value="">Select Gender</option>
                                        <option value="male" <?php echo ($volunteer['gender'] ?? '') === 'male' ? 'selected' : ''; ?>>Male</option>
                                        <option value="female" <?php echo ($volunteer['gender'] ?? '') === 'female' ? 'selected' : ''; ?>>Female</option>
                                        <option value="other" <?php echo ($volunteer['gender'] ?? '') === 'other' ? 'selected' : ''; ?>>Other</option>
                                    </select>
                                </div>
                                <div class="invalid-feedback">Please select gender.</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Date of Birth</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fas fa-calendar text-primary"></i></span>
                                    <input type="date" class="form-control" name="date_of_birth" value="<?php echo htmlspecialchars($volunteer['date_of_birth'] ?? ''); ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contact Information Section -->
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-light">
                        <h5 class="mb-0"><i class="fas fa-address-card me-2"></i>Contact Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Email <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fas fa-envelope text-primary"></i></span>
                                    <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($volunteer['email'] ?? ''); ?>" required>
                                </div>
                                <div class="invalid-feedback">Please enter a valid email.</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Phone Number <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fas fa-phone text-primary"></i></span>
                                    <input type="text" class="form-control" name="phone_number" value="<?php echo htmlspecialchars($volunteer['phone_number'] ?? ''); ?>" required>
                                </div>
                                <div class="invalid-feedback">Please enter phone number.</div>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold">Address</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fas fa-map-marker-alt text-primary"></i></span>
                                    <textarea class="form-control" name="address" rows="2"><?php echo htmlspecialchars($volunteer['address'] ?? ''); ?></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Volunteer Details Section -->
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-light">
                        <h5 class="mb-0"><i class="fas fa-tasks me-2"></i>Volunteer Details</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Skills</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fas fa-tools text-primary"></i></span>
                                    <input type="text" class="form-control" name="skills" value="<?php echo htmlspecialchars($volunteer['skills'] ?? ''); ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Role</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fas fa-user-tag text-primary"></i></span>
                                    <input type="text" class="form-control" name="role" value="<?php echo htmlspecialchars($volunteer['role'] ?? ''); ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Joined Date <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fas fa-calendar-check text-primary"></i></span>
                                    <input type="date" class="form-control" name="joined_date" value="<?php echo date('Y-m-d', strtotime($volunteer['joined_date'] ?? $volunteer['created_at'])); ?>" required>
                                </div>
                                <div class="invalid-feedback">Please select joined date.</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="col-12 text-end mt-4">
                <button type="button" class="btn btn-light px-4 me-2" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Cancel
                </button>
                <button type="submit" class="btn btn-primary px-4">
                    <i class="fas fa-save me-2"></i>Update Volunteer
                </button>
            </div>
        </div>
    </form>
</div>

<style>
.form-control, .form-select {
    border: 1px solid #dee2e6;
    padding: 0.5rem 0.75rem;
    transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
}

.form-control:focus, .form-select:focus {
    border-color: #80bdff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

.input-group-text {
    border: 1px solid #dee2e6;
}

.card {
    border: none;
    border-radius: 0.5rem;
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

.btn-light {
    background-color: #f8f9fa;
    border-color: #f8f9fa;
}

.btn-light:hover {
    background-color: #e2e6ea;
    border-color: #dae0e5;
}

.form-label {
    margin-bottom: 0.5rem;
    color: #495057;
}

.invalid-feedback {
    font-size: 0.875rem;
    margin-top: 0.25rem;
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
</style>

<script>
document.getElementById('editVolunteerForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    if (!this.checkValidity()) {
        e.stopPropagation();
        this.classList.add('was-validated');
        return;
    }
    
    const formData = new FormData(this);
    
    fetch('ajax/update_volunteer.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: 'Volunteer information updated successfully.',
                showConfirmButton: false,
                timer: 1500,
                customClass: {
                    popup: 'animated fadeInDown'
                }
            }).then(() => {
                window.location.reload();
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: data.message || 'Failed to update volunteer information.',
                customClass: {
                    popup: 'animated fadeInDown'
                }
            });
        }
    })
    .catch(error => {
        Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: 'An error occurred while updating volunteer information.',
            customClass: {
                popup: 'animated fadeInDown'
            }
        });
    });
});
</script> 
</script> 