<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Sivanandham Foundation</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="" name="keywords">
    <meta content="" name="description">

    <!-- Favicon -->
    <link href="img/favicon.ico" rel="icon">

    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Saira:wght@500;600;700&display=swap" rel="stylesheet"> 

    <!-- Icon Font Stylesheet -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Libraries Stylesheet -->
    <link href="lib/animate/animate.min.css" rel="stylesheet">
    <link href="lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">

    <!-- Customized Bootstrap Stylesheet -->
    <link href="css/bootstrap.min.css" rel="stylesheet">

    <!-- Template Stylesheet -->
    <link href="css/style.css" rel="stylesheet">
</head>

<body>
    <!-- Spinner Start -->
    <div id="spinner" class="show bg-white position-fixed translate-middle w-100 vh-100 top-50 start-50 d-flex align-items-center justify-content-center">
        <div class="spinner-grow text-primary" role="status"></div>
    </div>
    <!-- Spinner End -->

    <!-- Navbar Start -->
    <div class="container-fluid fixed-top px-0 wow fadeIn" data-wow-delay="0.1s">
        <div class="top-bar text-white-50 row gx-0 align-items-center d-none d-lg-flex">
            <div class="col-lg-6 px-5 text-start">
                <small><i class="fa fa-map-marker-alt me-2"></i>123 Street,tower, Singapore</small>
                <small class="ms-4"><i class="fa fa-envelope me-2"></i>info@sivanandhamfoundation.com</small>
            </div>
            <div class="col-lg-6 px-5 text-end">
                <small>Follow us:</small>
                <a class="text-white-50 ms-3" href=""><i class="fab fa-facebook-f"></i></a>
                <a class="text-white-50 ms-3" href=""><i class="fab fa-twitter"></i></a>
                <a class="text-white-50 ms-3" href=""><i class="fab fa-linkedin-in"></i></a>
                <a class="text-white-50 ms-3" href=""><i class="fab fa-instagram"></i></a>
            </div>
        </div>

        <nav class="navbar navbar-expand-lg navbar-dark py-lg-0 px-lg-5 wow fadeIn" data-wow-delay="0.1s">
            <a href="index.php" class="navbar-brand ms-4 ms-lg-0">
                <h1 class="fw-bold text-primary m-0">Sivanandham<span class="text-white">Foundation</span></h1>
            </a>
            <button type="button" class="navbar-toggler me-4" data-bs-toggle="collapse" data-bs-target="#navbarCollapse">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarCollapse">
                <div class="navbar-nav ms-auto p-4 p-lg-0">
                    <a href="index.php" class="nav-item nav-link active">Home</a>
                    <a href="about.php" class="nav-item nav-link">About</a>
                    <a href="causes.php" class="nav-item nav-link">Causes</a>
                    <div class="nav-item dropdown">
                        <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">Pages</a>
                        <div class="dropdown-menu m-0">
                            <a href="service.php" class="dropdown-item">Service</a>
                            <a href="volunteer.php" class="dropdown-item" id="navVolunteer">Volunteer</a>
                            <a href="donate.php" class="dropdown-item" id="navDonate">Donate</a>
                            <a href="team.php" class="dropdown-item">Our Team</a>
                            <a href="testimonial.php" class="dropdown-item">Testimonial</a>
                            <a href="404.php" class="dropdown-item">404 Page</a>
                        </div>
                    </div>
                    <li class="nav-item">
                        <?php if (isset($_SESSION['admin_id'])): ?>
                            <a href="admin/dashboard.php" class="nav-link" title="Admin Dashboard">
                                <i class="fas fa-user-shield"></i>
                            </a>
                        <?php else: ?>
                            <a href="#" class="nav-link" data-bs-toggle="modal" data-bs-target="#adminLoginModal" title="Admin Login">
                                <i class="fas fa-user-shield"></i>
                            </a>
                        <?php endif; ?>
                    </li>
                </div>
                <div class="d-none d-lg-flex ms-2">
                    <a class="btn btn-outline-primary py-2 px-3" href="donate.php">
                        Donate Now
                        <div class="d-inline-flex btn-sm-square bg-white text-primary rounded-circle ms-2">
                            <i class="fa fa-arrow-right"></i>
                        </div>
                    </a>
                </div>
            </div>
        </nav>
    </div>
    <!-- Navbar End --> 

<!-- Admin Login Modal -->
<div class="modal fade" id="adminLoginModal" tabindex="-1" aria-labelledby="adminLoginModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="adminLoginModalLabel"><i class="fas fa-user-shield me-2"></i>Admin Login</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="adminLoginForm" method="post" action="admin/verify_login.php">
          <div class="mb-3">
            <label for="admin-username" class="form-label">Username or Email</label>
            <input type="text" class="form-control" id="admin-username" name="username" required>
          </div>
          <div class="mb-3">
            <label for="admin-password" class="form-label">Password</label>
            <input type="password" class="form-control" id="admin-password" name="password" required>
          </div>
          <div id="adminLoginError" class="alert alert-danger d-none"></div>
          <div class="d-grid">
            <button type="submit" class="btn btn-primary">Login</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<!-- End Admin Login Modal --> 

<!-- AJAX scripts for forms will be added here --> 
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Admin Login Form
    const adminLoginForm = document.getElementById('adminLoginForm');
    if (adminLoginForm) {
        adminLoginForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(adminLoginForm);
            fetch('admin/verify_login.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    window.location.href = data.redirect;
                } else {
                    document.getElementById('admin-password').value = '';
                    document.getElementById('adminLoginError').classList.remove('d-none');
                    document.getElementById('adminLoginError').textContent = data.message;
                }
            })
            .catch(() => {
                document.getElementById('adminLoginError').classList.remove('d-none');
                document.getElementById('adminLoginError').textContent = 'An error occurred. Please try again.';
            });
        });
    }

    // Volunteer and Donate link validation (basic check)
    document.getElementById('navVolunteer').addEventListener('click', function(e) {
        fetch('volunteer.php', { method: 'HEAD' })
            .then(res => {
                if (!res.ok) {
                    e.preventDefault();
                    alert('Volunteer page not found.');
                }
            })
            .catch(() => {
                e.preventDefault();
                alert('Volunteer page not found.');
            });
    });
    document.getElementById('navDonate').addEventListener('click', function(e) {
        fetch('donate.php', { method: 'HEAD' })
            .then(res => {
                if (!res.ok) {
                    e.preventDefault();
                    alert('Donate page not found.');
                }
            })
            .catch(() => {
                e.preventDefault();
                alert('Donate page not found.');
            });
    });
});
</script> 