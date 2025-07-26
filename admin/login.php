<?php
session_start();
if (isset($_SESSION['admin_id'])) {
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Login</title>
    <link rel="stylesheet" href="../css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-4">
            <h3 class="mb-4 text-center">Admin Login</h3>
            <form id="adminLoginForm" method="POST">
                <div class="mb-3">
                    <input type="text" name="username" class="form-control" placeholder="Email or Name" required>
                </div>
                <div class="mb-3">
                    <input type="password" name="password" class="form-control" placeholder="Password" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Login</button>
                <div id="loginError" class="alert alert-danger mt-3 d-none"></div>
            </form>
        </div>
    </div>
</div>
<script>
document.getElementById('adminLoginForm').onsubmit = function(e) {
    e.preventDefault();
    var form = this;
    var data = new FormData(form);
    fetch('verify_login.php', {
        method: 'POST',
        body: data
    })
    .then(res => res.json())
    .then(resp => {
        if (resp.success) {
            window.location.href = resp.redirect;
        } else {
            document.getElementById('loginError').classList.remove('d-none');
            document.getElementById('loginError').textContent = resp.message;
        }
    });
};
</script>
</body>
</html> 