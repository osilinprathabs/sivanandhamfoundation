<?php
session_start();
require_once '../db.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../index.php');
    exit();
}
 
// Check if admin table exists
$admin_table_success = false;
$admin_table_error = '';
$admin_success_image_html = '';

 
// Get admin details
$admin_id = $_SESSION['admin_id'];
$admin_name = $_SESSION['admin_name'];
$admin_email = $_SESSION['admin_email'];

// Set base path for assets
$base_path = '../';

// Handle Delete Operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_volunteer']) && isset($_POST['id'])) {
        $id = (int)$_POST['id'];
        $stmt = $conn->prepare("DELETE FROM volunteers WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Volunteer deleted successfully!";
        } else {
            $_SESSION['error_message'] = "Failed to delete volunteer.";
        }
        header("Location: dashboard.php?tab=volunteers");
        exit();
    }

    if (isset($_POST['delete_contact']) && isset($_POST['id'])) {
        $id = (int)$_POST['id'];
        $stmt = $conn->prepare("DELETE FROM contact WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Contact message deleted successfully!";
        } else {
            $_SESSION['error_message'] = "Failed to delete contact message.";
        }
        header("Location: dashboard.php?tab=contacts");
        exit();
    }

    if (isset($_POST['delete_donation']) && isset($_POST['id'])) {
        $id = (int)$_POST['id'];
        $stmt = $conn->prepare("DELETE FROM donations WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Donation record deleted successfully!";
        } else {
            $_SESSION['error_message'] = "Failed to delete donation record.";
        }
        header("Location: dashboard.php?tab=donations");
        exit();
    }
}

// Get current tab
$current_tab = isset($_GET['tab']) ? $_GET['tab'] : 'volunteers';
?>

