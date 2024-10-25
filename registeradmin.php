<?php
session_start();
require 'db.php';

$admin_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = :admin_id");
$stmt->execute(['admin_id' => $admin_id]);
$admin = $stmt->fetch();


$notification = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $name = preg_replace('/[()\[\]{}?<>]/', '', trim($_POST['full_name']));
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $notification = "
            Swal.fire({
                icon: 'error',
                title: 'Invalid Email!',
                text: 'Please enter a valid email address.',
                position: 'center'
            });
        ";
    } else {
        
        $role = 'admin';

        $password = htmlspecialchars(trim($_POST['password']), ENT_QUOTES, 'UTF-8');
        $confirm_password = htmlspecialchars(trim($_POST['confirm_password']), ENT_QUOTES, 'UTF-8');
        $phone = preg_replace('/[^0-9\-\+\(\)\s]/', '', trim($_POST['phone_number']));

        if ($password !== $confirm_password) {
            $notification = "
                Swal.fire({
                    icon: 'error',
                    title: 'Password Mismatch!',
                    text: 'Passwords do not match!',
                    position: 'center'
                });
            ";
        } else {
            $stmt = $pdo->prepare("SELECT email FROM users WHERE email = :email");
            $stmt->execute(['email' => $email]);

            $stmt_phone = $pdo->prepare("SELECT phone_number FROM users WHERE phone_number = :phone_number");
            $stmt_phone->execute(['phone_number' => $phone]);

            if ($stmt->rowCount() > 0) {
                $notification = "
                    Swal.fire({
                        icon: 'warning',
                        title: 'Email Already Registered!',
                        text: 'Please login instead.',
                        confirmButtonText: 'Login',
                        position: 'center'
                    }).then(function() {
                        window.location.href = 'view_user.php';
                    });
                ";
            } elseif ($stmt_phone->rowCount() > 0) {
                $notification = "
                    Swal.fire({
                        icon: 'error',
                        title: 'Phone Number Already Used!',
                        text: 'Please use a different phone number.',
                        position: 'center'
                    });
                ";
            } else {

                $hashed_password = password_hash($password, PASSWORD_BCRYPT);
                $user_id = strtolower(explode(' ', $name)[0]) . rand(100000, 999999);

                $stmt = $pdo->prepare("INSERT INTO users (id, full_name, email, password, phone_number, role) 
                                       VALUES (:id, :full_name, :email, :password, :phone_number, :role)");
                $stmt->execute([
                    'id' => $user_id,
                    'full_name' => $name,
                    'email' => $email, 
                    'password' => $hashed_password,
                    'phone_number' => $phone,
                    'role' => $role
                ]);

                $notification = "
                    Swal.fire({
                        icon: 'success',
                        title: 'Registration Successful!',
                        text: 'Back to dashboard?.',
                        confirmButtonText: 'Continue',
                        position: 'center'
                    }).then(function() {
                        window.location.href = 'admin_dashboard.php';
                    });
                ";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Admin</title>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

</head>
<body>
<div class="header">
        <h2>Event Management</h2>
        <div class="user-profile">
            <div class="me-3">
                <a href="view_user.php"><button class="btn-view">User</button></a>
            </div>
            <a href="view_profile.php">
                <img src="uploads/<?php echo $admin['profile_picture']; ?>" alt="Profile Picture" width="50" height="50">    
                <?php echo $admin['full_name']; ?>
            </a>
        </div>
    </div>
    <div class="container col-5 d-flex justify-content-center">
        <div class="card mt-5">
            <div class="card-header">
                <h2>Input new admin</h2>
            </div>
            <div class="card-body">
    <form method="POST" action="" class="needs-validation" novalidate>
        <div class="mb-3">
            <label for="full_name" class="form-label">Nama Lengkap</label>
            <input type="text" name="full_name" id="full_name" class="form-control" placeholder="Nama Lengkap" required>
            <div class="invalid-feedback">
                Please enter your full name.
            </div>
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" name="password" id="password" class="form-control" placeholder="Password" required>
            <div class="invalid-feedback">
                Please provide a password.
            </div>
        </div>

        <div class="mb-3">
            <label for="confirm_password" class="form-label">Confirm Password</label>
            <input type="password" name="confirm_password" id="confirm_password" class="form-control" placeholder="Confirm Password" required>
            <div class="invalid-feedback">
                Please confirm your password.
            </div>
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" name="email" id="email" class="form-control" placeholder="Email" required>
            <div class="invalid-feedback">
                Please enter a valid email address.
            </div>
        </div>

        <div class="mb-3 phone-wrapper">
            <label for="phone_number" class="form-label">Phone Number</label>
            <input type="text" name="phone_number" id="phone_number" class="form-control" placeholder="Phone Number" required>
            <div class="invalid-feedback">
                Please enter your phone number.
            </div>
        </div>

        <button type="submit" class="btn btn-primary">Register</button>
    </form>
    <div class="form-link mt-3">
        <a href="view_user.php" class="btn btn-secondary">Back to dashboard</a>
    </div>
</div>

        </div>
    </div>
    <script>
        <?php if (!empty($notification)) echo $notification; ?>
    </script>
</body>
</html>
