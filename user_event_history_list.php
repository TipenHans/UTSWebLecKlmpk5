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

$stmt = $pdo->prepare("
    SELECT events.event_id, events.event_name, events.banner, events.start_date, events.status
    FROM participants
    JOIN events ON participants.event_id = events.event_id
    WHERE participants.user_id = :user_id
");
$stmt->execute(['user_id' => $user_id]);
$event_history = $stmt->fetchAll();
?>