<!DOCTYPE html>
<html lang="en">
 
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Shivanantham Foundation</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <style>
        /* Base styles */
        .sidebar {
            min-height: 100vh;
            background-color:rgb(88, 137, 98);
            padding-top: 20px;
            position: fixed;
            width: 250px;
            z-index: 1000;
        }
        .sidebar a {
            color: #fff;
            text-decoration: none;
            padding: 10px 15px;
            display: block;
            transition: all 0.3s;
        }
        .sidebar a:hover {
            background-color:rgb(122, 249, 64);
            padding-left: 20px;
        }
        .sidebar a.active {
            background-color:rgb(92, 239, 124);
        }
        .content {
            margin-left: 250px;
            padding: 20px;
        }

        /* Card Styles */
        .card {
            transition: transform 0.3s;
            border: none;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            background-color: #ffffff !important;
            overflow: hidden;
        }
        .card:hover {
            transform: translateY(-5px);
        }
        .stat-card {
            border-radius: 10px;
            border: none;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            background-color: #ffffff !important;
            position: relative;
            overflow: hidden;
        }
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(45deg, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0) 100%);
            z-index: 1;
        }
        .stat-card .card-body {
            padding: 1.5rem;
            position: relative;
            z-index: 2;
        }
        .stat-card .card-title {
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.5rem;
            color: rgba(255, 255, 255, 0.9);
        }
        .stat-card h2 {
            font-size: 2rem;
            font-weight: 600;
            margin-bottom: 0;
            color: #ffffff;
        }
        .stat-card .icon {
            position: absolute;
            right: 1.5rem;
            top: 1.5rem;
            font-size: 2.5rem;
            opacity: 0.8;
            color: rgba(255, 255, 255, 0.9);
            transition: all 0.3s ease;
        }
        .stat-card:hover .icon {
            transform: scale(1.1);
            opacity: 1;
        }

        /* Card Color Schemes */
        .stat-card.volunteers {
            background: linear-gradient(135deg, #4e73df 0%, #224abe 100%) !important;
        }
        .stat-card.donations {
            background: linear-gradient(135deg, #1cc88a 0%, #13855c 100%) !important;
        }
        .stat-card.contacts {
            background: linear-gradient(135deg, #36b9cc 0%, #258391 100%) !important;
        }
        .stat-card.amount {
            background: linear-gradient(135deg, #f6c23e 0%, #dda20a 100%) !important;
        }

        /* Welcome Section */
        .welcome-section {
            background: linear-gradient(135deg, #0d6efd 0%, #0dcaf0 100%);
            color: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }

        /* Table Styles */
        .table-container {
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            padding: 1rem;
            margin-top: 20px;
        }
        .table {
            width: 100%;
            margin-bottom: 0;
            background-color: #ffffff !important;
            border-collapse: separate;
            border-spacing: 0;
        }
        .table thead th {
            background: linear-gradient(135deg, #0d6efd 0%, #0dcaf0 100%) !important;
            color: white !important;
            font-weight: 600;
            padding: 15px;
            text-transform: uppercase;
            font-size: 0.9rem;
            letter-spacing: 0.5px;
            border: none;
            position: relative;
        }
        .table tbody tr {
            background-color: #ffffff !important;
            transition: background-color 0.3s ease;
        }
        .table tbody tr:hover {
            background-color: #f8f9fa !important;
        }
        .table tbody td {
            padding: 12px 15px;
            vertical-align: middle;
            border-color: #e9ecef;
            background-color: #ffffff !important;
        }
        .table-striped tbody tr:nth-of-type(odd) {
            background-color: #ffffff !important;
        }
        .table-striped tbody tr:nth-of-type(even) {
            background-color: #f8f9fa !important;
        }
        .table-striped tbody tr:nth-of-type(odd):hover,
        .table-striped tbody tr:nth-of-type(even):hover {
            background-color: #f8f9fa !important;
        }

        /* Modal Styles */
        .modal-content {
            background-color: #ffffff !important;
            border: none;
            border-radius: 8px;
            box-shadow: 0 0 20px rgba(0,0,0,0.2);
        }
        .modal-header {
            background: linear-gradient(135deg, #0d6efd 0%, #0dcaf0 100%);
            color: white;
            border-bottom: none;
            border-top-left-radius: 8px;
            border-top-right-radius: 8px;
            padding: 1rem 1.5rem;
        }
        .modal-body {
            padding: 1.5rem;
            background-color: #ffffff !important;
        }
        .modal-backdrop {
            background-color: rgba(0, 0, 0, 0.5);
        }

        /* Action Buttons */
        .action-buttons .btn {
            margin: 0 2px;
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
            border-radius: 4px;
            transition: all 0.3s ease;
        }
        .action-buttons .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }

        /* DataTables Customization */
        .dataTables_wrapper {
            padding: 1rem;
            background-color: #ffffff;
            border-radius: 8px;
        }
        .dataTables_filter input {
            border: 1px solid #ced4da;
            border-radius: 4px;
            padding: 0.375rem 0.75rem;
        }
        .dataTables_length select {
            border: 1px solid #ced4da;
            border-radius: 4px;
            padding: 0.375rem 0.75rem;
        }
        .dataTables_info {
            padding-top: 1rem;
        }
        .dataTables_paginate {
            padding-top: 1rem;
        }
        .paginate_button {
            border-radius: 4px !important;
            margin: 0 2px !important;
        }
        .paginate_button.current {
            background: linear-gradient(135deg, #0d6efd 0%, #0dcaf0 100%) !important;
            border: none !important;
            color: white !important;
        }

        /* Animations */
        .animated {
            animation-duration: 0.5s;
            animation-fill-mode: both;
        }
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .fadeIn {
            animation-name: fadeIn;
        }
        .fadeInDown {
            animation-name: fadeInDown;
        }

        .stat-card {
            transition: all 0.3s ease;
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        }
        .stat-card:active {
            transform: translateY(0);
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }

        .refresh-indicator-container {
            position: relative;
            z-index: 1000;
        }
        #refresh-indicator {
            animation: fadeInOut 1s ease-in-out;
        }
        @keyframes fadeInOut {
            0% { opacity: 0; }
            50% { opacity: 1; }
            100% { opacity: 0; }
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 sidebar">
                <div class="text-center mb-4">
                    <img src="../img/log.png" alt="Shivanantham Foundation" class="img-fluid mb-3" style="max-width: 100px;">
                    <h3 class="text-white">Admin Panel</h3>
                </div>
                <nav>
                    <a href="dashboard.php" class="green"><i class="fas fa-home me-2"></i> Dashboard</a>
                    <a href="dashboard.php?tab=volunteers" class="<?php echo $current_tab === 'volunteers' ? 'active' : ''; ?>">
                        <i class="fas fa-users me-2"></i> Volunteers
                    </a>
                    <a href="dashboard.php?tab=contacts" class="<?php echo $current_tab === 'contacts' ? 'active' : ''; ?>">
                        <i class="fas fa-envelope me-2"></i> Contacts
                    </a>
                    <a href="dashboard.php?tab=donations" class="<?php echo $current_tab === 'donations' ? 'active' : ''; ?>">
                        <i class="fas fa-hand-holding-usd me-2"></i> Donations
                    </a>
                    <a href="#" onclick="confirmLogout(event)" class="text-danger"><i class="fas fa-sign-out-alt me-2"></i> Logout</a>
                </nav>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 content">
                <?php echo $db_status_message; ?>
                <?php echo $admin_success_image_html; ?>
                <?php if ($admin_table_error) echo $admin_table_error; ?>
                <!-- Welcome Section -->
                <div class="welcome-section">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2>Welcome 🎉, <?php echo htmlspecialchars($admin_name); ?>!</h2>
                            <p class="mb-0">Here's what's happening with your website today.</p>
                        </div>
                        <div>
                            <span class="badge bg-light text-dark">
                                <i class="fas fa-clock me-1"></i>
                                <span id="current-time"></span>
                            </span>
                        </div>
                        
                    </div>
                </div>

            

                <!-- Display Success/Error Messages -->
                <?php if (isset($_SESSION['success_message'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php 
                        echo $_SESSION['success_message'];
                        unset($_SESSION['success_message']);
                        ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <?php if (isset($_SESSION['error_message'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php 
                        echo $_SESSION['error_message'];
                        unset($_SESSION['error_message']);
                        ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <!-- Statistics Cards -->
                <div class="row">
                    <div class="col-md-3 mb-4">
                        <div class="card stat-card volunteers" style="cursor: pointer;" onclick="window.location.href='dashboard.php?tab=volunteers'">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title">Total Volunteers</h6>
                                        <?php
                                        $result = $conn->query("SELECT COUNT(*) as count FROM volunteers");
                                        $count = $result->fetch_assoc()['count'];
                                        ?>
                                        <h2 class="mb-0"><?php echo $count; ?></h2>
                                    </div>
                                    <i class="fas fa-users icon"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-4">
                        <div class="card stat-card donations" style="cursor: pointer;" onclick="window.location.href='dashboard.php?tab=donations'">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title">Total Donations</h6>
                                        <?php
                                        $result = $conn->query("SELECT COUNT(*) as count FROM donations");
                                        $count = $result->fetch_assoc()['count'];
                                        ?>
                                        <h2 class="mb-0"><?php echo $count; ?></h2>
                                    </div>
                                    <i class="fas fa-hand-holding-usd icon"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-4">
                        <div class="card stat-card contacts" style="cursor: pointer;" onclick="window.location.href='dashboard.php?tab=contacts'">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title">Total Contacts</h6>
                                        <?php
                                        $result = $conn->query("SELECT COUNT(*) as count FROM contact");
                                        $count = $result->fetch_assoc()['count'];
                                        ?>
                                        <h2 class="mb-0"><?php echo $count; ?></h2>
                                    </div>
                                    <i class="fas fa-envelope icon"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-4">
                        <div class="card stat-card amount" style="cursor: pointer;" onclick="window.location.href='dashboard.php?tab=donations'">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title">Total Amount</h6>
                                        <?php
                                        $result = $conn->query("SELECT SUM(donation_amount) as total FROM donations");
                                        $total = $result->fetch_assoc()['total'] ?? 0;
                                        ?>
                                        <h2 class="mb-0">₹<?php echo number_format($total, 2); ?></h2>
                                    </div>
                                    <i class="fas fa-rupee-sign icon"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Data Tables -->
                <div class="card">
                    <div class="card-body">
                        <?php if ($current_tab === 'volunteers'): ?>
                            <h4 class="card-title mb-4">Volunteers List</h4>
                            <div class="table-responsive">
                                <table class="table table-striped" id="volunteersTable">
                                    <thead>
                                        <tr>
                                        <th> ID</th>
                                            <th>Volunteer ID</th>
                                            <th>Full Name</th>
                                            <th>Father's Name</th>
                                            <th>Mother's Name</th>
                                            <th>Email</th>
                                            <th>Phone</th>
                                            <th>Address</th>
                                            <th>D-O-B</th>
                                            <th>Gender</th>
                                            <th>Skills</th>
                                            <th>Role</th>
                                            <th>Joined Date</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $result = $conn->query("SELECT id, volunteer_id, full_name, father_name, mother_name, email, phone_number, address, date_of_birth, gender, skills, role, joined_date, created_at FROM volunteers ORDER BY created_at DESC");
                                        while ($row = $result->fetch_assoc()):
                                        ?>
                                        <tr>
                                        <td><?php echo htmlspecialchars($row['id']); ?></td>
                                            <td><?php echo htmlspecialchars($row['volunteer_id'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($row['full_name'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($row['father_name'] ?? 'N/A'); ?></td>
                                            <td><?php echo htmlspecialchars($row['mother_name'] ?? 'N/A'); ?></td>
                                            <td><?php echo htmlspecialchars($row['email'] ?? 'N/A'); ?></td>
                                            <td><?php echo htmlspecialchars($row['phone_number'] ?? 'N/A'); ?></td>
                                            <td><?php echo htmlspecialchars($row['address'] ?? 'N/A'); ?></td>
                                            <td><?php echo htmlspecialchars($row['date_of_birth'] ?? 'N/A'); ?></td>
                                            <td><?php echo htmlspecialchars($row['gender'] ?? 'N/A'); ?></td>
                                            <td><?php echo htmlspecialchars($row['skills'] ?? 'N/A'); ?></td>
                                            <td><?php echo htmlspecialchars($row['role'] ?? 'N/A'); ?></td>
                                            <td><?php echo date('d M Y', strtotime($row['joined_date'] ?? $row['created_at'])); ?></td>
                                            <td class="action-buttons">
                                                <button class="btn btn-sm btn-info" onclick="viewVolunteer(<?php echo $row['id']; ?>)">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button class="btn btn-sm btn-primary" onclick="editVolunteer(<?php echo $row['id']; ?>)">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-sm btn-danger" onclick="confirmDelete('volunteer', <?php echo $row['id']; ?>, '<?php echo htmlspecialchars($row['full_name'] ?? ''); ?>')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>

                        <?php elseif ($current_tab === 'contacts'): ?>
                            <h4 class="card-title mb-4">Contact Messages</h4>
                            <div class="table-responsive">
                                <table class="table table-striped" id="contactsTable">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Phone</th>
                                            <th>Subject</th>
                                            <th>Message</th>
                                            <th>Date</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $result = $conn->query("SELECT id, name, email, phone_number, subject, message, created_at FROM contact ORDER BY created_at DESC");
                                        while ($row = $result->fetch_assoc()):
                                        ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($row['id']); ?></td>
                                            <td><?php echo htmlspecialchars($row['name'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($row['email'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($row['phone_number'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($row['subject'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($row['message'] ?? ''); ?></td>
                                            <td><?php echo date('d M Y H:i', strtotime($row['created_at'])); ?></td>
                                            <td class="action-buttons">
                                                <button class="btn btn-sm btn-info" onclick="viewContact(<?php echo $row['id']; ?>)">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button class="btn btn-sm btn-primary" onclick="editContact(<?php echo $row['id']; ?>)">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-sm btn-danger" onclick="confirmDelete('contact', <?php echo $row['id']; ?>, '<?php echo htmlspecialchars($row['name']); ?>')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>

                        <?php elseif ($current_tab === 'donations'): ?>
                            <h4 class="card-title mb-4">Donations List</h4>
                            <div class="table-responsive">
                                <table class="table table-striped" id="donationsTable">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Donor Name</th>
                                            <th>Email</th>
                                            <th>Payment Method</th>
                                            <th>Date</th>
                                            <th>Amount</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $result = $conn->query("SELECT id, donor_name, donor_email, donation_amount, payment_method, created_at FROM donations ORDER BY created_at DESC");
                                        while ($row = $result->fetch_assoc()):
                                        ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($row['id']); ?></td>
                                            <td><?php echo htmlspecialchars($row['donor_name']); ?></td>
                                            <td><?php echo htmlspecialchars($row['donor_email']); ?></td>
                                            <td><?php echo htmlspecialchars($row['payment_method']); ?></td>
                                            <td><?php echo date('d M Y H:i', strtotime($row['created_at'])); ?></td>
                                            <td>₹<?php echo htmlspecialchars($row['donation_amount']); ?></td>
                                            <td class="action-buttons">
                                                <button class="btn btn-sm btn-info" onclick="viewDonation(<?php echo $row['id']; ?>)">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button class="btn btn-sm btn-primary" onclick="editDonation(<?php echo $row['id']; ?>)">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-sm btn-danger" onclick="confirmDelete('donation', <?php echo $row['id']; ?>, '<?php echo htmlspecialchars($row['donor_name']); ?>')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- View Modal -->
    <div class="modal fade" id="viewModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">View Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="viewModalBody">
                    <!-- Content will be loaded dynamically -->
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="editModalBody">
                    <!-- Content will be loaded dynamically -->
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function() {
            // Function to update time
            function updateTime() {
                const now = new Date();
                const options = { 
                    weekday: 'long',
                    year: 'numeric', 
                    month: 'long', 
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit',
                    hour12: true
                };
                document.getElementById('current-time').textContent = now.toLocaleString('en-US', options);
            }

            // Update time immediately and then every second
            updateTime();
            setInterval(updateTime, 1000);

            // Initialize DataTables
            $('#volunteersTable, #contactsTable, #donationsTable').DataTable({
                "order": [[0, "desc"]],
                "pageLength": 10,
                "language": {
                    "search": "Search:",
                    "lengthMenu": "Show _MENU_ entries per page",
                    "info": "Showing _START_ to _END_ of _TOTAL_ entries"
                }
            });

     

            // Add click animation to cards
            $('.stat-card').on('click', function() {
                $(this).addClass('clicked');
                setTimeout(() => {
                    $(this).removeClass('clicked');
                }, 200);
            });
        });

        // View functions
        function viewVolunteer(id) {
            $.get('ajax/view_volunteer.php', {id: id}, function(data) {
                $('#viewModalBody').html(data);
                $('#viewModal').modal('show');
            });
        }

        function viewContact(id) {
            $.get('ajax/view_contact.php', {id: id}, function(data) {
                $('#viewModalBody').html(data);
                $('#viewModal').modal('show');
            });
        }

        function viewDonation(id) {
            $.get('ajax/view_donation.php', {id: id}, function(data) {
                $('#viewModalBody').html(data);
                $('#viewModal').modal('show');
            });
        }

        // Edit functions
        function editVolunteer(id) {
            $.get('ajax/edit_volunteer.php', {id: id}, function(data) {
                $('#editModalBody').html(data);
                $('#editModal').modal('show');
            });
        }

        function editDonation(id) {
            $.get('ajax/edit_donation.php', {id: id}, function(data) {
                $('#editModalBody').html(data);
                $('#editModal').modal('show');
            });
        }

        function editContact(id) {
            $.get('ajax/edit_contact.php', {id: id}, function(data) {
                $('#editModalBody').html(data);
                $('#editModal').modal('show');
            });
        }

        function confirmDelete(type, id, name) {
            Swal.fire({
                title: 'Are you sure?',
                html: `Do you want to delete this ${type}?<br><strong>${name}</strong>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel',
                customClass: {
                    popup: 'animated fadeInDown'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    // Create a form and submit it
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = 'dashboard.php';
                    
                    const idInput = document.createElement('input');
                    idInput.type = 'hidden';
                    idInput.name = 'id';
                    idInput.value = id;
                    
                    const typeInput = document.createElement('input');
                    typeInput.type = 'hidden';
                    typeInput.name = `delete_${type}`;
                    typeInput.value = '1';
                    
                    form.appendChild(idInput);
                    form.appendChild(typeInput);
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        }

        // Logout confirmation function
        function confirmLogout(event) {
            event.preventDefault();
            Swal.fire({
                title: 'Are you sure you want to logout, <?php echo htmlspecialchars($admin_name); ?>?',
                html: "You will need to login again to access the dashboard.<br><br><strong>Current User:</strong> <?php echo htmlspecialchars($admin_name); ?><br><strong>Email:</strong> <?php echo htmlspecialchars($admin_email); ?>",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, logout',
                cancelButtonText: 'Cancel',
                customClass: {
                    popup: 'animated fadeInDown',
                    title: 'swal2-title',
                    htmlContainer: 'swal2-html-container'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'logout.php';
                }
            });
        }

        // Add animation classes
        document.addEventListener('DOMContentLoaded', function() {
            const tables = document.querySelectorAll('.table');
            tables.forEach(table => {
                table.classList.add('animated', 'fadeIn');
            });
        });

        function reloadTable() {
            // Get the current active tab
            const activeTab = document.querySelector('.nav-link.active').getAttribute('data-tab');
            
            // Show loading state
            const tableContainer = document.querySelector('.table-responsive');
            if (tableContainer) {
                tableContainer.innerHTML = '<div class="text-center p-5"><div class="spinner-border text-primary" role="status"></div><p class="mt-2">Loading data...</p></div>';
            }

            // Fetch fresh data based on the active tab
            fetch(`ajax/get_${activeTab}.php`)
                .then(response => response.text())
                .then(data => {
                    if (tableContainer) {
                        tableContainer.innerHTML = data;
                        // Reinitialize DataTable
                        if ($.fn.DataTable.isDataTable('.table')) {
                            $('.table').DataTable().destroy();
                        }
                        $('.table').DataTable({
                            responsive: true,
                            language: {
                                search: "_INPUT_",
                                searchPlaceholder: "Search records..."
                            }
                        });
                    }
                })
                .catch(error => {
                    console.error('Error reloading table:', error);
                    if (tableContainer) {
                        tableContainer.innerHTML = '<div class="alert alert-danger">Error loading data. Please try again.</div>';
                    }
                });
        }

        // Add event listeners for view and edit buttons to reload table after operations
        document.addEventListener('click', function(e) {
            if (e.target.matches('.view-btn, .edit-btn')) {
                // After the modal is hidden, reload the table
                e.target.closest('.modal').addEventListener('hidden.bs.modal', function() {
                    reloadTable();
                }, { once: true });
            }
        });
    </script>
</body>
</html> 