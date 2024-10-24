<?php
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (!isset($_GET['user_id']) || empty($_GET['user_id'])) {
        header("Location: view_user.php");
        exit;
    }

    $user_id = $_GET['user_id'];

    try {
 
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("SELECT event_id FROM participants WHERE user_id = :user_id");
        $stmt->execute(['user_id' => $user_id]);
        $events = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $stmt = $pdo->prepare("DELETE FROM participants WHERE user_id = :user_id");
        $stmt->execute(['user_id' => $user_id]);

        foreach ($events as $event_id) {
            $stmt = $pdo->prepare("UPDATE events SET current_participants = current_participants - 1 WHERE event_id = :event_id AND current_participants > 0");
            $stmt->execute(['event_id' => $event_id]);
        }

        $stmt = $pdo->prepare("DELETE FROM users WHERE id = :user_id");
        $stmt->execute(['user_id' => $user_id]);

        $pdo->commit();
        
        header("Location: view_user.php");
        exit;

    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error'] = "An error occurred while trying to delete the user: " . $e->getMessage();
        header("Location: view_user.php");
        exit;
    }
}
