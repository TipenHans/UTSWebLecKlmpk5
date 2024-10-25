<?php
session_start();
require 'db.php';

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
        
        $role = 'user';

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
                        window.location.href = 'login.php';
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
                        text: 'Please login to continue.',
                        confirmButtonText: 'Login',
                        position: 'center'
                    }).then(function() {
                        window.location.href = 'login.php';
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
    <title>Register</title>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

</head>
<body>
    <div class="container">
        <div class="container d-flex justify-content-center align-items-center vh-100">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-body">
                    <h2>Register</h2>
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="full_name" class="form-label">Nama Lengkap</label>
                                <input type="text" name="full_name" class="form-control" id="full_name" placeholder="Nama Lengkap" required>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" id="email" placeholder="Email" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" name="password" class="form-control" id="password" placeholder="Password" required>
                            </div>
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Confirm Password</label>
                                <input type="password" name="confirm_password" class="form-control" id="confirm_password" placeholder="Confirm Password" required>
                            </div>
                            <div class="mb-3">
                                <label for="phone_number" class="form-label">Phone Number</label>
                                <input type="text" name="phone_number" class="form-control" id="phone_number" placeholder="Phone Number" required>
                            </div>
                            <div class="d-grid gap-2 m-3">
                                <button type="submit" class="btn btn-success">Register</button>
                            </div>
                        </form>
                        <a href="login.php" class="text-decoration-none">Already have an account? Login here</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        <?php if (!empty($notification)) echo $notification; ?>
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>
