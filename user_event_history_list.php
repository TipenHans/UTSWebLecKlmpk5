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

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event History</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
</head>
<body>
    <div class="container">
        <div class="header d-flex justify-content-between align-items-center mb-4">
            <h2>Your Event History</h2>
            <img src="uploads/<?php echo !empty($user['profile_picture']) ? $user['profile_picture'] : 'default_profile.png'; ?>" alt="Profile" class="rounded-circle" width="50" height="50">
        </div>
        <div class="search-section">
            <label for="searchInput">Search by Name:</label>
            <input type="text" id="searchInput" onkeyup="applySortFilter()" placeholder="Search events...">
        </div>
        <div class="sort-section">
            <label for="sortOptions">Sort by:</label>
            <select id="sortOptions" onchange="applySortFilter()">
                <option value="name_asc">Name A-Z</option>
                <option value="name_desc">Name Z-A</option>
                <option value="date_asc">Starting Soonest</option>
                <option value="date_desc">Starting Latest</option>
            </select>
                </div>
                <div class="filter-section">
            <label for="statusFilter">Filter by:</label>
            <select id="statusFilter" onchange="applySortFilter()">
                <option value="all">All Events</option>
                <option value="open">Open</option>
                <option value="closed">Closed</option>
                <option value="cancelled">Cancelled</option>
            </select>
        </div>
        <?php if (empty($event_history)) { ?>
            <p>You have not registered for any events yet.</p>
        <?php } else { ?>
        <div class="event-grid" id="eventGrid">
            <?php foreach ($event_history as $event) { ?>
            <div class="event-card" data-name="<?php echo strtolower($event['event_name']); ?>" data-start-date="<?php echo $event['start_date']; ?>" data-status="<?php echo $event['status']; ?>">
                <img src="uploads/<?php echo $event['banner']; ?>" alt="Event Banner">
                <h4><?php echo $event['event_name']; ?></h4>
                <p><strong>Start Date:</strong> <?php echo $event['start_date']; ?></p>
                <p><strong>Status:</strong> <?php echo ucfirst($event['status']); ?></p>
                <a href="event_details_user.php?event_id=<?php echo $event['event_id']; ?>" class="btn btn-primary">Event Details</a>
            </div>
            <?php } ?>
        </div>
        <?php } ?>
        <a href="user_dashboard.php" class="btn btn-primary mt-5">Back to dashboard</a>
    </div>
</body>
</html>
