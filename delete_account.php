<?php
session_start();
require 'db.php';

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'User is not logged in.']);
    exit;
}

// Get the user ID from the session
$user_id = $_SESSION['user_id'];

try {
    // Start transaction
    $pdo->beginTransaction();
    
    // Fetch the events the user has registered for
    $stmt = $pdo->prepare("SELECT event_id FROM participants WHERE user_id = :user_id");
    $stmt->execute(['user_id' => $user_id]);
    $registeredEvents = $stmt->fetchAll();

    // Remove the user's event registrations
    $stmt = $pdo->prepare("DELETE FROM participants WHERE user_id = :user_id");
    $stmt->execute(['user_id' => $user_id]);

    // Decrease the participant count for each event the user was registered for
    foreach ($registeredEvents as $event) {
        $stmt = $pdo->prepare("UPDATE events SET current_participants = current_participants - 1 WHERE event_id = :event_id");
        $stmt->execute(['event_id' => $event['event_id']]);
    }
    
    // Delete the user account
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = :user_id");
    $stmt->execute(['user_id' => $user_id]);

    // Check if the account was deleted
    if ($stmt->rowCount() > 0) {
        // Commit transaction
        $pdo->commit();

        // Destroy session and ensure no further login
        session_unset();
        session_destroy();
        
        // Send success response
        echo json_encode(['status' => 'success', 'message' => 'Account deleted successfully.']);
    } else {
        // If no rows were affected, rollback and send error response
        $pdo->rollBack();
        echo json_encode(['status' => 'error', 'message' => 'Account could not be deleted.']);
    }

} catch (Exception $e) {
    // Rollback transaction in case of error
    $pdo->rollBack();
    // Send error response
    echo json_encode(['status' => 'error', 'message' => 'An error occurred: ' . $e->getMessage()]);
}

exit;
?>
