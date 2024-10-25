<?php
session_start();
require 'db.php';

if ($_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['event_id'])) {
    $event_id = $_POST['event_id'];

    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare("DELETE FROM participants WHERE event_id = :event_id");
        $stmt->execute(['event_id' => $event_id]);

        $stmt = $pdo->prepare("DELETE FROM events WHERE event_id = :event_id");
        $stmt->execute(['event_id' => $event_id]);

        $pdo->commit();

        echo json_encode(['status' => 'success', 'message' => 'Event and all associated participants deleted successfully']);
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['status' => 'error', 'message' => 'Failed to delete the event: ' . $e->getMessage()]);
    }
} else {
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['status' => 'error', 'message' => 'Invalid request.']);
}
