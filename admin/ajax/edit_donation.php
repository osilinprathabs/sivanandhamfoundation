<?php
session_start();
require_once '../../database.php';

if (!isset($_SESSION['admin_id'])) {
    die('Unauthorized access');
}

if (!isset($_GET['id'])) {
    die('No donation ID provided');
}

$id = (int)$_GET['id'];
$stmt = $conn->prepare("SELECT * FROM donations WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$donation = $result->fetch_assoc();

if (!$donation) {
    die('Donation not found');
}
?>

<div class="container-fluid p-4">
    <form id="editDonationForm" method="POST" action="ajax/update_donation.php" class="needs-validation" novalidate>
        <input type="hidden" name="id" value="<?php echo $donation['id']; ?>">
        
        <!-- Form Header -->
        <div class="form-header mb-4">
            <h4 class="text-primary mb-2"><i class="fas fa-hand-holding-heart me-2"></i>Edit Donation Information</h4>
            <p class="text-muted">Update the donation details below. Fields marked with <span class="text-danger">*</span> are required.</p>
        </div>

        <div class="row g-4">
            <!-- Donor Information Section -->
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-light">
                        <h5 class="mb-0"><i class="fas fa-user-circle me-2"></i>Donor Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Donor Name <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fas fa-user text-primary"></i></span>
                                    <input type="text" class="form-control" name="donor_name" value="<?php echo htmlspecialchars($donation['donor_name'] ?? ''); ?>" required>
                                </div>
                                <div class="invalid-feedback">Please enter donor name.</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Email <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fas fa-envelope text-primary"></i></span>
                                    <input type="email" class="form-control" name="donor_email" value="<?php echo htmlspecialchars($donation['donor_email'] ?? ''); ?>" required>
                                </div>
                                <div class="invalid-feedback">Please enter a valid email.</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Aadhar Number <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fas fa-id-card text-primary"></i></span>
                                    <input type="text" class="form-control" name="aadhar_number" value="<?php echo htmlspecialchars($donation['aadhar_number'] ?? ''); ?>" pattern="[0-9]{12}" maxlength="12" required>
                                </div>
                                <div class="invalid-feedback">Please enter a valid 12-digit Aadhar number.</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Phone Number</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fas fa-phone text-primary"></i></span>
                                    <input type="text" class="form-control" name="phone_number" value="<?php echo htmlspecialchars($donation['phone_number'] ?? ''); ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Donation Details Section -->
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-light">
                        <h5 class="mb-0"><i class="fas fa-money-bill-wave me-2"></i>Donation Details</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Donation Amount (₹) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fas fa-rupee-sign text-primary"></i></span>
                                    <input type="number" class="form-control" name="donation_amount" value="<?php echo htmlspecialchars($donation['donation_amount'] ?? ''); ?>" min="100" required>
                                </div>
                                <div class="invalid-feedback">Please enter a valid amount (minimum ₹100).</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Payment Method <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fas fa-credit-card text-primary"></i></span>
                                    <select class="form-select" name="payment_method" required>
                                        <option value="">Select Payment Method</option>
                                        <option value="Bank Transfer" <?php echo ($donation['payment_method'] ?? '') === 'Bank Transfer' ? 'selected' : ''; ?>>Bank Transfer</option>
                                        <option value="UPI" <?php echo ($donation['payment_method'] ?? '') === 'UPI' ? 'selected' : ''; ?>>UPI</option>
                                        <option value="Cash" <?php echo ($donation['payment_method'] ?? '') === 'Cash' ? 'selected' : ''; ?>>Cash</option>
                                        <option value="Cheque" <?php echo ($donation['payment_method'] ?? '') === 'Cheque' ? 'selected' : ''; ?>>Cheque</option>
                                    </select>
                                </div>
                                <div class="invalid-feedback">Please select a payment method.</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Donation Date <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fas fa-calendar text-primary"></i></span>
                                    <input type="datetime-local" class="form-control" name="created_at" value="<?php echo date('Y-m-d\TH:i', strtotime($donation['created_at'])); ?>" required>
                                </div>
                                <div class="invalid-feedback">Please select donation date.</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Transaction Number</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fas fa-hashtag text-primary"></i></span>
                                    <input type="text" class="form-control" name="transaction_number" value="<?php echo htmlspecialchars($donation['transaction_number'] ?? ''); ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Transfer Proof</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fas fa-file-image text-primary"></i></span>
                                    <input type="file" class="form-control" name="transfer_proof" accept=".jpg,.jpeg,.png">
                                </div>
                                <?php if (!empty($donation['transfer_proof'])): ?>
                                    <small class="text-muted">Current file: <?php echo basename($donation['transfer_proof']); ?></small>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Address Information Section -->
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-light">
                        <h5 class="mb-0"><i class="fas fa-map-marker-alt me-2"></i>Address Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">District</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fas fa-map text-primary"></i></span>
                                    <input type="text" class="form-control" name="district" value="<?php echo htmlspecialchars($donation['district'] ?? ''); ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">State</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fas fa-map-marked-alt text-primary"></i></span>
                                    <input type="text" class="form-control" name="state" value="<?php echo htmlspecialchars($donation['state'] ?? ''); ?>">
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold">Address</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fas fa-home text-primary"></i></span>
                                    <textarea class="form-control" name="address" rows="2"><?php echo htmlspecialchars($donation['address'] ?? ''); ?></textarea>
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold">Notes</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fas fa-sticky-note text-primary"></i></span>
                                    <textarea class="form-control" name="notes" rows="2"><?php echo htmlspecialchars($donation['notes'] ?? ''); ?></textarea>
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
                    <i class="fas fa-save me-2"></i>Update Donation
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
    min-height: 80px;
    resize: vertical;
}
</style>

<script>
document.getElementById('editDonationForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    if (!this.checkValidity()) {
        e.stopPropagation();
        this.classList.add('was-validated');
        return;
    }
    
    const formData = new FormData(this);
    
    fetch('ajax/update_donation.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: 'Donation information updated successfully.',
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
                text: data.message || 'Failed to update donation information.',
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
            text: 'An error occurred while updating donation information.',
            customClass: {
                popup: 'animated fadeInDown'
            }
        });
    });
});
</script> 