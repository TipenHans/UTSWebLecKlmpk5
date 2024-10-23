<?php
session_start();
require 'db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

if (isset($_GET['user_id']) && !empty($_GET['user_id'])) {
    $user_id = $_GET['user_id'];
    $event_id = $_GET['event_id'];

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
    <div class="container text-center">
        <div class="vh-100 d-flex align-items-center justify-content-center">
            <div>
                <img src="uploads/<?php echo htmlspecialchars($user['profile_picture'] ?? 'default_profile.png'); ?>" alt="Profile Picture" class="profile-picture" style="max-width: 250px; max-height: 250px; border-radius: 50%;">
                <h2><?php echo htmlspecialchars($user['full_name']); ?></h2>
                <div class="user-info"><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></div>
                <div class="user-info"><strong>Phone Number:</strong> <?php echo htmlspecialchars($user['phone_number']); ?></div>
                <div class="user-info"><strong>Role:</strong> <?php echo htmlspecialchars(ucfirst($user['role'])); ?></div>
                <a href="participants.php?event_id=<?php echo $event_id?>" class="btn btn-secondary back-button">Back to List</a>
            </div>
        </div>
    </div>
</body>
</html>
