<?php
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'];
    $event_id = $_POST['event_id'];

    $stmt = $pdo->prepare("DELETE FROM participants WHERE user_id = :user_id AND event_id = :event_id");
    $stmt->execute(['user_id' => $user_id, 'event_id' => $event_id]);

    if ($stmt->rowCount()) {
        $stmt = $pdo->prepare("UPDATE events SET current_participants = current_participants - 1 WHERE event_id = :event_id");
        $stmt->execute(['event_id' => $event_id]);

        echo 'success';
    } else {
        echo 'error';
    }
}
