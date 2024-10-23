<?php
    session_start();
    require 'db.php';

    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }

    $user_id = $_SESSION['user_id'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :user_id");
    $stmt->execute(['user_id' => $user_id]);
    $user = $stmt->fetch();

    $stmt = $pdo->query("SELECT * FROM events WHERE status = 'open' ORDER BY start_date ASC");
    $events = $stmt->fetchAll();
?>
