<?php
session_start();
require_once '../../database.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    die('Unauthorized access. Please login to continue.');
}

// Check if ID is provided
if (!isset($_GET['id'])) {
    die('No contact ID provided.');
}

$id = (int)$_GET['id'];

// Fetch contact details
$stmt = $conn->prepare("SELECT * FROM contact WHERE id = ?");
if (!$stmt) {
    die('Database error: ' . $conn->error);
}

$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$contact = $result->fetch_assoc();

if (!$contact) {
    die('Contact not found.');
}
?>

<div class="container-fluid p-4">
    <form id="editContactForm" method="POST" action="ajax/update_contact.php" class="needs-validation" novalidate>
        <input type="hidden" name="id" value="<?php echo $contact['id']; ?>">
        
        <!-- Form Header -->
        <div class="form-header mb-4">
            <h4 class="text-primary mb-2"><i class="fas fa-address-book me-2"></i>Edit Contact Information</h4>
            <p class="text-muted">Update the contact's details below. Fields marked with <span class="text-danger">*</span> are required.</p>
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
                                <label class="form-label fw-bold">Name <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fas fa-user text-primary"></i></span>
                                    <input type="text" class="form-control" name="name" value="<?php echo htmlspecialchars($contact['name'] ?? ''); ?>" required>
                                </div>
                                <div class="invalid-feedback">Please enter name.</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Email <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fas fa-envelope text-primary"></i></span>
                                    <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($contact['email'] ?? ''); ?>" required>
                                </div>
                                <div class="invalid-feedback">Please enter a valid email.</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Phone Number <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fas fa-phone text-primary"></i></span>
                                    <input type="text" class="form-control" name="phone_number" value="<?php echo htmlspecialchars($contact['phone_number'] ?? ''); ?>" required>
                                </div>
                                <div class="invalid-feedback">Please enter phone number.</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Subject</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fas fa-heading text-primary"></i></span>
                                    <input type="text" class="form-control" name="subject" value="<?php echo htmlspecialchars($contact['subject'] ?? ''); ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Message Section -->
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-light">
                        <h5 class="mb-0"><i class="fas fa-comment-alt me-2"></i>Message Details</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-bold">Message <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fas fa-comment text-primary"></i></span>
                                    <textarea class="form-control" name="message" rows="4" required><?php echo htmlspecialchars($contact['message'] ?? ''); ?></textarea>
                                </div>
                                <div class="invalid-feedback">Please enter message.</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Created At</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fas fa-calendar text-primary"></i></span>
                                    <input type="text" class="form-control" value="<?php echo date('F j, Y g:i A', strtotime($contact['created_at'])); ?>" readonly>
                                </div>
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
                    <i class="fas fa-save me-2"></i>Update Contact
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

textarea.form-control {
    min-height: 120px;
    resize: vertical;
}
</style>

<script>
document.getElementById('editContactForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    if (!this.checkValidity()) {
        e.stopPropagation();
        this.classList.add('was-validated');
        return;
    }
    
    const formData = new FormData(this);
    
    fetch('ajax/update_contact.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: 'Contact information updated successfully.',
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
                text: data.message || 'Failed to update contact information.',
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
            text: 'An error occurred while updating contact information.',
            customClass: {
                popup: 'animated fadeInDown'
            }
        });
    });
});
</script> 