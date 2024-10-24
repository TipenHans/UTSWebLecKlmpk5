<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = :user_id");
$stmt->execute(['user_id' => $user_id]);
$user = $stmt->fetch();

$stmt = $pdo->prepare("
    SELECT events.event_id, events.event_name, events.banner, events.start_date, events.status, events.current_participants, events.max_participants
    FROM participants
    JOIN events ON participants.event_id = events.event_id
    WHERE participants.user_id = :user_id AND events.start_date >= NOW()
");
$stmt->execute(['user_id' => $user_id]);
$registered_events = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Registered Events</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
</head>
<body>
    <div class="container">
        <div class="header d-flex justify-content-between align-items-center mb-4">
            <h2>My Ongoing Events</h2>
            <img src="uploads/<?php echo !empty($user['profile_picture']) ? $user['profile_picture'] : 'default_profile.png'; ?>" alt="Profile" class="rounded-circle" width="50" height="50">
        </div>
        <div class="sort-section">
            <div class="filter-section">
                <label for="statusFilter">Filter by:</label>
                <select id="statusFilter" class="form-control" onchange="applyFilterSort()">
                    <option value="all">All Events</option>
                    <option value="available">Available Events</option>
                    <option value="full">Full Events</option>
                </select>
            </div>
            <div class="sort-section">
                <label for="sortOptions">Sort by:</label>
                <select id="sortOptions" class="form-control" onchange="applyFilterSort()">
                    <option value="name_asc">Name A-Z</option>
                    <option value="name_desc">Name Z-A</option>
                    <option value="date_asc">Starting Soonest</option>
                    <option value="date_desc">Starting Latest</option>
                </select>
            </div>
        </div>
        <?php if (empty($registered_events)) { ?>
            <p>You have not registered for any upcoming events yet.</p>
        <?php } else { ?>
            <div id="noEventsMessage" class="no-events-message">No event matches your filter.</div>
            <div class="event-grid" id="eventGrid">
                <?php foreach ($registered_events as $event) { ?>
                    <div class="event-card" data-participants="<?php echo $event['current_participants']; ?>" data-status="<?php echo ($event['current_participants'] < $event['max_participants']) ? 'available' : 'full'; ?>">
                        <img src="uploads/<?php echo $event['banner']; ?>" alt="Event Banner" id="event-img">
                        <h4><?php echo $event['event_name']; ?></h4>
                        <p><strong>Start Date:</strong> <?php echo $event['start_date']; ?></p>
                        <p><strong>Status:</strong> <?php echo ucfirst($event['status']); ?></p>
                        <a href="event_details_user.php?event_id=<?php echo $event['event_id']; ?>" class="btn btn-primary">View Event Details</a>
                    </div>
                <?php } ?>
            </div>
        <?php } ?>

        <a href="user_dashboard.php" class="btn btn-primary mt-5">Back to Dashboard</a>

        <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
        <script>
            function applyFilterSort() {
                const sortOption = document.getElementById('sortOptions').value;
                const statusFilter = document.getElementById('statusFilter').value;
                const eventGrid = document.getElementById('eventGrid');
                const events = Array.from(eventGrid.getElementsByClassName('event-card'));

                let visibleCount = 0;

                events.forEach(event => {
                    const status = event.getAttribute('data-status');
                    if (statusFilter === 'all' || status === statusFilter) {
                        event.style.display = 'block';
                        visibleCount++;
                    } else {
                        event.style.display = 'none';
                    }
                });

                events.sort((a, b) => {
                    const nameA = a.querySelector('h4').innerText.toLowerCase();
                    const nameB = b.querySelector('h4').innerText.toLowerCase();
                    const dateA = new Date(a.querySelector('p').innerText.split(": ")[1]);
                    const dateB = new Date(b.querySelector('p').innerText.split(": ")[1]);

                    switch (sortOption) {
                        case 'name_asc':
                            return nameA.localeCompare(nameB);
                        case 'name_desc':
                            return nameB.localeCompare(nameA);
                        case 'date_asc':
                            return dateA - dateB;
                        case 'date_desc':
                            return dateB - dateA;
                        default:
                            return 0;
                    }
                });

                events.forEach(event => eventGrid.appendChild(event));

                const noEventsMessage = document.getElementById('noEventsMessage');
                if (visibleCount === 0) {
                    noEventsMessage.style.display = 'block';
                } else {
                    noEventsMessage.style.display = 'none';
                }
            }
        </script>
    </div>
</body>
</html>
