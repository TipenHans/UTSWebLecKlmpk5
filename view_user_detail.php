<?php
session_start();
require 'db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

if (isset($_GET['user_id']) && !empty($_GET['user_id'])) {
    $user_id = $_GET['user_id'];

    $admin_id = $_SESSION['user_id'];
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :admin_id");
    $stmt->execute(['admin_id' => $admin_id]);
    $admin = $stmt->fetch();

    $stmt = $pdo->prepare("SELECT full_name, email, phone_number, role, profile_picture FROM users WHERE id = :user_id");
    $stmt->execute(['user_id' => $user_id]);
    $user = $stmt->fetch();

    if (!$user) {
        $_SESSION['error'] = "User not found.";
        header('Location: view_user.php');
        exit;
    }
} else {
    header('Location: view_user.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>View User Detail</title>
        <link rel="stylesheet" href="style.css">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    </head>
    <body>
    <div class="header">
        <h2>Event Management</h2>
        <div class="user-profile">
            <div class="me-3">
                <a href="admin_dashboard.php"><button class="btn-view">Back to Dashboard</button></a>
            </div>  
            <img src="uploads/<?php echo htmlspecialchars($admin['profile_picture'] ?? 'default_profile.png'); ?>" alt="Profile Picture" width="50" height="50">
            <a href="view_profile.php"><?php echo htmlspecialchars($admin['full_name']); ?></a>
        </div>
    </div>
    <div class="container text-center">
        <div class="d-flex align-items-center justify-content-center mt-3 mb-3">
            <div class="card">
                <div class="card-header">
                    <h1>Profile</h1>
                </div>
                <div class="card-body text-center">
                    <img src="uploads/<?php echo htmlspecialchars($user['profile_picture'] ?? 'default_profile.png'); ?>" alt="Profile Picture" 
                        class="profile-picture img-fluid rounded-circle mb-3" 
                        style="width: 250px; height: 250px; object-fit: cover;">
                    <table class="table table-bordered table-hover">
                        <tbody>
                            <tr>
                                <th scope="row">Full Name</th>
                                <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                            </tr>
                            <tr>
                                <th scope="row">Email</th>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                            </tr>
                            <tr>
                                <th scope="row">Phone Number</th>
                                <td><?php echo htmlspecialchars($user['phone_number']); ?></td>
                            </tr>
                            <tr>
                                <th scope="row">Role</th>
                                <td><?php echo htmlspecialchars(ucfirst($user['role'])); ?></td>
                            </tr>
                        </tbody>
                    </table>
                    <div>
                    <a href="view_attended_event.php?user_id=<?php echo $user_id; ?>" class="btn btn-primary" class="d-flex justify-content-center">Event Information</a>
                    </div>
                    <div>
                        <a href="view_user.php" class="btn btn-secondary mt-3" class="d-flex justify-content-start">Back to Users</a>
                        <a href="javascript:void(0);" class="btn btn-danger mt-3" id="deleteButton">Delete User</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    document.getElementById('deleteButton').addEventListener('click', function () {
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = "delete_user.php?user_id=<?php echo $user_id; ?>";
            }
        })
    });
</script>

    </body>
</html>
