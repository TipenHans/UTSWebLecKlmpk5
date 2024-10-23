<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = :user_id");
$stmt->execute(['user_id' => $user_id]);
$user = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>User Profile</title>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@10/dist/sweetalert2.min.css">
        <link rel="stylesheet" href="style.css">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    </head>
    <body>
    <div class="header d-flex justify-content-between align-items-center mb-4">
        <h2>Event Management</h2>
    </div>    
    <div class="container">
        <div class="d-flex justify-content-center">
            <div class="card">
                <div class="card-header">
                    <h1>Profile</h1>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-center mb-3">
                        <img src="uploads/<?php echo $user['profile_picture']; ?>" alt="Profile Picture" class="profile-img w-25">
                    </div>
                    <table class="table table-bordered">
                        <tr>
                            <th>User ID:</th>
                            <td><?php echo $user['id']; ?></td>
                        </tr>
                        <tr>
                            <th>Full Name:</th>
                            <td><?php echo $user['full_name']; ?></td>
                        </tr>
                        <tr>
                            <th>Email:</th>
                            <td><?php echo $user['email']; ?></td>
                        </tr>
                        <tr>
                            <th>Phone Number:</th>
                            <td><?php echo $user['phone_number']; ?></td>
                        </tr>
                        <tr>
                            <th>Role:</th>
                            <td><?php echo ucfirst($user['role']); ?></td>
                        </tr>
                    </table>
                    <div class="d-flex justify-content-start">
                        <a href="edit_profile.php" class="btn btn-info me-2">Edit Profile</a>
                        <a href="<?php echo ($role === 'admin') ? 'admin_dashboard.php' : 'user_dashboard.php'; ?>" class="btn btn-primary me-2">Back to Dashboard</a>
                        <a href="logout.php" class="btn btn-secondary me-2" id="logoutBtn">Logout</a>
                        <form method="POST" id="deleteForm" class="d-inline-block">
                            <button type="submit" name="delete_account" class="btn btn-danger">Delete Account</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>            
    </div>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10/dist/sweetalert2.all.min.js"></script>
    <script>
        document.getElementById('logoutBtn').addEventListener('click', function(e) {
            e.preventDefault();
            Swal.fire({
                icon: 'success',
                title: 'Logged Out',
                text: 'You have successfully logged out.',
                confirmButtonText: 'OK'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'login.php';
                }
            });
        });
        document.getElementById('deleteForm').addEventListener('submit', function(e) {
            e.preventDefault();
            Swal.fire({
                title: 'Are you sure?',
                text: 'Do you want to delete your account? This action cannot be undone.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'No, cancel!'
            }).then((result) => {
                if (result.isConfirmed) {
                        fetch('delete_account.php', {
                        method: 'POST',
                        body: new FormData(this)
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Account Deleted',
                                text: 'Your account has been successfully deleted.',
                                confirmButtonText: 'OK'
                            }).then(() => {
                                window.location.href = 'login.php';
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: data.message || 'An error occurred while deleting your account. Please try again later.',
                                confirmButtonText: 'OK'
                            });
                        }
                    })
                    .catch(error => {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'An unexpected error occurred. Please try again later.',
                            confirmButtonText: 'OK'
                        });
                    });
                }
            });
        });
        </script>
    </body>
</html>
