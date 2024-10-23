
<?php
session_start();
require 'db.php';

$notification = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $notification = "
            Swal.fire({
                icon: 'error',
                title: 'Invalid Email Format',
                text: 'Please enter a valid email address!',
                position: 'center'
            });
        ";
    } else {

        $password = htmlspecialchars(trim($_POST['password']));

        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        if ($user) {
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['role'] = $user['role'];

                $dashboard_url = ($user['role'] === 'admin') ? 'admin_dashboard.php' : 'user_dashboard.php';
                $dashboard_url = htmlspecialchars($dashboard_url, ENT_QUOTES, 'UTF-8');

                $notification = "
                    Swal.fire({
                        icon: 'success',
                        title: 'Login Successful!',
                        text: 'Redirecting...',
                        showConfirmButton: false,
                        timer: 1500,
                        position: 'center'
                    }).then(function() {
                        window.location.href = '$dashboard_url';
                    });
                ";
            } else {
                $notification = "
                    Swal.fire({
                        icon: 'error',
                        title: 'Incorrect Email or Password',
                        text: 'Please try again!',
                        position: 'center'
                    });
                ";
            }
        } else {
            $notification = "
                Swal.fire({
                    icon: 'warning',
                    title: 'Account Not Registered',
                    text: 'Please register to continue',
                    confirmButtonText: 'OK',
                    position: 'center'
                }).then(function() {
                    window.location.href = 'register.php';
                });
            ";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

</head>
<body>
    <div class="container d-flex justify-content-center align-items-center vh-100">
        <div class="card shadow-lg p-4" style="width: 400px;">
            <h2 class="text-center mb-4">Login</h2>
            <form method="POST" action="">
                <div class="form-group mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email" required>
                </div>
                <div class="form-group mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Login</button>
            </form>
            <div class="text-center mt-3">
                <a href="register.php" class="text-decoration-none">Don't have an account? Register</a>
            </div>
        </div>
    </div>
    <script>
        <?php if (!empty($notification)) echo $notification; ?>
    </script>
</body>
</html>
