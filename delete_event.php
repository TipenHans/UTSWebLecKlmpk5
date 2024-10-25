<?php
session_start();
require 'db.php';

// Ensure that only admins can access this page
if ($_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// Check if the request is an AJAX POST request to delete the event
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['event_id'])) {
    $event_id = $_POST['event_id'];

    // Start a transaction to ensure atomicity
    $pdo->beginTransaction();
    try {
        // Delete all participants registered for this event
        $stmt = $pdo->prepare("DELETE FROM participants WHERE event_id = :event_id");
        $stmt->execute(['event_id' => $event_id]);

        // Delete the event itself
        $stmt = $pdo->prepare("DELETE FROM events WHERE event_id = :event_id");
        $stmt->execute(['event_id' => $event_id]);

        // Commit the transaction
        $pdo->commit();

        // Return success message
        echo json_encode(['status' => 'success', 'message' => 'Event and all associated participants deleted successfully']);
    } catch (Exception $e) {
        // Rollback the transaction if something went wrong
        $pdo->rollBack();
        echo json_encode(['status' => 'error', 'message' => 'Failed to delete the event: ' . $e->getMessage()]);
    }
} else {
    // If it's not an AJAX POST request, deny access
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['status' => 'error', 'message' => 'Invalid request.']);
}
