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
    <!-- View Header -->
    <div class="form-header mb-4">
        <h4 class="text-primary mb-2"><i class="fas fa-hand-holding-heart me-2"></i>Donation Details</h4>
        <p class="text-muted">View the complete donation information below.</p>
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
                            <label class="form-label fw-bold">Donor Name</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fas fa-user text-primary"></i></span>
                                <input type="text" class="form-control bg-white" value="<?php echo htmlspecialchars($donation['donor_name']); ?>" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Email</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fas fa-envelope text-primary"></i></span>
                                <input type="email" class="form-control bg-white" value="<?php echo htmlspecialchars($donation['donor_email']); ?>" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Aadhar Number</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fas fa-id-card text-primary"></i></span>
                                <input type="text" class="form-control bg-white" value="<?php echo htmlspecialchars($donation['aadhar_number']); ?>" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Phone Number</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fas fa-phone text-primary"></i></span>
                                <input type="text" class="form-control bg-white" value="<?php echo htmlspecialchars($donation['phone_number'] ?? 'Not provided'); ?>" readonly>
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
                            <label class="form-label fw-bold">Donation Amount</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fas fa-rupee-sign text-primary"></i></span>
                                <input type="text" class="form-control bg-white" value="₹<?php echo htmlspecialchars($donation['donation_amount']); ?>" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Payment Method</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fas fa-credit-card text-primary"></i></span>
                                <input type="text" class="form-control bg-white" value="<?php echo htmlspecialchars($donation['payment_method']); ?>" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Donation Date</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fas fa-calendar text-primary"></i></span>
                                <input type="text" class="form-control bg-white" value="<?php echo date('F j, Y, g:i a', strtotime($donation['created_at'])); ?>" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Transaction Number</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fas fa-hashtag text-primary"></i></span>
                                <input type="text" class="form-control bg-white" value="<?php echo htmlspecialchars($donation['transaction_number'] ?? 'Not provided'); ?>" readonly>
                            </div>
                        </div>
                        <?php if (!empty($donation['transfer_proof'])): ?>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Transfer Proof</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fas fa-file-image text-primary"></i></span>
                                <input type="text" class="form-control bg-white" value="<?php echo basename($donation['transfer_proof']); ?>" readonly>
                            </div>
                            <div class="mt-2">
                                <img src="../<?php echo htmlspecialchars($donation['transfer_proof']); ?>" 
                                     class="img-fluid rounded" 
                                     alt="Transfer Proof"
                                     style="max-height: 200px;">
                            </div>
                        </div>
                        <?php endif; ?>
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
                                <input type="text" class="form-control bg-white" value="<?php echo htmlspecialchars($donation['district'] ?? 'Not provided'); ?>" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">State</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fas fa-map-marked-alt text-primary"></i></span>
                                <input type="text" class="form-control bg-white" value="<?php echo htmlspecialchars($donation['state'] ?? 'Not provided'); ?>" readonly>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Address</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fas fa-home text-primary"></i></span>
                                <textarea class="form-control bg-white" rows="2" readonly><?php echo htmlspecialchars($donation['address'] ?? 'Not provided'); ?></textarea>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Notes</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fas fa-sticky-note text-primary"></i></span>
                                <textarea class="form-control bg-white" rows="2" readonly><?php echo htmlspecialchars($donation['notes'] ?? 'No notes provided'); ?></textarea>
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
</style> 