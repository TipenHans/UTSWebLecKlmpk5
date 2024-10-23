<?php
session_start();
require 'db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$admin_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = :admin_id");
$stmt->execute(['admin_id' => $admin_id]);
$admin = $stmt->fetch();

$stmt_users = $pdo->prepare("SELECT id, full_name, email, phone_number, role, profile_picture FROM users WHERE id != :admin_id");
$stmt_users->execute(['admin_id' => $admin_id]);
$users = $stmt_users->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Admin Dashboard - View Users</title>
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
    <div class="container mt-3">
        <div class="card mb-3">
            <div class="card-header search-sort-container">
                <input type="text" id="searchUser" onkeyup="searchUsers()" placeholder="Search user by name...">
            </div>
            <div class="card-body">
                <div class="row g-4">
                    <?php if (empty($users)) { ?>
                        <div class="col-md-1">
                            <p>No users available.</p>
                        </div>
                    <?php } else { ?>
                        <?php foreach ($users as $user) { ?>
                            <div class="col-md-3">
                                <a href="view_user_detail.php?user_id=<?php echo urlencode($user['id']); ?>" class="text-decoration-none text-dark">    
                                    <div class="event-card" style="height: 20rem; overflow:hidden;"  data-user-name="<?php echo strtolower($user['full_name']); ?>">
                                        <div class="card-head mb-5" >
                                            <img src="uploads/<?php echo htmlspecialchars($user['profile_picture'] ?? 'default_profile.png'); ?>" class="card-img-top" style="object-fit: cover;" alt="User Picture" id="event-img">
                                        </div>
                                        <div class="card-body">
                                            <h5 class="card-title"><?php echo htmlspecialchars($user['full_name']); ?></h5>
                                            <p class="card-text "><b>UID:</b> <?php echo htmlspecialchars($user['id']); ?></p>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        <?php } ?>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
    <div class="add-event">
        <a href="registeradmin.php" class="tombol">
            <img src="asset/add.png" alt="Add User" id="addButton" class="img-fluid ">
        </a>
        <p id="addTooltip">Add New User</p>
    </div>
    <script>
        document.getElementById('addButton').addEventListener('mouseover', function () {
            document.getElementById('addTooltip').style.visibility = 'visible';
        });
        document.getElementById('addButton').addEventListener('mouseout', function () {
            document.getElementById('addTooltip').style.visibility = 'hidden';
        });
        function searchUsers() {
            const searchInput = document.getElementById('searchUser').value.toLowerCase();
            const events = document.querySelectorAll('.event-card');

            events.forEach(event => {
                const eventName = event.getAttribute('data-user-name');
                if (eventName.includes(searchInput)) {
                    event.style.display = 'block';
                } else {
                    event.style.display = 'none';
                }
            });
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    </body>
</html>
