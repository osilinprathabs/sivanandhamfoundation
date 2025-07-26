<?php
session_start();
require_once '../../database.php';

if (!isset($_SESSION['admin_id'])) {
    die('Unauthorized access');
}

if (!isset($_GET['id'])) {
    die('No contact ID provided');
}

$id = (int)$_GET['id'];
$stmt = $conn->prepare("SELECT * FROM contact WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$contact = $result->fetch_assoc();

if (!$contact) {
    die('Contact not found');
}
?>

<div class="container-fluid p-4">
    <!-- View Header -->
    <div class="form-header mb-4">
        <h4 class="text-primary mb-2"><i class="fas fa-envelope me-2"></i>Contact Details</h4>
        <p class="text-muted">View the complete contact information below.</p>
    </div>

    <div class="row g-4">
        <!-- Contact Information Section -->
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-user-circle me-2"></i>Contact Information</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Name</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fas fa-user text-primary"></i></span>
                                <input type="text" class="form-control bg-white" value="<?php echo htmlspecialchars($contact['name']); ?>" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Email</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fas fa-envelope text-primary"></i></span>
                                <input type="email" class="form-control bg-white" value="<?php echo htmlspecialchars($contact['email']); ?>" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Phone Number</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fas fa-phone text-primary"></i></span>
                                <input type="text" class="form-control bg-white" value="<?php echo htmlspecialchars($contact['phone_number']); ?>" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Subject</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fas fa-tag text-primary"></i></span>
                                <input type="text" class="form-control bg-white" value="<?php echo htmlspecialchars($contact['subject'] ?? 'Not provided'); ?>" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Date Received</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fas fa-calendar text-primary"></i></span>
                                <input type="text" class="form-control bg-white" value="<?php echo date('F j, Y, g:i a', strtotime($contact['created_at'])); ?>" readonly>
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
                    <h5 class="mb-0"><i class="fas fa-comment-alt me-2"></i>Message</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fas fa-envelope-open-text text-primary"></i></span>
                                <textarea class="form-control bg-white" rows="5" readonly><?php echo htmlspecialchars($contact['message']); ?></textarea>
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