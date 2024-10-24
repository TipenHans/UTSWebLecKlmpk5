<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'User is not logged in.']);
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    $pdo->beginTransaction();
    
    $stmt = $pdo->prepare("SELECT event_id FROM participants WHERE user_id = :user_id");
    $stmt->execute(['user_id' => $user_id]);
    $registeredEvents = $stmt->fetchAll();

    $stmt = $pdo->prepare("DELETE FROM participants WHERE user_id = :user_id");
    $stmt->execute(['user_id' => $user_id]);

    foreach ($registeredEvents as $event) {
        $stmt = $pdo->prepare("UPDATE events SET current_participants = current_participants - 1 WHERE event_id = :event_id");
        $stmt->execute(['event_id' => $event['event_id']]);
    }
    
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = :user_id");
    $stmt->execute(['user_id' => $user_id]);

    if ($stmt->rowCount() > 0) {
        $pdo->commit();

        session_unset();
        session_destroy();
        
        echo json_encode(['status' => 'success', 'message' => 'Account deleted successfully.']);
    } else {
        $pdo->rollBack();
        echo json_encode(['status' => 'error', 'message' => 'Account could not be deleted.']);
    }

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['status' => 'error', 'message' => 'An error occurred: ' . $e->getMessage()]);
}

exit;
?>
