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
