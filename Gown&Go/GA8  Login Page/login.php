<?php
session_start();
include 'config.php';

$login_error = "";
$login_success = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    $query = mysqli_query($conn, "SELECT * FROM users WHERE email='$email' LIMIT 1");

    if (mysqli_num_rows($query) == 1) {
        $row = mysqli_fetch_assoc($query);

        if (password_verify($password, $row['password'])) {

            $_SESSION['user_id']  = $row['user_id'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['role']     = $row['role'];

            $login_success = "Login successful! Welcome, " . $row['username'] . ".";
        } else {
            $login_error = "Incorrect password.";
        }
    } else {
        $login_error = "Email not found.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login - Gown&Go</title>
  
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
  <link href="styles/login.css" rel="stylesheet"> 
  
  </head>

<body class="bg-light">

  <main class="container">
    <section class="left-panel">
      <h1>Elegant Gowns<br>for Everyone.</h1>
    </section>

    <section class="right-panel"> 
      <h2>Login to Your Account</h2>
      <p class="subtitle">Enter your email and password to access your account.</p>

      <form action="" method="POST">    
        <div class="mb-3">
          <label class="form-label">Email</label>
          <input type="email" name="email" class="form-control" required>
        </div>

        <div class="mb-3">
          <label class="form-label">Password</label>
          <input type="password" name="password" class="form-control" required>
        </div>
        <button class="btn submit-btn" type="submit">Login</button>
      </form>

      <hr>
      <p class="text-center">
          New user? <a href="register.php">Create an account</a>.
      </p>

    <div class="modal fade" id="errorModal" tabindex="-1">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header bg-danger text-white">
            <h5 class="modal-title">Login Error</h5>
            <button class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <?php echo $login_error; ?>
          </div>
          <div class="modal-footer">
            <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          </div>
        </div>
      </div>
    </div>

    <div class="modal fade" id="successModal" tabindex="-1">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header bg-success text-white">
            <h5 class="modal-title">Login Successful</h5>
            <button class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <?php echo $login_success; ?>
            <br><small>You will be redirected shortly...</small>
          </div>
          <div class="modal-footer">
            <button class="btn btn-success" data-bs-dismiss="modal">OK</button>
          </div>
        </div>
      </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js
"></script>

    <?php
    if (!empty($login_error)) {
        echo "<script>
                var e = new bootstrap.Modal(document.getElementById('errorModal'));
                e.show();
              </script>";
    }
    if (!empty($login_success)) {
        echo "<script>
                var s = new bootstrap.Modal(document.getElementById('successModal'));
                s.show();
                setTimeout(function() {
                    window.location.href = '" . 
                    ($_SESSION['role'] == "admin" ? "admin/dashboard.php" : "client_home.php") . 
                    "';
                }, 1500);
              </script>";
    }
    ?>
</body>
</html>
