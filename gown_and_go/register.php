<?php
session_start();
include 'config.php';

$register_error = "";
$register_success = "";

// If user submits the form
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $username = trim($_POST['username']);
    $email    = trim($_POST['email']);
    $password = trim($_POST['password']);
    $address  = trim($_POST['address']);
    $contact  = trim($_POST['contact']);
    $role     = "customer";

    // 1. Check if email already exists
    $check = mysqli_query($conn, "SELECT * FROM users WHERE email='$email' LIMIT 1");

    if (mysqli_num_rows($check) > 0) {
        $register_error = "This email is already registered.";
    } else {

        // 2. Hash the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // 3. Insert user
        $sql = "INSERT INTO users (username, email, password, address, contact_no, role)
                VALUES ('$username', '$email', '$hashed_password', '$address', '$contact', '$role')";

        if (mysqli_query($conn, $sql)) {
            // Registration successful
            $register_success = "Account created successfully!";
        } else {
            $register_error = "Database error: " . mysqli_error($conn);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Gown&Go</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <link href="styles/register.css" rel="stylesheet"> 
</head>
    
<body class="bg-light">

    <main class="container">
        <div class="left-panel">
            <h1>Welcome to Gown&Go</h1>
            <p>Your journey to elegance begins here. Create an account to explore our exquisite collection of gowns and accessories.</p>
        </div>
        <div class="right-panel">
            <h2>Create Account</h2>
            <?php if (!empty($register_error)): ?>
                <div class="error-message"><?php echo $register_error; ?></div>
            <?php endif; ?>
            <?php if (!empty($register_success)): ?>
                <div class="success-message"><?php echo $register_success; ?></div>
            <?php endif; ?>
            <form action="" method="POST">
                <input type="text" name="username" placeholder="Username" required>
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Password" required>
                <textarea name="address" rows="2" placeholder="Address" required></textarea>
                <input type="text" name="contact" placeholder="Contact Number" required>
                <button class="submit-btn" type="submit">Register</button>
            </form>

            <div class="login-link">Already have an account? <a href="login.php">Login Here.</a></div>
        </div>
    </main>
           

    <!-- ERROR MODAL -->
    <div class="modal fade" id="errorModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Registration Error</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <?php echo $register_error; ?>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- SUCCESS MODAL -->
    <div class="modal fade" id="successModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">Registration Successful</h5>
                    <button class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <?php echo $register_success; ?><br>
                    You can now <a href="login.php">login</a>.
                </div>
                <div class="modal-footer">
                    <a href="login.php" class="btn btn-success">Go to Login</a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js
"></script>

    <!-- Show modal depending on error/success -->
    <?php
        if (!empty($register_error)) {
        echo "<script>
            var e = new bootstrap.Modal(document.getElementById('errorModal'));
            e.show();
            </script>";
        }

        if (!empty($register_success)) {
        echo "<script>
            var s = new bootstrap.Modal(document.getElementById('successModal'));
            s.show();
            </script>";
        }
    ?>

</body>
</html>
